<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_house;
    protected $_medicine;
    protected $_medicine_unit;
    protected $_feeds;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
        $this->_medicine = $Medicine;
        $this->_medicine_unit = $MedicineUnit;
        $this->_feeds = $Feeds;
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

            $dailyhouseharvests = $this->_dailyhouseharvest->getFeedsandmedicineconsumption($param);
            /* $this->jsonData = $dailyhouseharvests; */
            $dailyhouseharvest = null;
            $result = [];
            $d = date('d');
            if($dailyhouseharvests){
                $i = 0;
                foreach ($dailyhouseharvests as $dailyhouseharvest) {
                    $result[$i] = $dailyhouseharvest->getData();
                    $medicine_ids = [];
                    if ($dailyhouseharvest->getData('medicine_ids')) {
                        $medicine_ids = explode(',', $dailyhouseharvest->getData('medicine_ids'));
                        $medicine_values = explode(',', $dailyhouseharvest->getData('medicine_values'));
                        $xx = 0;
                        foreach ($medicine_ids as $medicine_id) {
                            $medInfo = $this->_medicine->getMedicine($medicine_id);
                            $result[$i]['medicine_name'][$xx] = $medInfo;
                            $getUnit = $this->_medicine_unit->getByColumn(['id' => $medInfo['unit_id']], 1);
                            $result[$i]['medicine_name'][$xx]['medicine_value'] = $medicine_values[$xx];
                            $result[$i]['medicine_name'][$xx]['medicine_unit'] = $getUnit->getData();
                            $xx++;
                        }
                    }
                    if ($dailyhouseharvest->getData('feed_consumption') > 0) {
                        $feedBags = $this->_feeds->convertGramsToBags($dailyhouseharvest);
                        $result[$i]['feeds'] = $feedBags ? $feedBags : 0 ;
                    }
                    if ($dailyhouseharvest->getData('rec_feed_consumption') > 0) {
                        $req_feeds = $this->_feeds->convertGramsToBags($dailyhouseharvest, true);
                        $result[$i]['req_feeds'] = $req_feeds ? $req_feeds : 0 ;
                    }

                    if ($dailyhouseharvest->getData('feed_id')) {
                        $feedEntity = $this->_feeds->getByColumn(['id' => $dailyhouseharvest->getData('feed_id')], 1);
                        $result[$i]['feed_info'] = $feedEntity->getData();
                    }

                    $mortalityrate = 0;
                    if ($dailyhouseharvest->getData('prepared_by')) {
                        /* var_dump($dailyhouseharvest->getData('mortality'));
                        var_dump($dailyhouseharvest->getData('bird_count')); */
                        $mortalityrate = ($dailyhouseharvest->getData('mortality') / $dailyhouseharvest->getData('bird_count')) * 100;
                    }
                    $result[$i]['mortality_rate'] = $mortalityrate ? $mortalityrate : 0;

                    $end_bird_population = 0;
                    if ($dailyhouseharvest->getData('prepared_by')) {
                        $end_bird_population = $dailyhouseharvest->getData('bird_count') /* - $dailyhouseharvest->getData('mortality') */;
                    }
                    $result[$i]['end_bird_population'] = $end_bird_population ? $end_bird_population : 0;

                    $productionrate = 0;
                    if ($dailyhouseharvest->getData('prepared_by')) {
                        $productionrate = ($dailyhouseharvest->getData('real_egg_count') / $dailyhouseharvest->getData('bird_count')) * 100;
                    }
                    $result[$i]['production_rate'] = $productionrate ? $productionrate : 0;

                    $getReportcount = $this->_dailyhouseharvest->getReportcount($dailyhouseharvest->getData('house_id'), $dailyhouseharvest->getData('created_at'));
                    $d = date('d');
                    if ($getReportcount) {
                        $d = $getReportcount ? $getReportcount : date('d');
                    }
                    $i++;
                }
                $this->jsonData['data'] = $result;
            }else{
                $this->jsonData['message'] = 'No record found';
            }
            $house = $this->_house->getByColumn(['id'=>$param['house_id']], 1);
            $getlatestage = $this->_dailyhouseharvest->getLatestcockage($param['house_id'], date('Y-m-d'));

            $currentageofchicken = $this->_dailyhouseharvest->getRecordByDateHouse(NULL, $param['house_id']);
            if ($house) {
                $this->jsonData['house_details'] = array(
                    'house' => $house->getData('house_name'),
                    'daily_report_progress' => $d . '/' . date('t'),
                    // 'month' => date('F', strtotime($dailyhouseharvest->getData('created_at'))),
                    // 'year' => date('Y', strtotime($dailyhouseharvest->getData('created_at'))),
                    'beginning_population' => $currentageofchicken ? $currentageofchicken->getData('bird_count') : $house->getData('capacity'),
                    'age_chicken' => $getlatestage ? $getlatestage : null,
                    'current_age_of_chicken' => $currentageofchicken ? $currentageofchicken->getData() : null
                );
            }
            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>