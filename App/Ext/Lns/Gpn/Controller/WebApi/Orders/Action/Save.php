<?php
namespace Lns\Gpn\Controller\WebApi\Orders\Action;

class Save extends \Lns\Sb\Controller\Controller {

    /* customer registration */
    protected $_address;
    protected $_contact;
    protected $_password;
    protected $_audittrail;

    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_payment;
    protected $_cart;
    protected $_cartdetails;
    protected $_price;
    protected $_eggcarttype;
    protected $_eggtype;
    protected $_orderstatus;
    protected $_users;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\Cart $Cart,
        \Lns\Gpn\Lib\Entity\Db\CartDetails $CartDetails,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Address $Address,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_payment = $Payment;
        $this->_cart = $Cart;
        $this->_cartdetails = $CartDetails;
        $this->_price = $Price;
        $this->_eggcarttype = $EggCartType;
        $this->_eggtype = $Eggtype;
        $this->_orderstatus = $OrderStatus;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_address = $Address;
        $this->_contact = $Contact;
        $this->_password = $Password;
        $this->_audittrail = $AuditTrail;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $staffId = $payload['payload']['jti'];
            $staffInfo = $this->_users->getByColumn(['id' => $staffId], 1);

            $customer = $this->getParam('customer');
            $paymentData = $this->getParam('payment');
            $cartData = $this->getParam('cart');

            if (!$customer['user_id']) {
                $userId = $this->registerUser($customer);
            } else {
                $userId = $customer['user_id'];
            }
            
            if ($userId) {
                $ordernumber = $this->_orders->orderNumber();
                $transaction_id = date('Y-m') . "-" . sprintf("%05d", $ordernumber+1);

                $payment_type = $paymentData['mode_of_payment'];
                $note = $customer['request'];
                $total_price = $paymentData['total_price'];
                $payment = $paymentData['payment'];
                $balance = $paymentData['balance'];
                $duedate = $paymentData['due_date'];
                $due_date = date("Y-m-d", strtotime($duedate));
                
                $save = $this->_orders;
                $save->setData('user_id', $userId);
                $save->setData('transaction_id', $transaction_id);

                $order_status = 2;
                if ($staffInfo->getData('user_role_id') == 5){
                    $order_status = $payment_type == 1 ? 2 : 1;
                }
                $save->setData('order_status', $order_status); /* 1 = Pending */

                $save->setData('payment_status', 0); /* 0 = pag wala pa */
                $save->setData('mode_of_payment', $payment_type);
                $save->setData('total_price', $total_price);
                $save->setData('discount', 0);
                $save->setData('note', $note);
                $save->setData('feedback', null);
                $save->setData('walk_in_created_by', $staffId);

                $format = date('Y-m-d H:i:s');
                $date_to_pickup = date("Y-m-d H:i:s", strtotime("$format + 10 hours"));

                if ($staffInfo->getData('user_role_id') == 5) { /* sales */
                    if ($payment_type == 1) {
                        $save->setData('date_to_pickup', $date_to_pickup);
                    }
                } else if ($staffInfo->getData('user_role_id') == 4) { /* manager */
                    $save->setData('date_to_pickup', $date_to_pickup);
                    if ($payment_type > 1) {
                        $save->setData('balance_credit_approved', 1);
                        $save->setData('balance_credit_approved_by', $staffId);
                        $save->setData('balance_credit_approved_date', date("Y-m-d H:i:s", strtotime($format)));
                    }
                }

                $orderId = $save->__save();

                if ($orderId) {
                    /* SENDING CREDENTIALS TO EMAIL AFTER REGISTRATION */
                    $this->_orders->sendEmailorderstatus($userId, $this->_siteConfig, 'checkout', $orderId);

                    $entity = $this->_payment;
                    $entity->setData('order_id', $orderId);
                    $entity->setData('payment', $payment); 
                    $entity->setData('balance', $balance); 
                    /* $entity->setData('reason', $reason); */
                    if($payment_type==2 || $payment_type==3){
                        $entity->setData('due_date', $due_date);
                    }else{
                        $entity->setData('due_date', null);
                    }
                    $entity->__save();

                    $this->_orderstatus->updateStatus($orderId, 1);

                    $update = $this->_orders->getByColumn(['id' => $orderId], 1);

                    if ($staffInfo->getData('user_role_id') == 5) { /* sales */
                        if ($payment_type == 1) {
                            $this->_orderstatus->updateStatus($orderId, 2);

                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been approved.";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 16, null, $message, $update->getData('user_id'));

                            $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                            if ($to) {
                                $title = "Order Approved";
                                $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " has been approved.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_approved";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                            $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been approved.";
                            $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_approved');

                            $warehousemans = $this->_users->getUsersByRole(10);
                            if($warehousemans){
                                foreach ($warehousemans as $warehouseman) {
                                    $pushData = $update->getData();
                                    $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                                    if ($customer) {
                                        $pushData['customer_name'] = $customer;
                                    }
                                    $sender = $this->_userProfile->getFullNameById($userId);
                                    $message = "A new order is ready for processing.";
                                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 16, null, $message, $warehouseman->getData('id'));

                                    $to = $this->_deviceToken->getDeviceTokenById($warehouseman->getData('id'));
                                    if ($to) {
                                        $title = "For Processing";
                                        $message = "A new order is ready for processing.";
                                        $content = "Content Here";
                                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                                        $pushAction = "order_approved";
                                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                        $this->_cfs->save($warehouseman->getData('id'), $sender, $message, 'order_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                    }
                                }
                            }
                        } else {
                            if($update) {
                                $managers = $this->_users->getUsersByRole(4);
                                if($managers) {
                                    foreach ($managers as $manager) {
                                        $pushData = $update->getData();
                                        $sender = $this->_userProfile->getFullNameById($userId);
                                        $message = "A Customer has placed an order";
                                        $this->_notification->setNotification((int) $update->getData('id'), $userId, 15, null, $message, $manager->getData('id'));
        
                                        $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                                        if ($to) {
                                            $title = "New Order";
                                            $message = "A Customer has placed an order";
                                            $content = "Content Here";
                                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                                            $pushAction = "new_order";
                                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);
        
                                            $this->_cfs->save($manager->getData('id'), $sender, $message, 'new_order', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                        }
                                    }
                                }
                            }
                        }
                    } else if ($staffInfo->getData('user_role_id') == 4) { /* manager */
                        $this->_orderstatus->updateStatus($orderId, 2);

                        $pushData = $update->getData();
                        $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                        if ($customer) {
                            $pushData['customer_name'] = $customer;
                        }
                        $sender = $this->_userProfile->getFullNameById($userId);
                        $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been approved.";
                        $this->_notification->setNotification((int) $update->getData('id'), $userId, 16, null, $message, $update->getData('user_id'));

                        $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                        if ($to) {
                            $title = "Order Approved";
                            $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " has been approved.";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "order_approved";
                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                            $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                        $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been approved.";
                        $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_approved');

                        $warehousemans = $this->_users->getUsersByRole(10);
                        if($warehousemans){
                            foreach ($warehousemans as $warehouseman) {
                                $pushData = $update->getData();
                                $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                                if ($customer) {
                                    $pushData['customer_name'] = $customer;
                                }
                                $sender = $this->_userProfile->getFullNameById($userId);
                                $message = "A new order is ready for processing.";
                                $this->_notification->setNotification((int) $update->getData('id'), $userId, 16, null, $message, $warehouseman->getData('id'));

                                $to = $this->_deviceToken->getDeviceTokenById($warehouseman->getData('id'));
                                if ($to) {
                                    $title = "For Processing";
                                    $message = "A new order is ready for processing.";
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "order_approved";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    $this->_cfs->save($warehouseman->getData('id'), $sender, $message, 'order_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                            }
                        }
                    }
                    
                    foreach($cartData as $cart) {
                        $save = $this->_orderitems;
                        $save->setData('order_id', $orderId);
                        $save->setData('type_id', $cart['id']); 
                        $orderitemsId = $save->__save();

                        $price = $this->_price->getByColumn(['type_id' => $cart['id']], 1);
                        $price = $price ? $price->getData('price') : 0;

                        if ($orderitemsId) {
                            foreach($cart['items'] as $cartdetail) {
                                if ($cartdetail['qty']) {
                                    $entity = $this->_orderitemdetails;
                                    $entity->setData('order_item_id', $orderitemsId);
                                    $entity->setData('type_id', $cartdetail['type_id']);
                                    $entity->setData('qty', $cartdetail['qty']);
                                    $entity->setData('price', $price);
                                    $orderitemdetailsId = $entity->__save();

                                    $this->jsonData['error'] = 0;
                                    $this->jsonData['message'] = 'Order has been placed';
                                }
                            }
                        }
                    }
                }
            } else {
                $this->jsonData['message'] = 'Failed';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function registerUser($customer) {
        $first_name = $customer['customer_name'];
        $last_name = '';
        $location = $customer['location'];
        $number = $customer['number'];

        $count = $this->_users->getUsercount();
        $pw = "CST";
        $password = $pw . sprintf("%05d", $count);
        $hashedPassword = $this->_password->setPassword($password)->getHash();

        $user = $this->_users;
        $user->setData('password', $hashedPassword);
        $user->setData('status', 1);
        $user->setData('user_role_id', 3);

        $userId = $user->__save();
        if ($userId) {
            $profile = $this->_userProfile;
            $profile->setData('user_id', $userId);
            $profile->setData('first_name', $first_name);
            $profile->setData('last_name', $last_name);
            $profile->setData('location', $location);
            $profileID = $profile->__save();
            if($profileID){
                $hasAddress = $this->_address;
                $hasAddress->setData('profile_id', $userId);
                $hasAddress->setData('address', $location);
                $hasAddressID = $hasAddress->__save();

                if($hasAddressID){
                    $hasContact = $this->_contact;
                    $hasContact->setData('profile_id', $userId);
                    $hasContact->setData('number', $number);
                    $hasContactID = $hasContact->__save();
                    return $userId;
                }
            }
        }
    }
}
?>