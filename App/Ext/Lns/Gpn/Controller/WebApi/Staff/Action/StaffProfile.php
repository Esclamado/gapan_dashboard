<?php
namespace Lns\Gpn\Controller\WebApi\Staff\Action;

class StaffProfile extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_house;
    protected $_customer_type;
    protected $_roles;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\Roles $Roles,
        \Lns\Gpn\Lib\Entity\Db\CustomerType $CustomerType
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
        $this->_customer_type = $CustomerType;
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
            /* $userId = $payload['payload']['jti']; */

            $user_id = $this->getParam('id');

            if($user_id){
                $user = $this->_users->getUserById($user_id);
                if($user) {
                    $this->jsonData['data'] = $user;

                    $userRole = $this->_roles->getByColumn(['id' => $user['user_role_id']], 1);

                    $this->jsonData['data']['user_role_label'] = $userRole->getData('name');

                    if($userRole->getData('id')==3){
                        $this->jsonData['data']['customer_id'] = 'GPC' . sprintf("%04d", $user_id);
                    }else{
                        $this->jsonData['data']['staff_id'] = 'GPS' . sprintf("%04d", $user_id);
                    }

                    if ($user['customer_type_id']) {
                        $customer_type = $this->_customer_type->getByColumn(['id' => $user['customer_type_id']], 1);
                        $this->jsonData['data']['customer_type'] = $customer_type ? $customer_type->getData() : null;
                    }
                    if($user['profile_profile_pic']){
                        $this->jsonData['data']['profile_picture'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Sb',
                            'path' => '/images/uploads/profilepic/' . $user['id'],
                            'filename' => $user['profile_profile_pic']
                        ]);
                    }
                    $this->jsonData['error'] = 0;
                }
            }else{
                $this->jsonData['message'] = 'No user record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>