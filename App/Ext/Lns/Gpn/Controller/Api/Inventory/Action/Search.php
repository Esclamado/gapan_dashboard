<?php
namespace Lns\Gpn\Controller\Api\Inventory\Action;

class Search extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_egginventory;
    protected $_dailyHouseHarvest;
    protected $_sackInventory;
    protected $_trayinventoryreport;
    protected $_otherinventory;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Notifications $Notifications,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport,
        \Lns\Gpn\Lib\Entity\Db\OtherInventory $OtherInventory
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_notification = $Notifications;
        $this->token = $Validate;
        $this->_eggtype = $Eggtype;
        $this->_egginventory = $EggInventory;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_sackInventory = $Sackinventory;
        $this->_trayinventoryreport = $Trayinventoryreport;
        $this->_otherinventory = $OtherInventory;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        $userId = $payload['payload']['jti'];

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $date = date('Y-m-d');

            $param = $this->getParam();
            if($param){
                $eggInfo = $this->_eggtype->searchEgg($param);
                $otherInfo = $this->_otherinventory->searchOtherInventory($param);
            }else{
                $eggInfo = $this->_eggtype->getCollection();
                $otherInfo = $this->_otherinventory->getCollection();
            }
            if ($eggInfo) {
                $i = 0;
                foreach ($eggInfo as $egg) {
                    $this->jsonData['data']['egg_inventory'][$i] = $egg->getData();
                    $eggCounts = $this->_egginventory->getEachCount($egg->getData('id'));
                    $this->jsonData['data']['egg_inventory'][$i]['egg_count'] = $eggCounts;
                    $i++;
                }
            }
            if ($otherInfo) {
                $x = 0;
                foreach ($otherInfo as $info) {
                    $this->jsonData['data']['other_inventory'][] = $info->getData();

                    switch ($info->getData('id')) {
                        case 1:
                            $cullInfo = $this->_dailyHouseHarvest->getTotalCull();
                            $this->jsonData['data']['other_inventory'][$x]['count'] = $cullInfo;
                            break;
                        case 2:
                            $sackInfo = $this->_sackInventory->getLastEnding($date);
                            $count = 0;
                            if ($sackInfo) {
                                $count = $sackInfo['last_ending'];
                            }
                            $this->jsonData['data']['other_inventory'][$x]['count'] = $count;
                            break;
                        case 3:
                            $cartonTrayInfo = $this->_trayinventoryreport->getLastEnding(1, $date);
                            $count = 0;
                            if ($cartonTrayInfo) {
                                $count = $cartonTrayInfo['total_end'];
                            }
                            $this->jsonData['data']['other_inventory'][$x]['count'] = $count;
                            break;
                        case 4:
                            $plasticTrayInfo = $this->_trayinventoryreport->getLastEnding(2, $date);
                            $count = 0;
                            if ($plasticTrayInfo) {
                                $count = $plasticTrayInfo['total_end'];
                            }
                            $this->jsonData['data']['other_inventory'][$x]['count'] = $count;
                            break;
                    }
                    $x++;
                }
            }
            $eggCount = $this->_egginventory->getTotalCount();
            $this->jsonData['data']['total_egg_count'] = $eggCount;
            $this->jsonData['error'] = 0;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }

}
