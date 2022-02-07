<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineManagement\Action;

class Update extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_feeds;
    protected $_medicine;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_feeds = $Feeds;
        $this->_medicine = $Medicine;
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

           $id = $this->getParam('id');
           $pieces = $this->getParam('pieces');
           $type = $this->getParam('type');

           if($id){
            if($type == 'feeds'){
                $update = $this->_feeds->getByColumn(['id'=>$id, 1]);
                if($update){
                    $update->setData('pieces', $pieces);
                    $save = $update->__save();
                    if($save){
                        $this->jsonData['message'] = 'Feeds successfully updated';
                        $this->jsonData['error'] = 0;
                    }
                }
            }else if($type == 'meds'){
                $update = $this->_medicine->getByColumn(['id'=>$id, 1]);
                if($update){
                    $update->setData('pieces', $pieces);
                    $save = $update->__save();
                    if($save){
                        $this->jsonData['message'] = 'Medicine successfully updated';
                        $this->jsonData['error'] = 0;
                    }
                }
            }
            }else{
                    $this->jsonData['message'] = 'No record found';
                    $this->jsonData['error'] = 1;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>