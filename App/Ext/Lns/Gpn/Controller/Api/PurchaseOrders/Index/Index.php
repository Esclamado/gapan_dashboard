<?php
namespace Lns\Gpn\Controller\Api\PurchaseOrders\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_userprofile;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_eggtype;
    protected $_orderstatus;
    protected $_users;
    protected $_payment;
    protected $_paymentAttachments;
    protected $_eggCartType;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_userprofile = $UserProfile;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_eggtype = $Eggtype;
        $this->_orderstatus = $OrderStatus;
        $this->_users = $Users;
        $this->_payment = $Payment;
        $this->_paymentAttachments = $PaymentAttachments;
        $this->_eggCartType = $EggCartType;
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

            $orderId = $this->getParam('orderId');
            $orders = $this->_orders->getByColumn(['id' => $orderId], 1);
            if($orderId){
                $this->jsonData['data'] = $orders->getData();

                if ($orders->getData('approved_by')) {
                    $this->jsonData['data']['approved_by_name'] = $this->_userprofile->getFullNameById($orders->getData('approved_by'));
                }

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

                if ($orders->getData('prepared_by')) {
                    $this->jsonData['data']['prepared_by_name'] = $this->_userprofile->getFullNameById($orders->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($orders->getData('prepared_by'));
                    $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/warehouseman/' . $orders->getData('prepared_by'),
                        'filename' => $orders->getData('prepared_by_path')
                    ]);
                }

                if ($orders->getData('checked_by')) {
                    $this->jsonData['data']['checked_by_name'] = $this->_userprofile->getFullNameById($orders->getData('checked_by'));
                    $this->jsonData['data']['checked_by_role'] = $this->_users->getRoleByUserId($orders->getData('checked_by'));
                    $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/inspector/' . $orders->getData('checked_by'),
                        'filename' => $orders->getData('checked_by_path')
                    ]);
                }

                $customer = $this->_userprofile->getFullNameById($orders->getData('user_id'));
                if ($customer) {
                    $this->jsonData['data']['customer_name'] = $customer;
                }
                switch($orders->getData('mode_of_payment')){
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
                if($orders->getData('date_paid')){
                    $status = 'Paid';
                }else{
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

                $orderstatus = $this->_orderstatus->getTrackingStatus($orders->getData('id'));
                if ($orderstatus) {
                    $this->jsonData['data']['status'] = $orderstatus;
                }

                $orderitems = $this->_orderitems->getByColumn(['order_id' => $orders->getData('id')], 0);
                if($orderitems){
                    $i = 0;
                    $total_items = 0;
                    foreach ($orderitems as $orderitem) {
                        $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                        if ($eggtypes) {
                            foreach ($eggtypes as $eggtype) {
                                $this->jsonData['data']['egg_type'][] = $eggtype->getData();
                            }
                        }
                        $eggCartTypes = $this->_eggCartType->getCollection();
                        if ($eggCartTypes) {
                            $x = 0;
                            $pieces = 0;
                            foreach($eggCartTypes as $eggCartType) {
                                $orderitemdetail = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id'), 'type_id' => $eggCartType->getData('id')], 1);
                                if (!$orderitemdetail) {
                                    $this->jsonData['data']['egg_type'][$i]['package_type'][$x] = null;
                                } else {
                                    $this->jsonData['data']['egg_type'][$i]['package_type'][$x] = $orderitemdetail->getData();
                                    switch($orderitemdetail->getData('type_id')) {
                                        case 1:
                                            $package_type = 'Case';
                                        break;
                                        case 2:
                                            $package_type = 'Tray';
                                        break;
                                        case 3:
                                            $package_type = 'Piece';
                                        break;
                                    }
                                     $this->jsonData['data']['egg_type'][$i]['package_type'][$x]['type'] = $package_type;
    
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
                                    $this->jsonData['data']['egg_type'][$i]['total_eggs'] = $pieces;
                                }
                                $x++;
                            }
                            $total_items += $pieces;
                            $this->jsonData['data']['total_pieces'] = $total_items;
                        }
                        
                        /* $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                        if ($orderitemdetails) {
                            $x = 0;
                            $pieces = 0;
                            foreach ($orderitemdetails as $orderitemdetail) {

                                $this->jsonData['data']['egg_type'][$i]['package_type'][$x] = $orderitemdetail->getData();

                                switch($orderitemdetail->getData('type_id')) {
                                    case 1:
                                        $package_type = 'Case';
                                    break;
                                    case 2:
                                        $package_type = 'Tray';
                                    break;
                                    case 3:
                                        $package_type = 'Piece';
                                    break;
                                }
                                 $this->jsonData['data']['egg_type'][$i]['package_type'][$x]['type'] = $package_type;

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
                                $this->jsonData['data']['egg_type'][$i]['total_eggs'] = $pieces;
                                $x++;
                            }
                            $total_items += $pieces;
                            $this->jsonData['data']['total_pieces'] = $total_items;
                        } */
                        $i++; 
                    }
                }
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
