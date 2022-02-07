<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Lns\Sb\Controller\Admin\Logout\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session
	){
		parent::__construct($Url,$Message,$Session);
		$this->_session = $Session;
	}

	public function run(){
		$this->requireLogin();
		$this->_session->setAsLogout();
		$this->_message->setMessage('You are now logged out', 'success');
		$this->_url->redirect('/login');
	}
	
}