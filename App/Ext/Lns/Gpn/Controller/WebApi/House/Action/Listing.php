<?php
namespace Lns\Gpn\Controller\WebApi\House\Action;

class Listing extends \Lns\Sb\Controller\Controller {

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

            $param = $this->getParam();
            $result = [];
            
            if ($param) {
                $houses = $this->_house->searchHouse($param);
                $this->jsonData = $houses;
                if ($houses['datas']) {
                    $i = 0;
                    foreach ($houses['datas'] as $house) {
                        $result[$i] = $house->getData();
                        $dailyhouseharvests = $this->_dailyhouseharvest->getByColumn(['house_id' => $house->getData('id')], 0);

                        $beginning = $this->_dailyhouseharvest->getBeginningByHouse($house->getData('id'));

                        if ($beginning) {
                            $result[$i]['beginning'] = $beginning->getData();
                        } else {
                            $result[$i]['beginning'] = null;
                        }

                        if($dailyhouseharvests){
                            $canDelete = false;
                        }else{
                            $canDelete = true;
                        }
                        $result[$i]['canDelete'] = $canDelete;
                        $i++;
                    }
                    $this->jsonData['datas'] = $result;
                    $this->jsonData['error'] = 0;
                } else {
                    $this->jsonData['message'] = 'No house(s) found';
                }
            } else {
                $houses = $this->_house->getCollection();
                if ($houses) {
                    foreach ($houses as $house) {
                        $result[] = $house->getData();
                    }
                    $this->jsonData['datas'] = $result;
                    $this->jsonData['error'] = 0;
                } else {
                    $this->jsonData['message'] = 'No house(s) found';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>