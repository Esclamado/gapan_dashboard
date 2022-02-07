<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class CreditBalance extends \Lns\Sb\Controller\Controller {

    protected $_orders;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
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
            $result = [];
            $orders = $this->_orders->getWeeks($param);
            if($orders){
                foreach ($orders as $key => $order) {
                    $result[$key] = $order;
                    $balances = $this->_orders->getBalance($order['week']);
                    if($balances){
                        foreach ($balances as $balance) {
                            $result[$key]['balance'] = $balance->getData('sum');
                        }
                    }
                    $credits = $this->_orders->getCredit($order['week']);
                    if ($credits) {
                        foreach ($credits as $credit) {
                            $result[$key]['credit'] = $credit->getData('sum') ? $credit->getData('sum') : 0 ;
                        }
                    }
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>