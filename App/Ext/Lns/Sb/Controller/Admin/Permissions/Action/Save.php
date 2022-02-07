<?php
namespace Lns\Sb\Controller\Admin\Permissions\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Permissions';

	protected $_permissions;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Permissions $Permissions
	){
		parent::__construct($Url, $Message, $Session);
		$this->_permission = $Permissions;
	}

	public function run(){
		$this->requireLogin();

		$id = (int)$this->getPost('id');
		$name = $this->getPost('name');
		$code = $this->getPost('code');
		$description = $this->getPost('description');

		$msg = '';
		$msgtype = 'danger';

		if($name){
			$entity = null;
			if($id > 0){
				$canUpdate = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::UPDATE);
				if(!$canUpdate){
					$this->_message->setMessage('Your are not allowed to update permission.', 'danger');
					$this->_url->redirect('/permissions/action/edit/id/' . $id);
				}
				$permission = $this->_permission->getByColumn(['id'=>$id], 1);
				if($permission){
					$entity = $permission;
					$msg = 'Permission successfully updated.';
					$msgtype = 'success';
				} else {
					$msg = 'Cannot find the permission you are trying to update.';
					$msgtype = 'danger';
				}
			} else {
				$canCreate = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::CREATE);
				if(!$canCreate){
					$this->_message->setMessage('Your are not allowed to create new permission.', 'danger');
					$this->_url->redirect('/permissions');
				}
				$entity = $this->_permission;
				$msg = 'Permission successfully saved';
				$msgtype = 'success';
			}

			if($entity){
				$entity->setData('name', $name);
				$entity->setData('code', $code);
				$entity->setData('description', $description);
				
				$save = $entity->__save();
				if(!$save){
					$msg = 'Cannot save the permission try again later.';
					$msgtype = 'danger';

					$redirectTo = '/permissions';
				} else {
					$redirectTo = '/permissions/action/edit/id/' . $save;
				}
			}
		}

		$this->_message->setMessage($msg, $msgtype);
		$this->_url->redirect($redirectTo);
	}
}
?>