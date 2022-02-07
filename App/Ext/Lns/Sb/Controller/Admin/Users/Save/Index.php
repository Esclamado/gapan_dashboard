<?php
namespace Lns\Sb\Controller\Admin\Users\Save;

use Lns\Sb\Lib\Userrole;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save User';
	protected $_users;
	protected $_userProfile;
	protected $_address;
	protected $_contact;
	protected $_validator;
	protected $_password;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Http\Request $Request,
		\Lns\Sb\Lib\Entity\Db\Users $Users,
		\Lns\Sb\Lib\ValidateField $ValidateField,
		\Lns\Sb\Lib\Password\Password $Password,
		\Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
		\Lns\Sb\Lib\Entity\Db\Address $Address,
		\Lns\Sb\Lib\Entity\Db\Contact $Contact
	){
		parent::__construct($Url,$Message,$Session);
		$this->_users= $Users;
		$this->_userProfile = $UserProfile;
		$this->_address = $Address;
		$this->_contact = $Contact;
		$this->_validator = $ValidateField;
		$this->_password = $Password;
	}	

	public function run(){
		$this->requireLogin();

		$id = $this->getPost('id');
		
		$hashedPassword = null;
		$postParam = $this->getPost();
		$_SESSION['formfield'] = $postParam;
		
		/*$isValidEmail = $this->_validator->validateEmail($postParam['email']);
		if(!$isValidEmail){
			$this->_message->setMessage('Please enter a valid email address.', 'danger');
			$this->_url->redirect('/users/create');
		}*/
		if($postParam['password']){
			$hashedPassword = $this->_password->setPassword($postParam['password'])->getHash();
		}

		if(isset($postParam['birthdate'])){
			if(empty($postParam['birthdate'])){
				$postParam['birthdate'] = null;
			}
		}

		$_email = $this->_users->getByColumn(['email' => $postParam['email']], 1);

		if($id){
			$canUpdate = $this->checkPermission('MANAGEUSER', \Lns\Sb\Lib\Permission::UPDATE);
			if(!$canUpdate){
				$this->_message->setMessage('Your are not allowed to edit user.', 'danger');
				$this->_url->redirect('/users');
			}
			$user = $this->_users->getByColumn(['id' => $id]);
			if(!$user){
				$this->_message->setMessage('User not found.', 'danger');
				$this->_url->redirect('/users');
			} else {
				if($_email){
					if($_email->getData('email') != $user->getData('email')) {
						$this->_message->setMessage('Email already exists.', 'danger');
						$this->_url->redirect('/users/edit/?userId=' + $id); 
					}
				}
			}
		} else {
			$canCreate = $this->checkPermission('MANAGEUSER', \Lns\Sb\Lib\Permission::CREATE);
			if(!$canCreate){
				$this->_message->setMessage('Your are not allowed to add user.', 'danger');
				$this->_url->redirect('/users');
			}
			$user = $this->_users;
			if($_email){
				$this->_message->setMessage('Email already exists.', 'danger');
				$this->_url->redirect('/users/create'); 
			}
			if(!$hashedPassword){
				$this->_message->setMessage('Please enter a user\'s password.', 'danger');
				$this->_url->redirect('/users/create');
			}
			unset($postParam['id']);
		}

		if($hashedPassword){
			$postParam['password'] = $hashedPassword;
		} else {
			unset($postParam['password']);
		}
		$userId = $user->setDatas($postParam)->__save();
		
		if($userId > 0){
			unset($postParam['id']);
			$postParam['user_id'] = $userId;

			$_userProfile = $this->_userProfile->getByColumn(['user_id' => $userId], 1);
			$userProfileId = null;

			if($_userProfile){
				$_userProfile->setDatas($postParam)->__save();
				$userProfileId = $_userProfile->getData('id');
			} else {
				$userProfileId = $this->_userProfile->setDatas($postParam)->__save();
			}

			if($userProfileId > 0) {
				$postParam['profile_id'] = $userProfileId;
				$_address = $this->_address->getByColumn(['profile_id' => $userProfileId], 1);
				if($_address){
					$_address->setDatas($postParam)->__save();
				} else {
					$this->_address->setDatas($postParam)->__save();
				}

				$_contact = $this->_contact->getByColumn(['profile_id' =>  $userProfileId], 1);
				if($_contact){
					$_contact->setDatas($postParam)->__save();
				} else {
					$this->_contact->setDatas($postParam)->__save();
				}
			}
			
			unset($_SESSION['formfield']);

			$this->_message->setMessage('User successfully saved.');
			$this->_url->redirect('users/edit/?userId=' . $userId);
		}
	}
}
?>