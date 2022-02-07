<?php
namespace Lns\Sb\Controller\Api\User;

class Validateemail extends \Lns\Sb\Controller\Controller {
	
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
        
    }	
    
	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);



        $email = $this->getParam('email');
        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        }else{
            /* Parameter to pass
            Type (1 = Validate Email , 2 = Forgot password) , Email */
            $response = $this->_global->validateEmail(1,$email);
            $this->jsonData = $response;
        }

        $this->jsonEncode($this->jsonData);
        die;
       
    }
}

