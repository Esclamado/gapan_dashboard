<?php
namespace Lns\Gpn\Controller\WebApi\Discount\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_payment;
    protected $_audittrail;
    protected $_userProfile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_payment = $Payment;
        $this->_audittrail = $AuditTrail;
        $this->_userProfile = $UserProfile;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $order_id = $this->getParam('id');
            $discount = $this->getParam('discount');

            $orderEntity = $this->_orders->getByColumn(['id' => $order_id], 1);
            if ($orderEntity) {
                $total_price = (float)$orderEntity->getData('total_price') - (float)$discount;
                $orderEntity->setData('total_price', $total_price);
                $orderEntity->setData('discount', $discount);
                $orderEntity->__save();

                $paymentEntity = $this->_payment->getByColumn(['order_id' => $order_id], 1);
                if ($paymentEntity) {
                    if ($orderEntity->getData('mode_of_payment') == 1) {
                        $paymentEntity->setData('payment', $total_price);
                        $paymentEntity->__save();
                    } else if ($orderEntity->getData('mode_of_payment') == 2) {
                        $paymentEntity->setData('balance', $total_price);
                        $paymentEntity->__save();
                    } else if ($orderEntity->getData('mode_of_payment') == 3) {
                        $total_price = (float)$paymentEntity->getData('payment') - (float)$discount;
                        $paymentEntity->setData('payment', $total_price);
                        $paymentEntity->__save();
                    }
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $action = 'Added discount to <strong>Order no. ' . $orderEntity->getData('transaction_id') . '</strong>';
                    $this->_audittrail->saveAudittrail($userId, $sender, $action, 'discount_save');

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Discount saved';
                } else {
                    $this->jsonData['message'] = 'No record found';
                }
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>