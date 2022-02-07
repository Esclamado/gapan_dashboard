<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Email Templates';

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