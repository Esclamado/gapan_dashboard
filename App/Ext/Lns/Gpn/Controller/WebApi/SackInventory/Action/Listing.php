<?php
namespace Lns\Gpn\Controller\WebApi\SackInventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_sackinventory;
    protected $_userprofile;
    protected $_sackBldgInventory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_sackinventory = $Sackinventory;
        $this->_userprofile = $UserProfile;
        $this->_sackBldgInventory = $Sackbldginventory;
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
            $result = [];
            $sacks = $this->_sackinventory->getSackreport($param);
            $this->jsonData = $sacks;
            if($sacks['datas']){
                foreach ($sacks['datas'] as $key => $sack) {
                    $result[$key] = $sack->getData();
                    $userprofile = $this->_userprofile->getFullNameById($sack->getData('prepared_by'));
                    $result[$key]['name'] = $userprofile ? $userprofile : 'N/A' ;

                    $total = $this->_sackBldgInventory->getTotalBySackInvId($sack->getData('id'));
                    $result[$key]['total'] = $total;
                    $date = date("Y-m-d", strtotime($sack->getData('created_at')));
                    $result[$key]['last_data'] = $this->_sackinventory->getLastEnding($date);

                    $end = $sack->getData('last_ending');
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['data']['total_sacks'] = $end;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No tray record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>