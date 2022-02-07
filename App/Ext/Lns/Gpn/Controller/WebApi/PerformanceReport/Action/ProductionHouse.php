<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class ProductionHouse extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_daily_house_harvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
        $this->_daily_house_harvest = $Dailyhouseharvest;
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
            $reports = $this->_daily_house_harvest->getHarvestedProductionPerHouse($param);
            $total_count = $this->_daily_house_harvest->getHarvestedProductionPerHouse($param, true);
            if ($reports) {
                $i = 0;
                $this->jsonData = $reports;
                $this->jsonData['total_count'] = $total_count;
                foreach($reports['datas'] as $report) {
                    $grouped_date = date("Y-m-d", strtotime($report->getData('grouped_date')));
                    $result[$i] = array('grouped_date' => $report->getData('grouped_date'));
                    $houses = $this->_house->getCollection();
                    if ($houses) {
                        $a = 0;
                        foreach($houses as $house) {
                            $result[$i]['house'][$a] = $house->getData();
                            $report = $this->_daily_house_harvest->getHarvestedProductionByHouseDate($house->getData('id'), $grouped_date);
                            $result[$i]['house'][$a]['daily_sorting_report'] = $report ? $report->getData() : null;
                            $a++;
                        }
                    } else {
                        $this->jsonData['message'] = 'No record found';
                    }
                    $i++;
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['datas'] = $result;
            } else {
                $this->jsonData['message'] = 'No record found';
            }
            /* $houses = $this->_house->getProductionlisting($param);
            $this->jsonData = $houses;
            $result = [];
            if($houses['datas']){
                foreach ($houses['datas'] as $key => $house) {
                    $result[$key] = array(
                        'house_id'=>$house->getData('id'),
                        'house_name'=>$house->getData('house_name'),
                        'capacity' => $house->getData('capacity')
                    );
                    $dailyhouseharvests = $this->_dailyhouseharvest->getByColumn(['house_id'=> $house->getData('id')], 0);
                    if($dailyhouseharvests){
                        foreach ($dailyhouseharvests as $key2 => $dailyhouseharvest) {
                            $result[$key]['daily_house_harvest'][$key2] = $dailyhouseharvest->getData();
                            $productionrate = $dailyhouseharvest->getData('real_egg_count') / $dailyhouseharvest->getData('bird_count') * 100;
                            $result[$key]['daily_house_harvest'][$key2]['production_rate'] = $productionrate ? $productionrate : 0;
                        }
                    }
                }
                $this->jsonData['datas'] = $result;
            } */
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>