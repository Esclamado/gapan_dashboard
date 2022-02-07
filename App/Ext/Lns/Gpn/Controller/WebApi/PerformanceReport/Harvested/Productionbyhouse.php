<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class Productionbyhouse extends \Lns\Sb\Controller\Controller {
    protected $_house;
    protected $_daily_sorting_report;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
        $this->_daily_sorting_report = $Dailysortingreport;
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
            $reports = $this->_daily_sorting_report->getHarvestedProductionPerHouse($param);

            $this->jsonData = $reports;

            if ($reports) {
                $i = 0;
                foreach($reports['datas'] as $report) {
                    $result[$i] = $report->getData('grouped_date');
                    $houses = $this->_house->getCollection();
                    if ($houses) {
                        $a = 0;
                        foreach($houses as $house) {
                            $result[$i]['houses'][$a] = $house->getData();
                            $created_at = date("Y-m-d H:i:s", strtotime($report->getData('grouped_date')));
                            $report = $this->_daily_sorting_report->getHarvestedProductionByHouseDate($house->getData('id'), $created_at);
                            $result[$i]['houses'][$a]['daily_sorting_report'] = $report ? $report->getData() : null;
                            $a++;
                        }
                    } else {
                        $this->jsonData['message'] = 'No record found';
                    }
                    $i++;
                }
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}