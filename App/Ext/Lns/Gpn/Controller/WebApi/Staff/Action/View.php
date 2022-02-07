<?php
namespace Lns\Gpn\Controller\WebApi\Staff\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_incidentReport;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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

            $result = [];
            $dailyhouseharvests = $this->_dailyhouseharvest->getUserdailyhouserecord($param);
            $this->jsonData = $dailyhouseharvests;
            if($dailyhouseharvests['datas']){
                foreach ($dailyhouseharvests['datas'] as $key => $dailyhouseharvest) {
                    $result[$key] = $dailyhouseharvest->getData();
                    $result[$key]['recordStatus'] = $this->_incidentReport->getIncidentReport((int) 1, (int) $dailyhouseharvest->getData('prepared_by'), (int) $dailyhouseharvest->getData('checked_by'), (int) $dailyhouseharvest->getData('received_by'), (int) $dailyhouseharvest->getData('id'), 3);
                    $result[$key]['sortingRecordstatus'] = $this->_incidentReport->getIncidentReport((int) 1, (int) $dailyhouseharvest->getData('daily_sorting_report_prepared_by'), (int) $dailyhouseharvest->getData('daily_sorting_report_checked_by'), (int) $dailyhouseharvest->getData('daily_sorting_report_received_by'), (int) $dailyhouseharvest->getData('daily_sorting_report_id'), 6);
                }
            }else{
                $this->jsonData['message'] = 'No daily house harvest record found';
            }
            $this->jsonData['datas'] = $result;
            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>