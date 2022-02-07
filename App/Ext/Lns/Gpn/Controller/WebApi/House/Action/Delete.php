<?php
namespace Lns\Gpn\Controller\WebApi\House\Action;

class Delete extends \Lns\Sb\Controller\Controller {

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

            $houseId = $this->getParam('id');

            $house = $this->_house->getByColumn(['id'=> $houseId], 1);
            if($house){
                $dailyhouseharvest = $this->_dailyhouseharvest->getByColumn(['house_id'=> $houseId], 1);
                if($dailyhouseharvest){
                    $this->jsonData['message'] = 'House can not be deleted';
                }else {
                    $house->delete();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'A house has been deleted';
                }
            }else{
                $this->jsonData['message'] = 'No house record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>