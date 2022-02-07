<?php 
namespace Lns\Sb\Lib\Token; 

use Lns\Sb\Controller\Controller;

class Validate extends \Lns\Sb\Lib\Token\Jwt {


	
	/*
	*	validate the token
	*	@param $param | the request params
	*	@param $isUser | bool false if we dont need the user to be validated
	*		mostly used on not user based request.
	*		true to validate that the token has user ID
	*/
	public function validate($param, $isUser = false){
		$this->param = $param;
		
		$authorization_temp = null;
		// HTTP_DEVICETOKEN
		$device_token = $this->param->getServer('HTTP_DEVICETOKEN');
		/*
			we use session on web app, token is not needed here
			we only use token for unsecure request
			$check = null;   null = mobile , 1 = WEB 

			if(!$this->param->getServer('HTTP_DEVICETOKEN')){
				$check = 1;
			}
		*/
		$result = [
			'error' => 1,
			'message' => $this->_lang->getLang('api_invalid_devicetoken'),
			'payload' => null,
		];
		
		$token = $this->getBearer();

		if(is_array($token) && count($token) == 3){
			$this->setSecret($this->siteConfig->getData('site_api_secret'));
			$fullToken = implode('.', $token);
			
			$payload = $this->validateToken($fullToken, $device_token/*, $check*/);
			$payload['devicetoken'] = $device_token;
			if($payload['error'] == 0){
				if($isUser){
					if(!isset($payload['payload']['jti'])) {
						$payload['error'] = 1;
						$payload['message'] = $this->_lang->getLang('login_no');
						$payload['payload'] = null;
					} else {
						/*$user = $this->_userEntity->getByColumn(['id', $payload['jti']], 1);
						if(!$user){
							$payload['mesage'] = $this->_lang->getLang('loginaccount_not_found_no');
							$payload['payload'] = null;
						} else {
							$payload['mesage'] = $this->_lang->getLang('success');
							$payload['user'] = $user;
						}*/
					}
				}
			}
			$result = $payload;
		}

		return $result;
	}

	/*
	*	validate token generated from untrusted request.
	*	this request came from angular, ember, ionic etc.
	*	@param $param | the request params
	*/
	public function validateClientToken($param){
		$this->param = $param;
		$token = $this->getBearer();

		$result = [
			'error' => 1,
			'message' => '',
			'payload' => null
		];
		if($token && is_array($token)){
			list($header, $payload, $signature) = $token;
			$dataEncoded = $header . '.' . $payload;

			$rawSignature = hash_hmac("sha256", utf8_encode($dataEncoded), utf8_encode($this->siteConfig->getData('site_api_key')));

			$isValid = hash_equals($rawSignature, $signature);

			if($isValid){
				$payloadDecode = json_decode(base64_decode($payload), true);
				if(isset($payloadDecode['tzoffset']) && isset($payloadDecode['tzname'])){
					$tzoffset = $payloadDecode['tzoffset'];
					$tzname = $payloadDecode['tzname'];

					$tz = $this->validateTimeZone($tzoffset, $tzname);

					if($tz){
						date_default_timezone_set($tz);

						$now = time();
						$tokenTime = strtotime($payloadDecode['iat']);
						$max_valid_time = $this->siteConfig->getData('site_api_token_max_time', 60*3);

						$gap = $now - $tokenTime;

						$expEnabled = (int)$this->siteConfig->getData('site_api_exp_enabled', 0);

						$result['error'] = 0;
						$result['message'] = $this->_lang->getLang('success');
						$result['payload'] = $payloadDecode;

						if($expEnabled == 1){
							if($gap > $max_valid_time){
								$result['error'] = 1;
								$result['message'] = $this->_lang->getLang('api_expired_token');
								$result['payload'] = null;
							}
						}
						return $result;
					}
				}
			}
			$result['message'] = $this->_lang->getLang('invalid_request');
		}
	}

	/*
	*	there are 3 authorization variable in server
	*	2 of them came from $_SERVER variables
	*	and the other one is from url or post param
	*	return the autorization array | null
	*/
	protected function getBearer(){
		$a = $this->param->getServer('REDIRECT_HTTP_AUTHORIZATION');
		if(!$a){
			$a = $this->param->getServer('HTTP_AUTHORIZATION');
		}

		if($a){
			$authorization = $a;
		} else {
			$authorization = 'Bearer ' . $this->param->getParam('token');
		}

		if($authorization){
			$authorization = explode(' ', $authorization);
			if(isset($authorization[1])){
				$token = explode('.', $authorization[1]);
				if(count($token) == 3){
					return $token;
				}
			}
		}
		return null;
	}

	/*
	*	get all available time zone in the server
	*	will return time zone based on the $tzoffset and $tzname
	*/
	protected function validateTimeZone($tzoffset=null, $tzname=null) {
		$zones_array = array();
		$timestamp = time();
		foreach(timezone_identifiers_list() as $key => $zone) {

			$tz = date('P', $timestamp);
			$_key = str_replace(':', '', $tz);

			if($tzoffset && $tzname){
				if($tzoffset == $_key && $tzname == $zone){
					return $zone;
				}
			}
			$zones_array[$key]['zone'] = $zone;
			$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . $tz;
		}
		return null;
	}
}