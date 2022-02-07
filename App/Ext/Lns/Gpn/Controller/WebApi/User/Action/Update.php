<?php
namespace Lns\Gpn\Controller\WebApi\User\Action;

class Update extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_userprofile;
    protected $_address;
    protected $_contact;
    protected $_upload;
    protected $_password;

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
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\Password\Password $Password
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
        $this->_userprofile = $UserProfile;
        $this->_address = $Address;
        $this->_contact = $Contact;
        $this->_upload = $Upload;
        $this->_password = $Password;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

/*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; */

            $userId = $this->getParam('id');
            $first_name = $this->getParam('first_name');
            $last_name = $this->getParam('last_name');
            $location = $this->getParam('location');
            $number = $this->getParam('number');
            $photo = $this->upload($userId);
            $role = $this->getParam('role');
            $username = $this->getParam('username');
            $email = $this->getParam('email');
            $customer_type_id = $this->getParam('customer_type_id');
            $password = $this->getParam('password');

            $uname = $this->_users->getByColumn(['username' => $username], 1);
            $mail = $this->_users->getByColumn(['email' => $email], 1);

            $hasUser = $this->_users->getByColumn(['id' => $userId], 1);

            if($hasUser){
                if($hasUser->getData('user_role_id') == 3){
                    $validate = $mail;
                    $e = $email;
                    $u = '';
                }else if($hasUser->getData('user_role_id') == 4){
                    $validate = $uname ? $uname : $mail ? $mail : false ;
                    $e = $email;
                    $u = $username;
                } else if ($hasUser->getData('user_role_id') == 5) {
                    $validate = $uname ? $uname : $mail ? $mail : false;
                    $e = $email;
                    $u = $username;
                }else{
                    $validate = $uname;
                    $e = '';
                    $u = $username;
                }

                $isEdited = 0;
                if ($validate) {
                    if ($validate->getData('id') == $userId) {
                        $isEdited = 1;
                    } else {
                        $this->jsonData['message'] = 'Username or email already existing. Please try another one';
                    }
                } else {
                    $isEdited = 1;
                }

                if($isEdited==1){
                    if ($hasUser) {
                        $hasUser->setData('email', $e);
                        $hasUser->setData('username', $u);
                        $hasUser->setData('user_role_id', $role);
                        $hasUser->setData('customer_type_id', $customer_type_id);
                        if($password){
                            $hashedPassword = $this->_password->setPassword($password)->getHash();
                            $hasUser->setData('password', $hashedPassword);
                            $hasUser->setData('real_password', $password);
                        }
                        $hasuserID = $hasUser->__save();
                        if ($hasuserID) {
                            $hasProfile = $this->_userprofile->getByColumn(['user_id' => $userId], 1);
                            if ($hasProfile) {
                                $hasProfile->setData('first_name', $first_name);
                                $hasProfile->setData('last_name', $last_name);
                                $hasProfile->setData('location', $location);
                                if ($photo) {
                                    $hasProfile->setData('profile_pic', $photo);
                                }
                                $hasProfileID = $hasProfile->__save();
                                if ($hasProfileID) {
                                    $hasAddress = $this->_address->getByColumn(['profile_id' => $userId], 1);
                                    if ($hasAddress) {
                                        $hasAddress->setData('address', $location);
                                        $hasAddressID = $hasAddress->__save();
                                        if ($hasAddressID) {
                                            $hasContact = $this->_contact->getByColumn(['profile_id' => $userId], 1);
                                            if ($hasContact) {
                                                $hasContact->setData('number', $number);
                                                $hasContactID = $hasContact->__save();
                                            }
                                        }
                                    }
                                }
                                $this->jsonData['error'] = 0;
                                $this->jsonData['message'] = 'Profile updated successfully!';
                                $this->jsonData['data'] = array(
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'location' => $location,
                                    'number' => $number,
                                    'email' => $email,
                                    'username' => $username,
                                    'user_role_id' => $role
                                );
                                if ($photo) {
                                    $this->jsonData['data']['profile_pic'] = $this->getImageUrl([
                                        'vendor' => 'Lns',
                                        'module' => 'Sb',
                                        'path' => '/images/uploads/profilepic/' . $userId,
                                        'filename' => $photo
                                    ]);
                                }
                            }
                        }
                    }
                }
            } else {
                $this->jsonData['message'] = 'No user profile found';
            }
        /* } */
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