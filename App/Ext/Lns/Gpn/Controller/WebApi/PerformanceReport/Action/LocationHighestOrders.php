<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class LocationHighestOrders extends \Lns\Sb\Controller\Controller {

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
            $orders = $this->_orders->getLocationwithhighestorders($param);
            $total_count = $this->_orders->getLocationwithhighestorders($param, true);
            /* if($orders){
                foreach ($orders as $order) {
                    $result[] = $order->getData();
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No record found';
            } */
            if($orders && $orders['datas']) {
                $this->jsonData = $orders;
                $this->jsonData['total_count'] = $total_count;
                $result = [];
                foreach ($orders['datas'] as $key => $order) {
                    $result[] = $order->getData();
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>