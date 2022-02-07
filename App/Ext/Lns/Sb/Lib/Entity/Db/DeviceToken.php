<?php 
namespace Lns\Sb\Lib\Entity\Db;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;

class DeviceToken extends \Of\Db\Entity {
    
    const COLUMNS = [
		'id','user_ids','token','created_at','api_key','api_secret'
	];
	
	protected $tablename = 'device_token';
	protected $primaryKey = 'id';
	
	public function __construct(
	\Of\Http\Request $Request,
	$adapter=null){
		parent::__construct($Request,$adapter);
	}
	
	
	public function registerDevice($appkey,$device_token){
		$api_key = hash_hmac(
			"sha256", 
			utf8_encode( time() ), 
			utf8_encode($device_token . \Lns\Sb\Lib\Password\Password::generate(40)) 
		);
		$api_secret = hash_hmac(
			"sha256", 
			utf8_encode(time()), 
			utf8_encode($device_token . \Lns\Sb\Lib\Password\Password::generate(50))
		);

		$savedToken = $this->getByColumn(['token' => $device_token], 1);
		if(!$savedToken){
			$save = $this->setData('token', $device_token)
			->setData('user_ids', '')
			->setData('api_key', $api_key)
			->setData('api_secret', $api_secret)
			->__save();
			return $this->getByColumn(['id' => $save], 1);
		} else {
			return $savedToken;
		}

		/*if(!$device_token){

			
			
			$save = $this->setData('token', "")
			->setData('api_key', $api_key)
			->setData('api_secret', $api_secret)
			->__save();
			
			$this->setData('id', $save);
			return $this->getData();

		}else{

			$tokenInDb = $this->getByColumn(['token' => $device_token]);

			if(!$tokenInDb){
				$api_key = hash_hmac(
					"sha256", 
					utf8_encode( time() ), 
					utf8_encode($device_token) 
				);
				$api_secret = hash_hmac(
					"sha256", 
					utf8_encode(time()), 
					utf8_encode($device_token . \Lns\Sb\Lib\Password\Password::generate(30))
				);
				$save = $this->setData('token', $device_token)
				->setData('api_key', $api_key)
				->setData('api_secret', $api_secret)
				->__save();
				
				$this->setData('id', $save);
				return $this->getData();
			} else {
				return $tokenInDb->getData();
			}
			
		}*/
	}	
	public function saveToken($id, $token) {
        $this->resetQuery();
        if($id !== null) {

			$deviceToken = $this->getByColumn(['api_key' => $token['api_key']], 1);

			if($deviceToken){

				$this->setData('id', $deviceToken->getData('id'));
				$this->setData('user_ids', $id);
				$save = $this->__save();
			}else{

				$this->setData('user_ids', $id);
				$this->setData('token', $token['token']);
				$this->setData('api_key', $token['api_key']);
				$this->setData('api_secret', $token['api_secret']);
				$save = $this->__save();
			}
		
        }
    }
    public function deleteToken($id, $token) {
        $this->resetQuery();
        if($id !== null) {
            $this->delete(['user_ids' => $id]);
        }
        return true;
	}
	
    public function removeToken($id) {
        $getData = $this->getByColumn(['user_ids' => $id], 0, null, false);
		foreach($getData as $data) {
			$data->delete();
		}
	}
	public function getDeviceTokenById($id) {
		$this->resetQuery();
		$this->_select->where(["user_ids" => $id]);
		$this->_select->limit(1);
		$this->_select->order(['created_at' => 'desc']);
		$result = $this->getCollection();
		if ($result) {
			return $result[0];
		} else {
			return null;
		}
	}
    public function getUserDeviceToken($id) {
        $this->resetQuery();
        $this->_select->where(['user_ids' => $id]);
        $datas = $this->getCollection();
        $dataResults = $datas->toArray();
        $user_tokens = array();
        $data_tokens = array();
        foreach($dataResults as $i => $val) {
            if(!in_array($val['token'], $user_tokens, true)) {
            	array_push($user_tokens, $val['token']);
            	array_push($data_tokens, $val);
        	}
        }
        return $data_tokens;
	}
	public function getUserDeviceTokenByKey($id) {
        $this->resetQuery();
        $this->_select->where(['api_key' => $id]);
        
        $datas = $this->getCollection();
        $dataResults = $datas->toArray();
        $user_tokens = array();
        $data_tokens = array();
        foreach($dataResults as $i => $val) {
            if(!in_array($val['token'], $user_tokens, true)) {
            	array_push($user_tokens, $val['token']);
            	array_push($data_tokens, $val);
        	}
        }
        return $data_tokens;
	}
/*	public function getAllDeviceToken($id) {
        $this->resetQuery();
        $this->_select->where(['user_ids']);
        
        $datas = $this->getCollection();
        $dataResults = $datas->toArray();
		return $dataResults;
	}*/
}