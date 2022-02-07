<?php

namespace Lns\Gpn\Controller\WebApi\Orders\Action;

class Collectibles extends \Lns\Sb\Controller\Controller{

    protected $_orders;
    protected $_payment;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_payment = $Payment;
    }

    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $this->jsonData['error'] = 0;
            $id = $this->getParam('user_id');
            $collectibles = $this->_orders->getByColumn(['user_id' => $id, 'payment_status' => 0], 0);
            $collectible_amount = 0;
            if ($collectibles) {
                $i = 0;
                foreach($collectibles as $collectible) {
                    $collectible_amount += (float)$collectible->getData('total_price');
                    $i++;
                }
            }
            $this->jsonData['data']['collectible_no'] = $collectibles ? count($collectibles) : 0;
            $this->jsonData['data']['collectible_amount'] = $collectible_amount;

            $fully_paids = $this->_orders->getByColumn(['user_id' => $id, 'payment_status' => 1], 0);
            $fully_paid_amount = 0;
            if ($fully_paids) {
                $i = 0;
                foreach($fully_paids as $fully_paid) {
                    $fully_paid_amount += (float)$fully_paid->getData('total_price');
                    $i++;
                }
            }
            $this->jsonData['data']['fully_paid_amount'] = $fully_paid_amount;
            $this->jsonData['data']['total_revenue'] = (float)$collectible_amount + (float)$fully_paid_amount;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>