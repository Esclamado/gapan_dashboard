<?php
namespace Lns\Gpn\Controller\Api\IncidentReport\Action;

class Sorterincidentreport extends \Lns\Sb\Controller\Controller {

    protected $_cfs;
    protected $_dateTime;
    protected $_incidentReport;
    protected $_upload;
    protected $_notification;
    protected $_push;
    protected $_deviceToken;
    protected $_userProfile;
    protected $_house;
    protected $_dailyHouseHarvest;
    protected $_dailysortingreport;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dateTime = $DateTime;
        $this->_incidentReport = $IncidentReport;
        $this->_upload = $Upload;
        $this->_userProfile = $UserProfile;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_deviceToken = $DeviceToken;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_house = $House;
        $this->_cfs = $CloudFirestore;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_audittrail = $AuditTrail;
    }

    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $result = [];
        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {

            $userId = $payload['payload']['jti'];
            $photo = $this->upload($userId, 'sorter');

            if ($photo) {
                $entity = $this->_incidentReport;
                $entity->setDatas([
                    'sender_id' => $userId,
                    'receiver_id' => $this->getPost('receiver_id'),
                    'type' => 5,
                    'reason' => $this->getPost('reason'),
                    'declared_qty' => (int)$this->getPost('declared_qty'),
                    'validated_qty' => (int)$this->getPost('validated_qty'),
                    'reference_id' => $this->getPost('reference_id'),
                    'signature_path' => $photo
                ]);
                $save = $entity->__save();

                if ($save) {
                    /* send notif to inspector */
                    /* $record = $this->_dailyHouseHarvest->getRecordById($this->getPost('reference_id')); */

                    $entity = $this->_dailysortingreport->getByColumn(['id'=>$this->getPost('reference_id')], 1);
                    $record = $entity;
                    $house = $this->_house->getHouse($record->getData('house_id'));

                    $pushData = $entity->getData();
                    $pushData['house'] = $house;
                    $pushData['recordStatus'] = 'Pending Inspector Resolve';

                    $entity->setData('ir_status',2);
                    $entity->__save();

                    
                    $sorter = $this->_userProfile->getFullNameById($userId);

                    $message = "<strong>".$sorter."</strong> filed an incident report for house no. ".$house['house_name'] . ".";
                    
                    $this->_notification->setNotification((int)$record->getData('id'), $userId, 9, null, $message, $this->getPost('receiver_id'));
                    
                    $to = $this->_deviceToken->getDeviceTokenById($this->getPost('receiver_id'));
                    if ($to) {
                        $title = "Incident Report";
                        $message = $sorter. " filed an incident report for house no. ".$house['house_name'] . ".";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "sorter_file_incident_report";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        /* send notif to inspector */
                        $this->_cfs->save($this->getPost('receiver_id'), $sorter, $message, 'sorter_file_incident_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $action = "<strong>" . $sorter . "</strong> filed an incident report for house no. " . $house['house_name'] . ".";
                    $this->_audittrail->saveAudittrail($userId, $sorter, $action, 'incident_report_submit_sorter');
                   
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Incident report sent!';

                }else {
                    $this->jsonData['message'] = 'Unable to submit incident report. Please try again.';
                }
            }else {
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