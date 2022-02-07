<?php
namespace Lns\Sb\Controller\Admin\Users\ChangeProfilePhoto;

use Lns\Sb\Lib\AttachmentType;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Change Profile Photo';
	protected $_request;
	protected $_users;
	protected $Password;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Http\Request $Request,
		\Lns\Sb\Lib\Entity\Db\Users $Users
	){
		parent::__construct($Url,$Message,$Session);
		$this->_request = $Request;
		$this->_users= $Users;
	}

	public function run(){
		$this->requireLogin();
		
		$postedFormData = $this->_request->getParam();

		$user = $this->_users->getByColumn(['id' => $postedFormData['user_id']]);
		if($user){
			$userPhotoModel = $user->getProfilePicture();
			if(!$userPhotoModel){
				$userPhotoModel = $user->_attachments;
			}

			$postedFormData['attachment_type'] = AttachmentType::PROFILE_PHOTO;
			$postedFormData['uploader_id'] = $this->_session->getLogedInUser();

			if($userPhotoModel->saveEntity($postedFormData)){
				$this->_message->setMessage('User profile photo successfully saved.');
				$this->_url->redirect($postedFormData['redirectTo']);
			} else{
				$this->_url->redirectTo($this->_request->getUrlReferer());
			}
		} else{
			$this->_url->redirectTo($this->_request->getUrlReferer());
		}
	}
}
?>