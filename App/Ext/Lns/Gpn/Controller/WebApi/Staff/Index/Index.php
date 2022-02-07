<?php
namespace Lns\Gpn\Controller\WebApi\Staff\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_userprofile;
    protected $_dailyhouseharvest;
    protected $_house;
    protected $_incidentReport;
    protected $_feeds;
    protected $_medicine;
    protected $_dailysortingreport;
    protected $_users;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Sb\Lib\Entity\Db\Users $Users
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
        $this->_incidentReport = $IncidentReport;
        $this->_feeds = $Feeds;
        $this->_medicine = $Medicine;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_users = $Users;
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

            $dailyhouseId = $this->getParam('dailyhouse_id');

            $dailyhouseharvest = $this->_dailyhouseharvest->getByColumn(['id'=> $dailyhouseId], 1);
            if($dailyhouseharvest){
                $this->jsonData['data'] = $dailyhouseharvest->getData();

                $userprofile = $this->_userprofile->getFullNameById($dailyhouseharvest->getData('prepared_by'));
                if($userprofile){
                    $this->jsonData['data']['name'] = $userprofile;
                    $this->jsonData['data']['signature'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Sb',
                        'path' => '/images/uploads/signature/' . $dailyhouseharvest->getData('prepared_by'),
                        'filename' => $dailyhouseharvest->getData('prepared_by_path')
                    ]);
                }
                $house = $this->_house->getByColumn(['id'=> $dailyhouseharvest->getData('house_id')], 1);
                if($house){
                    $this->jsonData['data']['house'] = $house->getData();
                }
                if ($dailyhouseharvest->getData('medicine_ids')) {
                    $medicine_ids = explode(',', $dailyhouseharvest->getData('medicine_ids'));
                    foreach ($medicine_ids as $medicine_id) {
                        $this->jsonData['data']['medicine'][] = $this->_medicine->getMedicine($medicine_id);
                    }
                }
                $feedBags = $this->_feeds->convertGramsToBags($dailyhouseharvest);
                $req_feeds = $this->_feeds->convertGramsToBags($dailyhouseharvest, true);
                $this->jsonData['data']['feeds'] = $feedBags;
                $this->jsonData['data']['req_feeds'] = $req_feeds;

                $week = 'week';
                $day = 'day';
                if ($dailyhouseharvest->getData('age_week') > 1) {
                    $week = 'weeks';
                }
                if ($dailyhouseharvest->getData('age_day') > 1) {
                    $day = 'days';
                }
                $this->jsonData['data']['age'] = $dailyhouseharvest->getData('age_week') . " " . $week . ', ' . $dailyhouseharvest->getData('age_day') . " " . $day;

                if ($dailyhouseharvest->getData('prepared_by')) {
                    $this->jsonData['data']['prepared_by_name'] = $this->_userprofile->getFullNameById($dailyhouseharvest->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($dailyhouseharvest->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/flockman/' . $dailyhouseharvest->getData('prepared_by'),
                        'filename' => $dailyhouseharvest->getData('prepared_by_path')
                    ]);
                }
                if ($dailyhouseharvest->getData('checked_by')) {
                    $this->jsonData['data']['checked_by_name'] = $this->_userprofile->getFullNameById($dailyhouseharvest->getData('checked_by'));
                    $this->jsonData['data']['checked_by_role'] = $this->_users->getRoleByUserId($dailyhouseharvest->getData('checked_by'));
                    $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/inspector/' . $dailyhouseharvest->getData('checked_by'),
                        'filename' => $dailyhouseharvest->getData('checked_by_path')
                    ]);
                }
                if ($dailyhouseharvest->getData('received_by')) {
                    $this->jsonData['data']['received_by_name'] = $this->_userprofile->getFullNameById($dailyhouseharvest->getData('received_by'));
                    $this->jsonData['data']['received_by_role'] = $this->_users->getRoleByUserId($dailyhouseharvest->getData('received_by'));
                    $this->jsonData['data']['received_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/sorter/' . $dailyhouseharvest->getData('received_by'),
                        'filename' => $dailyhouseharvest->getData('received_by_path')
                    ]);
                }

                $this->jsonData['data']['recordStatus'] = $this->_incidentReport->getIncidentReport((int) 1, (int) $dailyhouseharvest->getData('prepared_by'), (int) $dailyhouseharvest->getData('checked_by'), (int) $dailyhouseharvest->getData('received_by'), (int) $dailyhouseharvest->getData('id'), 3);

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

                if ($dailyhouseharvest) {
                    $tracking[0] = array(
                        'message' => 'Harvest report sent',
                        'status' => 2,
                        'date' => $dailyhouseharvest->getData('prepared_by_date')
                    );
                    $tracking[1]['status'] = 1;
                    if ($dailyhouseharvest->getData('checked_by')) {
                        $tracking[2]['status'] = 1;
                        $incidentReport1 = $this->_incidentReport->getByColumn(['reference_id' => $dailyhouseharvest->getData('id'), 'type' => 1], 1);
                        if ($incidentReport1) {
                            $incidentReport2 = $this->_incidentReport->getByColumn(['reference_id' => $dailyhouseharvest->getData('id'), 'type' => 2], 1);
                            if ($incidentReport2) {
                                $incidentReport3 = $this->_incidentReport->getByColumn(['reference_id' => $dailyhouseharvest->getData('id'), 'type' => 3], 1);
                                if ($incidentReport3) {
                                    $tracking[1] = array(
                                        'message' => 'Approved with Incident Report',
                                        'status' => 2,
                                        'date' => $incidentReport3->getData('created_at')
                                    );
                                } else {
                                    $tracking[1] = array(
                                        'message' => 'Pending Inspector Resolve',
                                        'can_proceed' => 1,
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
                                'date' => $dailyhouseharvest->getData('checked_by_date')
                            );
                        }
                    }
                    $sortInfo = $this->_dailysortingreport->getByColumn(['house_harvest_id' => $dailyhouseharvest->getData('id')], 1);
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
                                    'date' => $dailyhouseharvest->getData('checked_by_date')
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
                    $this->jsonData['data']['tracking_status'] = $tracking;
                } else {
                    $this->jsonData['message'] = "This report does not exist.";
                }
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>