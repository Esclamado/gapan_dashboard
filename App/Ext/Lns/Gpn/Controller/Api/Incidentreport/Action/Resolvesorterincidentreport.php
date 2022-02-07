<?php
namespace Lns\Gpn\Controller\Api\Incidentreport\Action;

class Resolvesorterincidentreport extends \Lns\Sb\Controller\Controller {

    protected $_incidentreport;
    protected $_upload;
    protected $_dailysortingreport;
    protected $_house;
    protected $_push;
    protected $_deviceToken;
    protected $_cfs;
    protected $_userProfile;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_incidentreport = $IncidentReport;
        $this->token = $Validate;
        $this->_upload = $Upload;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_deviceToken = $DeviceToken;
        $this->_house = $House;
        $this->_cfs = $CloudFirestore;
        $this->_userProfile = $UserProfile;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_audittrail = $AuditTrail;
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
            $photo = $this->upload($userId, 'inspector');

            if ($photo) {

                $sender_id = $userId;
                $receiver_id = $this->getParam('receiver_id');
                $type = $this->getParam('type');
                $reference_id = $this->getParam('reference_id');
                $reason = $this->getParam('reason');
                $declared_qty = $this->getParam('declared_qty');
                $validated_qty = $this->getParam('validated_qty');
                $signature_path = $photo;

                $entity = $this->_incidentreport;
                $entity->setData('sender_id',$sender_id);
                $entity->setData('receiver_id',$receiver_id);
                $entity->setData('type',$type);
                $entity->setData('reference_id',$reference_id);
                $entity->setData('reason',$reason);
                $entity->setData('declared_qty',$declared_qty);
                $entity->setData('validated_qty',$validated_qty);
                $entity->setData('signature_path',$signature_path);
                $incidentReportId = $entity->__save();
        
                if($incidentReportId){

                    $sr = $this->_dailysortingreport->getByColumn(['id'=>$reference_id], 1);
                    $house = $this->_house->getHouse($sr->getData('house_id'));

                    $pushData = $sr->getData();
                    $pushData['house'] = $house;
                    $pushData['recordStatus'] = 'Approved w/ Correction';

                    if($sr) {
                        $sr->setData('ir_status', 3);
                        $sr->__save();
                        $inspector = $this->_userProfile->getFullNameById($userId);
                        $message = "<strong>".$inspector."</strong> approved the sorted report with correction for house/building no. ".$house['house_name'].".";
    
                        $this->_notification->setNotification($sr->getData('id'), $userId, 10, null, $message, $sr->getData('prepared_by'));
                        
                        $to = $this->_deviceToken->getDeviceTokenById($sr->getData('prepared_by'));
                        if ($to) {
                            $title = "Incident Report Approved";
                            $message = $inspector. " approved the sorted report with correction for house/building no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "sorting_with_correction_resolved";
                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                            $this->_cfs->save($sr->getData('prepared_by'), null, $message, 'sorting_with_correction_resolved', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                        $action = "<strong>" . $inspector . "</strong> approved the sorted report with correction for house/building no. " . $house['house_name'] . ".";
                        $this->_audittrail->saveAudittrail($userId, $inspector, $action, 'incident_report_approve_sortedreport');
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Incident report resolved';
                } else {
                    $this->jsonData['message'] = 'Unable to resolved incident report. Please try again.';
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