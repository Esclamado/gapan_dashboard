<?php
namespace Lns\Gpn\Controller\WebApi\User\Action;

class Add extends \Lns\Sb\Controller\Controller {

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

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            /* $userId = $payload['payload']['jti']; */
            
            $count = $this->_users->getUsercount();
            
            
            $first_name = $this->getParam('first_name');
            $last_name = $this->getParam('last_name');
            $location = $this->getParam('location');
            $number = $this->getParam('number');
            $username = $this->getParam('username');
            $email = $this->getParam('email');

            $uname = $this->_users->getByColumn(['username' => $username], 1);
            $mail = $this->_users->getByColumn(['email' => $email], 1);

            $role = $this->getParam('role');

            $customer_type_id = $this->getParam('customer_type_id');

            /* var_dump($count);
            var_dump($this->getParam());
            die; */
            if($role==3){
                $pw = "CST";
                $validate = $mail;
                $e = $email;
                $u = '';
            } else if ($role == 4) {
                $pw = "MNG";
                $validate = $uname || $mail ? true : false ;
                $e = $email;
                $u = $username;
            } else if ($role == 5) {
                $pw = "SAL";
                $validate = $uname || $mail ? true : false;
                $e = $email;
                $u = $username;
            } else if ($role == 6) {
                $pw = "INS";
                $validate = $uname;
                $e = '';
                $u = $username;
            } else if ($role == 7) {
                $pw = "INS";
                $validate = $uname;
                $e = '';
                $u = $username;
            } else if ($role == 8) {
                $pw = "FLM";
                $validate = $uname;
                $e = '';
                $u = $username;
            } else if ($role == 9) {
                $pw = "SRT";
                $validate = $uname;
                $e = '';
                $u = $username;
            } else if ($role == 10) {
                $pw = "WHM";
                $validate = $uname;
                $e = '';
                $u = $username;
            } else if ($role == 11) {
                $pw = "WHM";
                $validate = $uname;
                $e = '';
                $u = $username;
            }
            $password = $pw . sprintf("%05d", $count);
            $hashedPassword = $this->_password->setPassword($password)->getHash();

            if($validate){
                $this->jsonData['message'] = 'Username or email already existing. Please try another one';
            }else{
                $user = $this->_users;
                $user->setData('email', $e);
                $user->setData('username', $u);
                $user->setData('password', $hashedPassword);
                $user->setData('real_password', $password);
                $user->setData('status', 1);
                $user->setData('user_role_id', $role);
                $user->setData('customer_type_id', $customer_type_id);
                $userId = $user->__save();

                if($userId){
                    /* SENDING CREDENTIALS TO EMAIL AFTER REGISTRATION */
                    $this->_users->sendEmailCredential($userId, $this->_siteConfig, 'registration', $password);

                    $photo = $this->upload($userId);
                    $profile = $this->_userprofile;
                    $profile->setData('user_id', $userId);
                    $profile->setData('first_name', $first_name);
                    $profile->setData('last_name', $last_name);
                    $profile->setData('location', $location);
                    if ($photo) {
                        $profile->setData('profile_pic', $photo);
                    }
                    $profileID = $profile->__save();

                    if($profileID){
                        $hasAddress = $this->_address;
                        $hasAddress->setData('profile_id', $userId);
                        $hasAddress->setData('address', $location);
                        $hasAddressID = $hasAddress->__save();

                        if($hasAddressID){
                            $hasContact = $this->_contact;
                            $hasContact->setData('profile_id', $userId);
                            $hasContact->setData('number', $number);
                            $hasContactID = $hasContact->__save();

                            if($hasContactID){
                                $this->jsonData['error'] = 0;
                                $this->jsonData['message'] = 'A new user has been saved';
                                
/*                                 $this->jsonData['data'] = array(
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
                                }  */
                            }
                        }
                    }
                }
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