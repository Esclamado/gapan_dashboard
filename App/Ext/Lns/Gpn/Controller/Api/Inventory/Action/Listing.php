<?php
namespace Lns\Gpn\Controller\Api\Inventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_egginventory;
    protected $_eggtype;
    protected $_dailyHouseHarvest;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype
    ){
    parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_egginventory = $EggInventory;
        $this->_eggtype = $Eggtype;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
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

            $eggtypes = $this->_eggtype->getCollection();
            if($eggtypes){
                $i = 0;
                $total = 0;
                $this->jsonData['error'] = 0;
                foreach ($eggtypes as $eggtype) {
                    $eggCount = $this->_egginventory->getCount($eggtype->getData('id'));
                    $total += (int)$eggCount;
                    $this->jsonData['datas']['egg_inventory']['eggs'][$i] = $eggtype->getData();
                    $this->jsonData['datas']['egg_inventory']['eggs'][$i]['count'] = $eggCount;
                    $i++;
                }
                $totalCull = 0;
                $cull = $this->_dailyHouseHarvest->getCull();
                $totalCull += (int)$cull;

                $totalMortality = 0;
                $mortality = $this->_dailyHouseHarvest->getMortality();
                $totalMortality += (int)$mortality;
                
                $this->jsonData['datas']['egg_inventory']['total_count'] = $total;
                $this->jsonData['datas']['cull'] = $totalCull;
                $this->jsonData['datas']['mortality'] = $totalMortality;
            } else {
                $this->jsonData['message'] = 'No eggs found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>