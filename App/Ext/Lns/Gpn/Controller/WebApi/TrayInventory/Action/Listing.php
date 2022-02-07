<?php
namespace Lns\Gpn\Controller\WebApi\TrayInventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_trayreport;
    protected $_trayinventoryreport;
    protected $_userprofile;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayreport = $TrayReport;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_userprofile = $UserProfile;
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

            $param = $this->getParam();
            $result = [];
            $trays = $this->_trayreport->getTrayreport($param);
            $this->jsonData = $trays;
            if($trays['datas']){
                foreach ($trays['datas'] as $key => $tray) {
                    $result[$key] = $tray->getData();
                    $trayinventoryreports = $this->_trayinventoryreport->getByColumn(['tray_report_id'=> $tray->getData('id')], 0);
                    if($trayinventoryreports){
                        $i = 0;
                        $out = 0;
                        $end = 0;
                        $return = 0;
                        $beginning = 0;
                        foreach ($trayinventoryreports as $trayinventoryreport) {
                            /* $result[$key]['tray_inventory_report'][$i] = $trayinventoryreport->getData(); */
                            $date = date('Y-m-d', strtotime($tray->getData('created_at')));
                            $lastdata = $this->_trayinventoryreport->getLastEnding($trayinventoryreport->getData('type_id'), $date);
                            /* $result[$key]['tray_inventory_report'][$i]['last_data'] = $lastdata; */
                            if($lastdata){
                                $beginning += $lastdata['total_end'];
                            }

                            $out += $trayinventoryreport->getData('sorting') + $trayinventoryreport->getData('marketing') + $trayinventoryreport->getData('out_hiram');
                            $end += $trayinventoryreport->getData('total_end');
                            $return += $trayinventoryreport->getData('in_return');
                            $i++;
                        }
                    }
                    $result[$key]['beginning'] = $beginning;
                    $result[$key]['returned'] = $return;
                    $result[$key]['number_of_out_sales'] = $out;
                    $result[$key]['total_remaining'] = $end;
                    
                    $userprofile = $this->_userprofile->getFullNameById($tray->getData('prepared_by'));
                    $result[$key]['name'] = $userprofile ? $userprofile : 'N/A' ;
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['data']['grand_total'] = $end;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No tray record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>