<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_dailyhouseharvest;

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
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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
            
            $items = $this->_dailyhouseharvest->getHousesWithDailyReports($param);
            $count = $this->_dailyhouseharvest->getHousesWithDailyReports($param, true);
            $this->jsonData = $items;
            $this->jsonData['total_count'] = $count;
            $result = [];
            if ($items['datas']) {
                foreach ($items['datas'] as $key => $item) {
                    $result[$key] = $item->getData();
                    $result[$key]['house'] = $this->_house->getHouse($item->getData('house_id'));
                    $getReportcount = $this->_dailyhouseharvest->getReportcount($item->getData('house_id'), $item->getData('created_at'));
                    if ($getReportcount) {
                        $result[$key]['report_count'] = $getReportcount ? $getReportcount . "/" . date('t') : date('d') . "/" . date('t');
                    } else {
                        $result[$key]['report_count'] =  0 . "/" . date('t');
                    }
                    $getUpdatedAt = $this->_dailyhouseharvest->getReportUpdatedAt($item->getData('house_id'), $item->getData('created_at'));
                    $result[$key]['updated_at'] = $getUpdatedAt ? $getUpdatedAt : $item->getData('updated_at');
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
            
            /* $houses = $this->_house->getListing($param);
            $this->jsonData = $houses;
            if($houses['datas']){
                foreach ($houses['datas'] as $key => $house) {
                    $dailyhouseharvest = $this->_dailyhouseharvest->getByColumn(['house_id'=>$house->getData('id')], 0);
                    if($dailyhouseharvest){
                        $result[$key] = $house->getData();
                        foreach ($dailyhouseharvest as $dailyhouse) {
                            $getReportcount = $this->_dailyhouseharvest->getReportcount($dailyhouse->getData('house_id'), $dailyhouse->getData('created_at'));
                            if ($getReportcount) {
                                $result[$key]['report_count'] = $getReportcount ? $getReportcount . "/" . date('t') : date('d') . "/" . date('t');
                            } else {
                                $result[$key]['report_count'] =  0 . "/" . date('t');
                            }
                            $result[$key]['last_update'] = $dailyhouse->getData('updated_at');
                        }

                    }
                }
            $this->jsonData['datas'] = $result;
            $this->jsonData['error'] = 0;
            } */
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>