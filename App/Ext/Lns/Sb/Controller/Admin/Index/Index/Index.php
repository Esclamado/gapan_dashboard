<?php
namespace Lns\Sb\Controller\Admin\Index\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Dashboard';

	public function run(){
		$this->requireLogin();
		return parent::run();
	}
}
?>