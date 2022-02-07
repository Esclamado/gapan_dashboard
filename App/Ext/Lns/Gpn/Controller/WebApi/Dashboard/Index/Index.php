<?php
namespace Lns\Gpn\Controller\WebApi\Dashboard\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_dailysortingreport;
    protected $_egginventory;
    protected $_orders;
    protected $_paymenthistory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_egginventory = $EggInventory;
        $this->_orders = $Orders;
        $this->_paymenthistory = $Paymenthistory;
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

            $type = $this->getParam('type');
            $param = $this->getParam();

            $getDailyreports = $this->_dailyhouseharvest->getDailyreportsforapproval($param);
            $this->jsonData['data']['daily_reports_for_approval'] = $getDailyreports ? $getDailyreports : 0 ;
            
            $getSortedreports = $this->_dailysortingreport->getDailysortingreport($param);
            
            $this->jsonData['data']['sorted_reports_for_approval'] = $getSortedreports ? $getSortedreports : 0;

            $getEggcount = $this->_dailyhouseharvest->getTotaleggharvested($param);
            $this->jsonData['data']['total_eggs_harvested'] = $getEggcount ? $getEggcount : 0;

            $countdays = $this->_dailyhouseharvest->getDailyreportscount($param);

            $getProductionrate = $this->_dailyhouseharvest->getprodrate($param);
           
            if($getProductionrate){
                $this->jsonData['data']['production_rate'] = $getProductionrate / $countdays;
            }else{
                $this->jsonData['data']['production_rate'] = 0;
            }

            $getOrdersforapproval = $this->_orders->getOrdersforapproval($param);
            $this->jsonData['data']['collectible_orders_for_approval'] = $getOrdersforapproval ? $getOrdersforapproval : 0;

            $getCollectiblefullpayment = $this->_orders->getCollectiblefullpayment($param);
            $getCollectiblebalance = $this->_orders->getCollectiblebalance($param);
            $getCollectiblecredit = $this->_orders->getCollectiblecredit($param);
            $collectibles = 0;
            $collectibles = $getCollectiblefullpayment + $getCollectiblebalance + $getCollectiblecredit;
            $this->jsonData['data']['collectibles_amount'] = $collectibles ? $collectibles : 0 ;

            $getFullypaidorders = $this->_paymenthistory->getFullypaidorders($param);
            $this->jsonData['data']['fully_paid_orders'] = $getFullypaidorders ? $getFullypaidorders : 0;
            
            $totalrevenue = 0;
            $totalrevenue = $collectibles + $getFullypaidorders;
            $this->jsonData['data']['total_revenue'] = $totalrevenue ? $totalrevenue : 0;

            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}

?>
