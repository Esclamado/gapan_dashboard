<?php
namespace Lns\Gpn\Controller\Api\Outflow\Action;

class Listing extends \Lns\Sb\Controller\Controller {

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
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders
    ){
    parent::__construct($Url,$Message,$Session);
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

            $outflows = $this->_outflow->getOutflow($param);

            if($outflows['datas']){

                $this->jsonData = $outflows;

                $i = 0;
                $details = '';
                foreach ($outflows['datas'] as $outflow) {
                    $this->jsonData['datas'][$i] = $outflow->getData();

                    switch($outflow->getData('type')){
                        case 1: $details = $this->_orders->getByColumn(['id' => $outflow->getData('reference_id'), 'order_status' => 4], 1);
                                $this->jsonData['datas'][$i]['report'] = $details->getData();
                        break;
                        case 2: $details = $this->_trayreport->getByColumn(['id'=> $outflow->getData('reference_id')], 1);
                                $this->jsonData['datas'][$i]['report'] = $details->getData();
                        break;
                        case 3: $details = $this->_sackinventory->getByColumn(['id'=> $outflow->getData('reference_id')], 1);
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
