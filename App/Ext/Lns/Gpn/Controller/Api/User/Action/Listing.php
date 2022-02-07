<?php
namespace Lns\Gpn\Controller\Api\User\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_userprofile;
    protected $_roles;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Roles $Roles
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
        $this->_userprofile = $UserProfile;
        $this->_roles = $Roles;
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

            if($this->getParam('role')){
                $role = $this->_roles->getByColumn(['id' => $this->getParam('role')], 1);
                if ($role) {
                    $getUser = $this->_users->getbyColumn(['user_role_id' => $role->getData('id')], 0);
                    if ($getUser) {
                        foreach ($getUser as $user) {
                            $getUserProfile = $this->_userprofile->getByColumn(['user_id' => $user->getData('id')], 0);
                            foreach ($getUserProfile as $userProfile) {
                                $this->jsonData['data'][] = array(
                                    'user_id' => $userProfile->getData('user_id'),
                                    'first_name' => $userProfile->getData('first_name'),
                                    'last_name' => $userProfile->getData('last_name')
                                );
                            }
                        }
                        $this->jsonData['error'] = 0;
                    } else {
                        $this->jsonData['message'] = 'User not found';
                    }
                }else{
                    $this->jsonData['message'] = 'Role not found';
                }
            }else{
                $getUserProfile = $this->_userprofile->getCollection();
                if($getUserProfile){
                    foreach ($getUserProfile as $userProfile) {
                        $this->jsonData['data'][] = array(
                            'user_id' => $userProfile->getData('user_id'),
                            'first_name' => $userProfile->getData('first_name'),
                            'last_name' => $userProfile->getData('last_name')
                        );
                    }
                $this->jsonData['error'] = 0;
                }else{
                    $this->jsonData['message'] = 'User not found';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>