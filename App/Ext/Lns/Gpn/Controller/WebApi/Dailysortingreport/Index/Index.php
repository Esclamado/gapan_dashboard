<?php
namespace Lns\Gpn\Controller\WebApi\Dailysortingreport\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_dailysortingreport;
    protected $_dailysortinginventory;
    protected $_userProfile;
    protected $_users;
    protected $_eggtype;
    protected $_house;
    protected $_incidentReport;
    protected $_dailysortinginventoryhistory;
    protected $_dailyhouseharvest;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventoryhistory $Dailysortinginventoryhistory,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
    parent::__construct($Url,$Message,$Session);
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_userProfile = $UserProfile;
        $this->_incidentReport = $IncidentReport;
        $this->_users = $Users;
        $this->_eggtype = $Eggtype;
        $this->_house = $House;
        $this->token = $Validate;
        $this->_dailysortinginventoryhistory = $Dailysortinginventoryhistory;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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

            $userId = $payload['payload']['jti'];

            $house_harvest_id = $this->getParam('house_harvest_id');    

            $dailySortingreport = $this->_dailysortingreport->getbyColumn(['house_harvest_id'=>$house_harvest_id], 1);
    
            if($dailySortingreport){
    
                $this->jsonData['error'] = 0;

                    $this->jsonData['data'] = $dailySortingreport->getData();
    
                    $this->jsonData['data']['house'] = $this->_house->getHouse($dailySortingreport->getData('house_id'));

                    $hasIncidentReport = $this->_incidentReport->validate(4, $dailySortingreport->getData('id'));
                    $this->jsonData['data']['hasIncidentReport'] = $hasIncidentReport ? $hasIncidentReport->getData() : null;

                    $this->jsonData['data']['recordStatus'] = $this->_incidentReport->getIncidentReport((int)$userId, (int)$dailySortingreport->getData('prepared_by'), (int)$dailySortingreport->getData('checked_by'), (int)$dailySortingreport->getData('received_by'), (int)$dailySortingreport->getData('id'), 6);

                    $dailysortinginventorys = $this->_dailysortinginventory->getDailySortingInventory($dailySortingreport->getData('id'));

                    $i = 0;
                    foreach ($dailysortinginventorys as $dailysortinginventory) {
                        $this->jsonData['data']['eggs'][$i] = $dailysortinginventory->getData();

                        $egg_type = $this->_eggtype->getEggType($dailysortinginventory->getData('type_id'));

                        $this->jsonData['data']['eggs'][$i]['egg_type'] = $egg_type ? $egg_type->getData() : null ;
                        
                        $egg_history = $this->_dailysortinginventoryhistory->getSortinginventoryhistory($dailysortinginventory->getData('id'));

                        $this->jsonData['data']['eggs'][$i]['egg_history'] = $egg_history;

                        if ($egg_history) {
                            if ($egg_history['original_count'] > $egg_history['updated_count']) {
                                $sub = (int)$egg_history['original_count'] - (int)$egg_history['updated_count'];
                                $this->jsonData['data']['eggs'][$i]['egg_history']['egg_history_string'] = (int)$egg_history['original_count'] . ' - ' . $sub;
                            } else {
                                $sub = (int)$egg_history['updated_count'] - (int)$egg_history['original_count'];
                                $this->jsonData['data']['eggs'][$i]['egg_history']['egg_history_string'] = (int)$egg_history['original_count'] . ' + ' . $sub;
                            }
                        }
                        $i++;
                    }

                    $this->jsonData['data']['daily_harvest'] = $this->_dailyhouseharvest->getByColumn(['id'=>$dailySortingreport->getData('house_harvest_id')], 1)->getData();
                    
                    if ($dailySortingreport->getData('prepared_by')) {
                        $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/sorter/' . $dailySortingreport->getData('prepared_by'),
                            'filename' => $dailySortingreport->getData('prepared_by_path')
                        ]);
                    }
                    if ($dailySortingreport->getData('checked_by')) {
                        $this->jsonData['data']['checked_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('checked_by'));
                        $this->jsonData['data']['checked_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('checked_by'));
                        $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $dailySortingreport->getData('checked_by'),
                            'filename' => $dailySortingreport->getData('checked_by_path')
                        ]);
                    }
                    if ($dailySortingreport->getData('received_by')) {
                        $this->jsonData['data']['received_by_name'] = $this->_userProfile->getFullNameById($dailySortingreport->getData('received_by'));
                        $this->jsonData['data']['received_by_role'] = $this->_users->getRoleByUserId($dailySortingreport->getData('received_by'));
                        $this->jsonData['data']['received_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/warehouseman/' . $dailySortingreport->getData('received_by'),
                            'filename' => $dailySortingreport->getData('received_by_path')
                        ]);
                    }
            }else{
                $this->jsonData['message'] = 'No Record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>