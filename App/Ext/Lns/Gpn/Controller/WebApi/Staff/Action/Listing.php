<?php
namespace Lns\Gpn\Controller\WebApi\Staff\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_userprofile;
    protected $_users;
    protected $_roles;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\Roles $Roles
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_users = $Users;
        $this->_roles = $Roles;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $param = $this->getParam();

            $users = $this->_users->searchUser($param, $userId);
            $this->jsonData = $users;
            $result = [];
            if($users['datas']){
                foreach ($users['datas'] as $key => $user) {
                    $result[$key] = $user->getData();
                    $result[$key]['staff_id'] = 'GPS' . sprintf("%04d", $user->getData('id'));
                }
                $this->jsonData['datas'] = $result; 
                $this->jsonData['error'] = 0; 
            }
            $count = $this->_users->getStaffcount();
            $this->jsonData['data']['total_number_of_staff'] = $count ? $count : 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>