<?php
namespace Lns\Sb\Lib;

class Permission {

	const CREATE = 'create';
	const READ = 'read';
	const UPDATE = 'update';
	const DELETE = 'delete';
	const VIEW = 'view';

	protected $_session;
	protected $_permission;
	protected $_users;

	protected $permission;



	public function __construct(
		\Lns\Sb\Lib\Entity\Db\Users $Users,
		\Lns\Sb\Lib\Entity\Db\Permission $Permission
	){
		$this->_permission = $Permission;
		$this->_users = $Users;
	}

	public function setSession($Session){
		$this->_session = $Session;
		return $this;
	}

	public function check($permissionCode, $type, $user=null){
		return $this->getUserPermission($permissionCode, $type, $user);
		
	}

	protected function getUserPermission($permissionCode, $type, $user) {
		$userId = $this->_session->getLogedInUser();
		if(!$user){
			$user = $this->_users->getByColumn(['id' => $userId]);
		}

		$role = $user->getData('user_role_id');

		if($role == 1){
			return true;
		} else {
			$maxLiftime = 60 * 60;
			$this->_permission->setIsCache(true)->setCacheMaxLifeTime($maxLiftime);
			$permission = $this->_permission->getByColumn([
				'role_id' => $role,
				'permissions_code' => $permissionCode,
				$type => 1
			], 1);

			if($permission){
				return true;
			} else {
				return false;
			}
		}
	}
}

?>