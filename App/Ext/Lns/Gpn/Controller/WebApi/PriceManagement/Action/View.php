<?php
namespace Lns\Gpn\Controller\WebApi\PriceManagement\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_price_history;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Pricehistory $Pricehistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_price_history = $Pricehistory;
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

            $prices = $this->_price_history->getList($param);
            $this->jsonData = $prices;
            $result = [];
            if($prices['datas']){
                foreach ($prices['datas'] as $key => $price) {
                    $result[$key] = $price->getData();
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No price found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>