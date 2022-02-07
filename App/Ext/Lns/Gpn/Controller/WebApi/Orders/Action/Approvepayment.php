<?php

namespace Lns\Gpn\Controller\WebApi\Orders\Action;

use Lns\Gpn\Lib\Entity\Db\Orderitemdetails;
use Lns\Gpn\Lib\Entity\Db\Orderitems;

class Approvepayment extends \Lns\Sb\Controller\Controller{

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

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $orderId = $this->getParam('id');

            $entity = $this->_orders->getByColumn(['id'=> $orderId], 1);

            if($entity){
                $entity->setData('balance_credit_approved', 1);
                $entity->setData('balance_credit_approved_by', $userId);
                $format = date('Y-m-d H:i:s');
                $date = date("Y-m-d H:i:s", strtotime($format));
                $entity->setData('balance_credit_approved_date', $date);
                $save = $entity->__save();
                if($save) {
                    $pushData = $entity->getData();
                    $customer = $this->_userProfile->getFullNameById($entity->getData('user_id'));
                    if ($customer) {
                        $pushData['customer_name'] = $customer;
                    }
                    $sales = $this->_users->getUsersByRole(5);
                    if ($sales) {
                        foreach ($sales as $sale) {
                            
                            $sender = $this->_userProfile->getFullNameById($userId);

                            $payment_label = $entity->getData('mode_of_payment') == 2 ? 'Credit' : 'Balance';
                            $message = $payment_label . " request has been approved for Order no. " . $entity->getData('transaction_id');

                            $this->_notification->setNotification((int) $entity->getData('id'), $userId, 16, null, $message, $sale->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($sale->getData('id'));

                            if ($to) {
                                $title = $payment_label . " request approved";
                                $message = $payment_label . " request has been approved for Order no. " . $entity->getData('transaction_id');
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "mop_approved";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($sale->getData('id'), $sender, $message, 'mop_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }

                    $sender = $this->_userProfile->getFullNameById($userId);
                    $payment_label = $entity->getData('mode_of_payment') == 2 ? 'Credit' : 'Balance';
                    $message = $payment_label . " request has been approved for Order no. " . $entity->getData('transaction_id');
                    
                    $this->_notification->setNotification((int) $entity->getData('id'), $userId, 16, null, $message, $entity->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($entity->getData('user_id'));
                    if ($to) {
                        $title = $payment_label . " request approved";
                        /* $message = "Your " . 'Order no. ' . $entity->getData('transaction_id') . " has been approved."; */
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "payment_approved";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($entity->getData('user_id'), $sender, $message, 'order_approved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $action = $payment_label . " request has been approved for <strong>Order no. " . $entity->getData('transaction_id') . '</strong>';
                    $this->_audittrail->saveAudittrail($userId, $sender, $action, 'payment_approved');
                    

                    $label = $entity->getData('mode_of_payment') == 2 ? 'Credit' : 'Balance';
                    $this->jsonData['message'] = $label . ' request has been approved';
                    $this->jsonData['error'] = 0;
                }
            }else{
                $this->jsonData['message'] = 'No Record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>