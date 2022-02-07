<?php
namespace Lns\Gpn\Controller\WebApi\FresheggInventory\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_egginventory;
    protected $_eggtype;
    protected $_dailysortinginventory;
    protected $_orderitems;
    protected $_orderitemdetails;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_egginventory = $EggInventory;
        $this->_eggtype = $Eggtype;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        /*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; */

            $param = $this->getParam();

            $dailysortinginventorys = $this->_dailysortinginventory->getEgglist($param);
            if($dailysortinginventorys){
                $eggcount = 0;
                $total_eggcount = 0;
                foreach ($dailysortinginventorys as $key => $dailysortinginventory) {
                    $this->jsonData['datas'][$key] = $dailysortinginventory->getData();
                    $eggtype = $this->_eggtype->getEggType($dailysortinginventory->getData('type_id'));
                    if($eggtype){
                        $this->jsonData['datas'][$key]['egg_type'] = $eggtype->getData();
                    }
                    $eggcount = $this->_dailysortinginventory->getEachCount($dailysortinginventory->getData('type_id'), $dailysortinginventory->getData('created_at'));
                    $this->jsonData['datas'][$key]['harvested'] = $eggcount;
                    $total_eggcount += $eggcount;

                    $orderitems = $this->_orderitems->getByColumn(['type_id' => $dailysortinginventory->getData('type_id')], 0);
                    if ($orderitems) {
                        $x = 0;
                        $total_items = 0;
                        foreach ($orderitems as $orderitem) {
                        $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                        if ($orderitemdetails) {
                            $y = 0;
                            $pieces = 0;
                            foreach ($orderitemdetails as $orderitemdetail) {
                                switch ($orderitemdetail->getData('type_id')) {
                                    case 1:
                                        $pieces += 360 * (int) $orderitemdetail->getData('qty');
                                        break;
                                    case 2:
                                        $pieces += 30 * (int) $orderitemdetail->getData('qty');
                                        break;
                                    default:
                                        $pieces += (int) $orderitemdetail->getData('qty');
                                        break;
                                }
                                $y++;
                            }
                            $this->jsonData['datas'][$key]['sales'] = $pieces;
                            $total_items += $pieces;
                            /* $this->jsonData['data']['total_pieces'] = $total_items; */
                        }
                            $x++;
                        }
                    }
                }
                $this->jsonData['data']['harvested_eggs_for_this_day'] = $total_eggcount;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No fresh eggs record found';
            }
        /* } */
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>