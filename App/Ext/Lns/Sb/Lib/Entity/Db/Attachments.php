<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Attachments extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const PROFILE_PHOTO = 1;

	const COLUMNS = [
		'id',
		'uploader_id',
		'attachment_type',
		'filename',
		'filepath',
		'uploaded_at',
		'profile_id',
		'tablename',
		'primary_key'
	];
	
	protected $tablename = 'attachments';
	protected $primaryKey = 'id';
	
	public function saveAttachment($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$this->__save();
		
		return true;
	} 

	/* public function getRoles(){
		return $this->getFinalResponse(20);
	} */
}

 