<?php
namespace Lns\Gpn\Controller\Api\Dailyhouseharvest\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_dailyHouseHarvest;
    protected $_feeds;
    protected $_house;
    protected $_medicine;
    protected $_notification;
    protected $_dateTime;
    protected $_upload;
    protected $_userProfile;
    protected $token;
    protected $payload;
    protected $_push;
    protected $_deviceToken;
    protected $_users;
    protected $_cfs;

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
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dateTime = $DateTime;
        $this->_upload = $Upload;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_feeds = $Feeds;
        $this->_house = $House;
        $this->_medicine = $Medicine;
        $this->_userProfile = $UserProfile;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_cfs = $CloudFirestore;
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
            
/*             $hasRecord = $this->_dailyHouseHarvest->hasRecord($this->getParam('house_id')); */
                 /* $hasRecord = $this->_dailyHouseHarvest->getByColumn(['id'=>$this->getParam('id')], 1); */

                $entity = $this->_dailyHouseHarvest->getByColumn(['id' =>$this->getParam('id')], 1);
                if($entity){
                    $bird_count = /* ( */(int)$entity->getData('bird_count') /* + (int)$entity->getData('mortality') + (int)$entity->getData('cull')) */;
                    if ($entity->getData('prepared_by') == $userId) {
                        $house_id = $entity->getData('house_id');
                        $age_week = $entity->getData('age_week');
                        $age_day = $entity->getData('age_day');
                        $medicine_ids = $entity->getData('medicine_ids');
                        $feed_id = $entity->getData('feed_id');
                        $feed_consumption = $entity->getData('feed_consumption');
                        $feed_unit_id = $entity->getData('feed_unit_id');

                        $entity = $this->_dailyHouseHarvest;
                        $entity->setData('house_id', $house_id);
                        $entity->setData('age_week', $age_week);
                        $entity->setData('age_day', $age_day);
                        $entity->setData('medicine_ids', $medicine_ids);
                        $entity->setData('feed_id', $feed_id);
                        $entity->setData('feed_consumption', $feed_consumption);
                        $entity->setData('feed_unit_id', $feed_unit_id);
                    }
                    if(!$this->getParam('received_by')){
                        $mortality = (int)$this->getParam('mortality');
                        $cull = (int)$this->getParam('cull');
                        $bird_count = ($bird_count) - $mortality - $cull;
                        $entity->setData('mortality', $mortality);
                        $entity->setData('cull', $cull);
                        $entity->setData('bird_count', $bird_count);
                    }
                    
                    if ($this->getParam('prepared_by')) {
                        $egg_count = (int)$this->getParam('egg_count');
                        $entity->setData('egg_count', $egg_count);
                    }
    
               /* if ($hasRecord) {
                    $entity = $this->_dailyHouseHarvest->getByColumn(['id' => $hasRecord->getData('id')], 1);
                    $mortality = (int)$this->getParam('mortality');
                    $cull = (int)$this->getParam('cull');
                    $bird_count = ((int)$entity->getData('bird_count') + (int)$entity->getData('mortality') + (int)$entity->getData('cull')) - $mortality - $cull;
                    $entity->setData('mortality', $mortality);
                    $entity->setData('cull', $cull);
                    $entity->setData('bird_count', $bird_count);
                    if ($this->getParam('prepared_by')) {
                        $egg_count = (int)$this->getParam('egg_count');
                        $entity->setData('egg_count', $egg_count);
                    }
                } else {
                    $entity = $this->_dailyHouseHarvest;
                    $entity->setDatas($this->getParam());
                    $latestRecord = $this->_dailyHouseHarvest->getLatestRecord($this->getParam('house_id'));
                    if ($latestRecord) {
                        $age_week = (int)$latestRecord->getData('age_week');
                        $age_day = (int)$latestRecord->getData('age_day');
                        if ($age_day + 1 == 7) {
                            $age_week = $age_week + 1;
                            $age_day = 0;
                        } else {
                            $age_day = $age_day + 1;
                        }
                        $entity->setData('age_week', $age_week);
                        $entity->setData('age_day', $age_day);
                        $entity->setData('bird_count', (int)$latestRecord->getData('bird_count') - (int)$this->getParam('mortality') - (int)$this->getParam('cull'));
                    }
                } */

                $photo = null;
                if ($this->getParam('prepared_by')) {
                    $photo = $this->upload($this->getParam('prepared_by'), 'flockman');
                    $entity->setData('prepared_by', $this->getParam('prepared_by'));
                    $entity->setData('prepared_by_path', $photo);
                    $entity->setData('prepared_by_date', $this->_dateTime->getTimestamp());
                }
                if ($this->getParam('checked_by')) {
                    $photo = $this->upload($this->getParam('checked_by'), 'inspector');
                    $entity->setData('checked_by', $this->getParam('checked_by'));
                    $entity->setData('checked_by_path', $photo);
                    $entity->setData('checked_by_date', $this->_dateTime->getTimestamp());
                    
                    $entity->setData('real_egg_count', $this->getParam('real_egg_count'));
                    /* $entity->setData('mortality', $this->getParam('mortality'));
                    $entity->setData('bird_count', (int)$this->getPost('bird_count')); */
                }
                if ($this->getParam('received_by')) {
                    $photo = $this->upload($this->getParam('received_by'), 'sorter');
                    $entity->setData('received_by', $this->getParam('received_by'));
                    $entity->setData('received_by_path', $photo);
                    $entity->setData('received_by_date', $this->_dateTime->getTimestamp());
                }
                /* $entity = $this->_dailyHouseHarvest->save($this->getParam()); */
                if ($this->getParam('prepared_by_isdraft')) {
                    $entity->setData('prepared_by_isdraft', 1);
                    $entity->setData('checked_by_isdraft', 0);
                    $entity->setData('received_by_isdraft', 0);
                }
                if ($this->getParam('checked_by_isdraft')) {
                    $entity->setData('prepared_by_isdraft', 0);
                    $entity->setData('checked_by_isdraft', 1);
                    $entity->setData('received_by_isdraft', 0);
                }
                if ($this->getParam('received_by_isdraft')) {
                    $entity->setData('prepared_by_isdraft', 0);
                    $entity->setData('checked_by_isdraft', 0);
                    $entity->setData('received_by_isdraft', 1);
                }
                $entity->setData('user_id', $userId);
                $save = $entity->__save();
                if ($save) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Success';
    
                    $data = $this->_dailyHouseHarvest->getRecordById($save);
                    
                    $pushData = $data->getData();
                    $pushData['house'] = $this->_house->getHouse($data->getData('house_id'));
                    
                    $this->jsonData['data'] = $data->getData();
                    if ($data->getData('medicine_ids')) {
                        $medicine_ids = explode(',', $data->getData('medicine_ids'));
                        foreach ($medicine_ids as $medicine_id) {
                            $this->jsonData['data']['medicine'][] = $this->_medicine->getMedicine($medicine_id);
                        }
                    }
                    $this->jsonData['data']['house'] = $data->getData('house_id') ? $this->_house->getHouse($data->getData('house_id')) : null;
                    $this->jsonData['data']['feed'] = $data->getData('feed_id') ? $this->_feeds->getFeed($data->getData('feed_id')) : null;
                    if ($data->getData('prepared_by')) {
                        $pushData['prepared_by_name'] = $this->_userProfile->getFullNameById($data->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($data->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/flockman/' . $data->getData('prepared_by'),
                            'filename' => $data->getData('prepared_by_path')
                        ]);
                    }
                    if ($data->getData('checked_by')) {
                        $pushData['checked_by_name'] = $this->_userProfile->getFullNameById($data->getData('checked_by'));
                        $this->jsonData['data']['checked_by_name'] = $this->_userProfile->getFullNameById($data->getData('checked_by'));
                        $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $data->getData('checked_by'),
                            'filename' => $data->getData('checked_by_path')
                        ]);
                    }
                    if ($data->getData('received_by')) {
                        $pushData['received_by_name'] = $this->_userProfile->getFullNameById($data->getData('received_by'));
                        $this->jsonData['data']['received_by_name'] = $this->_userProfile->getFullNameById($data->getData('received_by'));
                        $this->jsonData['data']['received_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/sorter/' . $data->getData('received_by'),
                            'filename' => $data->getData('received_by_path')
                        ]);
                    }

                    if ($this->getParam('prepared_by')) {
                        /* send notif to inspector */
                        $inspectors = $this->_users->getUsersByRole(6);
                        if ($inspectors) {
                            foreach ($inspectors as $inspector) {
                                $house = $this->_house->getHouse($data->getParam('house_id'));
                                $flockman = $this->_userProfile->getFullNameById($userId);
        
                                $message = "<strong>".$flockman."</strong> sent you a daily house report for house no. ".$house['house_name'];
                                
                                $this->_notification->setNotification((int)$data->getData('id'), $userId, 1, null, $message, $inspector->getData('id'));
                                
                                $to = $this->_deviceToken->getDeviceTokenById($inspector->getData('id'));
                                if ($to) {
                                    $title = "Daily House Harvest";
                                    $message = $flockman. " sent you a daily house report for house no. ".$house['house_name'];
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "daily_house_harvest";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    /* save to cloud firestore */
                                    $this->_cfs->save($inspector->getData('id'), $flockman, $message, 'daily_house_harvest', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                            }
                        }
                        /* send notif to inspector */
                    }
                    if ($this->getParam('checked_by')) {
                        /* send notif to flockman success */
                        $house = $this->_house->getHouse($data->getParam('house_id'));
                        $inspector = $this->_userProfile->getFullNameById($userId);
    
                        $message = "<strong>".$inspector."</strong> approved a daily report for house/building no. ".$house['house_name'].".";
                        
                        $this->_notification->setNotification($data->getData('id'), $userId, 5, null, $message, $data->getData('prepared_by'));
                        
                        $to = $this->_deviceToken->getDeviceTokenById($data->getData('prepared_by'));
                        if ($to) {
                            $title = "Daily House Harvest";
                            $message = $inspector. " approved a daily report for house/building no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "daily_house_harvest_success";
                            $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                            /* save to cloud firestore */
                            $this->_cfs->save($data->getData('prepared_by'), $inspector, $message, 'daily_house_harvest_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            /* send notif to flockman success */
                        }
    
                        /* send notif to sorter */
                        $sorters = $this->_users->getUsersByRole(9);
                        if ($sorters) {
                            foreach ($sorters as $sorter) {
                                $house = $this->_house->getHouse($data->getParam('house_id'));
                               /*  $flockman = $this->_userProfile->getFullNameById($data->getData('prepared_by')); */
    
                                $message = "<strong>".$inspector."</strong> sent you a daily house report for house/building no. ".$house['house_name'].".";
                                
                                $this->_notification->setNotification($data->getData('id'), $userId, 5, null, $message, $sorter->getData('id'));
                                
                                $to = $this->_deviceToken->getDeviceTokenById($sorter->getData('id'));
                                if ($to) {
                                    $title = "Daily House Harvest";
                                    $message = $inspector. " sent you a daily house report for house/building no. ".$house['house_name'].".";
                                    $content = "Content Here";
                                    $api_key = $this->_siteConfig->getData('site_fcm_key');
                                    $pushAction = "daily_house_harvest_success";
                                    $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                    /* save to cloud firestore */
                                    $this->_cfs->save($sorter->getData('id'), $inspector, $message, 'daily_house_harvest_success', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                                }
                            }
                        }
                        /* send notif to sorter */
                    }
                    if ($this->getParam('received_by')) {
                        /* send notif to flockman ang inspector */
                        $house = $this->_house->getHouse($data->getData('house_id'));
                        $sorter = $this->_userProfile->getFullNameById($userId);

                        $message = "<strong>".$sorter."</strong> received the daily house report for house/building no. ".$house['house_name'].".";

                        $this->_notification->setNotification($data->getData('id'), $userId, 6, null, $message, $data->getData('checked_by'));

                        $to = $this->_deviceToken->getDeviceTokenById($data->getData('prepared_by'));
                        if ($to) {
                            $to = $to->getData('token');
                            $title = "Daily House Harvest";
                            $message = $sorter. " received the daily house report for house/building no. ".$house['house_name'].".";
                            $content = "Content Here";
                            $api_key = $this->_siteConfig->getData('site_fcm_key');
                            $pushAction = "daily_house_harvest_received";
                            $this->_push->sendNotif($to, $title, $message, $content, $api_key, $pushAction);
                            $this->_cfs->save($data->getData('prepared_by'), $sorter, $message, 'daily_house_harvest_received', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }

                        $to = $this->_deviceToken->getDeviceTokenById($data->getData('checked_by'));
                        if ($to) {
                            $to = $to->getData('token');
                            $this->_push->sendNotif($to, $title, $message, $content, $api_key, $pushAction);
                            $this->_cfs->save($data->getData('checked_by'), $sorter, $message, 'daily_house_harvest_received', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                        /* send notif to flockman ang inspector */
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Submit Success!';
                    
                }else{
                    $this->jsonData['message'] = 'No Record Found!';
                }
            } else {
                $this->jsonData['message'] = 'Failed';
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