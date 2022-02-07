<?php
namespace Lns\Gpn\Controller\Api\Internal\Action;

class Status extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_dailysortingreport;
    protected $_incidentReport;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_incidentReport = $IncidentReport;
     }

     public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $dhrInfo = $this->_dailyhouseharvest->getByColumn(['id' => $this->getParam('id')], 1);
            
            $tracking = array(
                array(
                    'message' => 'Pending Harvest Report',
                    'status' => 0,
                    'date' => null
                ),
                array(
                    'message' => 'Pending for Approval',
                    'status' => 0,
                    'date' => null
                ),
                array(
                    'message' => 'For Sorting Report',
                    'status' => 0,
                    'date' => null
                ),
                array(
                    'message' => 'Pending for Approval',
                    'status' => 0,
                    'date' => null
                ),
                array(
                    'message' => 'Transfer to warehouse',
                    'status' => 0,
                    'date' => null
                ),
            );

            if ($dhrInfo) {
                $tracking[0] = array(
                    'message' => 'Harvest report sent',
                    'status' => 2,
                    'date' => $dhrInfo->getData('prepared_by_date')
                );
                $tracking[1]['status'] = 1;
                if ($dhrInfo->getData('checked_by')) {
                    $tracking[2]['status'] = 1;
                    $incidentReport1 = $this->_incidentReport->getByColumn(['reference_id' => $dhrInfo->getData('id'), 'type' => 1], 1);
                    if ($incidentReport1) {
                        $incidentReport2 = $this->_incidentReport->getByColumn(['reference_id' => $dhrInfo->getData('id'), 'type' => 2], 1);
                        if ($incidentReport2) {
                            $incidentReport3 = $this->_incidentReport->getByColumn(['reference_id' => $dhrInfo->getData('id'), 'type' => 3], 1);
                            if ($incidentReport3) {
                                $tracking[1] = array(
                                    'message' => 'Approved with Incident Report',
                                    'status' => 2,
                                    'date' => $incidentReport3->getData('created_at')
                                );
                            } else {
                                $tracking[1] = array(
                                    'message' => 'Pending Inspector Resolve',
                                    'can_proceed'=>1,
                                    /* 'note' => 'Your Incident Report needs to be signed by your Inspector', */
                                    'status' => 1,
                                    'date' => $incidentReport2->getData('created_at')
                                );
                            }
                        } else {
                            $tracking[1] = array(
                                'message' => 'Proceed with Correction',
                                'can_proceed' => 1,
                                /* 'note' => 'You need to file this report upon proceeding', */
                                'status' => 1,
                                'date' => $incidentReport1->getData('created_at')
                            );
                        }
                    } else {
                        $tracking[1] = array(
                            'message' => 'Approved Daily House Report',
                            'status' => 2,
                            'date' => $dhrInfo->getData('checked_by_date')
                        );
                    }
                }
                $sortInfo = $this->_dailysortingreport->getByColumn(['house_harvest_id' => $dhrInfo->getData('id')], 1);
                if ($sortInfo) {
                    $tracking[3]['status'] = 1;
                    $tracking[2] = array(
                        'message' => 'Sorting Report sent',
                        'status' => 2,
                        'date' => $sortInfo->getData('prepared_by_date')
                    );
                    if ($sortInfo->getData('checked_by')) {
                        $tracking[4]['status'] = 1;
                        $incidentReport4 = $this->_incidentReport->getByColumn(['reference_id' => $sortInfo->getData('house_harvest_id'), 'type' => 4], 1);
                        if ($incidentReport4) {
                            $incidentReport5 = $this->_incidentReport->getByColumn(['reference_id' => $sortInfo->getData('house_harvest_id'), 'type' => 5], 1);
                            if ($incidentReport5) {
                                $incidentReport6 = $this->_incidentReport->getByColumn(['reference_id' => $sortInfo->getData('house_harvest_id'), 'type' => 6], 1);
                                if ($incidentReport6) {
                                    $tracking[3] = array(
                                        'message' => 'Approved with Incident Report',
                                        'status' => 2,
                                        'date' => $incidentReport6->getData('created_at')
                                    );
                                } else {
                                    $tracking[3] = array(
                                        'message' => 'Pending Inspector Resolve',
                                        'can_proceed' => 1,
                                        /* 'note' => 'Your Incident Report needs to be signed by your Inspector', */
                                        'status' => 1,
                                        'date' => $incidentReport5->getData('created_at')
                                    );
                                }
                            } else {
                                $tracking[3] = array(
                                    'message' => 'Proceed with Correction',
                                    'can_proceed' => 1,
                                    /* 'note' => 'You need to file this report upon proceeding', */
                                    'status' => 1,
                                    'date' => $incidentReport4->getData('created_at')
                                );
                            }
                        } else {
                            $tracking[3] = array(
                                'message' => 'Approved Sorting Report',
                                'status' => 2,
                                'date' => $dhrInfo->getData('checked_by_date')
                            );
                        }
                    }
                    if ($sortInfo->getData('received_by')) {
                        $tracking[4] = array(
                            'message' => 'Transferred to warehouse',
                            'status' => 2,
                            'date' => $sortInfo->getData('received_by_date')
                        );
                    }
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $tracking;
            } else {
                $this->jsonData['message'] = "This report does not exist.";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>