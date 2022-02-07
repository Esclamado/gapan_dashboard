<?php
namespace Lns\Gpn\Controller\WebApi\Customertype\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_customer_type;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\CustomerType $CustomerType
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_customer_type = $CustomerType;
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

            $customer_types = $this->_customer_type->getCollection();
            $result = [];
            if ($customer_types) {
                foreach($customer_types as $customer_type) {
                    $result[] = $customer_type->getData();
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No customer profile found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>