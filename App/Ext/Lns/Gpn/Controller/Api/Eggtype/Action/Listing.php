<?php
namespace Lns\Gpn\Controller\Api\Eggtype\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_price;
    protected $_egginventory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_eggtype = $Eggtype;
        $this->_price = $Price;
        $this->_egginventory = $EggInventory;
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

            $eggTypes = $this->_eggtype->getCollection();
            if ($eggTypes) {
                $i = 0;
                foreach ($eggTypes as $eggType) {
                    $result[$i] = $eggType->getData();
                    $egginventory = $this->_egginventory->getEggstocks($eggType->getData('id'));
                    if ($egginventory) {
                        $result[$i]['stock'] = $egginventory;
                    }
                    $eggprice = $this->_price->getByColumn(['type_id'=> $eggType->getData('id')], 0);
                    foreach ($eggprice as $price) {
                    $result[$i]['price'] = $price->getData();
                    }
                    $i++;
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = "No Egg Type Found";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>