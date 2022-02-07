<?php 

namespace Lns\Sb\Lib\Token; 
use Lns\Sb\Controller\Controller;
use \Of\Db\Entity;
class Jwt  {

	protected $siteConfig;
	protected $token;
	protected $secret = '';
	protected $key = '';
	protected $additionSecret = '';
	protected $header = [
		'alg' => 'HS256',
		'typ' => 'JWT',
	];
	protected $exp;
	
	protected $payload = [];
	protected $validationMessage = '';
    protected $_request;
	protected $_deviceToken;
	protected $_url;
	protected $_lang;

	// protected $_userEntity;
	
	public function __construct(
		\Of\Http\Request $Request,	
		\Of\Http\Url $Url,
		\Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken
        // ,\Application\Lib\Entity\Db\User $User
	){
		$this->_request = $Request;
		$this->_url = $Url;
		$this->_deviceToken = $DeviceToken;
	}

	/*
	*	set lang used in site
	*/
	public function setLang($lang){
		$this->_lang = $lang;
		return $this;
	}

	/*
	* set the site config class
	*/
	public function setSiteConfig($siteConfig){
		$this->siteConfig = $siteConfig;
		return $this;
	}
	
	/*
	*	set the algo to use
	*/
	public function setAlgo($algo = 'HS256'){
		$this->header['alg'] = $algo;
		return $this;
	}
	
	/*
	*	set claim for payload
	*/
	public function setClaim($key, $value){
		$this->payload[$key] = $value;
		return $this;
	}
	/*
	*	set issuer for payload
	*/
	public function setIssuer($issuer){
		return $this->setClaim('iss', $issuer);
	}
	/*
	*	set audience for payload
	*/
	public function setAudience($audience){
		return $this->setClaim('aud', $audience);
    }
	/*
	*	set expiration for payload
	*	if $clain is true exp will be included in token 
	*		@ $expiration | the exact exp date in mili sec
	*	else it will be used in validation condition and must be set on 
	*		@ $expiration | the mili from token create
	*	before validate() function call
	*	@
	*/
	public function setExpiration($expiration, $claim=true){
		if($claim == true) {
			return $this->setClaim('exp', (int)$expiration);
		} else {
			$this->exp = (int)$expiration;
			return $this;
		}
    }
	/*
	*	set id for payload
	*/
	public function setId($id){
		return $this->setClaim('jti', $id);
    }
	/*
	*	set the time of token issue
	*/
	public function setIssuedAt($issuedAt = null){
		if(!$issuedAt){
			$issuedAt = time();
		} else {
			$issuedAt = (int)$issuedAt;
		}
        return $this->setClaim('iat', $issuedAt);
    }
	/*
	*	set the actual validity time if this token is not valid now
	*/
	public function setNotBefore($notBefore){
		return $this->setClaim('nbf', $notBefore);
	}
	/*
	*	set the subject of this token
	*/
	public function setSubject($subject){
		return $this->setClaim('sub', $subject);
	}
	public function setAddSecret($subject){
		return $this->setClaim('data', $subject);
	}
	/*
	*	set the key for this token
	*/
	public function setKey($key){
		$this->key = $key;
		return $this;
	}
	/*
	*	set the secret for this token
	*/
	public function setSecret($secret){
		$this->secret = $secret;
		return $this;
	}
	/*
	*	set the additional secret for this token
	*/
	public function setAdditionalSecret($additionSecret){
		$this->additionSecret .= $additionSecret;
		return $this;
	}
	/*
	*	return additional secret
	*/
	public function getAdditionalSecret($secret){
		return $this->additionSecret;
	}
	/*
	*	return b64 encoder header of this token
	*/
	public function getHeader(){
		return $this->base64UrlEncode(json_encode($this->header));
	}
	/*
	*	return b64 encoder payload of this token
	*/
	public function getPayload(){
		return $this->base64UrlEncode(json_encode($this->payload));
	}
	/*
	*	return the hashed signature of the token
	*/
	public function getSignature(){
		$string = $this->getHeader() . '.' . $this->getPayload();
		$hash = $this->getHash($string);
		return $hash;
	}
	/*
	*	return generated token
	*/
	public function getToken(){
		return $this->getHeader() . '.' . $this->getPayload() . '.' . $this->getSignature();
	}
	
	/*
	*	return bool false if not valid
	*	return payload if valid
	*	for v1.0.1 $check was removed
	*	we use session on web app
	*/
	public function validateToken($token,$device_token/*,$check*/){
		$this->token = $token;
		list($header, $payload, $signature) = explode('.', $this->token);
		$this->payload = json_decode($this->base64UrlDecode($payload), true);
		
		/*$device_api_key = $this->payload['key'];*/
		$isValid = null;

		$result = [
			'error' => 1,
			'message' => $this->_lang->getLang('api_invalid_devicetoken'),
			'payload' => null
		];

		if($device_token) {
			/*
				we cannot trust generated api secret, since this 
				is only a generated string, that can be duplicated
				on the long run

				if(!$check){
					$savedDevice = $this->_deviceToken->getByColumn(['token' => $device_token]);
				}else{
					$savedDevice = $this->_deviceToken->getByColumn(['api_secret' => $device_token]);
				}
			*/
			$savedDevice = $this->_deviceToken->getByColumn(['token' => $device_token]);

			if($savedDevice) {
				$this->setAdditionalSecret($savedDevice->getData('api_secret'));
				$dataEncoded = $header . '.' . $payload;
				$rawSignature = $this->getHash($dataEncoded);
				$isValid = hash_equals($rawSignature, $signature);

				if($isValid){
					$result['error'] = 0;
					$result['message'] = $this->_lang->getLang('success');
					$result['payload'] = $this->payload;

					$expEnabled = (int)$this->siteConfig->getData('site_api_exp_enabled', 0);

					if(isset($this->payload['nbf'])){
						if($this->payload['nbf'] > time()){
							$result['error'] = 1;
							$result['payload'] = null;
							$result['message'] = $this->_lang->getLang('api_token_early');
						}
					}
					elseif(isset($this->payload['exp'])){
						if($expEnabled == 1){
							if($this->payload['exp'] < time()){
								$result['error'] = 1;
								$result['payload'] = null;
								$result['message'] = $this->_lang->getLang('api_expired_token');
							}
						}
					}
					elseif($this->exp){
						if($expEnabled == 1){
							$exp = $this->exp + $this->payload['iat'];
							if($exp < time()){
								$result['error'] = 1;
								$result['payload'] = null;
								$result['message'] = $this->_lang->getLang('api_expired_token');
							}
						}
					}
				} else {
					$result['mesage'] = $this->_lang->getLang('api_invalid_devicetoken');
				}
			} else if ($device_token == 'no-device') {
				$dataEncoded = $header . '.' . $payload;
				$rawSignature = $this->getHash($dataEncoded);
				$isValid = hash_equals($rawSignature, $signature);

				$result['error'] = 0;
				$result['message'] = $this->_lang->getLang('success');
				$result['payload'] = $this->payload;
			}
		}
		
		return $result;
		/*return $isValid;*/
	}
	
	/*
	*	return string of validation message
	*/
	public function getValidationMessage(){
		return $this->validationMessage;
	}
	/*
	*	return hash string
	*/
	protected function getHash($dataEncoded){
		return hash_hmac("sha256", utf8_encode($dataEncoded), utf8_encode($this->additionSecret.'_'.$this->secret));
	}
	
	public function base64UrlEncode($data){
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }
	
	public function base64UrlDecode($data) {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
	}
	



	








}