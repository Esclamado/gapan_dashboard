<?php
namespace Lns\Gpn\Controller\WebApi\FeedConsumption\Action;

class FeedConsumption extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_house;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
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

            $param = $this->getParam();

            $houses = $this->_house->getHouseAlpha();
            $i = 0;
            if($houses){
                foreach ($houses as $house) {
                    $result[$i] = $house->getData();
                    $dailyhouseharvests = $this->_dailyhouseharvest->getFeedConsumption($param, $house->getData('id'));
                    $result[$i]['consumed_feeds'] = $dailyhouseharvests ? $dailyhouseharvests : 0;
                    $i++;
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $result;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>
