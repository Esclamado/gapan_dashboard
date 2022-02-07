<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class OverallSales extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_payment;
    protected $_paymenthistory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_payment = $Payment;
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

            $param = $this->getParam();

            $orders = $this->_orders->getOverallsales($param);
            $total_count = $this->_orders->getOverallsales($param, true);
            /* $this->jsonData = $orders;
            $result = []; */
            if($orders && $orders['datas']) {
                $this->jsonData = $orders;
                $this->jsonData['total_count'] = $total_count;
                $result = [];
                foreach ($orders['datas'] as $key => $order) {
                    $result[$key] = array(
                        'date'=>$order->getData('grouped_date'),
                        'total'=> $order->getData('sum'),
                        'total_orders'=> $order->getData('total_orders'),
                    );
                }
                $this->jsonData['datas'] = $result;
            /*                 $collectibles = $this->_orders->getCollectibleAmount($param);
                $pendingOrders = $this->_orders->countPendingOrders($param);
                $collectible_count = 0;
                $collectible_amount = 0;
                $fully_paid = 0;
                if ($collectibles) {
                    foreach ($collectibles as $collectible) {
                        $payment = $this->_payment->getByColumn(['order_id' => $collectible->getData('id')], 1);
                        if ($collectible->getData('mode_of_payment') == 1) {
                            if ($collectible->getData('payment_status') == 0) {
                                $collectible_amount += (float)$collectible->getData('total_price');
                                $collectible_count += 1;
                            }
                        } else if ($collectible->getData('mode_of_payment') == 1) {
                            if ($collectible->getData('payment_status') == 0) {
                                $collectible_amount += (float)$collectible->getData('total_price');
                                $collectible_count += 1;
                            }
                        } else {
                            if ($collectible->getData('payment_status') == 0) {
                                $collectible_amount += (float)$collectible->getData('total_price');
                                $collectible_count += 1;
                            }
                        }

                        if ($collectible->getData('payment_status') == 1) {
                            $fully_paid += (float)$collectible->getData('total_price');
                        }
                    }
                } */
                $getCollectiblefullpayment = $this->_orders->getFullpayments($param);
                $getCollectiblebalance = $this->_orders->getBalances($param);
                $getCollectiblecredit = $this->_orders->getCredits($param);
                $collectibles = 0;
                $collectibles = $getCollectiblefullpayment + $getCollectiblebalance + $getCollectiblecredit;

                $getFullypaidorders = $this->_paymenthistory->getFullypaid($param);

                $Countcollectibles = $this->_orders->Countcollectibles($param);
                $Countpendings = $this->_orders->Countpendings($param);

                $salesRate = 0;
                $getSalesyesterday = $this->_paymenthistory->getSalesyesterday();
                $getSalestoday = $this->_paymenthistory->getSalestoday();
                if($getSalesyesterday && $getSalestoday){
                    $salesRate = $getSalestoday / $getSalesyesterday * 100;
                }

                $isIncreasing = false;
                if($getSalestoday>$getSalesyesterday){
                    $isIncreasing = true;
                }

                $this->jsonData['data']['is_increasing'] = $isIncreasing;
                $this->jsonData['data']['increase_rate'] = $salesRate ? $salesRate : 0 ;
                $this->jsonData['data']['collectibles_amount'] = $collectibles ? $collectibles : 0 ;
                $this->jsonData['data']['collectibles'] = $Countcollectibles ? $Countcollectibles : 0 ;
                $this->jsonData['data']['fully_paid_orders'] = $getFullypaidorders ? $getFullypaidorders : 0;
                $this->jsonData['data']['pending_orders'] = $Countpendings ? $Countpendings : 0 ;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>