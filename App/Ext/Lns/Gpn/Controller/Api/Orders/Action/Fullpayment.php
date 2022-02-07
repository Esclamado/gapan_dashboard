<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

class Fullpayment extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_ordercanceldecline;
    protected $_payment;
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
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
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
        $this->_payment = $Payment;
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

            $orderInfo = $this->_orders->getByColumn(['id' => $orderId], 1);
            if ($orderInfo) {
                if ($orderInfo->getData('mode_of_payment') == 1) {
                    $this->jsonData['message'] = 'Your order is already set as Full Payment.';
                } else {
                    $entity = $this->_ordercanceldecline->getByColumn(['order_id' => $orderId], 1);
                    $entity->delete();

                    $entity = $this->_orderstatus->getByColumn(['order_id' => $orderId, 'status' => 8], 1);
                    $entity->delete();

                    $entity = $this->_payment->getByColumn(['order_id' => $orderId], 1);
                    $entity->setData('payment', $orderInfo->getData('total_price'));
                    $entity->setData('balance', 0);
                    $entity->__save();

                    $orderInfo->setData('order_status', 1);
                    $orderInfo->setData('mode_of_payment', 1);
                    $orderInfo->__save();

                    $inspectors2 = $this->_users->getUsersByRole(5);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $orderInfo->getData();
                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "A Customer has placed an order";
                            $this->_notification->setNotification((int) $orderInfo->getData('id'), $userId, 15, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "New Order";
                                $message = "A Customer has placed an order";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "new_order";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'new_order', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }

                    /* send notif to manager */
                    $managers = $this->_users->getUsersByRole(4);
                    if ($managers) {
                        $sender = $this->_userProfile->getFullNameById($userId);
                        $pushData = $orderInfo->getData();
                        foreach ($managers as $manager) {
                            $message = "A Customer has placed an order";
                            $this->_notification->setNotification($orderInfo->getData('id'), $userId, 15, null, $message, $manager->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                            $this->_cfs->save($manager->getData('id'), $sender, $message, 'new_order', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                    /* send notif to manager */

                    /* need ko notification dito to send to inspector 2 from customer - DONE */
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'The payment for this order has been set as Full Payment.';
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