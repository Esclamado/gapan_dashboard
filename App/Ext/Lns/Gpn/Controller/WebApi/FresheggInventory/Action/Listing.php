<?php
namespace Lns\Gpn\Controller\WebApi\FresheggInventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_freshegginventory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_freshegginventory = $FresheggInventory;
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

            $freshegginventorys = $this->_freshegginventory->getList($param);
            $this->jsonData = $freshegginventorys;
            $result = [];
            if($freshegginventorys['datas']){
                foreach ($freshegginventorys['datas'] as $key => $freshegginventory) {
                    $result[] = $freshegginventory->getData();
                    $totaleggs = $freshegginventory->getdata('total_remaining_stocks');
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
                $this->jsonData['data']['total_quantity_of_eggs'] = $totaleggs ? $totaleggs : 0 ;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>