<?php 
namespace Lns\Sb\Lib\Entity\Db;

// use Zend\Db\TableGateway\TableGateway;

class Contact extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const DEFAULT = 'mobile';
	const HOME = 'home';
	const OFFICE = 'office';

	const COLUMNS = [
		'id',
		'profile_id',
		'number',
		'type',
	];

	protected $tablename = 'contact';
	protected $primaryKey = 'id';
	
	public function __construct(
		\Of\Http\Request $Request,
		$adapter=null
	){
		parent::__construct($Request,$adapter);
	}
	
	public function saveUserContact($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$this->__save();
		
		return true;
	}
    
}

 