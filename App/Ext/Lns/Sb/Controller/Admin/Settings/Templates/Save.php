<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save User';
	protected $_request;
	protected $_users;
	protected $_validator;
	protected $Password;
	protected $_userSession;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Http\Request $Request,
		\Lns\Sb\Lib\Entity\Db\Email $Email
	){
		parent::__construct($Url,$Message,$Session);
		$this->_request = $Request;
		$this->_email = $Email;
		$this->_userSession = $Session;
	}	

	public function run(){
		$this->requireLogin();
		$id = $this->getPost('id');

		if($id > 0){
			$canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
			if(!$canUpdate){
				$this->_message->setMessage('Your are not allowed to update mail template.', 'danger');
				$this->_url->redirect('');
			}
		} else {
			$canCreate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::CREATE);
			if(!$canCreate){
				$this->_message->setMessage('Your are not allowed to create mail template.', 'danger');
				$this->_url->redirect('');
			}
		}

		$save = $this->_email
		->saveTemplate($id);

		if($save['error'] == 0){
			$this->_message->setMessage($save['message'], 'success');
			$this->_url->redirect('/settings/templates');
		} else {
			$this->_message->setMessage($save['message'], 'danger');
			$this->_url->redirect('/settings/templates');
		}

		/* $this->jsonEncode($save);
		die; */

	}
}
?>