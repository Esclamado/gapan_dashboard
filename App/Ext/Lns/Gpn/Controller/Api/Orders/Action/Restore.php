<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

class Restore extends \Lns\Sb\Controller\Controller {

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
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken
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
            $userId = $payload['payload']['jti'];
        /* $userId = $this->getParam('userId'); */

            $ordernumber = $this->_orders->orderNumber();
            $transaction_id = date('Y-m') . "-" . sprintf("%05d", $ordernumber+1);

            $order_id = $this->getParam('orderId');
            $orders = $this->_orders->getByColumn(['id'=> $order_id], 1);
            if($orders){
                $orders->setData('decline_resolved', 1);
                $orders->__save();

                $orderItems = $this->_orderitems->getByColumn(['order_id'=> $order_id], 0);
                $total_price = 0;
                foreach ($orderItems as $orderItem) {
                    $eggprices = $this->_price->getByColumn(['type_id'=>$orderItem->getData('type_id')], 0);
                    if($eggprices){
                        $pieces = 0;
                        foreach ($eggprices as $eggprice) {
                            $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderItem->getData('id')], 0);
                            foreach ($orderitemdetails as $orderitemdetail) {
                                switch($orderitemdetail->getData('type_id')){
                                    case 1:
                                        $pieces = 360 * (int)$orderitemdetail->getData('qty');
                                    break;
                                    case 2:
                                        $pieces = 30 * (int)$orderitemdetail->getData('qty');
                                    break;
                                    case 3:
                                        $pieces = (int)$orderitemdetail->getData('qty');
                                    break;
                                }
                                $total_price += $pieces * (float) $eggprice->getData('price');
                            }
                        }
                    }
                }

                $save = $this->_orders;
                $save->setData('user_id', $userId);
                $save->setData('transaction_id', $transaction_id);
                $save->setData('order_status', 1); /* 1 = Pending */
                $save->setData('payment_status', 0); /* 0 = pag wala pa */
                $save->setData('mode_of_payment', 1);
                $save->setData('total_price', $total_price);
                $save->setData('discount', 0);
                $save->setData('note', $this->getParam('note'));
                $save->setData('feedback', null);
                $save->setData('date_to_pickup', null);
                $orderId = $save->__save();

                if ($orderId) {

                    $orderItems = $this->_orderitems->getByColumn(['order_id' => $order_id], 0);
                    foreach ($orderItems as $orderItem) {
                        $save = $this->_orderitems;
                        $save->setData('order_id', $orderId);
                        $save->setData('type_id', $orderItem->getData('type_id'));
                        $orderitemsId = $save->__save();

                        $price = $this->_price->getByColumn(['type_id' => $orderItem->getData('type_id')], 1);
                        $price = $price ? $price->getData('price') : 0;

                        $orderItemDetails = $this->_orderitemdetails->getByColumn(['order_item_id'=> $orderItem->getData('id')], 0);
                        foreach ($orderItemDetails as $orderItemDetail) {

                            $entity = $this->_orderitemdetails;
                            $entity->setData('order_item_id', $orderitemsId);
                            $entity->setData('type_id', $orderItemDetail->getData('type_id'));
                            $entity->setData('qty', $orderItemDetail->getData('qty'));
                            $entity->setData('price', $price);
                            $entity->__save();
                        }
                    }

                    $entity = $this->_payment;
                    $entity->setData('order_id', $orderId);
                    $entity->setData('payment', $total_price);
                    $entity->setData('balance', 0);
                    $entity->setData('reason', null);
                    $entity->setData('due_date', null);
                    /*  $entity->setData('approved_by', $approved_by);
                        $entity->setData('approved_path', $approved_path);
                        $entity->setData('approved_date', $approved_date); */
                    $entity->__save();

                    $this->_orderstatus->updateStatus($orderId, 1);

                    $notif = $this->_orders->getByColumn(['id' => $orderId], 1);
                    
                    if ($notif) {
                        $inspectors2 = $this->_users->getUsersByRole(5);
                        if ($inspectors2) {
                            foreach ($inspectors2 as $inspector2) {
                                $pushData = $notif->getData();
                                $sender = $this->_userProfile->getFullNameById($userId);
                                $message = "A Customer has placed an order.";
                                $this->_notification->setNotification((int) $notif->getData('id'), $userId, 15, null, $message, $inspector2->getData('id'));

                                $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                                if ($to) {
                                    $title = "New Order";
                                    $message = "A Customer has placed an order.";
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "new_order";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'new_order', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                            }
                        }
                    }
                    /* DISPLAY SAVED DATA : START */
                    $orders = $this->_orders->getByColumn(['id' => $orderId], 1);
                    if ($orders) {
                        $this->jsonData['data'] = $orders->getData();

                        $payment = $this->_payment->getByColumn(['order_id' => $orders->getData('id')], 1);
                        if ($payment) {
                            $this->jsonData['data']['payment'] = $payment->getData();
                        }

                        $orderstatus = $this->_orderstatus->getTrackingStatus($orders->getData('id'));
                        if ($orderstatus) {
                            $this->jsonData['data']['status'] = $orderstatus;
                        }

                        $orderitems = $this->_orderitems->getByColumn(['order_id' => $orders->getData('id')], 0);
                        if ($orderitems) {
                            $x = 0;
                            $total_items = 0;
                            foreach ($orderitems as $orderitem) {
                                $this->jsonData['data']['order_items'][] = $orderitem->getData();

                                $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                                if ($orderitemdetails) {
                                    $y = 0;
                                    $pieces = 0;
                                    foreach ($orderitemdetails as $orderitemdetail) {
                                        $this->jsonData['data']['order_items'][$x]['order_item_details'][$y] = $orderitemdetail->getData();

                                        switch ($orderitemdetail->getData('type_id')) {
                                            case 1:
                                                $pieces = 360 * (int) $orderitemdetail->getData('qty');
                                                break;
                                            case 2:
                                                $pieces = 30 * (int) $orderitemdetail->getData('qty');
                                                break;
                                            default:
                                                $pieces = (int) $orderitemdetail->getData('qty');
                                                break;
                                        }
                                        $total_items += $pieces;

                                        $eggcarttypes = $this->_eggcarttype->getByColumn(['id' => $orderitemdetail->getData('type_id')], 0);
                                        if ($eggcarttypes) {
                                            foreach ($eggcarttypes as $eggcarttype) {
                                                $this->jsonData['data']['order_items'][$x]['order_item_details'][$y]['egg_cart_type'] = $eggcarttype->getData();
                                            }
                                        }
                                        $y++;
                                    }
                                    $this->jsonData['data']['total_items'] = $total_items;
                                }
                                $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                                if ($eggtypes) {
                                    foreach ($eggtypes as $eggtype) {
                                        $this->jsonData['data']['order_items'][$x]['egg_type'] = $eggtype->getData();
                                        $eggprices = $this->_price->getByColumn(['type_id' => $eggtype->getData('id')], 0);
                                        if ($eggprices) {
                                            foreach ($eggprices as $eggprice) {
                                                $this->jsonData['data']['order_items'][$x]['egg_type']['egg_price'] = $eggprice->getData();
                                            }
                                        }
                                    }
                                }
                                $x++;
                            }
                        }
                    }
                /* DISPLAY SAVED DATA : END */
                    $this->jsonData['message'] = 'Save';
                }
            $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'Record not found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
