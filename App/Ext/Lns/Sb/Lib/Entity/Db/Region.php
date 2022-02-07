<?php 
namespace Lns\Sb\Lib\Entity\Db;


class Region extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const COLUMNS = [
		'id',
		'name',
		'code',
		'short_name',
	];
	

	protected $tablename = 'lib_regions';
	protected $primaryKey = 'id';

	public $_userProfile;
	public $_address;
	public $_contact;
	public $_attachments;
	public $_roles;
	protected $_session;
	protected $_validator;
	protected $_password;
	protected $result;

	public function __construct(
		\Of\Http\Request $Request,
		UserProfile $UserProfile,
		Address $Address,
		Contact $Contact,
		Attachments $Attachments,
		\Lns\Sb\Lib\ValidateField $ValidateField,
		\Lns\Sb\Lib\Password\Password $Password,
		Roles $Roles
	){
		$this->_userProfile = $UserProfile;
		$this->_address = $Address;
		$this->_contact = $Contact;
		$this->_attachments = $Attachments;
		$this->_roles = $Roles;
		$this->_validator = $ValidateField;
		$this->_password = $Password;
		parent::__construct($Request);
	}

	// public function getStateById($id){	
	// 	$mainTable = $this->getTableName();
	// 	$user = $this->getByColumn(['region_code' => $id], 1, null, false);
	// 	return $user->getData();
    // } 

    public function getRegions(){
		$this->setIsCache(true);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$regions = $this->getFinalResponse();

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $regions;
	}

	public function installData(){
		$mainTable = $this->getTableName();

		$query = "INSERT INTO `".$mainTable."` (`name`, `code`, `short_name`) VALUES
		('Region 1', 1, 'REG 1'),
		('Region 2', 2, 'REG 2'),
		('Region 3', 3, 'REG 3'),
		('Region 4-A', 4, 'REG 4A'),
		('Region 5', 5, 'REG 5'),
		('Region 6', 6, 'REG 6'),
		('Region 7', 7, 'REG 7'),
		('Region 8', 8, 'REG 8'),
		('Region 9', 9, 'REG 9'),
		('Region 10', 10, 'REG 10'),
		('Region 11', 11, 'REG 11'),
		('Region 12', 12, 'REG 12'),
		('Region 13', 16, 'REG 13'),
		('National Capital Region', 13, 'NCR'),
		('Cordillera Administrative Region', 14, 'CAR'),
		('Autonomous Region in Muslim Mindanao', 15, 'ARMM'),
		('Region 4-B', 17, 'REG 4B');";

		$datas = $this->_adapter->query(
			$query,
			\Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
		);
	}

}
