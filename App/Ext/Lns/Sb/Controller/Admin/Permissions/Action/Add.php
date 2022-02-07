<?php
namespace Lns\Sb\Controller\Admin\Permissions\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add Permissions';

	public function run(){
		$this->requireLogin();
		$canCreate = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::CREATE);
		if(!$canCreate){
			$this->_message->setMessage('Your are not allowed to create new permission.', 'danger');
			$this->_url->redirect('/permissions');
		}
		return parent::run();
	}
}
?>