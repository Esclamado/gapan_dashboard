<?php
namespace Lns\Sb\Controller\Admin\Cms\Action;

class Edit extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Edit Cms';

	public function run(){
        $this->requireLogin();
        $canUpdate = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::UPDATE);
		if (!$canUpdate) {
			$this->_message->setMessage($this->_lang->getLang('cms_update_error'), 'danger');
			$this->_url->redirect('/cms');
		}
		return parent::run();
	}
}
?>