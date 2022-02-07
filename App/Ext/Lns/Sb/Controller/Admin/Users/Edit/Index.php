<?php
namespace Lns\Sb\Controller\Admin\Users\Edit;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Edit User';

	public function run(){
		$this->requireLogin();
		$canUpdate = $this->checkPermission('MANAGEUSER', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage('Your are not allowed to edit user.', 'danger');
			$this->_url->redirect('/users');
		}
		return parent::run();
	}
}
?>