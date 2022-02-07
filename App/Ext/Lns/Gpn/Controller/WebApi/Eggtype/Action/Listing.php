<?php
namespace Lns\Gpn\Controller\WebApi\Eggtype\Action;

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
            $entity = $this->_eggtype->getCollection();
            if ($entity) {
                $this->jsonData['error'] = 0;
                $i = 0;
                foreach($entity as $data) {
                    $this->jsonData['datas'][$i] = $data->getData();
                    $egginventory = $this->_egginventory->getEggstocks($data->getData('id'));
                    if($egginventory) {
                        $this->jsonData['datas'][$i]['stock'] = $egginventory;
                    }
                    $eggprice = $this->_price->getByColumn(['type_id' => $data->getData('id')], 0);
                    foreach ($eggprice as $price) {
                        $this->jsonData['datas'][$i]['price'] = $price->getData();
                    }
                    $i++;
                }
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>