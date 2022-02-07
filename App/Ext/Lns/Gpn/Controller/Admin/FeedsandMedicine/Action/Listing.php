<?php
namespace Lns\Gpn\Controller\Admin\FeedsandMedicine\Action;

class Listing extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'View House/Building Details';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGEFEEDSANDMEDICINE', \Lns\Sb\Lib\Permission::VIEW);
		if (!$canCreate) {
			$this->_message->setMessage('Your are not allowed to view House/Building Details.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>