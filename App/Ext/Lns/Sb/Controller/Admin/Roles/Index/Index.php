<?php
namespace Lns\Sb\Controller\Admin\Roles\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Roles';

	public function run(){
		$this->requireLogin();
		$canView = $this->checkPermission('MANAGEROLE', \Lns\Sb\Lib\Permission::VIEW);
		if(!$canView){
			$this->_message->setMessage('Your are not allowed to view role listing.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>