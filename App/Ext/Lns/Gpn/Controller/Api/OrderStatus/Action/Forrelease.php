<?php

namespace Lns\Gpn\Controller\Api\OrderStatus\Action;

class Forrelease extends \Lns\Sb\Controller\Controller{

    protected $_orderstatus;
    protected $_orders;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_dateTime;
    protected $_upload;
    protected $_paymentattachments;
    protected $_payment;
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
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
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
        $this->_dateTime = $DateTime;
        $this->_upload = $Upload;
        $this->_paymentattachments = $PaymentAttachments;
        $this->_payment = $Payment;
        $this->_audittrail = $AuditTrail;
    }

    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

            $this->jsonData['error'] = 1;

            /* $userId = $this->getParam('userId'); */
            $userId = 18;
            $orderId = $this->getParam('orderId');
            $attachments = $this->getParam('attachment');

            $update = $this->_orders->getByColumn(['id'=>$orderId], 1);
            $payment = $this->_payment->getByColumn(['order_id'=> $orderId], 1);
            if($update){

                $update->setData('for_release',1);
                $update->setData('date_paid', $this->_dateTime->getTimestamp());
                $save = $update->__save();

                if($save){
                    if($attachments){
                        $attachment_type = 'transaction_form';
                        foreach ($attachments as $key => $attachment) {
                            /* if ($attachment['type'] == 1) {
                                $attachment_type = 'receipt';
                            } else if ($attachment['type'] == 2) {
                                $attachment_type = 'payment_form';
                            } else if ($attachment['type'] == 3) {
                                $attachment_type = 'credit_form';
                            } else if ($attachment['type'] == 4) {
                                $attachment_type = 'balance_form';
                            } */
                            $photo = $this->upload($payment->getData('id'), $attachment_type, $key);
                            $entity = $this->_paymentattachments;
                            $entity->setData('attachment', $photo);
                            $entity->setData('payment_id', $payment->getData('id'));
                            $entity->setData('type', $attachment['type']);
                            $entity->__save();
                        }
                    }

                    $inspectors2 = $this->_users->getUsersByRole(7);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }

                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready for releasing.";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 17, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Ready for Release";
                                $message = "" . 'Order no. ' . $update->getData('transaction_id') . " is ready for releasing.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_for_release";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready for releasing.";
                        $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_forrelease');
                    }

                    $pushData = $update->getData();
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready for releasing.";
                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 23, null, $message, $update->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                    if ($to) {
                        $title = "Order Ready for Release";
                        $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " is ready for releasing.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_for_release";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'An order is ready for releasing';
                }
            }else{
                $this->jsonData['message'] = 'No record found';
            }

        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId, $type, $key)
    {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads';

        if ($type) {
            $path .= DS . $type;
        }

        $path .= DS . $userId;

        $attach = $this->getFile('attachment');

        $file = [
            'name'=> $attach['name'][$key]['photo'],
            'type' => $attach['type'][$key]['photo'],
            'tmp_name' => $attach['tmp_name'][$key]['photo'],
            'error' => $attach['error'][$key]['photo'],
            'size' => $attach['size'][$key]['photo']
        ];

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