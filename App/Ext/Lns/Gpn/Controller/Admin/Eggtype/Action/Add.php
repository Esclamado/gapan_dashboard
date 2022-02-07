<?php
namespace Lns\Gpn\Controller\Admin\Eggtype\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add Egg Type';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGEEGGTYPE', \Lns\Sb\Lib\Permission::CREATE);
		if (!$canCreate) {
			$this->_message->setMessage('Your are not allowed to create new egg type.', 'danger');
			$this->_url->redirect('/cms');
		}
		return parent::run();
	}
}
?>