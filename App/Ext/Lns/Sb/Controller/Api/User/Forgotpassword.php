<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Forgotpassword extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
    protected $_userModel;
    protected $_deviceToken;
    protected $_global;
    protected $_password;
    protected $_activation;
	
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
        $this->_activation = $this->_di->get('Lns\Sb\Lib\Entity\Db\Activation');
	}	

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $email = $this->getParam('email');
        $type = $this->getParam('type');
        /* forgot password steps 1-3;
        type: 1 = send to email , 2  = check code , 3 = change password */
        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];        
        } else {
            if($type == 1){
                /*     Parameter to pass
                type (1 = Validate Email , 2 = Forgot password) , email */
                $verifyEmail = $this->_global->validateEmail(2,$email);

                if($verifyEmail['error'] == 0){
                    $tokenInDb = $this->_deviceToken->getByColumn([
                        'api_key' => $payload['payload']['key']
                    ]);
                    $code = strtoupper(\Lns\SB\Lib\Password\Password::generate(6));
                    $this->jsonData['code'] = $code;
                   /*  Parameter to pass
                    ID , Audience , Payload , New secret */
                    $response = $this->_global->addSecretInjwt($verifyEmail['data'],'forgot code', $tokenInDb,$code);
                    $mail = array(
                        "email" => $email,
                        "code" => $code,
                    );
                    /* $this->_global->sendMail($mail,'send_code'); */ 
                    $this->jsonData['error'] = 0;
                    $this->jsonData['token'] = $response;
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_sent');
                    // SENDING NOTIFICATION TO EMAIL AFTER REGISTRATION
                    $userId = $this->_userModel->getByColumn(['email' => $this->getParam('email')], 1)->getData('id');
                    $activation = $this->_activation->getByColumn(['user_id' => $userId], 1);
                    if (!$activation) {
                        $activation = $this->_activation;
                        $activation->setData('user_id', $userId);
                    }
                    $activation->setData('activation_code', $code)->__save();
                    $this->_userModel->sendCredential($userId, $this->_siteConfig, 'forgot_password');
            } else {
                    $this->jsonData= $verifyEmail;
                }
            } else if($type == 2) {
                $code = $this->getParam('code');
                $userId = $this->_userModel->getByColumn(['email' => $email], 1)->getData('id');
                $validate = $this->_activation->getByColumn(['user_id' => $userId, 'activation_code' => $code], 1);
                if ($validate/* $payload['payload']['data'] == $code */) {
                    $this->jsonData['error'] = 0;
                    $userId = $this->_userModel->getByColumn(['email' => $this->getParam('email')], 1)->getData('id');
                    $this->jsonData['userId'] = $userId;
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_valid');
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_invalid');
                }
            } else if($type == 3) {       
                $checkPassword = $this->_password->confirmPassword($this->getParam('password'),$this->getParam('retype_password'));
                if($checkPassword){

                    $hashedPassword = $this->_password->setPassword($this->getParam('retype_password'))->getHash();
                   /*  parameter to pass
                    User id , password */
                    $response = $this->_global->changePassword($payload['payload']['jti'],$hashedPassword);

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  $this->_lang->getLang('change_password_success');
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('password_new_confirm_not_match');
                }
            } else {
                $this->jsonData['message'] = "Invalid type!";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}

