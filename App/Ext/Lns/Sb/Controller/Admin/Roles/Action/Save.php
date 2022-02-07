<?php
namespace Lns\Sb\Controller\Admin\Roles\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Role';
	protected $_roles;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Roles $Roles
	){
		parent::__construct($Url, $Message, $Session);
		$this->_roles = $Roles;
	}

	public function run(){
		$this->requireLogin();

		if($this->getPost('save')){
			$id = (int)$this->getPost('id');
			$name = $this->getPost('name');
			$description = $this->getPost('description');

			$isEdit = false;

			if($id > 0){
				$canUpdate = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::UPDATE);
				if($canUpdate){
					$isEdit = true;
					if($id > 1){
						$data = $this->_roles->getByColumn(['id' => $id], 1);
						$data->setData('name', $name)
						->setData('description', $description)
						->__save();
					} else {
						$this->_message->setMessage('Editing super admin role is not allowed.');
						$this->_url->redirect('/roles/action/edit/id/' . $id);
					}
				} else {
					$this->_message->setMessage('Your are not allowed to update role.', 'danger');
					$this->_url->redirect('/roles/action/edit/id/' . $id);
				}
			} else {
				$canCreate = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::CREATE);
				if($canCreate){
					$id = $this->_roles->setData('name', $name)
					->setData('description', $description)
					->__save();
					$data = $this->_roles->getByColumn(['id' => $id], 1);
				} else {
					$this->_message->setMessage('Your are not allowed to create new role.', 'danger');
					$this->_url->redirect('/roles');
				}
			}

			if($id > 0){
				$this->_message->setMessage('Role successfully saved.');
				$this->_url->redirect('/roles/action/edit/id/' . $id);
			} else {
				$this->_message->setMessage('Email is empty.', 'danger');
			}
		}
		$this->_url->redirect('/roles');

		die;
	}
}
?>