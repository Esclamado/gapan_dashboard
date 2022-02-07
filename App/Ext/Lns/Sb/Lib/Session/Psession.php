<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Lns\Sb\Lib\Session;

class Psession extends \Of\Session\Session {
	
	protected $sessionKey = 'public';
	
	public function __construct(){
		parent::__construct();
		if(!isset($_SESSION[$this->sessionKey])) {
			$_SESSION[$this->sessionKey] = [];
		}

		if(isset($_SESSION[$this->sessionKey]['login_timeout'])){
			if($_SESSION[$this->sessionKey]['login_timeout'] > 0){
				if(isset($_SESSION[$this->sessionKey]['last_activity'])){
					$secondsInactive = time() - $_SESSION[$this->sessionKey]['last_activity'];
					$expireAfterSeconds = $_SESSION[$this->sessionKey]['login_timeout'];
					
					if($secondsInactive >= $expireAfterSeconds){
						$this->setAsLogout();
					}
				}
				$_SESSION[$this->sessionKey]['last_activity'] = time();
			}
		}
	}
	public function getLogedInUser(){
		$user_id = 0;
		if(isset($_SESSION[$this->sessionKey]['user_id'])) {
			$user_id = $_SESSION[$this->sessionKey]['user_id'];
		}
		return $user_id;
	}
	
	public function isLogedIn(){
		return (bool)$this->getLogedInUser();
	}
	
	/* session setter */
	public function setAsLoggedIn($userId, $remember = false, $expiration = 0){
		
		if($remember){
			$_SESSION[$this->sessionKey]['user_id'] = $userId;
		}else{
			$this->setLoginTimeOut($expiration);
			$_SESSION[$this->sessionKey]['user_id'] = $userId;
		}
	}
	
	public function setReturnUrl($url){
		$_SESSION[$this->sessionKey]['return_url'] = $url;
	}
	
	public function getReturnUrl(){
		if(isset($_SESSION[$this->sessionKey]['return_url'])){
			return $_SESSION[$this->sessionKey]['return_url'];
		}
	}
	
	public function setAsLogout(){
		$_SESSION[$this->sessionKey] = [];
		$this->destroy();
	}

	public function setLoginTimeOut($expiration){
		$_SESSION[$this->sessionKey]['login_timeout'] = $expiration * 60;
	}
}
?>