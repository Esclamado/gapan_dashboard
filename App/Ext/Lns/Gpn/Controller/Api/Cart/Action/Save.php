<?php
namespace Lns\Gpn\Controller\Api\Cart\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_cart;
    protected $_cartdetails;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Cart $Cart,
        \Lns\Gpn\Lib\Entity\Db\CartDetails $CartDetails
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_cart = $Cart;
        $this->_cartdetails = $CartDetails;
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
            $type_id = $this->getParam('type_id'); /* size */
            $cart = $this->getParam('cart'); /* qty, cart_type */
            $previous_type_id = $this->getParam('previous_type_id');

            if($type_id){
                if ($previous_type_id && $previous_type_id != $type_id) {
                    $entity = $this->_cart->getByColumn(['user_id'=> $userId, 'type_id' => $previous_type_id], 1);
                    if ($entity) {
                        $cartId = $entity->getData('id');
                        $hasRecords = $this->_cartdetails->getByColumn(['cart_id'=>$cartId], 0);
                        if ($hasRecords) {
                            foreach ($hasRecords as $hasRecord) {
                                $hasRecord->delete();
                            }
                        }
                        $entity->delete();
                    }
                }
                $entity = $this->_cart->getByColumn(['user_id'=> $userId, 'type_id' => $type_id], 1);
                $cartId = null;
                if(!$entity){
                    $entity = $this->_cart;
                    $entity->setData('user_id',$userId);
                    $entity->setData('type_id', $type_id);
                    $cartId = $entity->__save();
                }else{
                    $cartId = $entity->getData('id');
                }
                if($cartId){
                    $message = 'Item added to cart';
/*                     $hasRecord = $this->_cartdetails->getByColumn(['cart_id'=>$cartId], 0);
                    if($hasRecord){
                        $message = 'Item updated';
                        foreach ($hasRecord as $value) {

                        }
                    } */
                    if($cart){
                        foreach ($cart as $value) {
                            if ($value['quantity'] > 0) {
                                $entity = $this->_cartdetails->getbyColumn(['type_id' => $value['egg_cart_type_id'], 'cart_id'=> $cartId], 1);
                                if($entity){
                                    if(!$previous_type_id){
                                        $message = 'Item updated';
                                        $value['quantity'] += (int) $entity->getData('qty');
                                    }
                                }else{
                                    $entity = $this->_cartdetails;
                                }
                                $entity->setData('cart_id', $cartId);
                                $entity->setData('type_id', $value['egg_cart_type_id']);
                                $entity->setData('qty', $value['quantity']);
                                $entity->__save();
                            }else{
                                if ($previous_type_id) {
                                    $entity = $this->_cartdetails->getbyColumn(['type_id' => $value['egg_cart_type_id'], 'cart_id' => $cartId], 1);
                                    if($entity){
                                        $entity->delete();
                                    }
                                }
                            }
                        }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $message;
                    }
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>
