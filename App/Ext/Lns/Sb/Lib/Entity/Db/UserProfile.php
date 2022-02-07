<?php 
namespace Lns\Sb\Lib\Entity\Db;

class UserProfile extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const COLUMNS = [
		'id',
		'user_id',
		'salutation',
		'first_name',
		'last_name',
		'suffix',
		'position',
		'about',
		'information',
		'company',
		'push_notification',
		'birthdate',
		'gender',
		'profile_pic',
		'unique_id',
		'lon',
		'lat',
		'location',
		'created_at',
		'updated_at',
	];
	
	protected $tablename = 'user_profile';
	protected $primaryKey = 'id';
	
	public $_address;
	public $_contact;
	public $_attachments;
	protected $_password;
	
	public function __construct(
		\Of\Http\Request $Request,
		$adapter=null
	){
		parent::__construct($Request,$adapter);
		$this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
		$this->_contact = $this->_di->get('Lns\Sb\Lib\Entity\Db\Contact');
		$this->_attachments = $this->_di->get('Lns\Sb\Lib\Entity\Db\Attachments');
		$this->_password = $this->_di->get('\Lns\Sb\Lib\Password\Password');
	}
	
	public function generateUniqueId($digits = 6){
		$uniqueId = $this->_password->generate($digits);
		$isExist = $this->getByColumn(['unique_id' => $uniqueId]);
		if($isExist != null){
			return $this->generateUniqueId($digits);
		} else {
			return strtoupper($uniqueId);
		}
	}
	
	public function getTypeCount($type){
		$this->resetQuery();
		$this->_select->where(['type' => $type]);
		$datas = $this->getCollection();
		return $datas->count();
	}

	public function getFullName(){
		$fullname = null;
		if($this->getData('id')){
			$fullname = $this->getData('salutation') ? $this->getData('salutation') . ' ' : '';
			$fullname .= $this->getData('first_name') ? $this->getData('first_name') . ' ' : '';
			$fullname .= $this->getData('last_name') ? $this->getData('last_name') : '';
			$fullname .= $this->getData('suffix') ? ' ' . $this->getData('suffix') : '';
		}

		return $fullname;
	}

	public function getAllUser() {
        $this->resetQuery();
        $this->_select->where(['id']);
        
        $datas = $this->getCollection();
        $dataResults = $datas->toArray();
		return $dataResults;
	}
	
	public function saveUserProfile($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$savedUserId = $this->__save();
		return $savedUserId;
	} 

	public function getAddress($returnArray = false){
		if($this->getData('id')){
			$model = $this->_address->getByColumn(['profile_id' => $this->getData('id')]);
			if($model){
				if($returnArray){
					return $model->getData();
				} else{
					return $model;
				}
			} else{
				return null;
			}
		} else{
			return null;
		}

		/* PLEASE DON'T DELETE: CODE BACK UP */
		/* $returnModel = $this->_address;
		if($this->getData('id')){
			$model = $this->_address->getByColumn(['profile_id' => $this->getData('id')]);
			if($model){
				$returnModel = $model;
			}
		}

		if($returnArray){
			return $returnModel->getData();
		} else{
			return $returnModel;
		} */
	}
	
	public function getContact($returnArray = false){
		if($this->getData('id')){
			$this->_contact->_select->where(['profile_id' => $this->getData('id')]);
			$model = $this->_contact->getCollection();
			if($model){
				$_return = [];
				foreach($model as $r){
					if($returnArray){
						$_return[] = $r->getData();
					} else{
						$_return[] = $r;
					}
				}
				return $_return;
			} else {
				return null;
			}
		} else{
			return null;
		}

		/* PLEASE DON'T DELETE: CODE BACK UP */
        /* $returnModel = $this->_contact;
		if($this->getData('id')){
			$this->_contact->_select->where(['profile_id' => $this->getData('id')]);
			$model = $this->_contact->getCollection();
			if($model){
				$returnModel = $model;
			}
		}

		
		if($returnArray){
			if(is_array($returnModel)){
				$_return = [];
				foreach($returnModel as $r){
					$_return[] = $r->getData();
				}
				return $_return;
			} else{
				return $returnModel->getData();
			}
		} else{
			return $returnModel;
		} */
	}

	public function getProfilePicture($returnArray = false){
		if($this->getData('id')){
			$model = $this->_attachments->getByColumn(['profile_id' => $this->getData('id')]);
			if($model){
				if($returnArray){
					return $model->getData();
				} else{
					return $model;
				}
			} else{
				return null;
			}
		} else{
			return null;
		}

		/* PLEASE DON'T DELETE: CODE BACK UP */
		/* $returnModel = $this->_attachments;
		if($this->getData('id')){
			$model = $this->_attachments->getByColumn(['profile_id' => $this->getData('id')]);
			if($model){
				$returnModel = $model;
			}
		}

		if($returnArray){
			return $returnModel->getData();
		} else{
			return $returnModel;
		} */
	}
	public function getFullNameById($id) {
		$fullname = null;
		if ($id) {
			$user = $this->getByColumn(['user_id' => $id], 1);
			$fullname = $user->getData('salutation') ? $user->getData('salutation') . ' ' : '';
			$fullname .= $user->getData('first_name') ? $user->getData('first_name') . ' ' : '';
			$fullname .= $user->getData('last_name') ? $user->getData('last_name') : '';
			$fullname .= $user->getData('suffix') ? ' ' . $user->getData('suffix') : '';
		}
		return $fullname;
	}
}
