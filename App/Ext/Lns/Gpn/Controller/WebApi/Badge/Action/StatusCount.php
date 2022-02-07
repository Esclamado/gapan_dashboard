<?php
namespace Lns\Gpn\Controller\WebApi\Badge\Action;

class StatusCount extends \Lns\Sb\Controller\Controller {

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

            /* $param = $this->getParam(); */

            $pending = $this->_orders->getPendingcount();
            $balance = $this->_orders->getBalancecount();
            $credit = $this->_orders->getCreditcount();
            $completed = $this->_orders->getCompletedcount();

            $this->jsonData['data'] = array(
                'pending'=>$pending ? $pending : 0,
                'balance'=>$balance ? $balance : 0,
                'credit'=>$credit ? $credit : 0,
                'completed'=>$completed ? $completed : 0
            );
            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>