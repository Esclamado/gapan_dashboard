<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

class Decline extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_ordercanceldecline;
    protected $_orderstatus;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Ordercanceldecline $Ordercanceldecline,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_ordercanceldecline = $Ordercanceldecline;
        $this->_orderstatus = $OrderStatus;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_audittrail = $AuditTrail;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

/*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; */

/*             $userId = $this->getParam('user_id'); */
            $userId = 17;
            $orderId = $this->getParam('order_id');
            $message = $this->getParam('message');

            $orderInfo = $this->_orders->getByColumn(['id' => $orderId], 1);
            if ($orderInfo) {
                if ($orderInfo->getData('order_status') == 1) {
                    $entity = $this->_ordercanceldecline;
                    $entity->setData('order_id', $orderId);
                    $entity->setData('type', 2);
                    $entity->setData('message', $message);
                    $entity = $entity->__save();
                    if ($entity) {
                        $entity = $this->_orderstatus;
                        $entity->setData('order_id', $orderId);
                        $entity->setData('status', 8);
                        $entity->__save();

                        $orderInfo->setData('order_status', 8);
                        $save = $orderInfo->__save();
                        if ($save) {

                            $pushData = $orderInfo->getData();
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "Your <strong>" . 'Order no. ' . $orderInfo->getData('transaction_id') . "</strong> has been declined.";
                            $this->_notification->setNotification((int) $orderInfo->getData('id'), $userId, 19, null, $message, $orderInfo->getData('user_id'));

                            $to = $this->_deviceToken->getDeviceTokenById($orderInfo->getData('user_id'));
                            if ($to) {
                                $title = "Order Declined";
                                $message = "Your " . 'Order no. ' . $orderInfo->getData('transaction_id') . " has been declined.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_declined";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($orderInfo->getData('user_id'), $sender, $message, 'order_declined', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                            $action = "<strong>" . 'Order no. ' . $orderInfo->getData('transaction_id') . "</strong> has been declined.";
                            $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_decline');

                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'You have declined an order.';
                        } else {
                            $this->jsonData['message'] = 'Could not decline order. Please try again.';
                        }
                    } else {
                        $this->jsonData['message'] = 'Could not decline order. Please try again.';
                    }
                } else {
                    $this->jsonData['message'] = 'You are not allowed to decline once the order is being processed.';
                }
            } else {
                $this->jsonData['message'] = 'Order not found.';
            }
        /* } */
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>