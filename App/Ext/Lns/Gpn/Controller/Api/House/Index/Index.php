<?php
namespace Lns\Gpn\Controller\Api\House\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_push;
    protected $_deviceToken;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_push = $PushNotification;
        $this->_deviceToken = $DeviceToken;
    }
    public function run() {
        var_dump($this->_deviceToken->getDeviceTokenById(4)->getData('token'));die;
        /* var_dump($this->_siteConfig->getData('site_fcm_key')); */
        $to = "chDt0TEzOJg:APA91bFWJ7Y3AqpQ2huEiZba6K0IhULjIi7PR28XFRTmv1qevhfWLR30QRygByzfty25uYy7iIj1kl1b_HNkjryCV4CkBMY0X5RKwMNU5XAZKhVhJN5kZEWmY70TlD5NJdk2tHMJLZiJ";
        $title = "Title Here";
        $message = "Message Here";
        $content = "Content Here";
        $api_key = $this->_siteConfig->getData('site_fcm_key');
        $pushAction = "Pushit";
        $send = $this->_push->sendNotif($to, $title, $message, $content, $api_key, $pushAction);
        var_dump($send);
        /* $this->_push->sendNotif(true, true, true); */
    }
}
?>