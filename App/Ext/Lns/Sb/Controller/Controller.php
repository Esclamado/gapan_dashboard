<?php
namespace Lns\Sb\Controller;

use Zend\Json\Json;
class Controller extends \Of\Controller\Controller {
	

	const APPKEY = 'sjylor9bgrbngosauzyhjr7glomrqxhpphvvfqmxqr4mmrdyf2dyeqciugc68wo58fuwfrbad0fggeyrq4zmxtanxdn3o7s8emzo38huacnu0sfaersattcz93zsg7dayuxygux87q6vz0ypmb4r9yuwtqo5kypminzvbqpmgwh92jkz1hjanqpfugienai1vrhnusgn';
	const APPSECRET = 'r6bwjhulvp2q7ifmytryhtq3fjdlqdiklvct3rhxpiedyhkzwvpcxrycn1iy4h3qtpa7hetyvn1xffh7v1tyysnr9rxoakr3mzuhyp5zl9qzbyudjkhp90vg6kas334eg6uqo3p7cdfwi638nkzuonvgklvx3n9owefhoadvzdhogpnbodwsfsxpecwxzwpjhvlxorgr';
	protected $pageTitle = 'LNS Admin Panel';

	protected $_url;
	public $_message;
	protected $_session;
	protected $_request;
	public $_siteConfig;
	public $_perm;

	protected $result = [
		'error' => 1,
		'message' => '',
		'data' => []
	];

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session
	){
		$this->_di = \DI\ContainerBuilder::buildDevContainer();
		$this->_session = $Session;
		$this->_url = $Url;
		$this->_message = $Message;
		$this->_request = $this->_di->get('Lns\Sb\Lib\Entity\ClassOverride\OfHttpRequest');
		$this->_lang = $this->_di->get('Lns\Sb\Lib\Lang\Lang');
		$this->_siteConfig = $this->_di->get('Lns\Sb\Lib\Siteconfig');
		$this->_perm = $this->_di->get('Lns\Sb\Lib\Permission')->setSession($Session);
		$this->_lang->setLang($this->_siteConfig->getData('site_default_language') ? $this->_siteConfig->getData('site_default_language') : 'en');
	}


	public function setLayout($router){
		$this->_url->setRouter($router);
		return parent::setLayout($router);
	}

	protected function requireLogin(){
		$user = $this->_session->isLogedIn();
		if(!$user){
			$this->_url->redirect('/login');
		}
		$this->setUser($user);
	}
	protected function requireNotLogin(){
		$user = $this->_session->isLogedIn();
		if($user){
			$this->_url->redirect('/');
		}
	}

	protected function setUser($user){
	/* 	$this->_user = $this->_admins->setIsCache(true)->getByColumn(['id' => $user]);
		if(!$this->_user){
			$this->_session->setLogout();
			$this->_url->redirect('/admin/login');
		} */
	}

	public function getUser(){
		/*return $this->_user;*/
	}

	public function getMessage(){
		return $this->_message;
	}

	/* protected function getParam(){
		return $this->getServiceManager()->get('request');
	} */
	protected function jsonEncode($data){
		$j = Json::encode($data);
		/* $j = json_encode($data); */
		if (json_last_error()) {
			throw new \Exception(json_last_error_msg());
			die;
        }
		header("Content-Type: application/json; charset=UTF-8");
		echo $j;
		exit;
		die;
	}

	public function checkPermission($permissionCode, $type, $user=null){
		return $this->_perm->check($permissionCode, $type, $user);
	}

	protected function getImageUrl($params=array()){
		$str = 'the_image_param_must_be_an_array';
		if(is_array($params)){
			if(isset($params['path'])){
				$path = str_replace('=', '', strtr(base64_encode(json_encode($params)), '+/', '-_'));

				$path = strtolower($params['vendor']) . '/';
				$path .= strtolower($params['module']) . '/';
				$path .= opoink_b64encode($params['path']) . '/';

				if(isset($params['resize'])){
					$resize = $params['resize'];
					foreach($resize as $key => $val){
						$path .= strtolower($key) . '/' . strtolower($val) . '/';
					}
				}

				$str =  $this->_url->getStaticUrl('/images/'.$path.$params['filename']);
			} else {
				$str = 'the_path_of_image_is_important';
			}
		}
		return $str;
	}

	public function getPageName(){
		return $this->_router->getPageName(false, '_');
	}
}
?>