<?php
namespace Lns\Sb\Controller\Admin\Login\Auth;


class Index extends \Lns\Sb\Controller\Controller {
	
    protected $_userModel;
	
	protected $pageTitle = 'Login';
	protected $email = '';
	protected $password = '';
	protected $login_timeout = 60; /* 60 minutes/1 hour */
	protected $_password;
	
	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Std\Password $Password,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\Entity\Db\Users $User
	){
		parent::__construct($Url,$Message,$Session);
		$this->_password = $Password;
		$this->_adminUserModel = $User;
		$this->_request = $this->_di->get('Lns\Sb\Lib\Entity\ClassOverride\OfHttpRequest');
	}

	public function run(){
		$this->email = $this->getParam('email');
		$this->password = $this->getParam('password');
		$this->remember = ($this->getParam('remember_user') != '')?true:false;
		$this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
		
		if($this->email != ''){
			$user = $this->_adminUserModel->getByColumn(['email' => $this->email], 1);
			if($user){
				if ($user->getData('status') == 1) {
					$canLoginAdmin = $this->checkPermission('LOGINTOADMIN', \Lns\Sb\Lib\Permission::VIEW, $user);
					if(!$canLoginAdmin){
						if($this->isAjax){
							$this->result['message'] = $this->_lang->getLang('admin_login_error');
							$this->jsonEncode($this->result);
	        				die;
						} else{
							$this->_message->setMessage($this->_lang->getLang('admin_login_error'), 'danger');
							$this->_url->redirect('/permissions');
						}
					}
	                $verifypassword = $this->_password->setPassword($this->password)->setHash($user->getData('password'))->verify();
					if($verifypassword){
	                    $user->setData('last_login', date('Y-m-d H:i:s', time()));
						$user->__save();
	                    
						/* set the session */ 
						$this->_session->setAsLoggedIn($user, $this->remember);
						
						$this->_message->setMessage($this->_lang->getLang('admin_login_success') . $user->getData('firstname') . '.');
						if($this->isAjax){
							$this->result['error'] = 0;
							$this->result['message'] = 'Success.';
							$this->result['data'] = [
								'redirect' => $this->_url->getAdminUrl(),
							];
						} else{
							$this->_url->redirect('/');
						}
	                } else {
	                    if($this->isAjax){
							$this->result['message'] = $this->_lang->getLang('admin_login_failed');
						} else{
							$this->_message->setMessage($this->_lang->getLang('admin_login_failed'), 'danger');
	                    	$this->_url->redirect('/login');
						}
	                }
				} else {
					if($this->isAjax){
						$this->result['message'] = $this->_lang->getLang('admin_login_disabled');
					} else{
						$this->_message->setMessage($this->_lang->getLang('admin_login_disabled'), 'danger');
	                	$this->_url->redirect('/login');
					}
				}
			} else {
				if($this->isAjax){
					$this->result['message'] = $this->_lang->getLang('admin_login_failed');
				} else{
					$this->_message->setMessage($this->_lang->getLang('admin_login_failed'), 'danger');
					$this->_url->redirect('/login');
				}
			}
		} else {
			if($this->isAjax){
				$this->result['message'] = $this->_lang->getLang('admin_login_email_empty');
			} else{
				$this->_message->setMessage($this->_lang->getLang('admin_login_email_empty'), 'danger');
				$this->_url->redirect('/login');
			}
		}

		if($this->isAjax){
			$this->jsonEncode($this->result);
        	die;
		}
	}
}