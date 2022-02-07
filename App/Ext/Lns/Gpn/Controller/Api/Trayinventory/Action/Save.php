<?php
namespace Lns\Gpn\Controller\Api\Trayinventory\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_upload;
    protected $_trayReport;
    protected $_dateTime;
    protected $_trayinventoryreport;
    protected $_inflow;
    protected $_outflow;
    protected $_userProfile;
    protected $_users;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_deviceToken;
    protected $_audittrail;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\Inflow $Inflow,
        \Lns\Gpn\Lib\Entity\Db\Outflow $Outflow,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_upload = $Upload;
        $this->_trayReport = $TrayReport;
        $this->_dateTime = $DateTime;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_inflow = $Inflow;
        $this->_outflow = $Outflow;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_deviceToken = $DeviceToken;
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

            $user_id = $payload['payload']['jti'];

            $trays = json_decode($this->getParam('tray'));

            $save = $this->_trayReport;
            $photo = $this->upload($user_id, 'warehouseman');
            if ($photo) {
                $save->setData('prepared_by', $user_id);
                $save->setData('prepared_by_path', $photo);
                $save->setData('prepared_by_date', $this->_dateTime->getTimestamp());
                $saveId = $save->__save();

                if($saveId){
                    $hasOut = false;
                    $hasIn = false;
                    foreach ($trays as $tray) {
                        $entity = $this->_trayinventoryreport;
                        $entity->setData('type_id', $tray->type_id);
                        $entity->setData('in_return', $tray->in_return);
                        $entity->setData('sorting', $tray->sorting);
                        $entity->setData('marketing', $tray->marketing);
                        $entity->setData('out_hiram', $tray->out_hiram);
                        $entity->setData('total_end', $tray->total_end);
                        $entity->setData('tray_report_id', $saveId);
                        $trayId = $entity->__save();
                        if($tray->sorting>0|| $tray->marketing>0|| $tray->out_hiram>0){
                            $hasOut = true;
                        }
                        if($tray->in_return>0){
                            $hasIn = true;
                        }
                    }
                        /* Insert to inflow table, reference id = $sortingReportId, type = 2 default */
                    if($hasIn){
                        $entity = $this->_inflow;
                        $entity->setData('type', 2);
                        $entity->setData('reference_id', $saveId);
                        $entity->__save();
                    }

                    if ($hasOut) {
                        $entity = $this->_outflow;
                        $entity->setData('type', 2);
                        $entity->setData('reference_id', $saveId);
                        $entity->__save();
                    }

                    $warehouseman = $this->_userProfile->getFullNameById($user_id);

                    $message = "<strong>" . $warehouseman . "</strong> sent a daily report for trays";

                    $this->_audittrail->saveAudittrail($user_id, $warehouseman, $message, 'tray_report_submit');

                    $pushData = $this->_trayReport->getByColumn(['id' => $saveId], 1);
                    if ($pushData) {
                        $pushData = $pushData->getData();
                    } else {
                        $pushData = [];
                    }

                    /* Notification to Inspector - START */
                    $inspectors = $this->_users->getUsersByRole(6);
                    if ($inspectors) {
                        foreach ($inspectors as $inspector) {
                            $this->_notification->setNotification((int) $saveId, $user_id, 14, null, $message, $inspector->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                            if ($to) {
                                $title = "Daily Tray Report";
                                $message = $warehouseman . "sent a daily report for trays.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "daily_tray_report";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                /* save to cloud firestore */
                                $this->_cfs->save($inspector->getData('id'), $warehouseman, $message, 'daily_tray_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }
                    /* Notification to Inspector - END */

                    /* Notification to Inspector2 - START */
                    $inspectors = $this->_users->getUsersByRole(7);
                    if ($inspectors) {
                        foreach ($inspectors as $inspector) {
                            $this->_notification->setNotification((int) $saveId, $user_id, 14, null, $message, $inspector->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                            if ($to) {
                                $title = "Daily Tray Report";
                                $message = $warehouseman . "sent a daily report for trays";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "daily_tray_report";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                /* save to cloud firestore */
                                $this->_cfs->save($inspector->getData('id'), $warehouseman, $message, 'daily_tray_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }
                    /* Notification to Inspector2 - END */


                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Tray inventory saved!';
                }else{
                    $this->jsonData['error'] = 1;
                    $this->jsonData['message'] = 'Unable to save tray inventory. Please try again.';
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