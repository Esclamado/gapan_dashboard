<?php
namespace Lns\Gpn\Controller\WebApi\Customers\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_userprofile;
    protected $_user;
    protected $_customer_type;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\CustomerType $CustomerType
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_user = $Users;
        $this->_customer_type = $CustomerType;
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

            $userprofiles = $this->_user->getList($param);
            $this->jsonData = $userprofiles;
            $result = [];
            if($userprofiles['datas']){
                $i = 0;
                foreach ($userprofiles['datas'] as $key => $userprofile) {
                    $result[$i] = $userprofile->getData();
                    if ($userprofile->getData('customer_type_id')) {
                        $customer_type = $this->_customer_type->getByColumn(['id' => $userprofile->getData('customer_type_id')], 1);
                        $result[$i]['customer_type'] = $customer_type ? $customer_type->getData() : null;
                        $result[$i]['customer_id'] = 'GPC' . sprintf("%04d", $userprofile->getData('id'));
                    }
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $users = $this->_user->getCustomercount();
                $this->jsonData['data']['total_number_of_customers'] = $users ? $users : 0 ;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>