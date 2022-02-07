<?php
namespace Lns\Gpn\Controller\Api\User\Action;

class Update extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_userprofile;
    protected $_address;
    protected $_contact;
    protected $_upload;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Address $Address,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Of\Std\Upload $Upload
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
        $this->_userprofile = $UserProfile;
        $this->_address = $Address;
        $this->_contact = $Contact;
        $this->_upload = $Upload;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

		if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $first_name = $this->getParam('first_name');
            $last_name = $this->getParam('last_name');
            $location = $this->getParam('location');
            $number = $this->getParam('number');
            $photo = $this->upload($userId);

            $hasProfile = $this->_userprofile->getByColumn(['user_id'=> $userId], 1);
            if($hasProfile){
                $hasProfile->setData('first_name', $first_name);
                $hasProfile->setData('last_name', $last_name);
                $hasProfile->setData('location', $location);
                if($photo){
                    $hasProfile->setData('profile_pic', $photo);
                }
                $hasProfileID = $hasProfile->__save();
                if($hasProfileID){
                    $hasAddress = $this->_address->getByColumn(['profile_id'=> $userId], 1);
                    if($hasAddress){
                        $hasAddress->setData('address', $location);
                        $hasAddressID = $hasAddress->__save();
                        if($hasAddressID){
                            $hasContact = $this->_contact->getByColumn(['profile_id' => $userId], 1);
                            if($hasContact){
                                $hasContact->setData('number', $number);
                                $hasContact->__save();
                            }
                        }
                    }
                }
            $this->jsonData['error'] = 0;
            $this->jsonData['message'] = 'Profile updated successfully!';
            $this->jsonData['data'] = array(
                    'first_name'=> $first_name,
                    'last_name'=> $last_name,
                    'location'=> $location,
                    'number'=>$number
            );
            if($photo){
                $this->jsonData['data']['profile_pic'] = $this->getImageUrl([
                    'vendor' => 'Lns',
                    'module' => 'Sb',
                    'path' => '/images/uploads/profilepic/' . $userId,
                    'filename' => $photo
                ]);
            }
            }else{
            $this->jsonData['message'] = 'No user profile found';
            }
            
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId)
    {
        $path = 'Lns' . DS . 'Sb' . DS . 'View' . DS . 'images' . DS . 'uploads' . DS . 'profilepic' . DS . $userId;

        $file = $this->getFile('photo');

        $fileName = null;
        if ($file) {
            $_file = $this->_upload->setFile($file)
                ->setPath($path)
                ->setAcceptedFile(['ico', 'jpg', 'png', 'jpeg'])
                ->save();

            if ($_file['error'] == 0) {
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }
        }
        return $fileName;
    }
}
?>
