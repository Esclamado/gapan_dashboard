<?php
namespace Lns\Sb\Controller\Admin\Roles\Action;

class Edit extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Edit Role';

	public function run(){
		$this->requireLogin();
		$canUpdate = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage('Your are not allowed to update role.', 'danger');
			$this->_url->redirect('/roles');
		}
		return parent::run();
	}
}
?>