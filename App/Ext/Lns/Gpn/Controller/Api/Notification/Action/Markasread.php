<?php
namespace Lns\Gpn\Controller\Api\Notification\Action;

class Markasread extends \Lns\Sb\Controller\Controller {

    protected $_notification;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Notifications $Notifications,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_notification = $Notifications;
        $this->token = $Validate;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        $user_id = $payload['payload']['jti'];

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {

            $notif_id = $this->getParam('notif_id');
            $item_id = $this->getParam('item_id');
            $type = $this->getParam('type');

            if($user_id){
                if($notif_id){
                    $entity = $this->_notification->getByColumn(['id'=>$notif_id], 1);
                    if ($entity) {
                        $entity->setData('isRead', 1);
                        $isreadId = $entity->__save();
                    }
                }else{
                    $entity = $this->_notification->getByColumn(['read_by_user_id'=>$user_id, 'item_id'=>$item_id, 'type'=>$type], 0);
                    if ($entity) {
                        foreach ($entity as $ent) {
                            $ent->setData('isRead', 1)->__save();
                        }
                    }
                }
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>