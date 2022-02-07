<?php
namespace Lns\Sb\Controller\Admin\Users\UserProfile;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'User Profile';

	public function run(){
		$this->requireLogin();
		return parent::run();
	}
}
?>