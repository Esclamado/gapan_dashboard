<?php
namespace Lns\Gpn\Controller\WebApi\HarvestProductionStatus\Action;

class HarvestProductionStatus extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_users;
    protected $_userprofile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_users = $Users;
        $this->_userprofile = $UserProfile;
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
            $limit = $this->getParam('limit');

            $result = [];
            $users = $this->_users->getUsersByRole(8, $limit);
            if($users){
                $i = 0;
                foreach ($users as $user) {
                    $flockmans = $this->_userprofile->getByColumn(['user_id'=> $user->getData('id')], 0);
                    if($flockmans){
                        foreach ($flockmans as $flockman) {
                            $result[$i] = array(
                                'id'=> $flockman->getData('id'),
                                'first_name'=>$flockman->getData('first_name'),
                                'last_name' => $flockman->getData('last_name'),
                            );
                            $dailyhouseharvests = $this->_dailyhouseharvest->getProductionRate($param, $flockman->getData('id'));
                            if($dailyhouseharvests){
                                $result[$i]['total_egg_production'] = $dailyhouseharvests['real_egg_count'] ? $dailyhouseharvests['real_egg_count'] : 0;
                                $result[$i]['production_rate'] = $dailyhouseharvests['count'] ? $dailyhouseharvests['count'] : 0;
                            }       
                        }
                    }
                    $i++;
                }
                $this->jsonData['error'] = 0;
            }
            $this->jsonData['data'] = $result;

        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>