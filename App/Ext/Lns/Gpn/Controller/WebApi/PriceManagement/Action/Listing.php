<?php
namespace Lns\Gpn\Controller\WebApi\PriceManagement\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_price;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Price $Price
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_eggtype = $Eggtype;
        $this->_price = $Price;
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

            $prices = $this->_price->getPriceList($param);
            $count = $this->_price->getPriceList($param, true);
            $this->jsonData = $prices;
            $this->jsonData['total_count'] = $count;
            $result = [];
            if($prices['datas']){
                foreach ($prices['datas'] as $price) {
                    $result[] = $price->getData();
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No record found';
            }
            /* $eggtypes = $this->_eggtype->getList($param);
            $this->jsonData = $eggtypes;
            $result = [];
            if($eggtypes['datas']){
                foreach ($eggtypes['datas'] as $key => $eggtype) {
                    $result[$key] = $eggtype->getData();
                    $prices = $this->_price->getByColumn(['type_id'=> $eggtype->getData('id')], 0);
                    if($prices){
                        foreach ($prices as $key2 => $price) {
                            $result[$key]['per_piece'] = $price->getData('price');
                            $result[$key]['per_tray'] = $price->getData('price') * 30;
                            $result[$key]['per_case'] = $price->getData('price') * 360;
                        }
                    }
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No egg type unit found';
            } */
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>