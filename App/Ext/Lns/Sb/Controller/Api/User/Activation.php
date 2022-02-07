<?php
namespace Lns\Sb\Controller\Api\User;

class Activation extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
        $this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
        $this->_activation = $this->_di->get('Lns\Sb\Lib\Entity\Db\Activation');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
	}	

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $email = $this->getParam('email');
        $action = $this->getParam('action');

        $this->jsonData['error'] = 1;

        $userData = $this->_userModel->getByColumn(['email' => $email], 1);
        if ($userData) {
            $tokenInDb = $this->_deviceToken->getByColumn([
                'token' => $payload['devicetoken'],
                'api_key' => $payload['payload']['key'],
            ], 1);
            if ($tokenInDb) {
                if ($action == 'resend') {
                    $this->_activation->updateActivationCode($userData->getData('id'));
                    /* $this->_global->sendMail($signupForm,'register'); */
                    $this->jsonData['error'] = 0;
                    $this->jsonData['title'] = 'Sent';
                    $this->jsonData['message'] = $this->_lang->getLang('account_resent_activation');
                    // SENDING NOTIFICATION TO EMAIL AFTER REGISTRATION
                    $userId = $this->_userModel->getByColumn(['email' => $this->getParam('email')], 1)->getData('id');
                    $this->_userModel->sendCredential($userId, $this->_siteConfig, 'resend_activation');
                } else if ($action == 'activate') {
                    $activation_code = $this->getParam('activation_code');
                    $validate = $this->_activation->validateActivationCode($userData->getData('id'), $activation_code);
                    if ($validate) {
                        $userData->setData('status', 1)->__save();
                        /*  */
                        $userInfo = $this->_userModel->getUserById($userData->getData('id'));
                        if($userInfo){
                            $userInfo = $this->_global->reconstruct($userInfo);
                        }

                        $tokenInDb = $this->_deviceToken->getByColumn([
                            'token' => $payload['devicetoken'],
                            'api_key' => $payload['payload']['key'],
                        ], 1);

                        $responce  =  $this->_global->updatejwt($userData->getData('id'),$userData->getData('email'),$tokenInDb);
                        $this->jsonData = $responce;
                        $this->jsonData['user'] =   $userInfo;
                        /*  */
                        $this->jsonData['error'] = 0;
                        $this->jsonData['title'] = 'Success';
                        $this->jsonData['message'] = $this->_lang->getLang('account_activated');
                    } else {
                        $this->jsonData['error'] = 2;
                        $this->jsonData['title'] = 'Failed';
                        $this->jsonData['message'] = $this->_lang->getLang('account_activation_code_invalid');
                    }
                }
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
            }
        } else {
            $this->jsonData['message'] = $this->_lang->getLang('email_not_found');
        } 
        $this->jsonEncode($this->jsonData);
        die;
    }
}