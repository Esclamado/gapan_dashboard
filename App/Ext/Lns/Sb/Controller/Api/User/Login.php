<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Login extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
		$this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
        $this->Password = $this->_di->get('Lns\Sb\Lib\Password\Password');
        $this->_contact = $this->_di->get('Lns\Sb\Lib\Entity\Db\Contact');
        $this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
        $this->_activation = $this->_di->get('Lns\Sb\Lib\Entity\Db\Activation');
		/* $this->_attachments = $this->_di->get('Lns\Lib\Entity\Db\Attachments');
		$this->_mailTemplateEntity = $this->_di->get('Lns\Lib\Entity\Db\MailTemplate');
		$this->_mailer = $this->_di->get('Lns\Lib\Mailer'); */
	}	
	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $email = $this->getParam('email');
            $requestPassword = $this->getParam('password');

            if($email){
                $userExist = $this->_userModel->getByColumn(['email' => $email], 1);

                if($userExist){
                    $passwordVerify = $this->Password->setPassword($requestPassword)
                    ->setHash($userExist->getData('password'))->verify();
                    
                    if($passwordVerify){
                        $tokenInDb = $this->_deviceToken->getByColumn([
                            'token' => $payload['devicetoken'],
                            'api_key' => $payload['payload']['key'],
                        ], 1);
                        if($tokenInDb){

                            $this->_deviceToken->saveToken($userExist->getData('id'),$tokenInDb->getData());
                            $userInfo = $this->_userModel->getUserById($userExist->getData('id'));
                            
                            if($userInfo['status'] == 1){
                                if($userInfo){
                                    $userInfo =  $this->_global->reconstruct($userInfo);
                                }

                                $responce  =  $this->_global->updatejwt($userExist->getData('id'),$email,$tokenInDb);
                                $this->jsonData = $responce;
                                $this->jsonData['user'] = $userInfo;
                                $this->jsonData['user']['last_login'] = $userExist->getData('last_login') ? $userExist->getData('last_login') : null;
                                /* update last login */
                                $userExist->setData('last_login', date("Y-m-d H:i:s"));
                                $userExist->__save();
                                /* update last login */
                                if ($userInfo['profile_profile_pic']) {
                                    $this->jsonData['user']['profile_profile_pic'] = $this->getImageUrl([
                                        'vendor' => 'Lns',
                                        'module' => 'Sb',
                                        'path' => '/images/uploads/profilepic/'.$userExist->getData('id'),
                                        'filename' => $userInfo['profile_profile_pic']
                                    ]);
                                }
                            } else {
                                /* $this->_global->sendMail($signupForm,'register'); */
                                /* $this->_activation->updateActivationCode($userExist->getData('id')); */
                                $this->jsonData['error'] = 2;
                                $this->jsonData['message'] = $this->_lang->getLang('account_need_activate');
                            }

                        } else {
                            $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
                        }
                    } else {
                        $this->jsonData['message'] = $this->_lang->getLang('password_current_incorrect');
                    }
                } else {
                    $userExist = $this->_userModel->getByColumn(['username' => $email], 1);

                    if ($userExist) {
                        $passwordVerify = $this->Password->setPassword($requestPassword)
                        ->setHash($userExist->getData('password'))->verify();
                        
                        if($passwordVerify){
                            $tokenInDb = $this->_deviceToken->getByColumn([
                                'token' => $payload['devicetoken'],
                                'api_key' => $payload['payload']['key'],
                            ], 1);
                            if($tokenInDb){
                                $this->_deviceToken->saveToken($userExist->getData('id'),$tokenInDb->getData());
                                $userInfo = $this->_userModel->getUserById($userExist->getData('id'));
                                
                                if($userInfo['status'] == 1){
                                    if($userInfo){
                                        $userInfo =  $this->_global->reconstruct($userInfo);
                                    }

                                    $responce  =  $this->_global->updatejwt($userExist->getData('id'),$email,$tokenInDb);
                                    $this->jsonData = $responce;
                                    $this->jsonData['user'] = $userInfo;

                                    if ($userInfo['profile_profile_pic']) {
                                        $this->jsonData['user']['profile_profile_pic'] = $this->getImageUrl([
                                            'vendor' => 'Lns',
                                            'module' => 'Sb',
                                            'path' => '/images/uploads/profilepic/'.$userExist->getData('id'),
                                            'filename' => $userInfo['profile_profile_pic']
                                        ]);
                                    }
                                } else {
                                    /* $this->_global->sendMail($signupForm,'register'); */
                                    /* $this->_activation->updateActivationCode($userExist->getData('id')); */
                                    $this->jsonData['error'] = 2;
                                    $this->jsonData['message'] = $this->_lang->getLang('account_need_activate');
                                }

                            } else {
                                $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
                            }
                        } else {
                            $this->jsonData['message'] = $this->_lang->getLang('password_current_incorrect');
                        }
                    } else {
                        $this->jsonData['message'] = $this->_lang->getLang('account_not_found');
                    }
                }            
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('login_no_email');
            }  
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}