<?php

namespace Lns\Gpn\Controller\Api\OrderStatus\Action;

use Lns\Gpn\Lib\Entity\Db\Orderitemdetails;
use Lns\Gpn\Lib\Entity\Db\Orderitems;

class Update extends \Lns\Sb\Controller\Controller{

    protected $_orderstatus;
    protected $_orders;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_orderstatus = $OrderStatus;
        $this->_orders = $Orders;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_audittrail = $AuditTrail;
    }

    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

            $this->jsonData['error'] = 1;

            $format = date('Y-m-d H:i:s');
            /* $date_to_pickup = date("Y-m-d H:i:s", strtotime("$format + 10 hours")); */
            $date_to_pickup = date("Y-m-d H:i:s", strtotime("$format + 10 hours"));

           /*  $userId = $this->getParam('userId'); */
            $userId = 18;
            $orderId = $this->getParam('orderId');
            $orderStatus = $this->getParam('orderStatus');

            $update = $this->_orders->getByColumn(['id'=> $orderId], 1);
            if($update){
                $update->setData('order_status', $orderStatus);
                $save = $update->__save();

                if($save){
                    $this->_orderstatus->updateStatus($orderId, $orderStatus);

                    switch($orderStatus){
                        case 1: /* Pending */

                        break;
                        case 2: /* Approved */

                            $update->setData('date_to_pickup',$date_to_pickup);
                            $update->setData('approved_by', $userId);
                            $update->__save();

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
                                $action = "A new order is ready for processing.";
                                $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_processing');
                            }

                            /* $update->setData('approved_by', $userId);
                            $update->__save();
                            
                            $entity = $this->_orders->getbyColumn(['id'=>$orderId], 1); 
                            if($entity){
                                $entity->setData('date_to_pickup',$date_to_pickup);
                                $entity->__save();
                            } */ /* ilagay ko to sa taas ah. kasi ung push notif nawawala ung date_to_pickup */

                            $this->jsonData['message'] = 'Order Approved';
                        break;
                        case 3: /* Pick-up */
                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready to pick up.";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 17, null, $message, $update->getData('user_id'));

                            $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                            if ($to) {
                                $title = "Order Pickup";
                                $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " is ready to pick up.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_pickup";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_pickup', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                            $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready to pick up.";
                            $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_pickup');
                            $this->jsonData['message'] = 'Order Ready to Pickup';
                        break;
                        case 4: /* Completed */
                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been completed.";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 18, null, $message, $update->getData('user_id'));

                            $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                            if ($to) {
                                $title = "Order Completed";
                                $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " has been completed.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_completed";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_completed', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                            $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been completed.";
                            $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_completed');
                            $this->jsonData['message'] = 'Order Completed';
                        break;
                    }
                    $this->jsonData['error'] = 0;
                }
            }else{
                $this->jsonData['message'] = 'No Record Found';
            }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>