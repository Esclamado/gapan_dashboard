<?php
namespace Lns\Gpn\Controller\Api\Cart\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_cart;
    protected $_eggtype;
    protected $_eggcarttype;
    protected $_price;
    protected $_cartdetails;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Cart $Cart,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\CartDetails $CartDetails
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_cart = $Cart;
        $this->_eggtype = $Eggtype;
        $this->_eggcarttype = $EggCartType;
        $this->_price = $Price;
        $this->_cartdetails = $CartDetails;
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
            $type_id = $this->getParam('type_id');

            $entity = $this->_cart->getByColumn(['user_id'=> $userId, 'type_id' => $type_id], 1);

            if ($entity) {
                $result = $entity->getData();
                $cartDetails = $this->_cartdetails->getByColumn(['cart_id'=>$entity->getData('id')], 0);
                $eggtypes = $this->_eggtype->getEggType($entity->getData('type_id'));
                $result['egg_type'] = $eggtypes->getData();
                $x = 0;
                $pieces = 0;
                foreach ($cartDetails as $cartDetail) {
                    $result['cart_details'][$x] = $cartDetail->getData();

                    $eggcarttype = $this->_eggcarttype->getEggCartType($cartDetail->getData('type_id'));
                    $result['cart_details'][$x]['egg_cart_type'] = $eggcarttype->getData();

                    switch($cartDetail->getData('type_id')){
                        case 1:
                        /* 1 case = 360pcs */
                        $pieces += 360 * (int) $cartDetail->getData('qty');
                        break;
                        case 2:
                        $pieces += 30 * (int) $cartDetail->getData('qty');
                        break;
                        default:
                        $pieces += (int) $cartDetail->getData('qty');
                        break;
                    }
                    $x++;
                }
                $eggprice = $this->_price->getEggPrice($entity->getData('type_id'));
                $result['egg_type']['price'] = $eggprice ? $eggprice->getData() : null;
                $result['total_pieces'] = $pieces;
                $result['total_price'] = $pieces * $eggprice->getData('price');
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'Record does not exist';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>