<?php

namespace Lns\Gpn\Controller\Api\Orders\Action;

class Markasready extends \Lns\Sb\Controller\Controller{

    protected $_orders;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_upload;
    protected $_dateTime;
    protected $_orderstatus;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_upload = $Upload;
        $this->_dateTime = $DateTime;
        $this->_orderstatus = $OrderStatus;
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

    /*         $userId = $this->getParam('userId'); */

            $update = $this->_orders->getByColumn(['id' => $this->getParam('orderId')], 1);
            if($update){
                $photo = $this->upload($userId, 'warehouseman');
                $update->setData('prepared_by', $userId);
                $update->setData('prepared_by_path', $photo);
                $update->setData('prepared_by_date', $this->_dateTime->getTimestamp());
                $update->setData('order_status', 3);
                $save = $update->__save();

                $this->_orderstatus->updateStatus($this->getParam('orderId'), 3);

                if($save){
                    $pushData = $update->getData();
                    $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                    if ($customer) {
                        $pushData['customer_name'] = $customer;
                    }
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready to pick up";
                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 17, null, $message, $update->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                    if ($to) {
                        $title = "Order Pickup";
                        $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " is ready to pick up";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_pickup";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_pickup', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready to pick up";
                    $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_pickup');

                    $inspectors2 = $this->_users->getUsersByRole(5);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready to pick up";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 15, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Pickup";
                                $message = "" . 'Order no. ' . $update->getData('transaction_id') . " is ready to pick up";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_pickup";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_pickup', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }
                    /* send notif to manager */
                    $managers = $this->_users->getUsersByRole(4);
                    if ($managers) {
                        $sender = $this->_userProfile->getFullNameById($userId);
                        $pushData = $update->getData();
                        foreach ($managers as $manager) {
                            $message = "" . 'Order no. ' . $update->getData('transaction_id') . " is ready to pick up";
                            $this->_notification->setNotification($update->getData('id'), $userId, 15, null, $message, $manager->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                            $this->_cfs->save($manager->getData('id'), $sender, $message, 'order_pickup', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                    /* send notif to manager */

                    /* SENDING CREDENTIALS TO EMAIL AFTER REGISTRATION */
                    $this->_orders->sendEmailorderstatus($update->getData('user_id'), $this->_siteConfig, 'pickup_order', $this->getParam('orderId'));

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Order is ready for pickup.';
                }else{
                    $this->jsonData['message'] = 'Failed to update order status. Please try again.';
                }
            }else{
                $this->jsonData['message'] = 'No record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId, $type)
    {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads' . DS . 'signature';

        if ($type) {
            $path .= DS . $type;
        }

        $path .= DS . $userId;

        $file = $this->getFile('photo');

        $fileName = null;
        if ($file) {
            $_file = $this->_upload->setFile($file)
                ->setPath($path)
                ->setAcceptedFile(['ico', 'jpg', 'png', 'jpeg'])
                ->save();

            if ($_file['error'] == 0) {
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }
        }
        return $fileName;
    }
}
?>