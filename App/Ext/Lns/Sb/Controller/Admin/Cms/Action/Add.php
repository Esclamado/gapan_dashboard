<?php
namespace Lns\Sb\Controller\Admin\Cms\Action;

class Add extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Add Cms';

	public function run(){
        $this->requireLogin();
        $canCreate = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::CREATE);
		if (!$canCreate) {
			$this->_message->setMessage($this->_lang->getLang('cms_create_error'), 'danger');
			$this->_url->redirect('/cms');
		}
		return parent::run();
	}
}
?>