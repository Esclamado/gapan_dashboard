<?php
namespace Lns\Gpn\Controller\WebApi\SackInventory\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_sackinventory;
    protected $_sackbldginventory;
    protected $_house;
    protected $_userprofile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_sackinventory = $Sackinventory;
        $this->_sackbldginventory = $Sackbldginventory;
        $this->_house = $House;
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

            $sackinventoryId = $this->getParam('id');
            $sackinventory = $this->_sackinventory->getByColumn(['id'=>$sackinventoryId], 1);
            if($sackinventory){
                $this->jsonData['data'] = $sackinventory->getData();
                $user = $this->_userprofile->getFullNameById($sackinventory->getData('prepared_by'));
                if($user){
                    $this->jsonData['data']['name'] = $user;
                }
                $sackbldginventorys = $this->_sackbldginventory->getByColumn(['sack_inv_id'=> $sackinventoryId], 0);
                if($sackbldginventorys){
                    foreach ($sackbldginventorys as $key => $sackbldginventory) {
                        $this->jsonData['data']['sack_bldg_inventory'][$key] = $sackbldginventory->getData();
                        $house = $this->_house->getByColumn(['id'=> $sackbldginventory->getData('house_id')], 1);
                        if($house){
                            $this->jsonData['data']['sack_bldg_inventory'][$key]['house'] = $house->getData();
                        }
                    }
                }
                $date = date('Y-m-d', strtotime($sackinventory->getData('created_at')));
                $last_data = $this->_sackinventory->getLastEnding($date);
                $this->jsonData['data']['last_data'] = $last_data;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>