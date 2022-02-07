<?php
namespace Lns\Sb\Controller\Admin\Login\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Login';

	public function run(){
		$this->requireNotLogin();
		return parent::run();
	}
}
?>