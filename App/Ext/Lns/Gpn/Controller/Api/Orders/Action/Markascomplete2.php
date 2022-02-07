<?php

namespace Lns\Gpn\Controller\Api\Orders\Action;

class Markascomplete2 extends \Lns\Sb\Controller\Controller{

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
    protected $_outflow;

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
        \Lns\Gpn\Lib\Entity\Db\Outflow $Outflow
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
        $this->_outflow = $Outflow;
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
                $photo = $this->upload($userId, 'inspector');
                $update->setData('checked_by', $userId);
                $update->setData('checked_by_path', $photo);
                $update->setData('checked_by_date', $this->_dateTime->getTimestamp());
                $update->setData('order_status', 4);
                $update->setData('for_release', 0);
                $save = $update->__save();

                $this->_orderstatus->updateStatus($this->getParam('orderId'), 4);

                if($save){

                    $outflow = $this->_outflow;
                    $outflow->setData('type', 1);
                    $outflow->setData('reference_id', $this->getParam('orderId'));
                    $outflow->__save();

                    $pushData = $update->getData();
                    $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                    if ($customer) {
                        $pushData['customer_name'] = $customer;
                    }
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been completed";
                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 18, null, $message, $update->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                    if ($to) {
                        $title = "Order Completed";
                        $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " has been completed";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_completed";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_completed', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Completed.';
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
