<?php
namespace Lns\Gpn\Controller\WebApi\Paymentattachments\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_payment_attachments;
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
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_payment_attachments = $PaymentAttachments;
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
            $result = [];
            $order_id = $this->getParam('order_id');

            $orderEntity = $this->_orders->getByColumn(['id' => $order_id], 1);
            if ($orderEntity) {
                
                $paymentEntity = $this->_payment->getByColumn(['order_id' => $orderEntity->getData('id')], 1);

                if ($paymentEntity) {
                    $attachments = $this->_payment_attachments->getListByPaymentId($param, $paymentEntity->getData('id'));
                    $this->jsonData = $attachments;
                    if ($attachments['datas']) {
                        $i = 0;
                        foreach ($attachments['datas'] as $attachment) {
                            $result[$i] = $attachment->getData();
                            if ($attachment->getData('type') == 1) {
                                $result[$i]['type_label'] = 'Official Recepit';
                                /* $result[$i]['attachment'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/receipt/' . $attachment->getData('payment_id'),
                                    'filename' => $attachment->getData('attachment')
                                ]); */
                            } else if ($attachment->getData('type') == 2) {
                                $result[$i]['type_label'] = 'Payment Form';
                                /* $result[$i]['attachment'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/payment_form/' . $attachment->getData('payment_id'),
                                    'filename' => $attachment->getData('attachment')
                                ]); */
                            } else if ($attachment->getData('type') == 3) {
                                $result[$i]['type_label'] = 'Credit Form';
                                /* $result[$i]['attachment'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/credit_form/' . $attachment->getData('payment_id'),
                                    'filename' => $attachment->getData('attachment')
                                ]); */
                            } else if ($attachment->getData('type') == 4) {
                                $result[$i]['type_label'] = 'Balance Form';
                                /* $result[$i]['attachment'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/balance_form/' . $attachment->getData('payment_id'),
                                    'filename' => $attachment->getData('attachment')
                                ]); */
                            }
                            $result[$i]['attachment'] = null;
                            if ($attachment->getData('attachment')) {
                                $result[$i]['attachment'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Gpn',
                                    'path' => '/images/uploads/transaction_form/' . $attachment->getData('payment_id'),
                                    'filename' => $attachment->getData('attachment')
                                ]);
                            }
                            $paymenthistory = $this->_paymenthistory->getByColumn(['payment_attachments_id' => $attachment->getData('id')], 1);
                            if ($paymenthistory) {
                                $result[$i]['payment_amount'] = number_format($paymenthistory->getData('payment'));
                            }else{
                                $result[$i]['payment_amount'] = 0;
                            }
                            $i++;
                        }
                        $this->jsonData['datas'] = $result;
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $orderEntity->getData();
                    $this->jsonData['data']['payment'] = $paymentEntity->getData();
                } else {
                    $this->jsonData['error'] = 1;
                    $this->jsonData['message'] = 'No record found';
                }
            } else {
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>