<?php
namespace Lns\Sb\Controller\Admin\Permissions\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Permissions';

	public function run(){
		$this->requireLogin();
		$canView = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::VIEW);
		if(!$canView){
			$this->_message->setMessage('Your are not allowed to view permission.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>