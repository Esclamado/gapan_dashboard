<?php
namespace Lns\Sb\Controller\Admin\Roles\Permission;

class Assign extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Assign Role Permission';
	protected $_roles;
	protected $_permission;
	protected $_permissions;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Roles $Roles,
		\Lns\Sb\Lib\Entity\Db\Permission $Permission,
		\Lns\Sb\Lib\Entity\Db\Permissions $Permissions
	){
		parent::__construct($Url,$Message,$Session);
		$this->_permission = $Permission;
		$this->_permissions = $Permissions;
		$this->_roles = $Roles;
	}

	public function run(){
		$this->requireLogin();

		$canUpdate = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage('Your are not allowed to assign role permission.', 'danger');
			$this->_url->redirect('/roles');
		}

		$this->savePermission();

		return parent::run();
	}

	protected function savePermission(){
		$roleId = (int)$this->getPost('role_id');
		if($roleId > 1){
			$role = $this->_roles->getByColumn(['id' => $roleId], 1);

			if($role){
				$this->_permission->deleteAllEntityByAttributes(['role_id' => $roleId]);

				$permissions = $this->getPost('permission');

				foreach($permissions as $p){
					$this->_permission->setData('role_id', $roleId);
					$this->_permission->setData('permissions_id', $p['permissions_id']);

					$permissions = $this->_permissions->getByColumn(['id' => $p['permissions_id']], 1, null, true);
					$_c = 0;
					if($permissions){
						$_c = $permissions->getData('code');
						$this->_permission->setData('permissions_code', $_c);
					}
					

					$c = 0;
					if(isset($p['create'])){
						$c = (int)$p['create'];
					}
					$this->_permission->setData('create', $c);

					$r = 0;
					if(isset($p['read'])){
						$r = (int)$p['read'];
					}
					$this->_permission->setData('read', $r);

					$u = 0;
					if(isset($p['update'])){
						$u = (int)$p['update'];
					}
					$this->_permission->setData('update', $u);

					$d = 0;
					if(isset($p['delete'])){
						$d = (int)$p['delete'];
					}
					$this->_permission->setData('delete', $d);

					$v = 0;
					if(isset($p['view'])){
						$v = (int)$p['view'];
					}
					$this->_permission->setData('view', $v);
					$this->_permission->__save();
				}

				$this->_message->setMessage('Permission successfully assigned.');
				$this->_url->redirect('/roles/permission/assign/id/' . $roleId);
			} else {
				$this->_message->setMessage('You are trying to assign permission to a role that is not exists.');
				$this->_url->redirect('/roles');
			}
		}
	}
}
?>