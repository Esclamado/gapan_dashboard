<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

use Lns\Gpn\Lib\Entity\Db\Orderitemdetails;
use Lns\Gpn\Lib\Entity\Db\Orderitems;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_payment;
    protected $_eggtype;
    protected $_price;
    protected $_eggcarttype;
    protected $_orderstatus;
    protected $_ordercanceldecline;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Ordercanceldecline $Ordercanceldecline
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_payment = $Payment;
        $this->_eggtype = $Eggtype;
        $this->_price = $Price;
        $this->_eggcarttype = $EggCartType;
        $this->_orderstatus = $OrderStatus;
        $this->_ordercanceldecline = $Ordercanceldecline;
     }

     public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $this->jsonData['error'] = 1;

            $param = $this->getParam();

            $orders = $this->_orders->getList($param, $userId);
            $this->jsonData = $orders;
            $result = [];
            $i = 0;
            if ($orders['datas']) {
                foreach ($orders['datas'] as $order) {
                    $result[$i] = $order->getData();

                    $result[$i]['order_cancel'] = $this->_ordercanceldecline->getByOrderId($order->getData('id'), 1);
                    $result[$i]['order_decline'] = $this->_ordercanceldecline->getByOrderId($order->getData('id'), 2);

                    $payment = $this->_payment->getByColumn(['order_id'=> $order->getData('id')], 1);
                    if($payment){
                        $result[$i]['payment'] = $payment->getData();
                    }

                    $orderstatus = $this->_orderstatus->getByColumn(['order_id' => $order->getData('id')], 1);
                    if ($orderstatus) {
                        $result[$i]['status'] = $orderstatus->getData();
                    }

                    $orderitems = $this->_orderitems->getByColumn(['order_id'=> $order->getData('id')], 0);
                    if($orderitems){
                        $x = 0;
                        $total_items = 0;
                        foreach ($orderitems as $orderitem) {
                            $result[$i]['order_items'][$x] = $orderitem->getData();

                            $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                            if ($eggtypes) {
                                foreach ($eggtypes as $eggtype) {
                                    $result[$i]['order_items'][$x]['egg_type'] = $eggtype->getData();
                                    $eggprices = $this->_price->getByColumn(['type_id' => $eggtype->getData('id')], 0);
                                    if ($eggprices) {
                                        foreach ($eggprices as $eggprice) {
                                            
                                        }
                                    }
                                }
                            }

                            $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id'=> $orderitem->getData('id')], 0);
                            if($orderitemdetails){
                                $y = 0;
                                $pieces = 0;
                                foreach ($orderitemdetails as $orderitemdetail) {
                                    $result[$i]['order_items'][$x]['order_item_details'][$y] = $orderitemdetail->getData();

                                    $result[$i]['order_items'][$x]['egg_price'] = $orderitemdetail->getData('price');

                                    switch($orderitemdetail->getData('type_id')){
                                        case 1:
                                            $pieces = 360 * (int)$orderitemdetail->getData('qty');
                                        break;
                                        case 2:
                                            $pieces = 30 * (int)$orderitemdetail->getData('qty');
                                        break;
                                        default:
                                            $pieces = (int)$orderitemdetail->getData('qty');
                                        break;
                                    }
                                    $total_items += $pieces;

                                    $eggcarttypes = $this->_eggcarttype->getByColumn(['id'=> $orderitemdetail->getData('type_id')], 0);
                                    if($eggcarttypes){
                                        foreach ($eggcarttypes as $eggcarttype) {
                                            $result[$i]['order_items'][$x]['order_item_details'][$y]['egg_cart_type'] = $eggcarttype->getData();
                                        }
                                    }
                                    $y++;
                                }
                                $result[$i]['total_pieces'] = $total_items;
                            }
                            $x++;
                        }
                    }
                    $i++;
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