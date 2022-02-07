<?php
namespace Lns\Gpn\Controller\WebApi\Transactions\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_userprofile;
    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_payment;
    protected $_eggtype;
    protected $_price;
    protected $_eggcarttype;
    protected $_user;
    protected $_orderstatus;
    protected $_ordercanceldecline;
    protected $_paymentAttachments;
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
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Ordercanceldecline $Ordercanceldecline,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_userprofile = $UserProfile;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_eggtype = $Eggtype;
        $this->_price = $Price;
        $this->_eggcarttype = $EggCartType;
        $this->_user = $Users;
        $this->_orderstatus = $OrderStatus;
        $this->_ordercanceldecline = $Ordercanceldecline;
        $this->_payment = $Payment;
        $this->_paymentAttachments = $PaymentAttachments;
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

            $orderId = $this->getParam('order_id');

            $order = $this->_orders->getByColumn(['id'=> $orderId], 1);
            if($order){
                $this->jsonData['data'] = $order->getData();

                if ($order->getData('walk_in_created_by')) {
                    $this->jsonData['data']['created_by'] = $this->_userprofile->getFullNameById($order->getData('walk_in_created_by'));
                }
                $payment = $this->_payment->getByColumn(['order_id' => $order->getData('id')], 1);
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
                    $user = $this->_user->getUserById($order->getData('user_id'));
                    if($user){
                        $this->jsonData['data']['customer_details'] = $user;
                    }
                    $orderstatus = $this->_orderstatus->getOrderStatus($order);
                    $this->jsonData['data']['status'] = $orderstatus;

                    $trackingstatus = $this->_orderstatus->getTrackingStatus($orderId);
                    $this->jsonData['data']['tracking_status'] = $trackingstatus ? $trackingstatus : 'N/A';

                    $orderitems = $this->_orderitems->getByColumn(['order_id' => $order->getData('id')], 0);
                    if ($orderitems) {
                        $i = 0;
                        $total_items = 0;
                        foreach ($orderitems as $orderitem) {
                            $this->jsonData['data']['order_items'][$i] = $orderitem->getData();

                            $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                            if ($eggtypes) {
                                foreach ($eggtypes as $eggtype) {
                                    $this->jsonData['data']['order_items'][$i]['egg_type'] = $eggtype->getData();
                                    /* $eggprice = $this->_price->getEggPrice($eggtype->getData('id'));
                                    $this->jsonData['data']['order_items'][$i]['egg_type']['price'] = $eggprice ? $eggprice->getData() : null; */
                                }
                            }

                            $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                            if ($orderitemdetails) {
                                $y = 0;
                                $pieces = 0;
                                foreach ($orderitemdetails as $orderitemdetail) {
                                    $this->jsonData['data']['order_items'][$i]['order_item_details'][$y] = $orderitemdetail->getData();
                                    
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

                                    $eggcarttypes = $this->_eggcarttype->getByColumn(['id' => $orderitemdetail->getData('type_id')], 0);
                                    if ($eggcarttypes) {
                                        foreach ($eggcarttypes as $eggcarttype) {
                                        $this->jsonData['data']['order_items'][$i]['order_item_details'][$y]['egg_cart_type'] = $eggcarttype->getData();
                                            /* $this->jsonData['data']['order_items'][$i]['cart_details'][$y]['egg_cart_type'] = $eggcarttype->getData(); */
                                            $this->jsonData['data']['order_items'][$i]['total_pieces'] = $pieces;
                                            $this->jsonData['data']['order_items'][$i]['total_price'] = $pieces * $orderitemdetail->getData('price');
                                        }
                                    }
                                    $y++;
                                }
                                $total_items += $pieces;
                                $this->jsonData['data']['total_pieces'] = $total_items;
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
                    $this->jsonData['data']['payment_type'] = $payment_type;

                    if ($order->getData('date_paid')) {
                        $status = 'Paid';
                    } else {
                        $status = 'Pending for Payment';
                    }
                    $this->jsonData['data']['payment_status_label'] = $status;

                $ordercanceldecline = $this->_ordercanceldecline->getbyColumn(['order_id'=> $order->getData('id')], 1);
                if($ordercanceldecline){
                    $this->jsonData['data']['order_cancel_decline'] = $ordercanceldecline->getData();
                }

                $lastpayment = $this->_paymenthistory->getLastPayment($order->getData('id'));
                if ($lastpayment) {
                    $this->jsonData['data']['last_payment'] = $lastpayment->getData();
                }

                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No transaction record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>