<?php
namespace Lns\Sb\Controller\Admin\Cms\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Cms';

	public function run(){
        $this->requireLogin();
        $canView = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::VIEW);
		if (!$canView) {
			$this->_message->setMessage('Your are not allowed to view cms.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>