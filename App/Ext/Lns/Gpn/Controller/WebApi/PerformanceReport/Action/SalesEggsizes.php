<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class SalesEggsizes extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_eggtype = $Eggtype;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
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

            $orders = $this->_orders->getSaleseggsize($param);
            $total_count = $this->_orders->getSaleseggsize($param, true);
            
            $result = [];
            if($orders['datas']){
                $this->jsonData = $orders;
                $this->jsonData['total_count'] = $total_count;
                foreach ($orders['datas'] as $key => $order) {
                    $result[$key]['grouped_date'] = $order->getData('grouped_date');
                    /* $order_singles = $this->_orders->getSalesByDate($order->getData('grouped_date')); */
                    $eggtypes = $this->_eggtype->getCollection();
                    if($eggtypes){
                        $i = 0;
                        foreach ($eggtypes as $eggtype) {
                            $result[$key]['egg_types'][$i] = $eggtype->getData();
                            $result[$key]['egg_types'][$i]['total'] = 0;
                            $createdat = date("Y-m-d",strtotime($order->getData('grouped_date')));  
                            
                            $orderbydates = $this->_orders->getOrderbydate($createdat);
                            if($orderbydates){
                                $total = 0;
                                foreach ($orderbydates as $orderbydate) {
                                    $orderitems = $this->_orderitems->getByColumn(['order_id'=> $orderbydate->getData('id'), 'type_id'=>$eggtype->getData('id')], 1);
                                    if($orderitems){
                                        $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id'=> $orderitems->getData('id')], 0);
                                        if($orderitemdetails) {
                                            foreach ($orderitemdetails as $orderitemdetail) {
                                                switch($orderitemdetail->getData('type_id')){
                                                    case 1:
                                                        $total += (int)$orderitemdetail->getData('qty') * 360;
                                                        break;
                                                    case 2:
                                                        $total += (int)$orderitemdetail->getData('qty') * 30;
                                                        break;
                                                    case 3:
                                                        $total += (int)$orderitemdetail->getData('qty');
                                                        break;
                                                }
                                                /* $result[$key]['egg_types'][$i]['total'] = $total; */
                                            }
                                        }
                                    }


                                }
                                $result[$key]['egg_types'][$i]['total'] = $total;
                            }
                            $i++;
                        }
                    }
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No sales by egg sizes record found';
                $this->jsonData['error'] = 1;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>