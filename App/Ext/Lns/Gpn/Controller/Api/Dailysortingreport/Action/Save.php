<?php
namespace Lns\Gpn\Controller\Api\Dailysortingreport\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_dailysortingreport;
    protected $_dailysortinginventory;
    protected $_dateTime;
    protected $_upload;
    protected $_dailysortinginventoryhistory;
    protected $_egginventory;
    protected $_house;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_notification;
    protected $_push;
    protected $_cfs;
    protected $_inflow;
    protected $_freshegginventory;
    protected $_audittrail;
    protected $_inouteggs;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventoryhistory $Dailysortinginventoryhistory,
        \Lns\Gpn\Lib\Entity\Db\Inflow $Inflow,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail,
        \Lns\Gpn\Lib\Entity\Db\Inouteggs $Inouteggs
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_dateTime = $DateTime;
        $this->_upload = $Upload;
        $this->_inflow = $Inflow;
        $this->_dailysortinginventoryhistory = $Dailysortinginventoryhistory;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_egginventory = $EggInventory;
        $this->_house = $House;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_notification = $Notification;
        $this->_users = $Users;
        $this->_push = $PushNotification;
        $this->_cfs = $CloudFirestore;
        $this->_freshegginventory = $FresheggInventory;
        $this->_audittrail = $AuditTrail;
        $this->_inouteggs = $Inouteggs;
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

            $house_id = $this->getParam('house_id');
            $house_harvest_id = $this->getParam('house_harvest_id');
            $chicken_pop_id = $this->getParam('chicken_pop_id');
            $real_egg_count = $this->getParam('real_egg_count');
            $egg_count = $this->getParam('egg_count');
            $production_date = $this->getParam('production_date');
            $production_date = $this->getParam('production_date');
    
            /* Array of Eggs */
            $eggs = json_decode($this->getParam('egg'));

            /* Edit daily Sorting Report */
            $record_id = $this->getParam('id');
            $title = "Daily Sorting Report";
            $content = "Content Here";
            if ($record_id) {
                $entity = $this->_dailysortingreport->getByColumn(['id'=>$record_id], 1);
                if($entity){
                    /* Checked By Inspector */
                    $pushData = $entity->getData();
                    $pushData['house'] = $this->_house->getHouse($entity->getData('house_id'));

                    if ($this->getParam('checked_by')) {

                        /* Send Notif to Sorter - START */
                        $house = $this->_house->getHouse($entity->getParam('house_id'));
                        $inspector = $this->_userProfile->getFullNameById($user_id);
                        $message = "<strong>".$inspector."</strong> approved a sorted report for house/bldg no. ".$house['house_name'].".";
                        $this->_notification->setNotification($entity->getData('id'), $user_id, 11, null, $message, $entity->getData('prepared_by'));
                        /* Send Notif to Sorter - END */

                        /* send notif to manager */
                        $managers = $this->_users->getUsersByRole(4);
                        if ($managers) {
                            foreach ($managers as $manager) {
                                $message = $inspector." approved a sorted report for house/bldg no. ".$house['house_name'].".";
                                $this->_notification->setNotification($entity->getData('house_harvest_id'), $user_id, 11, null, $message, $manager->getData('id'));
                                $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                                $pushData['recordStatus'] = 'Approved';
                                $this->_cfs->save($manager->getData('id'), $inspector, $message, 'sorted_report_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        /* send notif to manager */

                        $to = $this->_deviceToken->getDeviceTokenById($entity->getData('prepared_by'));
                        if ($to) {
                            $title = "Daily Sorting Report";
                            $message = $inspector. " approved a sorted report for house/bldg no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "sorted_report_success";
                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                            /* save to cloud firestore */
                            $pushData['recordStatus'] = 'Approved';
                            $this->_cfs->save($entity->getData('prepared_by'), $inspector, $message, 'sorted_report_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            /* save to cloud firestore */
                        }
                        $action = "<strong>" . $inspector . "</strong> approved a sorted report for house/bldg no. " . $house['house_name'] . ".";
                        $this->_audittrail->saveAudittrail($user_id, $inspector, $action, 'daily_sorting_report_approve');

                        /* Send Notif to Warehouseman - START */
                        $warehousemans = $this->_users->getUsersByRole(10);
                        if ($warehousemans) {
                            foreach ($warehousemans as $warehouseman) {
                               /*  $sorter = $this->_userProfile->getFullNameById($entity->getData('prepared_by')); */
                                $message = "<strong>". $inspector."</strong> sent you a sorted egg report for house/bldg no. ".$house['house_name'];
                                $this->_notification->setNotification((int)$entity->getData('id'), $user_id, 11, null, $message, $warehouseman->getData('id'));
    
                                $to = $this->_deviceToken->getDeviceTokenById($warehouseman->getData('id'));
                                if ($to) {
                                    $title = "Daily Sorting Report";
                                    $message = $inspector. " sent you a sorted egg report for house/bldg no. ".$house['house_name'];
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "sorted_report_success";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    /* save to cloud firestore */
                                    $pushData['recordStatus'] = 'For Receive';
                                    $this->_cfs->save($warehouseman->getData('id'), $inspector, $message, 'sorted_report_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                    /* save to cloud firestore */
                                }
                            }
                        }
                        /* Send Notif to Warehouseman - START */

                        if($eggs){
                            foreach ($eggs as $egg) {
                                $eggInfo = $this->_dailysortinginventory->getByColumn(['id'=>$egg->id], 1);

                                if($eggInfo->getData('egg_count') != $egg->count){
                                    $save = $this->_dailysortinginventoryhistory;
                                    $save->setData('sorted_inv_id',$eggInfo->getData('id'));
                                    $save->setData('original_count',$eggInfo->getData('egg_count'));
                                    $save->setData('updated_count',$egg->count);
                                    $saveId = $save->__save();

                                    $this->jsonData['message'] = 'Egg count mismatched!';

                                    if($saveId){
                                        $eggInfo->setData('egg_count',$egg->count);
                                        $updatedEggId = $eggInfo->__save();
                                    }
                                }
                            }
                        }
                        $entity->setData('real_egg_count', $real_egg_count);
                        $photo = $this->upload($this->getParam('checked_by'), 'inspector');
                        $entity->setData('checked_by', $this->getParam('checked_by'));
                        $entity->setData('checked_by_path', $photo);
                        $entity->setData('checked_by_date', $this->_dateTime->getTimestamp());
                        $sortingInventoryId = $entity->__save();
                        
                        if($sortingInventoryId){
                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'Record Successfully Updated';
                        }else{
                            $this->jsonData['error'] = 1;
                            $this->jsonData['message'] = 'Failed';
                        }
                    }
                    /* End Check By Inspector */

                    /* Received by Warehouseman */
                    if ($this->getParam('received_by')){
                        /* Send Notif from Warehouseman to Inspector - START */
                        $house = $this->_house->getHouse($entity->getData('house_id'));
                        $warehouseman = $this->_userProfile->getFullNameById($user_id);
                        $message = "<strong>".$warehouseman."</strong> received the sorted report for house/bldg no. ".$house['house_name'].".";
                        $this->_notification->setNotification($entity->getData('id'), $user_id, 12, null, $message, $entity->getData('checked_by'));
                        /* Send Notif from Warehouseman to Inspector - END */

                        /* send notif to manager */
                        $managers = $this->_users->getUsersByRole(4);
                        if ($managers) {
                            foreach ($managers as $manager) {
                                $message = $warehouseman." received the sorted report for house/bldg no. ".$house['house_name'].".";
                                $this->_notification->setNotification($entity->getData('house_harvest_id'), $user_id, 12, null, $message, $manager->getData('id'));
                                $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                                $pushData['recordStatus'] = 'Received';
                                $this->_cfs->save($manager->getData('id'), $warehouseman, $message, 'sorted_report_received', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        /* send notif to manager */

                        /* Push Notif */
                        $to = $this->_deviceToken->getDeviceTokenById($entity->getData('checked_by'));
                        if ($to) {
                            $to = $to->getData('token');
                            $title = "Daily Sorting Report";
                            $message = $warehouseman. " received the sorted report for house/building no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "sorted_report_received";
                            $this->_push->sendNotif($to, $title, $message, $content, $api_key, $pushAction);
                            $pushData['recordStatus'] = 'Received';
                            $this->_cfs->save($entity->getData('checked_by'), $warehouseman, $message, 'sorted_report_received', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                        $action = "<strong>" . $warehouseman . "</strong> received the sorted report for house/bldg no. " . $house['house_name'] . ".";
                        $this->_audittrail->saveAudittrail($user_id, $warehouseman, $action, 'daily_sorting_report_receive');

                        $message = "<strong>".$warehouseman."</strong> received the sorted report for house/bldg no. ".$house['house_name'].".";
                        $this->_notification->setNotification($entity->getData('id'), $user_id, 12, null, $message, $entity->getData('prepared_by'));
                        $to = $this->_deviceToken->getDeviceTokenById($entity->getData('prepared_by'));
                        if ($to) {
                            $to = $to->getData('token');
                            $title = "Daily Sorting Report";
                            $message = $warehouseman. " received the sorted report for house/building no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "sorted_report_received";
                            $this->_push->sendNotif($to, $title, $message, $content, $api_key, $pushAction);
                            $pushData['recordStatus'] = 'Approved';
                            $this->_cfs->save($entity->getData('checked_by'), $warehouseman, $message, 'sorted_report_received', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                        /* Push Notif */

                        $photo = $this->upload($this->getParam('received_by'), 'warehouseman');
                        $entity->setData('received_by', $this->getParam('received_by'));
                        $entity->setData('received_by_path', $photo);
                        $entity->setData('received_by_date', $this->_dateTime->getTimestamp());
                        $sortingInventoryId = $entity->__save();

                        if($sortingInventoryId){

                            $inf = $this->_inflow;
                            $inf->setData('type', 1);
                            $inf->setData('reference_id', $sortingInventoryId);
                            $inf->__save();

                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'Record Successfully Saved';

                            $dailysortinginventorys = $this->_dailysortinginventory->getByColumn(['sorted_report_id'=>$entity->getData('id')], 0);
                            $stocks = 0;
                            $waste = 0;
                            $lastremainingstocks = 0;
                            foreach ($dailysortinginventorys as $dailysortinginventory) {
                                $save = $this->_egginventory;
                                $save->setData('type_id', $dailysortinginventory->getData('type_id'));
                                $save->setData('house_id', $entity->getData('house_id'));
                                $save->setData('egg_count', $dailysortinginventory->getData('egg_count'));
                                $egginventoryId = $save->__save();

                                $stocks += $dailysortinginventory->getData('egg_count');
                                if ($dailysortinginventory->getData('type_id') == 13) {
                                    $waste += $dailysortinginventory->getData('egg_count');
                                }
                            }
                            if ($egginventoryId) {
                                $date = date('Y-m-d');
                                /*  */
                                $inouteggs = $this->_inouteggs->validate($date);
                                if ($inouteggs) {
                                    $egg_in = (int)$inouteggs->getData('egg_in') + $stocks;
                                    $inouteggs->setData('egg_in', $egg_in);
                                    $inouteggs->setData('updated_at', $this->_dateTime->getTimestamp());
                                    $inouteggs->__save();
                                } else {
                                    $inouteggs = $this->_inouteggs;
                                    $egg_in = $stocks;
                                    $inouteggs->setData('egg_in', $egg_in);
                                    $inouteggs->__save();
                                }
                                /*  */


                                $freshegginventory = $this->_freshegginventory->getRecordtoday($date);
                                $update = $this->_freshegginventory;

                                if ($freshegginventory) {
                                    $update = $this->_freshegginventory->getByColumn(['id' => $freshegginventory->getData('id')], 1);
                                    $waste += $update->getData('waste_sales');
                                    $stocks += $update->getData('total_harvested');
                                }

                                $lastending = $this->_freshegginventory->getLastEnding($date);
                                
                                if ($lastending) {
                                    $lastremainingstocks = $lastending->getData('total_remaining_stocks');
                                    $lastremainingstocks ? $lastremainingstocks : 0;
                                }
                                $remaining = $lastremainingstocks + $stocks - $waste;

                                $update->setData('beginning_stocks', $lastremainingstocks);
                                $update->setData('total_harvested', $stocks);
                                $update->setData('waste_sales', $waste);
                                $update->setData('total_remaining_stocks', $remaining);
                                $fresheggId = $update->__save();

                                if ($fresheggId) {
                                    $this->jsonData['error'] = 0;
                                    /* $this->jsonData['message'] = 'saved'; */
                                }
                            }
                        }else{
                            $this->jsonData['error'] = 1;
                            $this->jsonData['message'] = 'Failed';
                        }
                    }
                    /* End Received By Warehouseman */
                    if ($this->getParam('checked_by')) {
                        $this->jsonData['message'] = 'Daily sorting report approved!';
                    }
                    if ($this->getParam('received_by')) {
                        $this->jsonData['message'] = 'Daily sorting report received!';
                    }
                }
            } else {
                /* make isSorted to 1 */
                $dhr = $this->_dailyhouseharvest->getByColumn(['id' => $house_harvest_id], 1);
                $dhr->setData('isSorted', 1);
                $dhr->__save();

                /* Add Daily Sorting Report - Sorter */
                $entity = $this->_dailysortingreport;
                $entity->setData('user_id', $user_id);
                $entity->setData('house_id', $house_id);
                $entity->setData('house_harvest_id', $house_harvest_id);
                $entity->setData('chicken_pop_id', $chicken_pop_id);
                $entity->setData('egg_count', $egg_count);
                $entity->setData('production_date', $production_date);
                if ($this->getParam('prepared_by')) {
                    $photo = $this->upload($this->getParam('prepared_by'), 'sorter');
                    $entity->setData('prepared_by', $this->getParam('prepared_by'));
                    $entity->setData('prepared_by_path', $photo);
                    $entity->setData('prepared_by_date', $this->_dateTime->getTimestamp());
                }
                $sortingReportId = $entity->__save();
                /* End of Add Daily Sorting Report - Sorter */

                /* Add Daily Sorting Inventory - Sorter */
                if($sortingReportId){
                    $data = $this->_dailysortingreport->getByColumn(['id'=> $sortingReportId],1);
                    $house_id = $data->getData('house_id');
                    $pushData = $data->getData();
                    $house = $this->_house->getHouse($data->getData('house_id'));
                    $pushData['house'] = $house;
                    /* $pushData['recordStatus'] = 'Sorted'; */
                    /* Insert to inflow table, reference id = $sortingReportId, type = 1 default */
                    /* $entity = $this->_inflow;
                    $entity->setData('type', 1);
                    $entity->setData('reference_id', $sortingReportId);
                    $entity->__save(); */

                    /* Send Notif to Inspector - START */
                    $inspectors = $this->_users->getUsersByRole(6);
                    $sorter = $this->_userProfile->getFullNameById($user_id);
                    $message = "<strong>".$sorter."</strong> sent you a sorted report for house/bldg no. ".$house['house_name'] . ".";
                    if ($inspectors) {
                        foreach ($inspectors as $inspector) {
                            $this->_notification->setNotification((int)$sortingReportId, $user_id, 7, null, $message, $inspector->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                            if ($to) {
                                $title = "Daily Sorting Report";
                                $message = $sorter. " sent you a sorted house report for house/bldg no. ".$house['house_name'] . ".";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "daily_sorted_report";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                /* save to cloud firestore */
                                $pushData['recordStatus'] = 'Pending';
                                $this->_cfs->save($inspector->getData('id'), $sorter, $message, 'daily_sorted_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $action = "<strong>" . $sorter . "</strong> sent a sorted report for house/bldg no. " . $house['house_name'] . ".";
                        $this->_audittrail->saveAudittrail($user_id, $sorter, $action, 'daily_sorting_report_submit');
                    }
                    /* Send Notif to Inspector - END */

                    /* send notif to manager */
                    $managers = $this->_users->getUsersByRole(4);
                    if ($managers) {
                        foreach ($managers as $manager) {
                            $message = $sorter. " sent you a sorted house report for house/bldg no. ".$house['house_name'] . ".";
                            $this->_notification->setNotification($entity->getData('house_harvest_id'), $user_id, 7, null, $message, $manager->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                            $pushData['recordStatus'] = 'Pending';
                            $this->_cfs->save($manager->getData('id'), $sorter, $message, 'daily_sorted_report', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                    /* send notif to manager */

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Daily sorting report sent!';
        
                    foreach ($eggs as $egg) {
                        /* $this->jsonData['dd'][] = $egg; */
                        $entity = $this->_dailysortinginventory;
                        $entity->setData('sorted_report_id', $sortingReportId);
                        $entity->setData('house_id', $house_id);
                        $entity->setData('type_id', $egg->type_id);
                        $entity->setData('egg_count', $egg->count);
                        $sortingInventoryId = $entity->__save();
                    }
        
                }else{
                    $this->jsonData['error'] = 1;
                    $this->jsonData['message'] = 'Failed';
                }
                /* End of Add Daily Sorting Inventory - Sorter */
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