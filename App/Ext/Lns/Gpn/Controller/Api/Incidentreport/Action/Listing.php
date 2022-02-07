<?php
namespace Lns\Gpn\Controller\Api\Incidentreport\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_incidentreport;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_incidentreport = $IncidentReport;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

		if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $incident_id = $this->getParam('incident_id');

            if($incident_id){
                $IncidentInfos = $this->_incidentreport->getbyColumn(['id'=>$incident_id], 0);
            }else{
                $IncidentInfos = $this->_incidentreport->getIncidentReportInfo();
            }
            
            $result = [];
            if($IncidentInfos){

                $this->jsonData['error'] = 0;

                foreach ($IncidentInfos as $IncidentInfo) {
                    $result[] = $IncidentInfo->getData();
                }
                $this->jsonData['datas'] = $result;
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