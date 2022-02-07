<?php 
namespace Lns\Sb\Lib\Entity\Db;

use Lns\Sb\Lib\Entity\Db\UserProfile;
use Lns\Sb\Lib\Entity\Db\Contact;
use Lns\Sb\Lib\Entity\Db\Address;
use Lns\Sb\Lib\Entity\Db\Attachments;
use Lns\Sb\Lib\AttachmentType;
use Lns\Gpn\Lib\Entity\Db\CustomerType;

class Users extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const SUPER_ADMIN = 1;
	const ADMIN = 2;

	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	const COLUMNS = [
		'id',
		'password',
		'real_password',
		'email',
		'username',
		'status',
		'created_at',
		'update_at',
		'created_by',
		'update_by',
		'user_role_id',
		'customer_type_id',
		'last_login',
		'archive',

	];

	protected $tablename = 'user';
	protected $primaryKey = 'id';
	protected $_siteConfig;
	protected $_mailer;
	protected $_activation;

	public $_userProfile;
	public $_roles;
	protected $_session;
	protected $_validator;
	protected $_password;
	protected $result;
	public $_address;
	public $_contact;
	public $_customer_type;

	public function __construct(
		\Of\Http\Request $Request
	){
		parent::__construct($Request);
		$this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
		$this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
		$this->_contact = $this->_di->get('Lns\Sb\Lib\Entity\Db\Contact');
		$this->_customer_type = $this->_di->get('Lns\Gpn\Lib\Entity\Db\CustomerType');
		$this->_roles = $this->_di->get('Lns\Sb\Lib\Entity\Db\Roles');
		$this->_mailer = $this->_di->get('\Of\Std\Mailer');
		$this->_activation = $this->_di->get('Lns\Sb\Lib\Entity\Db\Activation');
	}

	public function saveUserInfo($user_id = NULL){

		$this->result['error'] = 1;
		$postedFormData = $this->_request->getParam();
		
		if($user_id){
			$user = $this->getByColumn(['id' => $postedFormData['id']]);
			if(!$user){
				$this->result['message'] = 'User not found.';
				die;
			}else{
				$_password = $this->_password->generate(); 
				$hashedPassword = $this->_password->setPassword($_password)->getHash();
				$postedFormData['password'] = $hashedPassword;
			}
		}else{
			if($this->getByColumn(['email' => $postedFormData['email']])){
				$this->result['message'] = 'Email already exists.';
				die;
				/* $this->_message->setMessage('Email already exists.', 'danger');
				$this->_url->redirectTo($this->_request->getUrlReferer()); */
			}
		}

		/** EMAIL IS VALIDATED */
		if($this->_validator->validateEmail($postedFormData['email'])){
			
			if($postedFormData['password']){
				$hashedPassword = $this->_password->setPassword($postedFormData['password'])->getHash();
				$postedFormData['password'] = $hashedPassword;
			}

			$postedFormData['user_id'] = $this->saveEntity($postedFormData);
			if($postedFormData['user_id']){
				unset($postedFormData['id']);

				$userProfile = $this->_userProfile;
				$address = $this->_address;
				$contact = $this->_contact;
				if($user_id){
					$userProfile = $this->getUserProfile();
					$address = $this->getAddress();
					$contact = $this->getContact();
				}
				/** insert here: send raw generated password to new user's email */
				$userProfile->saveEntity($postedFormData);
				$address->saveEntity($postedFormData);
				$contact->saveEntity($postedFormData);

				$this->result['error'] = 0;
				$this->result['message'] = 'User successfully saved.';
			} else{
				$this->result['message'] = 'Unable to save data.';
			}
		} else{
			$this->result['message'] = 'Email is invalid.';
		}
		
		return $this->result;

	}

	public function getLoggedInUser(){
		$this->_session = $this->_di->get('\Lns\Sb\Lib\Session\Session');
		$userId = $this->_session->getLogedInUser();
		$user = $this->getByColumn(['id' => $userId]);

		return $user;
	}
	
	public function deleteUser($userId){
		$user = $this->getByColumn(['id' => $userId]);
		if($user){
			return $this->delete([$this->primaryKey => $userId]);
		} else{
			return false;
		}
	}

	public function getUsers(){
		$this->setOrderBy('created_at');
		$this->setIsCache(true);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$users = $this->getFinalResponse();

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $users;
	}

	public function getUserProfile($returnArray = false){
		if($this->getData('id')){
			$model = $this->_userProfile->getByColumn(['user_id' => $this->getData('id')]);
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
	}

	public function getRole($returnArray = false){
		if($this->getData('id')){
			$model = $this->_roles->getByColumn(['id' => $this->getData('user_role_id')]);
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
	}
	public function sendCredential($id, $siteConfig, $template_code){
		$this->_siteConfig = $siteConfig;
		$d = $this->getByColumn(['id' => $id], 1);
		$result = [
		   'error' => 1,
		   'message' => ''
		];
		if($d){
		   $email = $d->getData('email');
		   $_mailTemplate = $this->_di->get('Lns\Sb\Lib\Entity\Db\MailTemplate');
		   $mailTpl = $_mailTemplate->getByColumn(['template_code' => $template_code], 1);
		   $activations = $this->_activation->getByColumn(['user_id'=>$id], 1);
		   $activationcode = $activations->getData('activation_code');
		  
		   if($mailTpl){
			//   $fullname = $d->getData('fullname');
			  $messageBody = $mailTpl->getData('template');
			  $messageBody = str_replace('{{delegate_name}}', $email, $messageBody);
			  $messageBody = str_replace('{{email}}', $email, $messageBody);
			  $messageBody = str_replace('{{password}}', $activationcode, $messageBody);
			  ob_start();
				  include(ROOT.DS.'App/Ext/Lns/Sb/View/Template/mail/email_template.phtml');
				  $tplHtml = ob_get_contents();
			  ob_end_clean();
			//   $mailer = $this->_di->make('\Of\Std\Mailer');
			$this->_mailer->addAddress($email, '', 'To')
			  ->setFrom($mailTpl->getData('email'), $mailTpl->getData('from_name'))
			  ->setSubject($mailTpl->getData('subject'))
			  ->setMessage($tplHtml)
			  ->send();
			  $result['error'] = 0;
			  $result['message'] = 'Email Sent';
		   } else {
			  $result['message'] = 'Email Template Not Found';
		   }
		} else {
		   $result['message'] = 'Email Not Found';
		}
		return $result;
	}

	public function sendEmailCredential($id, $siteConfig, $template_code, $password)
	{
		$this->_siteConfig = $siteConfig;
		$d = $this->getByColumn(['id' => $id], 1);
		$result = [
			'error' => 1,
			'message' => ''
		];
		if ($d) {
			$email = $d->getData('email');
			$_mailTemplate = $this->_di->get('Lns\Sb\Lib\Entity\Db\MailTemplate');
			$mailTpl = $_mailTemplate->getByColumn(['template_code' => $template_code], 1);
			$activations = $this->_activation->getByColumn(['user_id' => $id], 1);
			/* $activationcode = $activations->getData('activation_code'); */

			if ($mailTpl) {
				//   $fullname = $d->getData('fullname');
				$messageBody = $mailTpl->getData('template');
				$messageBody = str_replace('{{delegate_name}}', $email, $messageBody);
				$messageBody = str_replace('{{email}}', $email, $messageBody);
				$messageBody = str_replace('{{password}}', $password, $messageBody);
				ob_start();
				include(ROOT . DS . 'App/Ext/Lns/Sb/View/Template/mail/email_template.phtml');
				$tplHtml = ob_get_contents();
				ob_end_clean();
				//   $mailer = $this->_di->make('\Of\Std\Mailer');
				$this->_mailer->addAddress($email, '', 'To')
					->setFrom($mailTpl->getData('email'), $mailTpl->getData('from_name'))
					->setSubject($mailTpl->getData('subject'))
					->setMessage($tplHtml)
					->send();
				$result['error'] = 0;
				$result['message'] = 'Email Sent';
			} else {
				$result['message'] = 'Email Template Not Found';
			}
		} else {
			$result['message'] = 'Email Not Found';
		}
		return $result;
	}

	public function getCompleteUserInfo($returnArray = false){

		if($this->getData('id')){
			$_userProfile = $this->getUserProfile();
			return [
				'user' => $returnArray ? $this->getData() : $this,
				'user_profile' => $returnArray ? $_userProfile->getData() : $_userProfile,
				'address' => $_userProfile->getAddress($returnArray),
				'contact' => $_userProfile->getContact($returnArray),
				'role' => $this->getRole($returnArray),
				'profile_picture' => $_userProfile->getProfilePicture($returnArray),
			];
		} else{
			return null;
		}
	}

	public function getUserById($userId){	

		$mainTable = $this->getTableName();
		$this->__join('profile_', $mainTable.'.id', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
 
		$this->__join('address_', $mainTable.'.id', 'address', '', 'left', 'profile_id', Address::COLUMNS);
 
		$this->__join('contact_', $mainTable.'.id', 'contact', '', 'left', 'profile_id', Contact::COLUMNS);
 
		$this->__join('attachments_', $mainTable.'.id', 'attachments', '', 'left', 'profile_id', Attachments::COLUMNS);
 
 		$user = $this->getByColumn([$mainTable.'.id' => $userId], 1, null, false);
 		if($user){
 			return $user->getData();
 		}
	} 

	public function saveUser($userData){

		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$savedUserId = $this->__save();
		return $savedUserId;
	}

	public function getUsersByRole($role_id, $limit = NULL){
		$this->resetQuery();
		$this->_select->where("user_role_id = $role_id");
		if ($limit) {
			$this->_select->limit($limit);
		}
		return $this->getCollection();
	}
	public function getRoleByUserId($id) {
		$user = $this->getByColumn(['id' => $id], 1);
		$role = $this->_roles->getByColumn(['id' => $user->getData('user_role_id')], 1);
		return $role->getData('name');
	}
	public function searchUser($param, $userId)
	{
		$this->resetQuery();
		$mainTable = $this->getTableName();
		$userprofile = $this->_userProfile->getTableName();
		$limit = 1;
		$where = "(`" . $mainTable . "`.`id` != " . $userId;
		$where .= ") AND (`" . $mainTable . "`.`user_role_id` != 1) AND (`" . $mainTable . "`.`user_role_id` != 3)";
		$this->_select->where($where);


		if (isset($param['limit'])) {
			$limit = $param['limit'];
		}
		$this->__join('roles_', $mainTable . '.user_role_id', 'roles', '', 'left', 'id', Roles::COLUMNS);
		$this->__join('profile_', $mainTable . '.id', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
		$this->__join('address_', $mainTable . '.id', 'address', '', 'left', 'profile_id', Address::COLUMNS);
		$this->__join('contact_', $mainTable . '.id', 'contact', '', 'left', 'profile_id', Contact::COLUMNS);
		
		if (isset($param['order_by_column']) && isset($param['order_by'])) {
			$this->_select->order([$param['order_by_column'] => $param['order_by']]);
		}
		if (isset($param['from']) && isset($param['to'])) {
			$where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
			$this->_select->where($where);
		}
		if (isset($param['search'])) {
			$where = "(`" . $mainTable . "`.`id` != " . $userId;
			$where .= ") AND ((`" . $mainTable . "`.`email` LIKE '%". $param['search'] . "%' ) OR (`" . $mainTable . "`.`username` LIKE '%" . $param['search'] . "%' ) OR (`" . $mainTable . "`.`last_login` LIKE '%" . $param['search'] . "%' )) OR ";
			$where .= "((`" . $userprofile . "`.`first_name` LIKE '%" . $param['search'] . "%' ) OR (`" . $userprofile . "`.`last_name` LIKE '%" . $param['search'] . "%'))";
			$this->_select->where($where);
		}
		if (isset($param['role_id'])) {
			$where = $this->likeQuery(['id'], $this->escape($param['role_id']), 'roles', true);
			$this->_select->where($where);
		}
		return $this->getFinalResponse($limit);
	}
	public function getCustomercount()
	{
		$this->resetQuery();
		$mainTable = $this->getTableName();
		$this->_select->setComlumn(new \Zend\Db\Sql\Expression("COUNT(`" . $mainTable . "`.`id`)"), 'total_users');
		$this->_select->where("`" . $mainTable . "`.`user_role_id` = 3");
		$this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if ($count) {
            $totalCount = $count->getData('total_users');
        }
        return $totalCount;
	}
	public function getStaffcount()
	{
		$mainTable = $this->getTableName();
		$this->resetQuery();
		$where = "`" . $mainTable . "`.`user_role_id` != 1 AND `" . $mainTable . "`.`user_role_id` != 3";
		$this->_select->where($where);
		$result = $this->getCollection();
		$getCount = count($result);
		return $getCount;
	}
	public function getList($param)
	{
		$mainTable = $this->getTableName();
		$userprofile = $this->_userProfile->getTableName();
		$contact = $this->_contact->getTableName();
		$customer_type = $this->_customer_type->getTableName();

		$where = "user_role_id = 3";
		$this->_select->where($where);
		$this->__join('profile_', $mainTable . '.id', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
		$this->__join('address_', $mainTable . '.id', 'address', '', 'left', 'profile_id', Address::COLUMNS);
		$this->__join('contact_', $mainTable . '.id', 'contact', '', 'left', 'profile_id', Contact::COLUMNS);
		$this->__join('customer_type_', $mainTable . '.customer_type_id', 'customer_type', '', 'left', 'id', CustomerType::COLUMNS);
		
		$limit = 1;
		if (isset($param['limit'])) {
			$limit = $param['limit'];
		}
		if (isset($param['search'])) {
			$where = "((`" . $userprofile . "`.`first_name` LIKE '%" . $param['search'] . "%' ) OR (`" . $userprofile . "`.`last_name` LIKE '%" . $param['search'] . "%'))";
			$this->_select->where($where);
		}
		if (isset($param['order_by_column']) && isset($param['order_by'])) {
			$order_by_column = $mainTable. ".id";
			if ($param['order_by_column'] == 'id' || $param['order_by_column'] == 'created_at') {
				$order_by_column = $mainTable. "." . $param['order_by_column'];
			} else if ($param['order_by_column'] == 'first_name') {
				$order_by_column = $userprofile. "." . $param['order_by_column'];
			} else if ($param['order_by_column'] == 'number') {
				$order_by_column = $contact. "." . $param['order_by_column'];
			} else if ($param['order_by_column'] == 'customer_type') {
				$order_by_column = $customer_type. "." . 'type';
			}
			$this->_select->order([$order_by_column => $param['order_by']]);
        }
		return $this->getFinalResponse($limit);
	}
	public function getUsercount()
	{
		/* $this->resetQuery();
		$result = $this->getCollection();
		$getCount = count($result);
		return $getCount; */
		$this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
		
		/* need to remove group if any to prevent count mistake */
		$group = $this->_select->getGroup();
		$this->_select->setGroup(null);
		$c = $this->__getQuery($this->getLastSqlQuery());

		$count = 0;
		if ($c) {
			$count = $c->getData('count');
		}
		return $count;
	}
	public function getLatestActivity($user_role_id = null) {
		if ($user_role_id) {
			$this->_select->where('user_role_id = ' . $user_role_id);
		} else {
			$this->_select->where('user_role_id >= 4');
		}
		$this->_select->order(['update_at' => 'desc']);
		
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
	}
}

/** 
 * 	REUSABLE FUNCTIONS FOR CRUD
 */

/* public function deleteUser($userId){
	$user = $this->getByColumn(['id' => $userId]);
	if($user){
		return $this->delete([$this->primaryKey => $userId]);
	} else{
		return false;
	}
} */

/* public function getUsers(){
	$this->setOrderBy('created_at');
	$this->setIsCache(true);
	$this->setCacheMaxLifeTime(60*60*24*30);
	$users = $this->getFinalResponse(5);

	// need to reset cache for the next database call
	$this->setIsCache(false)->setCacheMaxLifeTime(0);

	return $users;
} */



/** 
 * 	END: REUSABLE FUNCTIONS FOR CRUD
 */
