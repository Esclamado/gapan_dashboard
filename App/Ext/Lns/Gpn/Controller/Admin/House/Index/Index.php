<?php
namespace Lns\Gpn\Controller\Admin\House\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'House/Building Management';

	public function run(){
        $this->requireLogin();
        $canView = $this->checkPermission('MANAGEHOUSE', \Lns\Sb\Lib\Permission::VIEW);
		if (!$canView) {
			$this->_message->setMessage('Your are not allowed to view house/building.', 'danger');
			$this->_url->redirect('');
		} 
		return parent::run();
	}
}
?>