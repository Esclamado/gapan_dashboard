<?php
namespace Lns\Gpn\Controller\Api\Dailyhouseharvest\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_medicine;
    protected $_medicine_unit;
    protected $_house;
    protected $_incidentReport;
    protected $_feeds;
    protected $_userProfile;
    protected $_users;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_medicine = $Medicine;
        $this->_house = $House;
        $this->_feeds = $Feeds;
        $this->_incidentReport = $IncidentReport;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->token = $Validate;
        $this->_medicine_unit = $MedicineUnit;
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
            $house_harvest_id = $this->getParam('house_harvest_id');

            $hasRecords = $this->_dailyhouseharvest->getByColumn(['id'=>$house_harvest_id], 1);
    
            $result = [];
            if($hasRecords){
    
                $this->jsonData['error'] = 0;
    
                $house = $this->_house->getHouse($hasRecords->getData('house_id'));
                $feed = $this->_feeds->getFeed($hasRecords->getData('feed_id'));
                $feeds = $this->_feeds->convertGramsToBags($hasRecords);
                $rec_feeds = $this->_feeds->convertGramsToBags($hasRecords,true);
    
                if ($hasRecords->getData('medicine_ids')) {
                   
                    $medicine_ids = explode(',', $hasRecords->getData('medicine_ids'));
                    $medicine_values = explode(',', $hasRecords->getData('medicine_values'));
                    $x = 0;
                    foreach ($medicine_ids as $medicine_id) {
                        $medicine = $this->_medicine->getMedicine($medicine_id);
                        $result[$x] = $medicine;
                        $medicine_unit = $this->_medicine_unit->getByColumn(['id' => $medicine['unit_id']], 1);
                        $result[$x]['unit'] = $medicine_unit->getData('unit');
                        $result[$x]['medicine_value'] = $medicine_values[$x];
                        $x++;
                    }
                }       
                $this->jsonData['data'] = $hasRecords->getData();
                $this->jsonData['data']['medicine'] = $result;
                $this->jsonData['data']['house'] = $house;
                $this->jsonData['data']['feed'] = $feed;
                $this->jsonData['data']['feeds'] = $feeds;
                $this->jsonData['data']['rec_feeds'] = $rec_feeds;
    
                if ($hasRecords->getData('prepared_by')) {
                    $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($hasRecords->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($hasRecords->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/flockman/' . $hasRecords->getData('prepared_by'),
                        'filename' => $hasRecords->getData('prepared_by_path')
                    ]);
                }
                if ($hasRecords->getData('checked_by')) {
                    $this->jsonData['data']['checked_by_name'] = $this->_userProfile->getFullNameById($hasRecords->getData('checked_by'));
                    $this->jsonData['data']['checked_by_role'] = $this->_users->getRoleByUserId($hasRecords->getData('checked_by'));
                    $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/inspector/' . $hasRecords->getData('checked_by'),
                        'filename' => $hasRecords->getData('checked_by_path')
                    ]);
                }
                if ($hasRecords->getData('received_by')) {
                    $this->jsonData['data']['received_by_name'] = $this->_userProfile->getFullNameById($hasRecords->getData('received_by'));
                    $this->jsonData['data']['received_by_role'] = $this->_users->getRoleByUserId($hasRecords->getData('received_by'));
                    $this->jsonData['data']['received_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/sorter/' . $hasRecords->getData('received_by'),
                        'filename' => $hasRecords->getData('received_by_path')
                    ]);
                }
                $hasIncidentReport = $this->_incidentReport->validate(1, $hasRecords->getData('id'));
                $this->jsonData['data']['hasIncidentReport'] = $hasIncidentReport ? $hasIncidentReport->getData() : null;
                $this->jsonData['data']['recordStatus'] = $this->_incidentReport->getIncidentReport((int)$userId, (int)$hasRecords->getData('prepared_by'), (int)$hasRecords->getData('checked_by'), (int)$hasRecords->getData('received_by'), (int)$hasRecords->getData('id'), 3);
            }else{
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = 'No Record Found';
            }
        }


        $this->jsonEncode($this->jsonData);
		die;
    }
}

?>