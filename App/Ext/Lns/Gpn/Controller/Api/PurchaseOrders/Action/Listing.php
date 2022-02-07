<?php
namespace Lns\Gpn\Controller\Api\PurchaseOrders\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_orders;
    protected $_users;
    protected $_userprofile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_users = $Users;
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

            $orders = $this->_orders->getPurchaseOrders($param);
            $this->jsonData = $orders;
            $result = [];
            if ($orders['datas']) {
                foreach ($orders['datas'] as $key => $order) {
                    $result[$key] = $order->getData();
                    $customer = $this->_userprofile->getFullNameById($order->getData('user_id'));
                    if ($customer) {
                        $result[$key]['customer_name'] = $customer;
                    }
                    if ($order->getData('receipt')) {
                            $result[$key]['receipt_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/receipt/' . $order->getData('id'),
                            'filename' => $order->getData('receipt')
                        ]);
                    }
                    if ($order->getData('prepared_by')) {
                        $result[$key]['prepared_by_name'] = $this->_userprofile->getFullNameById($order->getData('prepared_by'));
                        $result[$key]['prepared_by_role'] = $this->_users->getRoleByUserId($order->getData('prepared_by'));
                        $result[$key]['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/warehouseman/' . $order->getData('prepared_by'),
                            'filename' => $order->getData('prepared_by_path')
                        ]);
                    }
    
                    if ($order->getData('checked_by')) {
                        $result[$key]['checked_by_name'] = $this->_userprofile->getFullNameById($order->getData('checked_by'));
                        $result[$key]['checked_by_role'] = $this->_users->getRoleByUserId($order->getData('checked_by'));
                        $result[$key]['checked_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $order->getData('checked_by'),
                            'filename' => $order->getData('checked_by_path')
                        ]);
                    }
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>