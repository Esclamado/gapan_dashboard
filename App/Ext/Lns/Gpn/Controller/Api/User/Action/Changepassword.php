<?php
namespace Lns\Gpn\Controller\Api\User\Action;

class Changepassword extends \Lns\Sb\Controller\Controller {
    
    protected $_userModel;
    protected $Password;

    protected $_deviceToken;
    protected $_global;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_global = $AllFunction;
        $this->_deviceToken = $DeviceToken;
        $this->token = $Validate;

        $this->_userModel = $Users;
        $this->Password = $Password;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = (int)$this->getParam('userId');
            $password = $this->getParam('password');
            $hashedPassword = $this->Password->setPassword($password)->getHash();
            
            $entity = $this->_userModel->getByColumn(['id' => $userId], 1);
            /* $email = $entity->getData('email'); */
            $entity->setData('password', $hashedPassword);
            $save = $entity->__save();
            if ($save) {
                /* $tokenInDb = $this->_deviceToken->getByColumn([
                    'token' => $payload['devicetoken'],
                    'api_key' => $payload['payload']['key'],
                ], 1);
                if($tokenInDb){
                    $this->_deviceToken->saveToken($userId, $tokenInDb->getData());
                    $userInfo = $this->_userModel->getUserById($userId);
                    
                    if($userInfo){
                        $userInfo =  $this->_global->reconstruct($userInfo);
                    }

                    $responce  =  $this->_global->updatejwt($userId, $email, $tokenInDb);
                    $this->jsonData = $responce;
                    $this->jsonData['user'] = $userInfo;
                    if ($userInfo['profile_profile_pic']) {
                        $this->jsonData['user']['profile_profile_pic'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Sb',
                            'path' => '/images/uploads/profilepic/'.$userId,
                            'filename' => $userInfo['profile_profile_pic']
                        ]);
                    }
                } */
                $this->jsonData['auth'] = array(
                    'email' => $entity->getData('email'),
                    'password' => $password
                );
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = 'Password updated successfully!';
            } else {
                $this->jsonData['message'] = 'Could not save new password';
            }
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}
?>