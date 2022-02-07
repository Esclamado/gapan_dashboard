<?php
namespace Lns\Sb\Controller\Admin\Settings\Templates;

class Delete extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Delete Template';

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\MailTemplate $MailTemplate
	){
		parent::__construct($Url, $Message, $Session);
		$this->_mailTemplate = $MailTemplate;
	}

	public function run(){
		$this->requireLogin();
		$canDelete = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::DELETE);
		if(!$canDelete){
			$this->_message->setMessage('Your are not allowed to delete mail template.', 'danger');
			$this->_url->redirect('');
		}

		$id = $this->getPost('id');
		$this->_mailTemplate->deleteEntityByPk($id);

		$template = $this->_mailTemplate->getByColumn(['id' => $id], 1);
		if($template) {
			$this->_message->setMessage('Cannot delete email template, please try again.', 'danger');
		} else {
			$this->_message->setMessage('Email template successfully deleted.');
		}
		$this->_url->redirect('/settings/templates');
	}
}
?>