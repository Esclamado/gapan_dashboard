<?php
namespace Lns\Sb\Controller\Admin\Users\Create;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Create Users';

	public function run(){
		$this->requireLogin();
		$canCreate = $this->checkPermission('MANAGEUSER', \Lns\Sb\Lib\Permission::CREATE);
		if(!$canCreate){
			$this->_message->setMessage('Your are not allowed to add user.', 'danger');
			$this->_url->redirect('/users');
		}
		return parent::run();
	}
}
?>