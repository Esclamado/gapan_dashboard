<?php
namespace Lns\Gpn\Controller\Api\Notification\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_notification;
    protected $_upload;
    protected $_userprofile;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Notifications $Notifications,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_notification = $Notifications;
        $this->token = $Validate;
        $this->_upload = $Upload;
        $this->_userprofile = $UserProfile;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        $user_id = $payload['payload']['jti'];

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {

            $param = $this->getParam();

            $notification = $this->_notification->getNotification($param, $user_id);

            if($notification){
    
                $this->jsonData = $notification;
    
                $i = 0;
                $result = [];
                foreach ($notification['datas'] as $notif) {
                    $result[$i] = $notif->getData();
                    $result[$i]['isToday'] = null;
                    $hasProfile = $this->_userprofile->getByColumn(['user_id' => $notif->getData('owner_user_id')], 1);
                    if ($hasProfile) {
                        if($hasProfile->getData('profile_pic')){
                            $result[$i]['owner_profile_pic'] = $this->getImageUrl([
                                'vendor' => 'Lns',
                                'module' => 'Sb',
                                'path' => '/images/uploads/profilepic/' . $notif->getData('owner_user_id'),
                                'filename' => $hasProfile->getData('profile_pic')
                            ]);
                        }else{
                            $result[$i]['owner_profile_pic'] = null;
                        }
                    }else{
                        $result[$i]['owner_profile_pic'] = null;
                    }
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
        }

        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId)
    {
        $path = 'Lns' . DS . 'Sb' . DS . 'View' . DS . 'images' . DS . 'uploads' . DS . 'profilepic' . DS . $userId;

        $file = $this->getFile('photo');

        $fileName = null;
        if ($file) {
            $_file = $this->_upload->setFile($file)
                ->setPath($path)
                ->setAcceptedFile(['ico', 'jpg', 'png', 'jpeg'])
                ->save();

            if ($_file['error'] == 0) {
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }
        }
        return $fileName;
    }
}
?>