<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class ProductionEggsize extends \Lns\Sb\Controller\Controller {

    protected $_dailysortingreport;
    protected $_dailysortinginventory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
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

            $dailysortingreports = $this->_dailysortingreport->getProductionlisting($param);

            $total_count = $this->_dailysortingreport->getProductionlisting($param, true);
            $this->jsonData = $dailysortingreports;
            $this->jsonData['total_count'] = $total_count;
            $result = [];
            if($dailysortingreports['datas']){
                foreach ($dailysortingreports['datas'] as $key => $dailysortingreport) {
                    $result[$key] = $dailysortingreport->getData();
                    /* $dailysortinginventorys = $this->_dailysortinginventory->getByColumn(['sorted_report_id'=> $dailysortingreport->getData('id')], 0); */
                    $date = date('Y-m-d', strtotime($dailysortingreport->getData('grouped_date')));
                    $dailysortinginventorys = $this->_dailysortinginventory->getByCreatedAt($date, $param);
                    if($dailysortinginventorys) {
                        foreach ($dailysortinginventorys as $dailysortinginventory) {
                            $result[$key]['daily_sorting_inventory'][] = $dailysortinginventory->getData();
                        }
                    }
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['datas'] = $result;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>