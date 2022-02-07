<?php
namespace Lns\Sb\Controller\Admin\Users\UserProfile;

class ChangePassword extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Change Password';
	protected $request;

	public function run(){
		$this->requireLogin();

		if($this->isAjaxRequest){
			$params = $this->getParam();

			$apiChangePassword = $this->_di->get('Lns\Sb\Controller\Api\User\Changepassword');
			$id = $this->_session->getLogedInUser();

			$result = $apiChangePassword->checkPassword($id, $this->getParam());
			echo json_encode($result);
		} else{
			return parent::run();
		}
		
	}
}
?>


