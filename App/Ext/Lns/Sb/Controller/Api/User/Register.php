<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Register extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_pushNotification = $this->_di->get('Lns\Sb\Lib\PushNotification\PushNotification');
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
		$this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
        $this->Password = $this->_di->get('Lns\Sb\Lib\Password\Password');
        $this->_contact = $this->_di->get('Lns\Sb\Lib\Entity\Db\Contact');
        $this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
        $this->_activation = $this->_di->get('Lns\Sb\Lib\Entity\Db\Activation');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
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
            $signupForm = $this->getParam();
            
            $hashedPassword = $this->Password->setPassword($signupForm['password'])->getHash();
            $signupForm['password'] = $hashedPassword;
            $email = $signupForm['email'];
            $userExist = $this->_userModel->getByColumn(['email' => $email], 1);

            if($userExist){
                $this->jsonData['message'] = $this->_lang->getLang('email_exists');
            } else {
                $userId = $this->register($payload,$email,$hashedPassword,$signupForm);
                $this->_activation->saveActivationCode($userId);
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = $this->_lang->getLang('account_create');
                // SENDING NOTIFICATION TO EMAIL AFTER REGISTRATION
                /* $this->_userModel->sendCredential($userId, $this->_siteConfig, 'welcome'); */
            }
        }
		$this->jsonEncode($this->jsonData);
		die;
    }
    
    public function register($payload,$email,$hashedPassword,$signupForm, $fromSocial = false){

        $uniqueId = $this->_userProfile->generateUniqueId();
        $signupForm['password'] = $hashedPassword;
        $signupForm['status'] = 1;
        $signupForm['user_role_id'] = 3;

        $userId = $this->_userModel->saveUser($signupForm);
        $signupForm['user_id'] = $userId;
        $signupForm['unique_id'] = $uniqueId;
        
        $userId = $this->_userProfile->saveUserProfile($signupForm);
        $signupForm['profile_id'] = $userId;
        $this->_contact->saveUserContact($signupForm);
        $this->_address->saveUserAddress($signupForm);
             
        if($userId){
            $tokenInDb = $this->_deviceToken->getByColumn([
                'token' => $payload['devicetoken'],
                'api_key' => $payload['payload']['key'],
            ], 1);

            if($tokenInDb){
                $this->_deviceToken->saveToken($userId,$tokenInDb->getData());
                /* $userInfo = $this->_userModel->getUserById($userId);
                $userInfo =  $this->_global->reconstruct($userInfo); */
                $responce  =  $this->_global->updatejwt($userId, $email, $tokenInDb);
                $this->jsonData = $responce;
                $userInfo = $this->_userProfile->getByColumn(['user_id' => $userId], 1);

                $this->jsonData['user'] = array(
                    'id' => $userId,
                    'username' => /* $userInfo->getData('username') */$this->getParam('username'),
                    'email' => $email,
                    'status' => 1,
                    'user_role_id' => 3,
                    'profile_first_name' => $userInfo->getData('first_name'),
                    'profile_last_name' => $userInfo->getData('last_name'),
                    'profile_about' => $userInfo->getData('about'),
                    'profile_birthdate' => $userInfo->getData('birthdate'),
                    'profile_gender' => $userInfo->getData('gender'),
                    'profile_lon' => $userInfo->getData('lon'),
                    'profile_lat' => $userInfo->getData('lat'),
                    'profile_location' => $userInfo->getData('location')
                );
                if ($userInfo->getData('profile_profile_pic')) {
                    $this->jsonData['user']['profile_profile_pic'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Sb',
                        'path' => '/images/uploads/profilepic/'.$userId,
                        'filename' => $userInfo->getData('profile_profile_pic')
                    ]);
                }
            }
            return $userId;
        } else {
            $this->jsonData['message'] = "Failed to register!";
        }
    }
}