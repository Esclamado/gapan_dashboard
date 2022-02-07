<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Sociallogin extends \Lns\Sb\Controller\Controller {
	
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
        $this->_social = $this->_di->get('Lns\Sb\Lib\Entity\Db\SocialLogin');
        $this->_register = $this->_di->get('Lns\Sb\Controller\Api\User\Register');
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

        $loginForm = $this->getParam();
        $tokenInDb = $this->_deviceToken->getByColumn([
            'token' => $payload['devicetoken'],
            'api_key' => $payload['payload']['key'],
        ], 1);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $email = $this->getParam('email');

            if($email){
                $userExist = $this->_userModel->getByColumn(['email' => $email], 1);
                
                if($userExist){
                    /* if(!$userExist->getData('password')){ */
                        $this->_deviceToken->saveToken($userExist->getData('id'),$tokenInDb->getData());
                        $token = $this->_global->updatejwt($userExist->getData('id'),$email,$tokenInDb);
                        $response =  $this->_global->reconstruct($this->_userModel->getUserById($userExist->getData('id')));

                        $socialExist = $this->_social->getByColumn(['email' => $email, 'type' => $loginForm['type']], 1);
                        if (!$socialExist) {
                            $social_details = array(
                                'type' => $loginForm['type'],
                                'social_id' => $loginForm['social_id'],
                                'email' => $loginForm['email'],
                                'name' => /* $loginForm['name'] */$loginForm['first_name'] . ' ' . $loginForm['last_name'],
                                'first_name' => $loginForm['first_name'],
                                'last_name' => $loginForm['last_name'],
                                'image_url' => $loginForm['image_url'],
                            );
                            $loginForm['user_id'] = $userExist->getData('id');
                            $loginForm['social_details'] = json_encode($social_details);
                            $this->_social->saveSocialAccount($loginForm);
                        }

                        $this->jsonData['error'] = 0;
                        $this->jsonData['message'] = $this->_lang->getLang('login_success');
                        $this->jsonData['data'] = $response;
                        if ($response['profile_profile_pic']) {
                            $this->jsonData['data']['profile_profile_pic'] = $this->getImageUrl([
                                'vendor' => 'Lns',
                                'module' => 'Sb',
                                'path' => '/images/uploads/profilepic/'.$userExist->getData('id'),
                                'filename' => $response['profile_profile_pic']
                            ]);
                        }
                        $this->jsonData['token'] = $token['token'];
                    /* } else {
                        $this->jsonData['message'] = $this->_lang->getLang('email_exists');
                    } */
                } else {

                    $userId = $this->_register->register($payload,$email,'',$loginForm, true);
                    $social_details = array(
                        'type' => $loginForm['type'],
                        'social_id' => $loginForm['social_id'],
                        'email' => $loginForm['email'],
                        'name' => /* $loginForm['name'] */$loginForm['first_name'] . ' ' . $loginForm['last_name'],
                        'first_name' => $loginForm['first_name'],
                        'last_name' => $loginForm['last_name'],
                        'image_url' => $loginForm['image_url'],
                    );
                    $loginForm['user_id'] = $userId;
                    $loginForm['social_details'] = json_encode($social_details);

                    $this->_social->saveSocialAccount($loginForm);
                    
                    $useProfileId = $this->_userProfile->getByColumn(['user_id' => $userId], 1);
                    $userProfile = $loginForm;
                    $userProfile['id'] = $useProfileId->getData('id');
                    $this->_userProfile->saveUserProfile($userProfile);
                    /* $this->_deviceToken->saveToken($userId,$tokenInDb->getData()); */
                    $token = $this->_global->updatejwt($userId,$email,$tokenInDb);
                    $response =  $this->_global->reconstruct($this->_userModel->getUserById($userId));
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $this->_lang->getLang('login_success');
                    $this->jsonData['data'] = $response;
                    $this->jsonData['token'] = $token['token'];
                }
                
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('login_no_email');
            }
        }

        $this->jsonEncode($this->jsonData);
		die;
    }
}