<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class Feeds extends \Lns\Sb\Controller\Controller {

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
            $reports = $this->_daily_house_harvest->getFeedConsumptionPerHouse($param);
            $total_count = $this->_daily_house_harvest->getFeedConsumptionPerHouse($param, true);
            if ($reports) {
                $i = 0;
                $this->jsonData = $reports;
                $this->jsonData['total_count'] = $total_count;
                foreach($reports['datas'] as $report) {
                    /* $grouped_date = date("Y-m-d", strtotime($report->getData('grouped_date'))); */
                    $result[$i] = array(
                        'age_week' => $report->getData('age_week'),
                        'rec_feed_consumption' => $report->getData('rec_feed_consumption')
                    );
                    $houses = $this->_house->getCollection();
                    if ($houses) {
                        $a = 0;
                        foreach($houses as $house) {
                            $result[$i]['house'][$a] = $house->getData();
                            $report2 = $this->_daily_house_harvest->getFeedConsumptionPerHouseAgeWeek($house->getData('id'), $report->getData('age_week')/* 1 */, $param);
                            $result[$i]['house'][$a]['daily_harvest_report'] = $report2 ? $report2 : null;
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
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>