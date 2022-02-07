<?php
namespace Lns\Sb\Controller\Admin\Cms\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Cms';

	protected $_cms;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Cms $Cms
	){
		parent::__construct($Url, $Message, $Session);
		$this->_cms = $Cms;
	}

	public function run(){
		$this->requireLogin();

		$id = (int)$this->getPost('id');
		$title = $this->getPost('title');
        $page = $this->getPost('page');
        $content = $this->getPost('content');

		$msg = '';
		$msgtype = 'danger';

		if ($page) {
			$entity = null;
			if($id > 0) {
                $canUpdate = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::UPDATE);
				if(!$canUpdate){
					$this->_message->setMessage($this->_lang->getLang('cms_update_error'), 'danger');
					$this->_url->redirect('/cms/action/edit/id/' . $id);
				}
				$isExists = $this->_cms->isExists($id, $page);
				if ($isExists) {
					$this->_message->setMessage($this->_lang->getLang('cms_create_exist_error'), 'danger');
					$this->_url->redirect('/cms/action/edit/id/' . $id);
				}
				$cms = $this->_cms->getByColumn(['id'=>$id], 1);
				if($cms) {
					$entity = $cms;
					$msg = $this->_lang->getLang('cms_update_success');
					$msgtype = 'success';
				} else {
					$msg = $this->_lang->getLang('cms_update_noexist_error');
					$msgtype = 'danger';
				}
			} else {
                $canCreate = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::CREATE);
				if (!$canCreate) {
					$this->_message->setMessage($this->_lang->getLang('cms_create_error'), 'danger');
					$this->_url->redirect('/cms');
				}
				$isExists = $this->_cms->getByColumn(['page' => $page], 1);
				if ($isExists) {
					$this->_message->setMessage($this->_lang->getLang('cms_create_exist_error'), 'danger');
					$this->_url->redirect('/cms/action/add');
				}
				$entity = $this->_cms;
				$msg = $this->_lang->getLang('cms_create_success');
				$msgtype = 'success';
			}

			if ($entity) {
				$entity->setData('title', $title);
                $entity->setData('page', $page);
                $entity->setData('content', $content);
				$save = $entity->__save();
				if(!$save){
					$msg = $this->_lang->getLang('cms_create_failed');
					$msgtype = 'danger';
					$redirectTo = '/cms';
				} else {
					$redirectTo = '/cms/action/edit/id/' . $save;
				}
			}
		}
		$this->_message->setMessage($msg, $msgtype);
		$this->_url->redirect($redirectTo);
	}
}
?>