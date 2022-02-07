<?php
namespace Lns\Gpn\Controller\Api\Sackinventory\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_sackInventory;
    protected $_sackBldgInventory;
    protected $_dateTime;
    protected $_upload;
    protected $token;
    protected $payload;
    protected $_inflow;
    protected $_outflow;
    protected $_userProfile;
    protected $_users;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_deviceToken;
    protected $_audittrail;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
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
        $this->_sackInventory = $Sackinventory;
        $this->_sackBldgInventory = $Sackbldginventory;
        $this->_dateTime = $DateTime;
        $this->_upload = $Upload;
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
            $this->jsonData['error'] = 1;

            $id = $this->getParam('id');
            $total_out = $this->getParam('total_out');
            $sales = $this->getParam('sales');
            $remarks = $this->getParam('remarks') || $this->getParam('remarks')=='null' ? $this->getParam('remarks') : null;
            $remarks_out = $this->getParam('remarks_out') || $this->getParam('remarks_out')=='null' ? $this->getParam('remarks_out') : null;
            $last_ending = $this->getParam('last_ending');
            $prepared_by = $payload['payload']['jti'];

            $sackBldgs = json_decode($this->getParam('sackbldg'));

            if ($id) {
                $entity = $this->_sackInventory->getByColumn(['id' => $id], 1);
            } else {
                $entity = $this->_sackInventory;
            }
            $photo = $this->upload($prepared_by, 'warehouseman');
            if ($photo) {
                $entity->setData('total_out', $total_out);
                $entity->setData('sales', $sales);
                if ($remarks) {
                    $entity->setData('remarks', $remarks);
                }
                if ($remarks_out) {
                    $entity->setData('remarks_out', $remarks_out);
                }
                $entity->setData('last_ending', $last_ending);
                $entity->setData('prepared_by', $prepared_by);
                $entity->setData('prepared_by_path', $photo);
                $entity->setData('prepared_by_date', $this->_dateTime->getTimestamp());
                $entityId = $entity->__save();
                if ($entityId) {

                        if($total_out>0||$sales>0){
                            $entity = $this->_outflow;
                            $entity->setData('type', 3);
                            $entity->setData('reference_id', $entityId);
                            $entity->__save();
                        }

                    if ($sackBldgs) {
                        $total_in = 0;
                        $hasIn = false;
                        foreach ($sackBldgs as $sackBldg) {
                            $total_in += (int)$sackBldg->count;
                            $entity = $this->_sackBldgInventory;
                            $entity->setData('sack_inv_id', $entityId);
                            $entity->setData('house_id', $sackBldg->house_id);
                            $entity->setData('count', $sackBldg->count);
                            $entity->__save();
                            if($sackBldg->count>0){
                                $hasIn = true;
                            }
                        }
                    }
                    if($hasIn){
                        /* Insert to inflow table, reference id = $sortingReportId, type = 2 default */
                        $entity = $this->_inflow;
                        $entity->setData('type', 3);
                        $entity->setData('reference_id', $entityId);
                        $entity->__save();
                    }
                    
                    $entity = $this->_sackInventory->getByColumn(['id' => $entityId], 1);
                    $entity->setData('total_in', $total_in);
                    $entity->__save();

                    $warehouseman = $this->_userProfile->getFullNameById($prepared_by);

                    $message = "<strong>" . $warehouseman . "</strong> sent a daily report for sacks";

                    $this->_audittrail->saveAudittrail($prepared_by, $warehouseman, $message, 'sack_report_submit');
                    
                    $pushData = $this->_sackInventory->getByColumn(['id' => $entityId], 1);
                    if($pushData){
                        $pushData = $pushData->getData();
                    }else {
                        $pushData = [];
                    }

                    /* Notification to Inspector - START */
                    $inspectors = $this->_users->getUsersByRole(6);
                    if ($inspectors) {
                        foreach ($inspectors as $inspector) {
                            $this->_notification->setNotification((int) $entityId, $prepared_by, 13, null, $message, $inspector->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                            if ($to) {
                                $title = "Daily Sack Report";
                                $message = $warehouseman . "sent a daily report for sacks.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "daily_sack_report";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                /* save to cloud firestore */
                                $this->_cfs->save($inspector->getData('id'), $warehouseman, $message, 'daily_sack_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        
                    }
                    /* Notification to Inspector - END */

                    /* Notification to Inspector2 - START */
                    $inspectors = $this->_users->getUsersByRole(7);
                    if ($inspectors) {
                        foreach ($inspectors as $inspector) {
                            $this->_notification->setNotification((int) $entityId, $prepared_by, 13, null, $message, $inspector->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                            if ($to) {
                                $title = "Daily Sack Report";
                                $message = $warehouseman . "sent a daily report for sacks";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "daily_sack_report";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                /* save to cloud firestore */
                                $this->_cfs->save($inspector->getData('id'), $warehouseman, $message, 'daily_sack_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                    }
                    /* Notification to Inspector2 - END */

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Sack inventory saved!';
                } else {
                    $this->jsonData['message'] = 'Unable to save sack inventory. Please try again.';
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