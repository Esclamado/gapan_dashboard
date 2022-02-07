<?php
namespace Lns\Sb\Controller\Api\Token;

class Get extends \Lns\Sb\Controller\Controller {
		protected $token;
		protected $payload;	
		protected $_deviceToken;
	
		public function __construct(
			\Of\Http\Url $Url,
			\Of\Std\Message $Message,
			\Lns\Sb\Lib\Session\Session $Session
		){
			parent::__construct($Url,$Message, $Session);
			$this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
		}
		public function run(){
			$_jwt = $this->_di->get('Lns\Sb\Lib\Token\Validate');

			$isValidToken = $_jwt->setSiteConfig($this->_siteConfig)
			->setLang($this->_lang)
			->validateClientToken($this->_request);

			if($isValidToken['error'] == 0){
				$device_token = $this->getParam('device_token');
				$savedDevice = $this->_deviceToken->registerDevice($this->_siteConfig->getData('site_api_key'),$device_token);

				if($savedDevice){
					$savedDevice = $savedDevice->getData();
					$jwt = $this->_di->make('Lns\Sb\Lib\Token\Jwt');
					$jwt->setIssuer($this->_url->getDomain());
					$jwt->setIssuedAt(time());
					$jwt->setClaim('key', $savedDevice['api_key']);
					$jwt->setAdditionalSecret($savedDevice['api_secret']);
					$jwt->setSecret($this->_siteConfig->getData('site_api_secret'));
					
					$this->result['error'] = 0;
					$this->result['message'] = $this->_lang->getLang('success');
					$this->result['data']['token'] = $jwt->getToken();
				}
			} else{
				$this->result['message'] = $isValidToken['message'];
			}

			$this->jsonEncode($this->result);
			die;
		}
	}