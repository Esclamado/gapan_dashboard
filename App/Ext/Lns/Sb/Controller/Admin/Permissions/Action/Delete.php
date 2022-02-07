<?php
namespace Lns\Sb\Controller\Admin\Permissions\Action;

class Delete extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Delete Permission';
	protected $_permissions;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Permissions $Permissions
	){
		parent::__construct($Url, $Message, $Session);
		$this->_permissions = $Permissions;
	}

	public function run(){
		$this->requireLogin();

		$canDelete = $this->checkPermission('MANAGEPERMISSIONS', \Lns\Sb\Lib\Permission::DELETE);
		if(!$canDelete){
			$this->_message->setMessage('Your are not allowed to delete permission.', 'danger');
			$this->_url->redirect('/permissions');
		}
		
		$id = (int)$this->getPost('id');

		$msg = 'Cannot find the permission you are trying to update.';
		$msgtype = 'danger';

		if($id){
			$per = $this->_permissions->getByColumn(['id'=>$id], 1);
			if($per){
				$per->delete();
				$msg = 'Permission deleted successfully.';
				$msgtype = 'success';
			}
		}

		$this->_message->setMessage($msg, $msgtype);
		$this->_url->redirect('permissions');
	}
}
?>