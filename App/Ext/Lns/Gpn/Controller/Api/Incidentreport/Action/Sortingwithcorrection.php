<?php
namespace Lns\Gpn\Controller\Api\Incidentreport\Action;

class Sortingwithcorrection extends \Lns\Sb\Controller\Controller {

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
    protected $_dailysortingreport;
    protected $_dailysortinginventory;
    protected $_dailysortinginventoryhistory;
    protected $_audittrail;

    protected $token;
    protected $payload;

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
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventoryhistory $Dailysortinginventoryhistory,
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
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_dailysortinginventoryhistory = $Dailysortinginventoryhistory;
        $this->_audittrail = $AuditTrail;
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
            $record_id = $this->getParam('reference_id');
            $eggs = json_decode($this->getParam('egg'));
            $real_egg_count = $this->getParam('real_egg_count');
            $photo = $this->upload($userId, 'inspector');
            if ($photo) {
                $entity = $this->_incidentReport;
                $entity->setDatas([
                    'sender_id' => $userId,
                    'receiver_id' => $this->getPost('receiver_id'),
                    'type' => 4,
                    'reason' => $this->getPost('reason'),
                    'declared_qty' => (int)$this->getPost('egg_count'),
                    'validated_qty' => (int)$this->getPost('real_egg_count'),
                    'reference_id' => $this->getPost('reference_id'),
                    'signature_path' => $photo
                ]);
                $save = $entity->__save();

                if($save){
                    if ($record_id) {
                        $entity = $this->_dailysortingreport->getByColumn(['id'=>$record_id], 1);
                        if($entity){
                            $house = $this->_house->getHouse($entity->getData('house_id'));

                            $pushData = $entity->getData();
                            $pushData['house'] = $house;
                            $pushData['recordStatus'] = 'Proceed w/ Correction';

                            if ($eggs) {
                                foreach ($eggs as $egg) {
                                    $eggInfo = $this->_dailysortinginventory->getByColumn(['id'=>$egg->id], 1);
                                    if($eggInfo->getData('egg_count') != $egg->count){
                                        $save = $this->_dailysortinginventoryhistory;
                                        $save->setData('sorted_inv_id',$eggInfo->getData('id'));
                                        $save->setData('original_count',$eggInfo->getData('egg_count'));
                                        $save->setData('updated_count',$egg->count);
                                        $saveId = $save->__save();
        
                                        if($saveId){
                                            $eggInfo->setData('egg_count',$egg->count);
                                            $updatedEggId = $eggInfo->__save();
                                        }
                                    }
                                }
                            }
                            $entity->setData('real_egg_count', $real_egg_count);
                            /* $photo = $this->upload($userId, 'inspector'); */
                            $entity->setData('checked_by', $userId);
                            $entity->setData('checked_by_path', $photo);
                            $entity->setData('checked_by_date', $this->_dateTime->getTimestamp());
                            $entity->setData('ir_status', 1);
                            $sortingInventoryId = $entity->__save();
        
                            if($sortingInventoryId){
                                $inspector = $this->_userProfile->getFullNameById($userId);
                                /* $house = $this->_house->getHouse($entity->getData('house_id')); */
                                /* send notif to sorter to file incident report */
                                $message = "<strong>Sorted Report for house/building no. ".$house['house_name']."</strong> must file an incident report within this day.";
                                $this->_notification->setNotification($this->getPost('reference_id'), $userId, 8, null, $message, $this->getPost('receiver_id'));
                                
                                $to = $this->_deviceToken->getDeviceTokenById($this->getPost('receiver_id'));
                                if ($to) {
                                    $title = "Incident Report Required";
                                    $message = "Sorted Egg Report for house/building no. ".$house['house_name']." must file an incident report within this day.";
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "sorting_with_correction";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    /* send notif to inspector */
                                    $this->_cfs->save($this->getPost('receiver_id'), null, $message, 'sorting_with_correction', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                                $action = "<strong> . $inspector . </strong> sent a message to file an incident report for house/building no. " . $house['house_name'] . ".";
                                $this->_audittrail->saveAudittrail($userId, $inspector, $action, 'incident_report_mustfile');
                                /* send notif to sorter to file incident report */
                                /* send notif to warehouseman */
                                $warehousemans = $this->_users->getUsersByRole(10);
                                if ($warehousemans) {
                                    
                                    foreach ($warehousemans as $warehouseman) {
                                        /* $sorter = $this->_userProfile->getFullNameById($this->getPost('receiver_id')); */
        
                                        $message = "<strong>".$inspector."</strong> sent you a sorted egg report for house/building no. ".$house['house_name'].".";
                                        
                                        $this->_notification->setNotification($entity->getData('id'), $userId, 11, null, $message, $warehouseman->getData('id'));
                                        
                                        $to = $this->_deviceToken->getDeviceTokenById($warehouseman->getData('id'));
                                        if ($to) {
                                            $title = "Sorted Egg Report";
                                            $message = $inspector. " sent you a sorted egg report for house/building no. ".$house['house_name'].".";
                                            $content = "Content Here";
                                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                                            $pushAction = "sorted_report_success";
                                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                            /* send notif to inspector */
                                            $pushData['recordStatus'] = 'For Receive';
                                            $this->_cfs->save($warehouseman->getData('id'), $inspector, $message, 'sorted_report_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                        }
                                    }
                                    $action = "<strong>" . $inspector . "</strong> sent a sorted egg report for house/building no. " . $house['house_name'] . ".";
                                    $this->_audittrail->saveAudittrail($userId, $inspector, $action, 'incident_report_sentyou');
                                }
                                /* send notif to warehouseman */
                                $this->jsonData['error'] = 0;
                                $this->jsonData['message'] = 'Request for incident report sent!';
                            }else{
                                $this->jsonData['error'] = 1;
                                $this->jsonData['message'] = 'Unable to submit request. Please try again.';
                            }
                        }
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