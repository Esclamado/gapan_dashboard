<?php
namespace Lns\Sb\Controller\Admin\Settings\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Site Settings';

	public function run(){
		$this->requireLogin();

		$canView = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::VIEW);
		if(!$canView){
			$this->_message->setMessage('Your are not allowed to view settings.', 'danger');
			$this->_url->redirect('');
		}

		return parent::run();
	}
}
?>