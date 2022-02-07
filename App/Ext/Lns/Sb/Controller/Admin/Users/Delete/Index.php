<?php
namespace Lns\Sb\Controller\Admin\Users\Delete;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Delete User';
	protected $_users;
	protected $_address;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Users $Users,
		\Lns\Sb\Lib\Entity\Db\Address $Address,
		\Lns\Sb\Lib\Entity\Db\Contact $Contact
	){
		parent::__construct($Url, $Message, $Session);
		$this->_users = $Users;
		$this->_address = $Address;
		$this->_contact = $Contact;
	}

	public function run(){
		$this->requireLogin();

		$_userId = $this->_request->getParam('userId');
		
		$toDelete = $this->_users->getByColumn(['id' => $_userId]);
		if($_userId && $toDelete){
			$userProfile = $toDelete->getUserProfile();
			
			if($userProfile){
				$a = $this->_address->getByColumn(['profile_id' => $userProfile->getData('id')], 1);
				if($a){
					$a->delete();
				}
				$c = $this->_contact->getByColumn(['profile_id' => $userProfile->getData('id')], 1);
				if($c){
					$c->delete();
				}
				$userProfile->delete();
			}
			$toDelete->delete();

			$isDeleted = $this->_users->getByColumn(['id' => $_userId]);

			if(!$isDeleted){
				$this->_url->redirect('/users');
			} else{
				$this->_url->redirectTo($this->_request->getUrlReferer());
			}
		} else{
			$this->_url->redirectTo($this->_request->getUrlReferer());
		}
		
	}
}
?>