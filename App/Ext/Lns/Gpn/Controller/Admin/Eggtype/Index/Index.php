<?php
namespace Lns\Gpn\Controller\Admin\Eggtype\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Egg Type';

	public function run(){
        $this->requireLogin();
        $canView = $this->checkPermission('MANAGEEGGTYPE', \Lns\Sb\Lib\Permission::VIEW);
		if (!$canView) {
			$this->_message->setMessage('Your are not allowed to view egg types.', 'danger');
			$this->_url->redirect('');
		} 
		return parent::run();
	}
}
?>