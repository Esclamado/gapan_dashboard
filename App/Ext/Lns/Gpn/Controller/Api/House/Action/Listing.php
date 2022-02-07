<?php
namespace Lns\Gpn\Controller\Api\House\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $result = [];
        $this->jsonData['error'] = 1;

		if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $houses = $this->_house->getHouses();
            if ($houses) {
                foreach ($houses as $house) {
                    $result[] = $house->getData();
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = "No House Found";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>