<?php 
namespace Lns\Sb\Lib\Entity\Db;

class SocialLogin extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'social';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'user_id',
		'type',
		'social_id',
        'first_name',
		'last_name',
        'email',
		'image_url',
		'social_details',
		'created_at',
		'updated_at',
	];


	public function saveSocialAccount($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$savedUserId = $this->__save();
		return $savedUserId;
	} 

	/* public function getRoles(){
		return $this->getFinalResponse(20);
	} */
}

 