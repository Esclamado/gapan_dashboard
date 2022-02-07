<?php
namespace Lns\Gpn\Controller\Api\Dailysortingreport\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_dailysortingreport;
    protected $_dailysortinginventory;
    protected $_userProfile;
    protected $_users;
    protected $_eggtype;
    protected $_house;
    protected $_incidentReport;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
    parent::__construct($Url,$Message,$Session);
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_incidentReport = $IncidentReport;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->_eggtype = $Eggtype;
        $this->_house = $House;
        $this->token = $Validate;
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
            
            $param = $this->getParam();

            $userId = $payload['payload']['jti'];

            $role = $this->_users->getByColumn(['id' => $userId], 1);

            $dailySortingreports = $this->_dailysortingreport->getList($param, $role->getData('user_role_id'));

            if($dailySortingreports['datas']){

                $this->jsonData = $dailySortingreports;

                $i = 0;
                $result = [];
                foreach ($dailySortingreports['datas'] as $dailySortingreport) {
                    
                    $result[$i] = $dailySortingreport->getData();

/*                     $dailySortinginventorys = $this->_dailysortinginventory->getDailySortingInventory($dailySortingreport->getData('id'));

                        if($dailySortinginventorys){
                            $x = 0;
                            foreach ($dailySortinginventorys as $dailySortinginventory) {
                                $result[$i]['egg_info'][$x] = $dailySortinginventory->getData();
                                $eggTypes = $this->_eggtype->getEggType($dailySortinginventory->getData('type_id'));
                                $result[$i]['egg_info'][$x]['type'] = $eggTypes->getData();
                            $x++;
                            }
                        } */

                        $result[$i]['recordStatus'] = $this->_incidentReport->getIncidentReport((int)$userId, (int)$dailySortingreport->getData('prepared_by'), (int)$dailySortingreport->getData('checked_by'), (int)$dailySortingreport->getData('received_by'), (int)$dailySortingreport->getData('id'), 6);

                        $result[$i]['house'] = $this->_house->getHouse($dailySortingreport->getData('house_id'));
        
                    if ($dailySortingreport->getData('prepared_by')) {
                        $result[$i]['prepared_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('prepared_by'));
                        $result[$i]['prepared_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('prepared_by'));
                        $result[$i]['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/sorter/' . $dailySortingreport->getData('prepared_by'),
                            'filename' => $dailySortingreport->getData('prepared_by_path')
                        ]);
                    }
                    if ($dailySortingreport->getData('checked_by')) {
                        $result[$i]['checked_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('checked_by'));
                        $result[$i]['checked_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('checked_by'));
                        $result[$i]['checked_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $dailySortingreport->getData('checked_by'),
                            'filename' => $dailySortingreport->getData('checked_by_path')
                        ]);
                    }
                    if ($dailySortingreport->getData('received_by')) {
                        $result[$i]['received_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('received_by'));
                        $result[$i]['received_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('received_by'));
                        $result[$i]['received_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/warehouseman/' . $dailySortingreport->getData('received_by'),
                            'filename' => $dailySortingreport->getData('received_by_path')
                        ]);
                    }
                    $i++;
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['datas'] = $result;
            }else{
                $this->jsonData['message'] = 'No Record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>