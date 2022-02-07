<?php

namespace Lns\Gpn\Controller\Api\Outflow\Index;

class Index extends \Lns\Sb\Controller\Controller
{

    protected $_outflow;
    protected $_dailysortingreport;
    protected $_trayreport;
    protected $_sackinventory;
    protected $_dailysortinginventory;
    protected $_eggtype;
    protected $_trayinventoryreport;
    protected $_traytype;
    protected $_sackbldginventory;
    protected $_house;

    protected $_orders;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_eggcarttype;

    protected $_userProfile;
    protected $_users;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Outflow $Outflow,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_outflow = $Outflow;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_trayreport = $TrayReport;
        $this->_sackinventory = $Sackinventory;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_eggtype = $Eggtype;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_traytype = $Traytypes;
        $this->_sackbldginventory = $Sackbldginventory;
        $this->_house = $House;
        $this->_orders = $Orders;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->_eggcarttype = $EggCartType;
    }

    public function run()
    {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {

            $userId = $payload['payload']['jti'];
            $this->jsonData['error'] = 1;

            $hasRecord = $this->_outflow->getByColumn(['type' => $this->getParam('type'), 'reference_id' => $this->getParam('reference_id')], 1);
            if ($hasRecord) {
                $details = '';
                $i = 0;
                switch ($this->getParam('type')) {
                    case 1:
                        /* $details = $this->_order->getByColumn(['id' => $this->getParam('reference_id')], 1);
                        $getOrderDetails = $this->_orderdetails->getByColumn(['order_id' => $details->getData('id')], 0);
                        $this->jsonData['data'] = $details->getData();
                        $egg_count = 0;
                        foreach ($getOrderDetails as $getOrderDetail) {
                            $egg_count += (int)$getOrderDetail->getData('qty');
                            $this->jsonData['data']['eggs'][] = $getOrderDetail->getData();
                            $getEggType = $this->_eggtype->getByColumn(['id' => $getOrderDetail->getData('type_id')], 0);
                            foreach ($getEggType as $getEgg) {
                                $this->jsonData['data']['eggs'][$i]['egg_type'] = $getEgg->getData();
                                $i++;
                            }
                        }
                        $this->jsonData['data']['egg_count'] = $egg_count; */
                        $details = $this->_orders->getByColumn(['id' => $this->getParam('reference_id')], 1);
                        $this->jsonData['data'] = $details->getData();

                        if ($details->getData('prepared_by')) {
                            $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($details->getData('prepared_by'));
                            $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($details->getData('prepared_by'));
                            $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                                'vendor' => 'Lns',
                                'module' => 'Gpn',
                                'path' => '/images/uploads/signature/warehouseman/' . $details->getData('prepared_by'),
                                'filename' => $details->getData('prepared_by_path')
                            ]);
                        }
        
                        if ($details->getData('checked_by')) {
                            $this->jsonData['data']['checked_by_name'] = $this->_userProfile->getFullNameById($details->getData('checked_by'));
                            $this->jsonData['data']['checked_by_role'] = $this->_users->getRoleByUserId($details->getData('checked_by'));
                            $this->jsonData['data']['checked_by_path'] = $this->getImageUrl([
                                'vendor' => 'Lns',
                                'module' => 'Gpn',
                                'path' => '/images/uploads/signature/inspector/' . $details->getData('checked_by'),
                                'filename' => $details->getData('checked_by_path')
                            ]);
                        }

                        $orderitems = $this->_orderitems->getByColumn(['order_id'=> $details->getData('id')], 0);
                        if ($orderitems) {
                            $x = 0;
                            $total_items = 0;
                            foreach ($orderitems as $orderitem) {
                                $this->jsonData['data']['order_items'][] = $orderitem->getData();

                                $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                                if ($eggtypes) {
                                    foreach ($eggtypes as $eggtype) {
                                        $this->jsonData['data']['order_items'][$x]['egg_type'] = $eggtype->getData();
                                    }
                                }

                                $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id'=> $orderitem->getData('id')], 0);
                                if($orderitemdetails){
                                    $y = 0;
                                    $pieces = 0;
                                    foreach ($orderitemdetails as $orderitemdetail) {
                                        /* $this->jsonData['data']['order_items'][$x]['order_item_details'][$y] = $orderitemdetail->getData(); */
                                        $this->jsonData['data']['order_items'][$x]['cart_details'][$y] = $orderitemdetail->getData();

                                        $this->jsonData['data']['order_items'][$x]['egg_price'] = $orderitemdetail->getData('price');

                                        switch($orderitemdetail->getData('type_id')){
                                            case 1:
                                                $pieces += 360 * (int)$orderitemdetail->getData('qty');
                                            break;
                                            case 2:
                                                $pieces += 30 * (int)$orderitemdetail->getData('qty');
                                            break;
                                            default:
                                                $pieces += (int)$orderitemdetail->getData('qty');
                                            break;
                                        }
                                        $eggcarttypes = $this->_eggcarttype->getByColumn(['id'=> $orderitemdetail->getData('type_id')], 0);
                                        if($eggcarttypes){
                                            foreach ($eggcarttypes as $eggcarttype) {
                                                $this->jsonData['data']['order_items'][$x]['total_pieces'] = $pieces;
                                                $this->jsonData['data']['order_items'][$x]['total_price'] = $pieces * $orderitemdetail->getData('price');
                                            }
                                        }
                                        $y++;
                                    }
                                    $total_items += $pieces;
                                    $this->jsonData['data']['total_pieces'] = $total_items;
                                }
                                $x++;
                            }
                        }
                        break;  
                    case 2:
                        $details = $this->_trayreport->getByColumn(['id' => $this->getParam('reference_id')], 1);
                        $trayinventoryreport = $this->_trayinventoryreport->getTrayInventoryReport($details->getData('id'));
                        $this->jsonData['data'] = $details->getData();
                        $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($details->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($details->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/warehouseman/' . $details->getData('prepared_by'),
                            'filename' => $details->getData('prepared_by_path')
                        ]);
                        if ($trayinventoryreport) {
                            $date = date("Y-m-d", strtotime($details->getData('created_at')));
                            $a = 0;
                            foreach ($trayinventoryreport as $trayinventory) {
                                $this->jsonData['data']['tray_inventory_report'][$a] = $trayinventory->getData();
                                /* $this->jsonData['data']['tray_inventory_report'][$a]['total_end'] = (int) $trayinventory->getData('total_end') + (int) $trayinventory->getData('sorting') + (int) $trayinventory->getData('marketing') + (int) $trayinventory->getData('out_hiram'); */
                                $this->jsonData['data']['tray_inventory_report'][$a]['last_data'] = $this->_trayinventoryreport->getLastEnding($trayinventory->getData('type_id'), $date);
                                $getTrayType = $this->_traytype->getByColumn(['id' => $trayinventory->getData('type_id')], 0);
                                foreach ($getTrayType as $getTray) {
                                    $this->jsonData['data']['tray_inventory_report'][$i]['tray_type'] = $getTray->getData();
                                    $i++;
                                }
                                $a++;
                            }
                        }
                    break;
                    case 3:
                        /* $details = $this->_sackinventory->getByColumn(['id' => $this->getParam('reference_id')], 1);
                        $this->jsonData['data'][$i]['report'] = $details->getData();
                        break; */
                        $details = $this->_sackinventory->getByColumn(['id' => $this->getParam('reference_id')], 1);
                        $sackinventory = $this->_sackbldginventory->getSackInventory($details->getData('id'));
                        $this->jsonData['data'] = $details->getData();
                        $date = date("Y-m-d", strtotime($details->getData('created_at')));
                        $last_data = $this->_sackinventory->getLastEnding($date);
                        $this->jsonData['data']['last_data'] = $last_data;
                        if ($last_data) {
                            $this->jsonData['data']['total_in'] = (int)$details->getData('total_in') + (int)$last_data['last_ending'];
                        }
                        $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($details->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($details->getData('prepared_by'));
                        $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/warehouseman/' . $details->getData('prepared_by'),
                            'filename' => $details->getData('prepared_by_path')
                        ]);
                        $i = 0;
                        foreach ($sackinventory as $sack) {
                            $this->jsonData['data']['sack_bldg_inventory'][$i] = $sack->getData();
                            $this->jsonData['data']['sack_bldg_inventory'][$i]['house'] = $this->_house->getHouse($sack->getData('house_id'));
                            $i++;
                        }
                    break;
                }
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = 'No Record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
