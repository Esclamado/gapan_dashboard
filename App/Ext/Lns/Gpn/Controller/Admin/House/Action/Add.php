<?php
namespace Lns\Gpn\Controller\Admin\House\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add new house/building';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGEHOUSE', \Lns\Sb\Lib\Permission::CREATE);
		if (!$canCreate) {
			$this->_message->setMessage('Your are not allowed to create new house/building.', 'danger');
			$this->_url->redirect('/house');
		}
		return parent::run();
	}
}
?>