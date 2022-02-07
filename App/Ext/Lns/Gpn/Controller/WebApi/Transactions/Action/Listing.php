<?php
namespace Lns\Gpn\Controller\WebApi\Transactions\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_userprofile;
    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_orderstatus;
    protected $_eggtype;
    protected $_payment;
    protected $_paymenthistory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_eggtype = $Eggtype;
        $this->_payment = $Payment;
        $this->_orderstatus = $OrderStatus;
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

            $orders = $this->_orders->getOrderslisting($param);
            $this->jsonData = $orders;
            $result = [];
            if($orders['datas']){
                foreach ($orders['datas'] as $key => $order) {
                    $result[$key] = $order->getData();

                    $user = $this->_userprofile->getFullNameById($order->getData('user_id'));
                    if($user){
                        $result[$key]['customer_name'] = $user;
                    }

                    $payment = $this->_payment->getByColumn(['order_id'=> $order->getData('id')], 1);
                    if($payment){
                        $result[$key]['payment'] = $payment->getData();
                    }
                    $orderstatus = $this->_orderstatus->getOrderStatus($order);
                    $result[$key]['status'] = $orderstatus;

                    $orderitems = $this->_orderitems->getByColumn(['order_id' => $order->getData('id')], 0);
                    if ($orderitems) {
                        $i = 0;
                        $total_items = 0;
                        foreach ($orderitems as $orderitem) {
                            $result[$key]['order_items'][] = $orderitem->getData();

                            $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                            if ($eggtypes) {
                                foreach ($eggtypes as $eggtype) {
                                    $result[$key]['order_items'][$i]['egg_type'] = $eggtype->getData();
                                }
                            }

                            $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                            if ($orderitemdetails) {
                                $pieces = 0;
                                foreach ($orderitemdetails as $orderitemdetail) {

                                    switch ($orderitemdetail->getData('type_id')) {
                                        case 1:
                                            $pieces += 360 * (int) $orderitemdetail->getData('qty');
                                            break;
                                        case 2:
                                            $pieces += 30 * (int) $orderitemdetail->getData('qty');
                                            break;
                                        default:
                                            $pieces += (int) $orderitemdetail->getData('qty');
                                            break;
                                    }
                                }
                                $total_items += $pieces;
                                $result[$key]['total_pieces'] = $total_items;
                            }
                            $i++;
                        }
                    }

                    switch ($order->getData('mode_of_payment')) {
                        case 1:
                            $payment_type = 'Full Payment';
                            break;
                        case 2:
                            $payment_type = 'With Credit';
                            break;
                        case 3:
                            $payment_type = 'With Balance';
                            break;
                    }
                    $result[$key]['payment_type'] = $payment_type;

                    if ($order->getData('date_paid')) {
                        $lastpayments = $this->_paymenthistory->getByColumn(['order_id'=> $order->getData('id')], 0);
                        $amounttopay = $this->_orders->getByColumn(['id' => $order->getData('id')], 1);
                        $getpayment = 0;
                        $status = '';
                        foreach ($lastpayments as $lastpayment) {
                            $getpayment += $lastpayment->getData('payment');
                            if ($amounttopay->getData('total_price') == $getpayment) {
                                $status = 'Paid';
                            } else if($getpayment==0){
                                $status = 'Pending for Payment';
                            }else{
                                $status = 'Partially Paid';
                            }
                        }
 
                    } else {
                        $status = 'Pending for Payment';
                    }
                    $result[$key]['payment_status_label'] = $status;

                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
            $count = $this->_orders->getCount();
            $this->jsonData['data']['total_number_of_transactions'] = $count ? $count : 0 ;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>