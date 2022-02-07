<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Create extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Users';

	public function run(){
		$this->requireLogin();

		$canCreate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::CREATE);
		if(!$canCreate){
			$this->_message->setMessage('Your are not allowed to create mail template.', 'danger');
			$this->_url->redirect('');
		}
		return parent::run();
	}
}
?>