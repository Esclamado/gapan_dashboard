<?php
namespace Lns\Gpn\Controller\Api\Trayinventory\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;

    protected $_trayreport;
    protected $_traytypes;
    protected $_trayinventoryreport;

    protected $_userProfile;
    protected $_users;
    protected $_house;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayreport = $TrayReport;
        $this->_traytypes = $Traytypes;
        $this->_trayinventoryreport = $Trayinventoryreport;
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
            $id = $this->getParam('id');
            
            $trayReport = $this->_trayreport->getByColumn(['id' => $id], 1);

            if ($trayReport) {
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $trayReport->getData();

                $this->jsonData['data']['prepared_by_name'] = $this->_userProfile->getFullNameById($trayReport->getData('prepared_by'));
                $this->jsonData['data']['prepared_by_role'] = $this->_users->getRoleByUserId($trayReport->getData('prepared_by'));
                $this->jsonData['data']['prepared_by_path'] = $this->getImageUrl([
                    'vendor' => 'Lns',
                    'module' => 'Gpn',
                    'path' => '/images/uploads/signature/warehouseman/' . $trayReport->getData('prepared_by'),
                    'filename' => $trayReport->getData('prepared_by_path')
                ]);

                $trayinventoryreports = $this->_trayinventoryreport->getByColumn(['tray_report_id'=>$trayReport->getData('id')], 0);
                if($trayinventoryreports){
                    $i = 0;
                    foreach ($trayinventoryreports as $trayinventoryreport) {
                        $this->jsonData['data']['tray_inventory_report'][$i] = $trayinventoryreport->getData();
                        /*                         $this->jsonData['data']['tray_inventory_report'][$i]['last_data'] = $this->_trayinventoryreport->getLastDataByType($trayinventoryreport->getData('type_id'), 2); */
                        $date = date('Y-m-d', strtotime($trayReport->getData('created_at')));
                        $this->jsonData['data']['tray_inventory_report'][$i]['last_data'] = $this->_trayinventoryreport->getLastEnding($trayinventoryreport->getData('type_id'), $date);
                        $trayType = $this->_traytypes->getByColumn(['id' => $trayinventoryreport->getData('type_id')], 1);
                        if ($trayType) {
                            $this->jsonData['data']['tray_inventory_report'][$i]['type'] = $trayType->getData();
                        }
                        $i++;
                    }
                }
            } else {
                $this->jsonData['message'] = 'Record not found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}

?>