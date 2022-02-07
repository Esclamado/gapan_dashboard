<?php
namespace Lns\Gpn\Controller\WebApi\Temporaryfiles\Action;

class Testing extends \Lns\Sb\Controller\Controller {

    protected $_dailysortingreport;
    protected $_dailysortinginventory;
    protected $_egginventory;
    protected $_freshegginventory;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_egginventory = $EggInventory;
        $this->_freshegginventory = $FresheggInventory;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

/*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; */

/*         $record_id = $this->getParam('id');
        if ($record_id) {
            $entity = $this->_dailysortingreport->getByColumn(['id' => $record_id], 1);
            if ($entity) {
                $dailysortinginventorys = $this->_dailysortinginventory->getByColumn(['sorted_report_id' => $entity->getData('id')], 0);
                $stocks = 0;
                $waste = 0;
                $lastremainingstocks = 0;
                foreach ($dailysortinginventorys as $dailysortinginventory) {
                    $save = $this->_egginventory;
                    $save->setData('type_id', $dailysortinginventory->getData('type_id'));
                    $save->setData('house_id', $entity->getData('house_id'));
                    $save->setData('egg_count', $dailysortinginventory->getData('egg_count'));
                    $egginventoryId = $save->__save();

                    $stocks += $dailysortinginventory->getData('egg_count');
                    if($dailysortinginventory->getData('type_id')==13){
                        $waste += $dailysortinginventory->getData('egg_count');
                    }
                }
                if ($egginventoryId) {
                    $date = date('Y-m-d');
                    $freshegginventory = $this->_freshegginventory->getRecordtoday($date);
                    if($freshegginventory){
                        $update = $this->_freshegginventory->getByColumn(['id'=> $freshegginventory->getData('id')], 1);
                        $waste += $update->getData('waste_sales');
                        $stocks += $update->getData('total_harvested');
                    }else{
                        $update = $this->_freshegginventory;
                    }
                        $lastending = $this->_freshegginventory->getLastEnding($date);
                        if($lastending){
                            $lastremainingstocks = $lastending->getData('total_remaining_stocks');
                            $lastremainingstocks ? $lastremainingstocks : 0;
                        }
                        $remaining = $lastremainingstocks + $stocks - $waste;

                        $update->setData('beginning_stocks', $lastremainingstocks);
                        $update->setData('total_harvested', $stocks);
                        $update->setData('waste_sales', $waste);
                        $update->setData('total_remaining_stocks', $remaining);
                        $fresheggId = $update->__save();

                        if($fresheggId){
                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = 'saved';
                        }
                }
            }
        } */


        





        /* } */
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>