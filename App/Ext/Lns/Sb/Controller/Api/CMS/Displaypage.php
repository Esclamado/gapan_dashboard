<?php
namespace Lns\Sb\Controller\Api\CMS;

class Displaypage extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
		$this->_cms = $this->_di->get('Lns\Sb\Lib\Entity\Db\Page');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');

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
            $response = $this->_cms->dispalyPage();
         
            $this->jsonData['error'] = 0;
            $this->jsonData['message'] = $this->_lang->getLang('success');
            $this->jsonData['data'] = $response;
        }
     
        $this->jsonEncode($this->jsonData);
        die;
    }
}

