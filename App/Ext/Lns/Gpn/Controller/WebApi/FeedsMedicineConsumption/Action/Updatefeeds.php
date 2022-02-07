<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Updatefeeds extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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

            $formdata = $this->getParam();

            $update = $this->_dailyhouseharvest->getByColumn(['id'=> $formdata['daily_house_report_id']], 1);
            if($update){
                $update->setData('feed_consumption', $formdata['feed']['feed_consumption']);
                $update->setData('feed_id', $formdata['feed']['feed_id']);
                $save = $update->__save();

                if($save){
                    $this->jsonData['message'] = 'Updated';
                    $this->jsonData['error'] = 0;
                }
            }

            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>