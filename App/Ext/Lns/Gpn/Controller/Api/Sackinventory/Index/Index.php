<?php
namespace Lns\Gpn\Controller\Api\Sackinventory\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;

    protected $_sackInventory;
    protected $_sackBldgInventory;

    protected $_userProfile;
    protected $_users;
    protected $_house;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_sackInventory = $Sackinventory;
        $this->_sackBldgInventory = $Sackbldginventory;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->_house = $House;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $this->jsonData['error'] = 1;
            $id = $this->getParam('id');
            $data = $this->_sackInventory->getByColumn(['id' => $id], 1);
            if ($data) {
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $data->getData();
                $total = $this->_sackBldgInventory->getTotalBySackInvId($data->getData('id'));
                $this->jsonData['data']['total'] = $total;
                $date = date("Y-m-d", strtotime($data->getData('created_at')));
                $this->jsonData['data']['last_data'] = $this->_sackInventory->getLastEnding($date);

                $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($data->getData('prepared_by'));
                $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($data->getData('prepared_by'));
                $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                    'vendor' => 'Lns',
                    'module' => 'Gpn',
                    'path' => '/images/uploads/signature/warehouseman/' . $data->getData('prepared_by'),
                    'filename' => $data->getData('prepared_by_path')
                ]);
                
                $houses = $this->_house->getCollection();
                if ($houses) {
                    $i = 0;
                    foreach ($houses as $house) {
                        $sackBldgs = $this->_sackBldgInventory->getByColumn(['sack_inv_id' => $id, 'house_id' => $house->getData('id')], 1);
                        if ($sackBldgs) {
                            $this->jsonData['data']['sack_bldg'][$i] = $sackBldgs->getData();
                            $this->jsonData['data']['sack_bldg'][$i]['house'] = $house->getData();
                        }
                        $i++;
                    }
                }
            } else {
                $this->jsonData['message'] = "Sack not found";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}