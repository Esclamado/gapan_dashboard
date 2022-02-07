<?php
namespace Lns\Gpn\Controller\Api\Cart\Action;

class Delete extends \Lns\Sb\Controller\Controller {

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
            $cartIds = $this->getParam('cartId');
            $selectAllCollection = $this->getParam('selectAllCollection');

            if ($selectAllCollection) {
                $cartRecords = $this->_cart->getByColumn(['user_id' => $userId], 0);
                if ($cartRecords) {
                    foreach ($cartRecords as $cartRecord) {
                        $cartdetailsRecords = $this->_cartdetails->getByColumn(['cart_id' => $cartRecord->getData('id')], 0);
                        foreach ($cartdetailsRecords as $cartdetailsRecord) {
                            $cartdetailsRecord->delete();
                        }
                        $cartRecord->delete();
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Your basket has been cleared.';
                } else {
                    $this->jsonData['message'] = 'Record not found';
                }
            } else {
                if ($cartIds) {
                    $cart_ids = explode(',', $cartIds);
                    foreach ($cart_ids as $cartId) {
                        $cartRecord = $this->_cart->getByColumn(['user_id' => $userId, 'id' => $cartId], 1);
                        if ($cartRecord) {
                            $cartdetailsRecord = $this->_cartdetails->getByColumn(['cart_id' => $cartRecord->getData('id')], 0);
                            foreach ($cartdetailsRecord as $value) {
                                $value->delete();
                            }
                            $cartRecord->delete();
    
                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'Item(s) has been deleted.';
                        } else {
                            $this->jsonData['message'] = 'Record not found';
                        }
                    }
                } else {
                    $this->jsonData['message'] = 'Please select an item to delete';
                }
            }
            
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>