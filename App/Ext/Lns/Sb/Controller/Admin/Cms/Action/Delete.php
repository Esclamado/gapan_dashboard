<?php
namespace Lns\Sb\Controller\Admin\Cms\Action;

class Delete extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Delete Cms';
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

        $canDelete = $this->checkPermission('MANAGECMS', \Lns\Sb\Lib\Permission::DELETE);
		if(!$canDelete){
			$this->_message->setMessage($this->_lang->getLang('cms_delete_error'), 'danger');
			$this->_url->redirect('/cms');
        }
        
		$id = (int)$this->getPost('id');
		$msg = $this->_lang->getLang('cms_delete_noexist_error');
		$msgtype = 'danger';
		if ($id) {
			$per = $this->_cms->getByColumn(['id'=>$id], 1);
			if($per){
				$per->delete();
				$msg = $this->_lang->getLang('cms_delete_success');
				$msgtype = 'success';
			}
		}

		$this->_message->setMessage($msg, $msgtype);
		$this->_url->redirect('cms');
	}
}
?>