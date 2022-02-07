<?php
namespace Lns\Sb\Controller\Admin\Roles\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add Role';

	public function run(){
		$this->requireLogin();
		$canCreate = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::CREATE);
		if(!$canCreate){
			$this->_message->setMessage('Your are not allowed to create new role.', 'danger');
			$this->_url->redirect('/roles');
		}
		return parent::run();
	}
}
?>