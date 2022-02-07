<?php
namespace Lns\Gpn\Controller\Admin\FeedsandMedicine\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add new monthly record';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGEFEEDSANDMEDICINE', \Lns\Sb\Lib\Permission::CREATE);
		if (!$canCreate) {
			$this->_message->setMessage('Your are not allowed to create new monthly record.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>