<?php
namespace Lns\Gpn\Controller\Api\Inflow\Action;

class Listing extends \Lns\Sb\Controller\Controller {

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
        \Lns\Gpn\Lib\Entity\Db\House $House
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
            $this->jsonData['error'] = 1;

            $param = $this->getParam();

            $inflows = $this->_inflow->getInflow($param);

            if($inflows['datas']){

                $this->jsonData = $inflows;

                $i = 0;
                $details = '';
                foreach ($inflows['datas'] as $inflow) {
                    $this->jsonData['datas'][$i] = $inflow->getData();

                    switch($inflow->getData('type')){
                        case 1: $details = $this->_dailysortingreport->getByColumn(['id'=> $inflow->getData('reference_id')], 1);
                                $getHouse = $this->_house->getHouseType($details->getData('house_id'));
                                $this->jsonData['datas'][$i]['report'] = $details->getData();
                                if($getHouse){
                                    foreach ($getHouse as $House) {
                                        $this->jsonData['datas'][$i]['report']['house_name'] = $House->getData();
                                    }
                                } 
                        break;
                        case 2: $details = $this->_trayreport->getByColumn(['id'=> $inflow->getData('reference_id')], 1);
                                $this->jsonData['datas'][$i]['report'] = $details->getData();
                        break;
                        case 3: $details = $this->_sackinventory->getByColumn(['id'=> $inflow->getData('reference_id')], 1);
                                $this->jsonData['datas'][$i]['report'] = $details->getData();
                        break;
                    }
                $i++;
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
?>
