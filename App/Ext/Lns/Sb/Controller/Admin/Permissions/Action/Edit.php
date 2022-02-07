<?php
namespace Lns\Sb\Controller\Admin\Permissions\Action;

class Edit extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Edit Permission';

	public function run(){
		$this->requireLogin();
		$canUpdate = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage('Your are not allowed to update permission.', 'danger');
			$this->_url->redirect('/permissions');
		}
		return parent::run();
	}
}
?>