<?php
namespace Lns\Gpn\Controller\Api\Orders\Index;

use Lns\Gpn\Lib\Entity\Db\Orderitemdetails;
use Lns\Gpn\Lib\Entity\Db\Orderitems;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_payment;
    protected $_eggtype;
    protected $_price;
    protected $_eggcarttype;
    protected $_orderstatus;
    protected $_ordercanceldecline;
    protected $_userprofile;
    protected $_paymentAttachments;

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
        \Lns\Gpn\Lib\Entity\Db\Ordercanceldecline $Ordercanceldecline,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments
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
        $this->_userprofile = $UserProfile;
        $this->_paymentAttachments = $PaymentAttachments;
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

            $orderId = $this->getParam('orderId');
            $orders = $this->_orders->getByColumn(['id'=> $orderId], 1);
            if ($orders) {
                    $this->jsonData['data'] = $orders->getData();

                    $this->jsonData['data']['order_cancel'] = $this->_ordercanceldecline->getByOrderId($orders->getData('id'), 1);
                    $this->jsonData['data']['order_decline'] = $this->_ordercanceldecline->getByOrderId($orders->getData('id'), 2);

                    $payment = $this->_payment->getByColumn(['order_id' => $orders->getData('id')], 1);
                    if ($payment) {
                        $this->jsonData['data']['payment'] = $payment->getData();

                        $paymentAttachments = $this->_paymentAttachments->getByColumn(['payment_id' => $payment->getData('id')], 0);
                        if ($paymentAttachments) {
                            foreach ($paymentAttachments as $key => $paymentAttachment) {
                                $this->jsonData['data']['payment']['attachments'][$key] = $paymentAttachment->getData();
                                
                                $type = 'transaction_form';
                                $type_label = '';
                                switch($paymentAttachment->getData('type')) {
                                    case 1:
                                        /* $type = 'receipt'; */
                                        $type_label = 'Receipt';
                                    break;
                                    case 2:
                                        /* $type = 'payment_form'; */
                                        $type_label = 'Payment Form';
                                    break;
                                    case 3:
                                        /* $type = 'credit_form'; */
                                        $type_label = 'Credit Form';
                                    break;
                                    case 4:
                                        /* $type = 'balance_form'; */
                                        $type_label = 'Balance Form';
                                    break;
                                }
                                $this->jsonData['data']['payment']['attachments'][$key]['type_label'] = $type_label;
                                $this->jsonData['data']['payment']['attachments'][$key]['attachment_path'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/'.$type.'/' . $payment->getData('id'),
                                    'filename' => $paymentAttachment->getData('attachment')
                                ]);
                            }
                        }
                    }



                    $orderstatus = $this->_orderstatus->getTrackingStatus($orders->getData('id'));
                    if ($orderstatus) {
                        $this->jsonData['data']['status'] = $orderstatus;
                    }

                    if ($orders->getData('receipt')) {
                        $this->jsonData['data']['receipt_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/receipt/' . $orders->getData('id'),
                            'filename' => $orders->getData('receipt')
                        ]);
                    }
                    if ($orders->getData('approved_by')) {
                        $this->jsonData['data']['approved_by_name'] = $this->_userprofile->getFullNameById($orders->getData('approved_by'));
                    }

                    switch ($orders->getData('mode_of_payment')) {
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
                    $this->jsonData['data']['payment_type'] = $payment_type;
                    if ($orders->getData('date_paid')) {
                        $status = 'Paid';
                    } else {
                        $status = 'Pending for Payment';
                    }
                    /*                 switch ($orders->getData('date_paid')) {
                        case 1:
                            $status = 'Paid';
                            break;
                            default:
                            $status = 'Pending for Payment';
                            break;
                    } */
                    $this->jsonData['data']['payment_status_label'] = $status;

                    $orderitems = $this->_orderitems->getByColumn(['order_id'=> $orders->getData('id')], 0);
                    if($orderitems){
                        $x = 0;
                        $total_items = 0;
                        foreach ($orderitems as $orderitem) {
                            $this->jsonData['data']['order_items'][] = $orderitem->getData();

                            $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                            if ($eggtypes) {
                                foreach ($eggtypes as $eggtype) {
                                    $this->jsonData['data']['order_items'][$x]['egg_type'] = $eggtype->getData();
                                    $eggprice = $this->_price->getEggPrice($eggtype->getData('id'));
                                    $this->jsonData['data']['order_items'][$x]['egg_type']['price'] = $eggprice ? $eggprice->getData() : null;
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
                                    /* $this->jsonData['data']['order_items'][$x]['order_item_details'][$y] = $orderitemdetail->getData(); */
                                    $this->jsonData['data']['order_items'][$x]['cart_details'][$y] = $orderitemdetail->getData();

                                    $this->jsonData['data']['order_items'][$x]['egg_price'] = $orderitemdetail->getData('price');

                                    switch($orderitemdetail->getData('type_id')){
                                        case 1:
                                            $pieces += 360 * (int)$orderitemdetail->getData('qty');
                                        break;
                                        case 2:
                                            $pieces += 30 * (int)$orderitemdetail->getData('qty');
                                        break;
                                        default:
                                            $pieces += (int)$orderitemdetail->getData('qty');
                                        break;
                                    }
                                    $eggcarttypes = $this->_eggcarttype->getByColumn(['id'=> $orderitemdetail->getData('type_id')], 0);
                                    if($eggcarttypes){
                                        foreach ($eggcarttypes as $eggcarttype) {
                                            /* $this->jsonData['data']['order_items'][$x]['order_item_details'][$y]['egg_cart_type'] = $eggcarttype->getData(); */
                                            $this->jsonData['data']['order_items'][$x]['cart_details'][$y]['egg_cart_type'] = $eggcarttype->getData();
                                            /* $this->jsonData['data']['order_items'][$x]['total_items'] = $pieces; */
                                            $this->jsonData['data']['order_items'][$x]['total_pieces'] = $pieces;
                                            $this->jsonData['data']['order_items'][$x]['total_price'] = $pieces * $orderitemdetail->getData('price');
                                        }
                                    }
                                    $y++;
                                }
                                $total_items += $pieces;
                                $this->jsonData['data']['total_pieces'] = $total_items;
                            }
                            $x++;
                        }
                    }
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
