<?php
namespace Lns\Gpn\Controller\WebApi\User\Action;

class Confirmpassword extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_password;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Password\Password $Password
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
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
            $userId = $payload['payload']['jti'];

            /* $userId = $this->getParam('id'); */
            $password = $this->getParam('password');

            $users = $this->_users->getByColumn(['id'=>$userId], 1);
            if($users){
            $passwordVerify = $this->_password->setPassword($password)->setHash($users->getData('password'))->verify();
                if($passwordVerify){
                    $this->jsonData['message'] = 'Your password is correct';
                    $this->jsonData['error'] = 0;
                }else{
                    $this->jsonData['message'] = 'Incorrect Password';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>