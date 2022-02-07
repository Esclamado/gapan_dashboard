<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Preview extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Users';

	public function run(){
		$this->requireLogin();
		$canView = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::VIEW);
		if(!$canView){
			$this->_message->setMessage('Your are not allowed to view mail template.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>