<?php
namespace Lns\Gpn\Controller\WebApi\TrayInventory\Action;

class View extends \Lns\Sb\Controller\Controller {

    protected $_trayreport;
    protected $_trayinventoryreport;
    protected $_userprofile;
    protected $_traytypes;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayreport = $TrayReport;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_userprofile = $UserProfile;
        $this->_traytypes = $Traytypes;
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

            $trayreportId = $this->getParam('id');
            $trayreport = $this->_trayreport->getByColumn(['id'=> $trayreportId], 1);
            if($trayreport){
                $this->jsonData['data'] = $trayreport->getData();
                $user = $this->_userprofile->getFullNameById($trayreport->getData('prepared_by'));
                if($user){
                    $this->jsonData['data']['name'] = $user;
                }
                $trayinventorys = $this->_trayinventoryreport->getByColumn(['tray_report_id'=> $trayreport->getData('id')], 0);
                if($trayinventorys){
                    $beginning = 0;
                    $out = 0;
                    $end = 0;
                    $return = 0;
                    foreach ($trayinventorys as $key => $trayinventory) {
                        $this->jsonData['data']['tray_inventory_report'][] = $trayinventory->getData();
                        $traytypes = $this->_traytypes->getByColumn(['id'=> $trayinventory->getData('type_id')], 1);
                        if($traytypes){
                            $this->jsonData['data']['tray_inventory_report'][$key]['tray_type'] = $traytypes->getData();
                        }
                        $date = date('Y-m-d', strtotime($trayreport->getData('created_at')));
                        $lastdata = $this->_trayinventoryreport->getLastEnding($trayinventory->getData('type_id'), $date);
                        if($lastdata){
                            $beginning += $lastdata['total_end'];
                        }
                        $this->jsonData['data']['tray_inventory_report'][$key]['last_data'] = $this->_trayinventoryreport->getLastEnding($trayinventory->getData('type_id'), $date);
                        $out += $trayinventory->getData('sorting') + $trayinventory->getData('marketing') + $trayinventory->getData('out_hiram');
                        $end += $trayinventory->getData('total_end');
                        $return += $trayinventory->getData('in_return');
                    }
                    $this->jsonData['data']['beginning_stock'] = $beginning;
                    $this->jsonData['data']['returned'] = $return;
                    $this->jsonData['data']['number_of_out_sales'] = $out;
                    $this->jsonData['data']['total_remaining'] = $end;
                }
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No tray report found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>