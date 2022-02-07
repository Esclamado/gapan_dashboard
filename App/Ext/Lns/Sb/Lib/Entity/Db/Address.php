<?php 
namespace Lns\Sb\Lib\Entity\Db;

// use Zend\Db\TableGateway\TableGateway;

class Address extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'address';
	protected $primaryKey = 'id';
	
    const COLUMNS = [
		'id',
		'profile_id',
		'address',
		'city',
		'region',
		'zip_code',
		'state',
		'country_id',
		'created_at',
		'updated_at',
	]; 
	
	public function __construct(
		\Of\Http\Request $Request,
		$adapter=null
	){
		parent::__construct($Request,$adapter);
	}
	
	public function saveUserAddress($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$this->__save();
		
		return true;
	} 
    
}

