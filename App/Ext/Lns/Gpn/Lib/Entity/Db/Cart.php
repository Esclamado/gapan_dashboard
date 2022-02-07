<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Cart extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

  protected $tablename = 'cart';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
		'id',
		'user_id',
    'type_id',
    'created_at'
  ];

  protected $_cartdetails;
  protected $_price;

  public function __construct(
    \Of\Http\Request $Request
  ) {
    parent::__construct($Request);
    $this->_cartdetails = $this->_di->get('Lns\Gpn\Lib\Entity\Db\CartDetails');
    $this->_price = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Price');
  }

  public function getCartInfo($param,$id){
    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    $where = "user_id=" . $id;
    $this->_select->where($where);
    return $this->getFinalResponse($limit);
  }

  public function getGrandTotal($userId, $isPieces = false){
    $total = 0;
    $pieces = 0;
    $carts = $this->getByColumn(['user_id'=>$userId], 0);
    if($carts){
      foreach ($carts as $cart) {
        $eggprice = $this->_price->getByColumn(['id'=> $cart->getData('type_id')], 1);
        $eggprice = $eggprice ? $eggprice->getData('price') : 0 ;
        $cartdetails = $this->_cartdetails->getByColumn(['cart_id'=> $cart->getData('id')], 0);
        if($cartdetails){
          foreach ($cartdetails as $cartdetail) {
            switch ($cartdetail->getData('type_id')) {
              case 1:
                /* 1 case = 360pcs */
                $pieces = 360 * (int) $cartdetail->getData('qty');
                break;
              case 2:
                $pieces = 30 * (int) $cartdetail->getData('qty');
                break;
              default:
                $pieces = (int) $cartdetail->getData('qty');
                break;
            }
            if ($isPieces) {
              $total += $pieces;
            } else {
              $total += $pieces * (float)$eggprice;
            }
          }
        }
      }
    }
    return $total;
  }
/*   public function getCart($id){
    $data = $this->getByColumn(['id'=> $id], 0);
    if($data){
      return $data;
    }else{
      return null;
    }
  }
  public function getMyCart($user_id){
    $data = $this->getByColumn(['user_id' => $user_id], 0);
    if ($data) {
      return $data;
    } else {
      return null;
    }
  } */
}
?>