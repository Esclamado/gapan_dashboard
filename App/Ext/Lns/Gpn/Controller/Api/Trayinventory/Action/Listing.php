<?php
namespace Lns\Gpn\Controller\Api\Trayinventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_trayreport;
    protected $_traytypes;
    protected $_trayinventoryreport;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayreport = $TrayReport;
        $this->_traytypes = $Traytypes;
        $this->_trayinventoryreport = $Trayinventoryreport;
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

            $user_id = $payload['payload']['jti'];
                
            $param = $this->getParam();

            $trayReports = $this->_trayreport->getList($param);

            if($trayReports['datas']){

                $this->jsonData = $trayReports;

                $z = 0;
                $result = [];
                foreach ($trayReports['datas'] as $trayReport) {
                    $result[$z] = $trayReport->getData();

                    $trayinventoryreports = $this->_trayinventoryreport->getByColumn(['tray_report_id'=>$trayReport->getData('id')], 0);
                    if($trayinventoryreports){
                        $x = 0;
                        foreach ($trayinventoryreports as $trayinventoryreport) {
                            $result[$z]['tray_inventory_report'][$x] = $trayinventoryreport->getData();

                            $trayTypes = $this->_traytypes->getByColumn(['id' => $trayinventoryreport->getData('type_id')], 0);
                            if($trayTypes){
                                
                                foreach ($trayTypes as $trayType) {
                                    $result[$z]['tray_inventory_report'][$x]['type'] = $trayType->getData();
                                }
                            }
                            $x++;
                        }
                    }
                    $z++;
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['datas'] = $result;

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