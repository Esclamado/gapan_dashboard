<?php
namespace Lns\Gpn\Controller\Api\Orders\Action;

class Count extends \Lns\Sb\Controller\Controller {

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
            $order_statuses = [];
            $this->jsonData['error'] = 0;
            if ($this->getParam('order_statuses')) {
                $order_statuses = explode(',', $this->getParam('order_statuses'));
                foreach ($order_statuses as $order_status) {
                    $entity = $this->_orders->getByColumn(['order_status' => $order_status, 'user_id' => $userId], 0);
                    $this->jsonData['data'][]['count'] = count($entity);
                }
            } else {
                $entity = $this->_orders->getByColumn(['user_id' => $userId], 0);
                $this->jsonData['data'][] = count($entity);
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>
