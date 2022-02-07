<?php
namespace Lns\Gpn\Controller\WebApi\RecentTransactions\Action;

class RecentTransactions extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_userprofile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_userprofile = $UserProfile;
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
            /* $orders = $this->_orders->getCollection(10); */
            $orders = $this->_orders->getLatestTransactions($param);
            
            if($orders){
                foreach ($orders as $key => $order) {
                    $result[$key] = $order->getData();
                    $userprofile = $this->_userprofile->getFullNameById($order->getData('user_id'));
                    $result[$key]['customer_name'] = $userprofile;

                    switch ($order->getData('order_status')) {
                        case 1:
                            $orderstatus = 'Pending for Approval';
                            break;
                        case 2:
                            $orderstatus = 'Processing';
                            break;
                        case 3:
                            $orderstatus = 'Ready for Pick Up';
                            break;
                        case 4:
                            $orderstatus = 'Completed';
                            break;
                        case 7:
                            $orderstatus = 'Cancelled';
                            break;
                        case 8:
                            $orderstatus = 'Declined';
                            break;
                    }
                    $result[$key]['status'] = $orderstatus;
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            }

        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>