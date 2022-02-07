<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

class Cancel extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_ordercanceldecline;
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
        \Lns\Gpn\Lib\Entity\Db\Ordercanceldecline $Ordercanceldecline,
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
        $this->_ordercanceldecline = $Ordercanceldecline;
        $this->_orderstatus = $OrderStatus;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $orderId = $this->getParam('order_id');
            $message = $this->getParam('message');

            $orderInfo = $this->_orders->getByColumn(['id' => $orderId], 1);
            if ($orderInfo) {
                if ($orderInfo->getData('order_status') == 1) {
                    $entity = $this->_ordercanceldecline;
                    $entity->setData('order_id', $orderId);
                    $entity->setData('type', 1);
                    $entity->setData('message', $message);
                    $entity = $entity->__save();
                    if ($entity) {
                        $entity = $this->_orderstatus;
                        $entity->setData('order_id', $orderId);
                        $entity->setData('status', 7);
                        $entity->__save();

                        $orderInfo->setData('order_status', 7);
                        $orderInfoId = $orderInfo->__save();
                        if ($orderInfoId) {

                            $inspectors2 = $this->_users->getUsersByRole(5);
                            if ($inspectors2) {
                                foreach ($inspectors2 as $inspector2) {
                                    $pushData = $orderInfo->getData();
                                    $sender = $this->_userProfile->getFullNameById($userId);
                                    $message = "A Customer with <strong>" . 'Order no. ' . $orderInfo->getData('transaction_id') . "</strong> has cancelled an order";
                                    $this->_notification->setNotification((int) $orderInfo->getData('id'), $userId, 15, null, $message, $inspector2->getData('id'));

                                    $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                                    if ($to) {
                                        $title = "Order Cancelled";
                                        $message = "A Customer with " . 'Order no. ' . $orderInfo->getData('transaction_id') . " has cancelled an order";
                                        $content = "Content Here";
                                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                                        $pushAction = "order_cancelled";
                                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                        $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_cancelled', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                    }
                                }
                            }

                            /* send notif to manager */
                            $managers = $this->_users->getUsersByRole(4);
                            if ($managers) {
                                foreach ($managers as $manager) {
                                    $sender = $this->_userProfile->getFullNameById($userId);
                                    $message = "A Customer with " . 'Order no. ' . $orderInfo->getData('transaction_id') . " has cancelled an order";
                                    $this->_notification->setNotification((int)$orderInfo->getData('id'), $userId, 15, null, $message, $manager->getData('id'));
                                    $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                                    $pushData = $orderInfo->getData();
                                    $this->_cfs->save($manager->getData('id'), $sender, $message, 'order_cancelled', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                            }
                            /* send notif to manager */

                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'Your order has been cancelled.';
                        } else {
                            $this->jsonData['message'] = 'Could not cancel order. Please try again.';
                        }
                    } else {
                        $this->jsonData['message'] = 'Could not cancel order. Please try again.';
                    }
                } else {
                    $this->jsonData['message'] = 'You are not allowed to cancel once the order is being processed.';
                }
            } else {
                $this->jsonData['message'] = 'Order not found.';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>