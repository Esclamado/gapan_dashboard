<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Lns\Sb\Lib\Session;

use Of\Std\Di;
use Of\Token\Jwt;
use Of\Http\Url;
use Lns\Sb\Lib\Entity\Db\Users;

class Session extends \Of\Session\Session {
	
	protected $sessionKey = 'admin';
	
	public function __construct(){
		parent::__construct();
		if(!isset($_SESSION[$this->sessionKey])) {
			$_SESSION[$this->sessionKey] = [];
		}
	}
	public function getLogedInUser(){
		$user_id = 0;
		if(isset($_SESSION[$this->sessionKey]['user_id'])) {
			$user_id = $_SESSION[$this->sessionKey]['user_id'];
		} 
		elseif(isset($_COOKIE['rme'])){
			$validToken = $this->validateCookieToken();
			if($validToken){
				$user_id = $validToken['jti'];
				$exp = time() + (60*60*24*60);

				setcookie('rme', $_COOKIE['rme'], $exp, '/');

				$_SESSION[$this->sessionKey]['user_id'] = $user_id;
			} else {
				setcookie('rme', "", time()-3600, '/');
			}
		}
		return $user_id;
	}

	/*
	*	this will validate the user's cookie
	*	for remember me login.
	*	return null if not valid || payload
	*/
	protected function validateCookieToken(){
		$valid = null;
		$jwt = new Jwt();

		$tokenArray = explode('.', $_COOKIE['rme']);
		if(count($tokenArray) == 3){
			list($header, $payload, $signature) = $tokenArray;
			$payload = json_decode($jwt->base64UrlDecode($payload), true);
			if(isset($payload['jti'])){
				$id = new Di();
				$user = $id->get('Lns\Sb\Lib\Entity\Db\Users');

				$u = $user->getByColumn(['id' => $payload['jti']]);

				if($u){
					$valid = $jwt->setSecret($u->getData('password'))->validateToken($_COOKIE['rme']);
				}
			}
		}
		return $valid;
	}
	
	public function isLogedIn(){
		return (bool)$this->getLogedInUser();
	}
	
	/* session setter */
	public function setAsLoggedIn($user, $remember = false){
		$userId = $user->getData('id');
		if($remember){
			/* 
			* set browser cookie here exp in 60 days
			*/
			$_url = new Url();
			$jwt = new Jwt();

			$jwt->setIssuer($_url->getDomain());
			$jwt->setIssuedAt(time());
			$jwt->setId($user->getData('id'));
			$jwt->setSecret($user->getData('password'));
			$exp = time() + (60*60*24*60);
			setcookie('rme', $jwt->getToken(), $exp, '/');
		}

		$_SESSION[$this->sessionKey]['user_id'] = $userId;
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
		setcookie('rme', "", time()-3600, '/');
		$this->destroy();
	}

	/*
	*	deprecated function will be removed on next release
	*/
	public function setLoginTimeOut($expiration){
		trigger_error("Deprecated function setLoginTimeOut() called.", E_USER_NOTICE);
		$_SESSION[$this->sessionKey]['login_timeout'] = $expiration * 60;
	}

	public function setData($key, $val){
		if(!isset($_SESSION[$this->sessionKey])){
			$_SESSION[$this->sessionKey] = [];
		}

		$_SESSION[$this->sessionKey][$key] = $val;
		return $this;
	}

	public function getData($key=null){
		if($key){
			if(isset($_SESSION[$this->sessionKey][$key])){
				return $_SESSION[$this->sessionKey][$key];
			} else {
				return null;
			}
		} else {
			return $_SESSION[$this->sessionKey];
		}
	}
}
?>