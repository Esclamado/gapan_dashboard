<?php
namespace Lns\Gpn\Controller\Api\EggCartType\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_eggcarttype;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_eggcarttype = $EggCartType;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

		if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $result = [];
            $i = 0;
            $eggcarttype = $this->_eggcarttype->getCollection();
            if($eggcarttype){
                foreach ($eggcarttype as $eggcart) {
                    $result[$i] = $eggcart->getData();
                    switch($eggcart->getData('id')){
                        case 1:
                            $result[$i]['quantity'] = 360;
                        break;
                        case 2:
                            $result[$i]['quantity'] = 30;
                        break;
                        default:
                            $result[$i]['quantity'] = 1;
                        break;
                    }
                    $i++;
                }
            $this->jsonData['data'] = $result;
            $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
