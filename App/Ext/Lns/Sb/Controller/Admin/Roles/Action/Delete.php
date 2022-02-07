<?php
namespace Lns\Sb\Controller\Admin\Roles\Action;

class Delete extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Delete Role';
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

		$canDelete = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::DELETE);
		if(!$canDelete){
			$this->_message->setMessage('Your are not allowed to delete role.', 'danger');
			$this->_url->redirect('/roles');
		}

		$id = $this->getPost('id');
		$this->_roles->deleteEntityByPk($id);

		$role = $this->_roles->getByColumn(['id' => $id], 1);

		if($role) {
			$this->_message->setMessage('Cannot delete role, please try again.', 'danger');
		} else {
			$this->_message->setMessage('Role successfully deleted.');
		}
		$this->_url->redirect('/roles');
	}
}
?>