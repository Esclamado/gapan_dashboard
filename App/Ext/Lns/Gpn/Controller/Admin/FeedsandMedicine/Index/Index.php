<?php
namespace Lns\Gpn\Controller\Admin\FeedsandMedicine\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Feeds and Medicine Management';

	public function run(){
        $this->requireLogin();
        $canView = $this->checkPermission('MANAGEFEEDSANDMEDICINE', \Lns\Sb\Lib\Permission::VIEW);
		if (!$canView) {
			$this->_message->setMessage('Your are not allowed to view feeds and medicine.', 'danger');
			$this->_url->redirect('');
		} 
		return parent::run();
	}
}
?>