<?php
namespace Lns\Gpn\Controller\Cron\Cancelorder\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_orders;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_orders = $Orders;
    }
    public function run() {

        $orderInfos = $this->_orders->cancelOrder();
        $sender = '';
        if($orderInfos){
            foreach ($orderInfos as $key => $orderInfo) {
                $update = $this->_orders->getByColumn(['id'=> $orderInfo->getData('id')], 1);
                if($update){
                    $update->setData('order_status',7); 
                    $save = $update->__save();

                    if($save){
                        $pushData = $orderInfo->getData();
/*                         $sender = $this->_userProfile->getFullNameById($userId);
                        $message = "Your <strong>" . 'Order no. ' . $orderInfo->getData('transaction_id') . "</strong> has been cancelled";
                        $this->_notification->setNotification((int) $orderInfo->getData('id'), $userId, 19, null, $message, $orderInfo->getData('user_id')); */

                        $to = $this->_deviceToken->getDeviceTokenById($orderInfo->getData('user_id'));
                        if ($to) {
                            $title = "Order Cancelled";
                            $message = "Your " . 'Order no. ' . $orderInfo->getData('transaction_id') . " has been cancelled";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "order_cancelled";
                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                            $this->_cfs->save($orderInfo->getData('user_id'), $sender, $message, 'order_cancelled', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                }
            }
        }
    }
}
?>