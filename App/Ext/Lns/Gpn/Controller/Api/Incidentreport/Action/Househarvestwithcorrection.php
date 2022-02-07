<?php
namespace Lns\Gpn\Controller\Api\Incidentreport\Action;

class Househarvestwithcorrection extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;
    protected $_cfs;
    protected $_dailyHouseHarvest;
    protected $_house;
    protected $_dateTime;
    protected $_incidentReport;
    protected $_upload;
    protected $_push;
    protected $_deviceToken;
    protected $_notification;
    protected $_users;
    protected $_userProfile;
    protected $_audittrail;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_dateTime = $DateTime;
        $this->_incidentReport = $IncidentReport;
        $this->_upload = $Upload;
        $this->_push = $PushNotification;
        $this->_deviceToken = $DeviceToken;
        $this->_house = $House;
        $this->_notification = $Notification;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->_cfs = $CloudFirestore;
        $this->_audittrail = $AuditTrail;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $result = [];
        $this->jsonData['error'] = 1;

		if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $photo = $this->upload($userId, 'inspector');
            if ($photo) {
                $entity = $this->_incidentReport;
                $entity->setDatas([
                    'sender_id' => $userId,
                    'receiver_id' => $this->getPost('receiver_id'),
                    'type' => 1,
                    'reason' => $this->getPost('reason'),
                    'declared_qty' => (int)$this->getPost('egg_count'),
                    'validated_qty' => (int)$this->getPost('real_egg_count'),
                    'reference_id' => $this->getPost('reference_id'),
                    'signature_path' => $photo
                ]);
                $save = $entity->__save();
                if ($save) {
                    $entity = $this->_dailyHouseHarvest->getRecordById($this->getPost('reference_id'));
                    $house = $this->_house->getHouse($entity->getData('house_id'));

                    $pushData = $entity->getData();
                    $pushData['house'] = $house;
                    $pushData['recordStatus'] = 'Proceed w/ Correction';

                    if ($entity) {
                        $entity->setData('mortality', (int)$this->getPost('mortality'));
                        $entity->setData('real_egg_count', (int)$this->getPost('real_egg_count'));
                        $entity->setData('bird_count', (int)$this->getPost('bird_count'));
                        $entity->setData('prepared_by_isdraft', 0);
                        $entity->setData('checked_by', $userId);
                        $entity->setData('checked_by_path', $photo);
                        $entity->setData('checked_by_date', $this->_dateTime->getTimestamp());
                        $save = $entity->__save();
                        if ($save) {
                            /* $house = $this->_house->getHouse($entity->getData('house_id')); */
                            $inspector = $this->_userProfile->getFullNameById($userId);
                            /* send notif to flockman to file incident report */
                            $message = "<strong>Daily Report for house/building no. ".$house['house_name']."</strong> must file an incident report within this day.";
                            $this->_notification->setNotification($this->getPost('reference_id'), $userId, 2, null, $message, $this->getPost('receiver_id'));
                            
                            $to = $this->_deviceToken->getDeviceTokenById($this->getPost('receiver_id'));
                            if ($to) {
                                $title = "Incident Report Required";
                                $message = "Daily Report for house/building no. ".$house['house_name']." must file an incident report within this day.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "house_harvest_with_correction";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);
                                
                                /* send notif to inspector */
                                $pushData['recordStatus'] = 'Proceed w/ Correction';
                                $this->_cfs->save($this->getPost('receiver_id'), '', $message, 'house_harvest_with_correction', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                            $action = "<strong> . $inspector . </strong> sent a message to file an incident report for house/building no. " . $house['house_name'] . ".";
                            $this->_audittrail->saveAudittrail($userId, $inspector, $message, 'incident_report_mustfile_dhr');
                            /* send notif to flockman to file incident report */
                            /* send notif to sorter */
                            $sorters = $this->_users->getUsersByRole(9);
                            if ($sorters) {
                                foreach ($sorters as $sorter) {
                                    /* $flockman = $this->_userProfile->getFullNameById($this->getPost('receiver_id')); */

                                    $message = "<strong>".$inspector."</strong> sent you a daily house report for house/building no. ".$house['house_name'].".";
                                    
                                    $this->_notification->setNotification($entity->getData('id'), $userId, 5, null, $message, $sorter->getData('id'));
                                    
                                    $to = $this->_deviceToken->getDeviceTokenById($sorter->getData('id'));
                                    if ($to) {
                                        $title = "Daily House Harvest";
                                        $message = $inspector. " sent you a daily house report for house/building no. ".$house['house_name'].".";
                                        $content = "Content Here";
                                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                                        $pushAction = "daily_house_harvest_success";
                                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                        /* send notif to inspector */
                                        $pushData['recordStatus'] = 'For Receive';
                                        $this->_cfs->save($sorter->getData('id'), $inspector, $message, 'daily_house_harvest_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'));
                                    }
                                }
                                $action = "<strong>" . $inspector . "</strong> sent a daily house report for house/building no. " . $house['house_name'] . ".";
                                $this->_audittrail->saveAudittrail($userId, $inspector, $action, 'incident_report_sentyou');
                            }
                            /* $message = "<strong>Daily Report for house/building no. ".$house['house_name']."</strong> must file an incident report within this day.";
                            $this->_notification->setNotification($this->getPost('reference_id'), $userId, 2, null, $message, $this->getPost('receiver_id'));
                            
                            $to = $this->_deviceToken->getDeviceTokenById($this->getPost('receiver_id'));
                            if ($to) {
                                $title = "Incident Report Required";
                                $message = "Daily Report for house/building no. ".$house['house_name']." must file an incident report within this day.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "house_harvest_with_correction";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);
                            } */
                            /* send notif to sorter */
                            
                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'Request for incident report sent!';
                        } else {
                            $this->jsonData['message'] = 'Unable to submit request. Please try again.';
                        }
                    } else {
                        $this->jsonData['message'] = 'Unable to locate report. Please try again.';
                    }
                } else {
                    $this->jsonData['message'] = 'Unable to submit request. Please try again.';
                }
            } else {
                $this->jsonData['message'] = 'Unable to upload signature. Please try again.';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId, $type) {
        $path = 'Lns'.DS.'Gpn'.DS.'View'.DS.'images'.DS.'uploads'.DS.'signature';

        if ($type) {
            $path .= DS.$type;
        }
        
        $path .= DS.$userId;

        $file = $this->getFile('photo');
        
        $fileName = null;
        if($file){
            $_file = $this->_upload->setFile($file)
            ->setPath($path)
            ->setAcceptedFile(['ico','jpg','png','jpeg'])
            ->save();

            if($_file['error'] == 0){
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }   
        }
        return $fileName;
    }
}
?>