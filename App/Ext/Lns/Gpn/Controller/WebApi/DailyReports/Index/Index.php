<?php
namespace Lns\Gpn\Controller\WebApi\DailyReports\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_dailyhouseharvest;
    protected $_medicine;
    protected $_feeds;
    protected $_userprofile;
    protected $_incidentReport;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_medicine = $Medicine;
        $this->_feeds = $Feeds;
        $this->_userprofile = $UserProfile;
        $this->_incidentReport = $IncidentReport;
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

            $dailyhousereports = $this->_dailyhouseharvest->getHouserecord($param);
            $this->jsonData = $dailyhousereports;
            $houses = $this->_house->getByColumn(['id' => $param['house_id']], 1);
            $this->jsonData['data']['house_name'] = $houses->getData();
            
            if ($dailyhousereports) {
                
                $househarvest = $this->_dailyhouseharvest->getByColumn(['house_id' => $param['house_id']], 1);
                $week = 'week';
                $day = 'day';
                if ($houses) {
                    if($househarvest && $househarvest->getData('age_week')>1){
                        $week = 'weeks';
                    }
                    if($househarvest && $househarvest->getData('age_day') > 1){
                        $day = 'days'; 
                    }
                    $this->jsonData['data'] = array(
                        'house_name' => $houses->getData(),
                        'beginning_population' => $househarvest ? $househarvest->getData('bird_count') : 0,
                        'beginning_age' => $househarvest ? $househarvest->getData('age_week') . ' ' . $week . ', ' . $househarvest->getData('age_day') . ' ' . $day : NULL,
                    );
                    $this->jsonData['error'] = 0;
                }
    
                $result = [];
                if($dailyhousereports['datas']){
                    foreach ($dailyhousereports['datas'] as $key => $dailyhousereport) {
                        $result[] = $dailyhousereport->getData();
    
                        $week = 'week';
                        $day = 'day';
                        if($dailyhousereport->getData('age_week')>1){
                            $week = 'weeks';
                        }
                        if($dailyhousereport->getData('age_day')>1){
                            $day = 'days';
                        }
                        $result[$key]['age'] = $dailyhousereport->getData('age_week') . " " . $week . ', ' . $dailyhousereport->getData('age_day') . " " . $day;
    
                        
                        $mortalityrate = $dailyhousereport->getData('mortality') / $dailyhousereport->getData('bird_count') * 100;
                        $result[$key]['mortality_rate'] = $mortalityrate ? $mortalityrate : 0;
                        
                        $end_bird_population = $dailyhousereport->getData('bird_count') - $dailyhousereport->getData('mortality');
                        $result[$key]['end_bird_population'] = $end_bird_population ? $end_bird_population : 0;
    
                        $productionrate = $dailyhousereport->getData('real_egg_count') / $dailyhousereport->getData('bird_count') * 100;
                        $result[$key]['production_rate'] = $productionrate ? $productionrate : 0;
    
                        $user = $this->_userprofile->getFullNameById($dailyhousereport->getData('prepared_by'));
                        $result[$key]['flockman'] = $user;
    
                        $feedBags = $this->_feeds->convertGramsToBags($dailyhousereport);
                        $req_feeds = $this->_feeds->convertGramsToBags($dailyhousereport, true);
                        $result[$key]['feeds'] = $feedBags;
                        $result[$key]['req_feeds'] = $req_feeds;
    
                        if ($dailyhousereport->getData('medicine_ids')) {
                            $medicine_ids = explode(',', $dailyhousereport->getData('medicine_ids'));
                            foreach ($medicine_ids as $medicine_id) {
                                $result[$key]['medicine_name'][] = $this->_medicine->getMedicine($medicine_id);
                            }
                        }
    
                        $result[$key]['recordStatus'] = $this->_incidentReport->getIncidentReport((int) 1, (int) $dailyhousereport->getData('prepared_by'), (int) $dailyhousereport->getData('checked_by'), (int) $dailyhousereport->getData('received_by'), (int) $dailyhousereport->getData('id'), 3);
                        $result[$key]['sortingRecordstatus'] = $this->_incidentReport->getIncidentReport((int) 1, (int) $dailyhousereport->getData('daily_sorting_report_prepared_by'), (int) $dailyhousereport->getData('daily_sorting_report_checked_by'), (int) $dailyhousereport->getData('daily_sorting_report_received_by'), (int) $dailyhousereport->getData('daily_sorting_report_id'), 6);
     
                    }
                    $this->jsonData['datas'] = $result;
                }
            } else {
                $this->jsonData['error'] = 1;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>