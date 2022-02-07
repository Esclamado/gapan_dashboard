<?php
namespace Lns\Sb\Controller\Api\CMS;

class Page extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        /* $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken'); */
        $this->_cms = $this->_di->get('Lns\Sb\Lib\Entity\Db\Cms');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
	}

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $page = $this->getParam('get');

        $this->jsonData['error'] = 1;
        $isExists = $this->_cms->getByColumn(['page' => $page], 1);
        /* $tokenInDb = $this->_deviceToken->getByColumn([
            'token' => $payload['devicetoken'],
            'api_key' => $payload['payload']['key'],
        ], 1); */
        /* if ($tokenInDb) { */
            if ($isExists) {
                $this->jsonData['error'] = 0;
                $this->jsonData['content'] = $isExists->getData('content');
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('cms_not_exists');
            }
        /* } else {
            $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
        } */
        $this->jsonEncode($this->jsonData);
        die;
    }
}