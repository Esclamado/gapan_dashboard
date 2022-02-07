<?php
namespace Lns\Gpn\Controller\WebApi\House\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_dateTime;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\DateTime $DateTime
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
        $this->_dateTime = $DateTime;
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

            $houseName = $this->getParam('house_name');
            $capacity = $this->getParam('capacity');
            $houseId = $this->getParam('id');

            $datetime = $this->_dateTime->getTimestamp();

            $exist = $this->_house->getByColumn(['house_name' => $houseName], 1);
            if($exist){
                $house_name = $exist->getData('house_name');
                $house_id = $exist->getData('id');
            }else{
                $house_name = 0;
                $house_id = 0;
            }
            if($houseId){
                $house = $this->_house->getByColumn(['id' => $houseId], 1);
                if($house){
                    $isExist = $this->_house->getByColumn(['house_name' => $houseName], 1);
                    if($isExist){
                        if($house_id == $houseId){
                            $house->setData('house_name', $houseName);
                            $house->setData('capacity', $capacity);
                            $house->setData('updated_at', $datetime);
                            $house->__save();
                            $this->jsonData['message'] = 'A house has been updated';
                            $this->jsonData['error'] = 0;
                        }else{
                            $this->jsonData['message'] = 'House number is already existing';
                        }
                    }else{
                        $house->setData('house_name', $houseName);
                        $house->setData('capacity', $capacity);
                        $house->setData('updated_at', $datetime);
                        $house->__save();
                        $this->jsonData['message'] = 'A house has been updated';
                        $this->jsonData['error'] = 0;
                    }
                }else{
                    $this->jsonData['message'] = 'House not found';
                }
            }else{
                $house = $this->_house;
                $house->setData('house_name', $houseName);
                $house->setData('capacity', $capacity);
                $house->setData('updated_at', $datetime);
                $house->__save();
                $this->jsonData['message'] = 'A new house has been saved';
                $this->jsonData['error'] = 0;
                /* if ($house_name == $houseName) {
                    if($houseName==0){
                        $this->jsonData['message'] = 'No house found';
                    }else{
                    $this->jsonData['message'] = 'House number is already existing';
                    }
                } else {
                    $house = $this->_house;
                    $house->setData('house_name', $houseName);
                    $house->setData('capacity', $capacity);
                    $house->setData('updated_at', $datetime);
                    $house->__save();
                    $this->jsonData['message'] = 'A new house has been saved';
                    $this->jsonData['error'] = 0;
                } */
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>