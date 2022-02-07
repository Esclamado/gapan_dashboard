<?php
namespace Lns\Sb\Controller\Api\User;

use Lns\Sb\Lib\Entity\Db\Attachments;

class Updateprofile extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
    protected $_files;
    protected $_upload;

	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Lns\Sb\Lib\Entity\Db\Address $Address
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_deviceToken = $DeviceToken;
        $this->_global = $AllFunction;
		$this->_userModel = $Users;
        $this->_upload = $Upload;
        $this->_userProfile = $UserProfile;
        $this->_contact = $Contact;
        $this->_address = $Address;
	}	
	public function run(){    
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $updateForm = $this->getParam();

            $userInfo = $this->_userModel->getByColumn(['id' => $payload['payload']['jti']], 1);
            $userProfileInfo = $this->_userProfile->getByColumn(['user_id' => $userInfo->getData('id')], 1);
            $contactInfo = $this->_contact->getByColumn(['profile_id' => $userProfileInfo->getData('id')], 1);
            $addressInfo = $this->_address->getByColumn(['profile_id' => $userProfileInfo->getData('id')], 1);

            if($userInfo){
                $this->updateProfile( $updateForm,$userInfo->getData('id'),$userProfileInfo,$contactInfo,$addressInfo);
            } else {
                $this->jsonData['message'] = "Invalid Token!";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }   
    public function updateProfile($updateForm,$userId,$userProfileInfo,$contactInfo,$addressInfo){

        $updateForm['id'] = $userId;
        $this->_userModel->saveUser($updateForm);

        $updateForm['id'] = $userProfileInfo->getData('id');
        $profilepic = $this->upload($userProfileInfo->getData('profile_pic'), $userId);
        if ($profilepic) {
            $updateForm['profile_pic'] = $profilepic;
        }
        $this->_userProfile->saveUserProfile($updateForm);

        $updateForm['id'] = $contactInfo->getData('id');
        $this->_contact->saveUserContact($updateForm);

        $updateForm['id'] = $addressInfo->getData('id');
        $this->_address->saveUserAddress($updateForm);

        $response =  $this->_global->reconstruct($this->_userModel->getUserById($userId));

        $this->jsonData['error'] = 0;
        $this->jsonData['message'] = "Successfully updated!";
        $this->jsonData['data'] = $response;

        if ($response['profile_profile_pic']) {
            $this->jsonData['data']['profile_profile_pic'] = $this->getImageUrl([
                'vendor' => 'Lns',
                'module' => 'Sb',
                'path' => '/images/uploads/profilepic/'.$userId,
                'filename' => $response['profile_profile_pic']
            ]);
        }
        $this->jsonEncode($this->jsonData);
        die;
    }   

    protected function upload($oldPic, $userId){
        $path = 'Lns'.DS.'Sb'.DS.'View'.DS.'images'.DS.'uploads'.DS.'profilepic'.DS.$userId;

        $file = $this->getFile('attachment');
        $fileName = null;
        if($file){
            $_file = $this->_upload->setFile($file)
            ->setPath($path)
            ->setAcceptedFile(['ico','jpg','png','jpeg'])
            ->save();

            if($_file['error'] == 0){
                if($oldPic){
                    $t = ROOT.DS.'App'.DS.'Ext'.DS.$path.DS.$oldPic;
                    
                    if(file_exists($t) && is_file($t)){
                        unlink($t);
                    }
                }
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }   
        }
        return $fileName;
    }
}