<?php
namespace Lns\Gpn\Controller\Api\Inflow\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_inflow;
    protected $_dailysortingreport;
    protected $_trayreport;
    protected $_sackinventory;
    protected $_dailysortinginventory;
    protected $_eggtype;
    protected $_trayinventoryreport;
    protected $_traytype;
    protected $_sackbldginventory;
    protected $_house;

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
        \Lns\Gpn\Lib\Entity\Db\Inflow $Inflow,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_inflow = $Inflow;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_trayreport = $TrayReport;
        $this->_sackinventory = $Sackinventory;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_eggtype = $Eggtype;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_traytype = $Traytypes;
        $this->_sackbldginventory = $Sackbldginventory;
        $this->_house = $House;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
    }

     public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; 

        $hasRecord = $this->_inflow->getByColumn(['type'=> $this->getParam('type'), 'reference_id'=> $this->getParam('reference_id')], 1);
        if($hasRecord){
            $details = '';
            $i = 0;
            switch ($this->getParam('type')) {
                case 1:
                    $details = $this->_dailysortingreport->getByColumn(['id' => $this->getParam('reference_id')], 1);
                    $sortedReportData = $this->_dailysortinginventory->getDailySortingInventory($details->getData('id'));
                    $getHouse = $this->_house->getHouse($details->getData('house_id'));
                    $this->jsonData['data'] = $details->getData();
                    if($sortedReportData){
                        foreach ($sortedReportData as $sortedReport) {
                            $this->jsonData['data']['eggs'][] = $sortedReport->getData();
                            $getEggType = $this->_eggtype->getByColumn(['id' => $sortedReport->getData('type_id')], 0);
                            foreach ($getEggType as $getEgg) {
                                $this->jsonData['data']['eggs'][$i]['egg_type'] = $getEgg->getData();
                                $i++;
                            }
                        }
                            $this->jsonData['data']['house_name'] = $getHouse;
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

                    if($trayinventoryreport){
                        $date = date("Y-m-d", strtotime($details->getData('created_at')));
                        $a = 0;
                        foreach ($trayinventoryreport as $trayinventory) {
                            $this->jsonData['data']['tray_inventory_report'][$a] = $trayinventory->getData();
                                /* $this->jsonData['data']['tray_inventory_report'][$a]['last_data'] = $this->_trayreport->getLastEnding($date); */
                                $this->jsonData['data']['tray_inventory_report'][$a]['total_end'] = (int)$trayinventory->getData('total_end')+ (int)$trayinventory->getData('sorting') + (int)$trayinventory->getData('marketing') + (int) $trayinventory->getData('out_hiram');
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
        }else{
            $this->jsonData['error'] = 1;
            $this->jsonData['message'] = 'No Record Found';
        }
        } 
        $this->jsonEncode($this->jsonData);
        die;
    }
}
