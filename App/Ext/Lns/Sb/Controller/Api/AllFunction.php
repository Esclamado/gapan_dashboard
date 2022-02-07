<?php
namespace Lns\Sb\Controller\Api;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;
use \Of\Db\Entity;

class AllFunction extends \Lns\Sb\Controller\Controller {
	
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
        $this->_mailTemplateEntity = $this->_di->get('Lns\Sb\Lib\Entity\Db\MailTemplate');
		$this->_mailer = $this->_di->get('Lns\Sb\Lib\Mailer');

    }

    public function updatejwt($id,$aud,$token){
        $jwt = $this->_di->get('Lns\Sb\Lib\Token\Jwt');
        $jwt->setIssuer($this->_url->getDomain());
        $jwt->setAudience($aud);
        $jwt->setId($id);
        $jwt->setIssuedAt(time());
        $jwt->setSubject('user token');
        $jwt->setClaim('key', $token->getData('api_key'));
        $jwt->setExpiration(time() + $this->_siteConfig->getData('site_api_logged_id_token_max_age'));
        $jwt->setAdditionalSecret($token->getData('api_secret'));
        $jwt->setSecret($this->_siteConfig->getData('site_api_secret'));
        
        $this->jsonData['error'] = 0;
        $this->jsonData['message'] = 'Success';
        $this->jsonData['token'] = $jwt->getToken();

        return $this->jsonData;
    }

    public function addSecretInjwt($id,$aud,$token,$addSecret){

        $userInfo = $this->_userModel->getByColumn(['id' => $id]);
        $jwt = $this->_di->get('Lns\Sb\Lib\Token\Jwt');
        $jwt->setIssuer($this->_url->getDomain());
        $jwt->setAudience($aud);
        $jwt->setId($id);
        $jwt->setIssuedAt(time());
        $jwt->setSubject('user token');
        $jwt->setClaim('key', $token->getData('api_key'));
        $jwt->setAddSecret($addSecret);
        $jwt->setExpiration(time() + (60 * 60 * 24)); /* token expire in 48 hrs, there are times that the mail are delayed */
        $jwt->setAdditionalSecret($token->getData('api_secret'));
        $jwt->setSecret($this->_siteConfig->getData('site_api_secret'));
 
        return $jwt->getToken();
    }

    public function validateEmail($type,$data){
        $emailExist = $this->_userModel->getByColumn(['email' => $data], 1);  
        if($type == 1){

            if($emailExist){
                $this->result['message'] = $this->_lang->getLang('email_exists');
            }else{
                $this->result['error'] = 0;
                $this->result['message'] = $this->_lang->getLang('email_available');
            }

        }else{

            if(!$emailExist){
                $this->result['message'] = $this->_lang->getLang('email_not_found');
            }else{
                $this->result['error'] = 0;
                $this->result['message'] = $this->_lang->getLang('verification_code_sent');
                $this->result['data'] = $emailExist->getData('id');
            }
        }

        return $this->result;
    }

    public function reconstruct($data){

        unset($data['password']);
        unset($data['created_at']);
        unset($data['update_at']);
        unset($data['created_by']);
        unset($data['update_by']);
        unset($data['last_login']);
        unset($data['archive']);

        /* unset($data['user_role_id']); */
        unset($data['profile_created_at']);
        unset($data['profile_updated_at']);
        unset($data['address_id']);
        unset($data['address_created_at']);
        unset($data['address_updated_at']);
        unset($data['contact_id']);
        unset($data['contact_profile_id']);
        unset($data['address_profile_id']);
        unset($data['profile_id']);
        unset($data['profile_user_id']);
        unset($data['profile_id']);

        unset($data['attachments_id']);
        unset($data['attachments_uploader_id']);
        unset($data['attachments_attachment_type']);
        unset($data['attachments_uploaded_at']);
        unset($data['attachments_profile_id']);

        return $data;

    }

    public function changePassword($id,$password){
        $userinfo = $this->_userModel->getByColumn(['id' => $id], 1);

        $save = $this->_userModel
        ->setData('id', $id)
        ->setData('password', $password)
        ->__save();

        return  true;
    }

    public function sendMail($signupForm,$type){
        
        $template = $this->_mailTemplateEntity->getByColumn(['template_name' => $type]);
		if($template){

			/* $fullName = ucwords($signupForm['fullname']); */
            $subject = $template->getData('subject');
            if($type == 'send_code'){

                $messageBody = nl2br(str_replace("code: ",$signupForm->getData('code') ,$template->getData('template')));
            }

            $messageBody = nl2br(str_replace("{{username}}",$signupForm['email'],  $template->getData('template')));
            $sender = $signupForm;
			
			ob_start();
				$mailTemplate = __DIR__ . '../../../view/template/mail/email_template.phtml';
				include($mailTemplate);
				$templatedMessage = ob_get_contents();
			ob_end_clean();
			
			$this->_mailer
			->setFrom($template->getData('email'), 'Support')
            ->addAddress($signupForm['email'], 'To')
			/* ->addAddress($signupForm['email'], $fullName, 'To') */
			->setSubject($subject)
			->setMessage($templatedMessage)
			->send();
        }
        
    }
   
}

