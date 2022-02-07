<?php
namespace Lns\Gpn\Controller\Admin\House\Action;

class Edit extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Update house/building';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGEHOUSE', \Lns\Sb\Lib\Permission::UPDATE);
		if (!$canCreate) {
			$this->_message->setMessage('Your are not allowed to update house/building.', 'danger');
			$this->_url->redirect('/house');
		}
		return parent::run();
	}
}
?>