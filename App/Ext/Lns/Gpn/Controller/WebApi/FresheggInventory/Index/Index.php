<?php
namespace Lns\Gpn\Controller\WebApi\FresheggInventory\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_daily_sorting_report;
    protected $_daily_sorting_inventory;
    protected $_fresh_egg_inventory;
    protected $_egg_inventory;
    protected $_egg_type;
    protected $_orders;
    protected $_order_items;
    protected $_order_item_details;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_daily_sorting_report = $Dailysortingreport;
        $this->_egg_inventory = $EggInventory;
        $this->_fresh_egg_inventory = $FresheggInventory;
        $this->_egg_type = $Eggtype;
        $this->_daily_sorting_inventory = $Dailysortinginventory;
        $this->_orders = $Orders;
        $this->_order_items = $Orderitems;
        $this->_order_item_details = $Orderitemdetails;
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

            $date = $this->getParam('date');
            $data = $this->_fresh_egg_inventory->getRecordtoday($date);
            if ($data) {
                $egg_types = $this->_egg_type->getCollection();

                $result = [];

                foreach ($egg_types as $egg_type) {
                    $beginning = $this->_daily_sorting_inventory->getPrevious($date, $egg_type->getData('id'));
                    $harvested = $this->_daily_sorting_inventory->getToday($date, $egg_type->getData('id'));

                    $beginning_orders = null;

                    if ($beginning) {
                        $beginning_orders = $this->_orders->getSalesByDate(date('Y-m-d', strtotime($beginning->getData('created_at'))));
                    }
                    $orders = $this->_orders->getSalesByDate($date);
                    $beg_sale_count = 0;
                    $sale_count = 0;
                    if ($orders) {
                        foreach ($orders as $order) {
                            $order_items = $this->_order_items->getByColumn(['order_id' => $order->getData('id'), 'type_id' => $egg_type->getData('id')], 0);
                            foreach($order_items as $order_item) {
                                $order_item_details = $this->_order_item_details->getByColumn(['order_item_id' => $order_item->getData('id')], 0);
                                foreach($order_item_details as $order_item_detail) {
                                    if ($order_item_detail->getData('type_id') == 1) {
                                        $sale_count += 360 * (int)$order_item_detail->getData('qty');
                                    } else if ($order_item_detail->getData('type_id') == 2) {
                                        $sale_count += 30 * (int)$order_item_detail->getData('qty');
                                    } else if ($order_item_detail->getData('type_id') == 3) {
                                        $sale_count += (int)$order_item_detail->getData('qty');
                                    }
                                }
                            }
                        }
                    }
                    if ($beginning_orders) {
                        foreach ($beginning_orders as $order) {
                            $order_items = $this->_order_items->getByColumn(['order_id' => $order->getData('id'), 'type_id' => $egg_type->getData('id')], 0);
                            foreach($order_items as $order_item) {
                                $order_item_details = $this->_order_item_details->getByColumn(['order_item_id' => $order_item->getData('id')], 0);
                                foreach($order_item_details as $order_item_detail) {
                                    if ($order_item_detail->getData('type_id') == 1) {
                                        $beg_sale_count += 360 * (int)$order_item_detail->getData('qty');
                                    } else if ($order_item_detail->getData('type_id') == 2) {
                                        $beg_sale_count += 30 * (int)$order_item_detail->getData('qty');
                                    } else if ($order_item_detail->getData('type_id') == 3) {
                                        $beg_sale_count += (int)$order_item_detail->getData('qty');
                                    }
                                }
                            }
                        }
                    }


                    $this->jsonData['datas'][] = array(
                        'egg_type' => $egg_type->getData(),
                        'beginning' => $beginning ? (int)$beginning->getData('sum') - (int)$beg_sale_count : null,
                        'harvested' => $harvested ? $harvested->getData('sum') : null,
                        'sales' => $sale_count
                    );
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $data->getData();

            } else {
                $this->jsonData['message'] = "No record found";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>