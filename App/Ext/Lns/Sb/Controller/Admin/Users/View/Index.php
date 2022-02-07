<?php
namespace Lns\Sb\Controller\Admin\Users\View;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'View User';

	public function run(){
		$this->requireLogin();
		return parent::run();
	}
}
?>