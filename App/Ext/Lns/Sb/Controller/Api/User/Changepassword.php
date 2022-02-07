<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Changepassword extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
        $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
        $this->_password = $this->_di->get('Lns\Sb\Lib\Password\Password');
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
            $response =$this->checkPassword($payload['payload']['jti']);
            $this->jsonData = $response;
        }

        $this->jsonEncode($this->jsonData);
        die;
    }
    
    public function checkPassword($id){
        $current_password = $this->getParam('current_password');
        $new_password = $this->getParam('new_password');
        $confirm_password = $this->getParam('confirm_password');

        $userExist = $this->_userModel->getByColumn(['id' => $id], 1);

        $passwordVerify = $this->_password->setPassword($current_password)
        ->setHash($userExist->getData('password'))->verify();

        if($passwordVerify){
            return $this->changePassword($userExist->getData('id'),$new_password,$confirm_password);
        } else {
            $this->result['message'] = $this->_lang->getLang('password_current_incorrect');    
            return $this->result;
        }
    }
    public function changePassword($id,$new_password,$confirm_password){
        $checkPassword = $this->_password->confirmPassword($new_password,$confirm_password);

        if($checkPassword){
            $hashedPassword = $this->_password->setPassword($confirm_password)->getHash();
            $response = $this->_global->changePassword($id,$hashedPassword);
            $this->result['error'] = 0;
            $this->result['message'] = $this->_lang->getLang('change_password_success');  
        } else {
            $this->result['message'] = $this->_lang->getLang('password_new_confirm_not_match');
        }

        return $this->result;
    }


}

