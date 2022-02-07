<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Edit extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Users';

	public function run(){
		$this->requireLogin();
		$canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage('Your are not allowed to update mail template.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>