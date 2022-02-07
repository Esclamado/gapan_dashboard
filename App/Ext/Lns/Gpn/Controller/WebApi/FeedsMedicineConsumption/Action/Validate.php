<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Validate extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_dailyhouseharvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
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

            $houses = $this->_house->getCollection();

            $count_houses = count($houses);
            $count_has_record = 0;
            foreach ($houses as $house) {
                $hasRecord = $this->_dailyhouseharvest->validateByHouse($house->getData('id'), date('Y-m-d'));
                if ($hasRecord) {
                    $this->jsonData['datas'][] = $hasRecord->getData();
                    $count_has_record += 1;
                }
            }
            if ($count_houses != $count_has_record) {
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>