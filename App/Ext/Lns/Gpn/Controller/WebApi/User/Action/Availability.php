<?php
namespace Lns\Gpn\Controller\WebApi\User\Action;

class Availability extends \Lns\Sb\Controller\Controller {

    protected $_users;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $userId = $this->getParam('id');
            $status = $this->getParam('status');

            $update = $this->_users->getByColumn(['id'=>$userId], 1);
            if($update){
                $update->setData('status',$status);
                $user = $update->__save();

                if($status==0){
                    $message = 'User account set to inactive';
                }else{
                    $message = 'User account set to active';
                }
                if($user){
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $message;
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>