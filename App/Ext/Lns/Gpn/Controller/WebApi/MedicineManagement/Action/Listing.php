<?php
namespace Lns\Gpn\Controller\WebApi\MedicineManagement\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_medicine;
    protected $_medicineunit;
    protected $_daily_house_harvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_medicine = $Medicine;
        $this->_medicineunit = $MedicineUnit;
        $this->_daily_house_harvest = $Dailyhouseharvest;
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

            $medicines = $this->_medicine->getMedicinelist($param);
            $this->jsonData = $medicines;
            if($medicines['datas']){
                $i = 0;
                foreach ($medicines['datas'] as $medicine) {
                    $result[$i] = $medicine->getData();
                    $medicineunit = $this->_medicineunit->getbyColumn(['id'=> $medicine->getData('unit_id')], 1);
                    $result[$i]['medicine_unit'] = $medicineunit->getData();
                    $consumed = $this->_daily_house_harvest->getTotalConsumedMedicine($medicine->getData('id'));
                    $result[$i]['consumed'] = $consumed;
                    $result[$i]['remaining'] = ((float)$medicine->getData('net_weight') * (float)$medicine->getData('pieces')) - $consumed;
                    $i++;
                }
            $this->jsonData['datas'] = $result;
            $count = $this->_medicine->getCount();
            $this->jsonData['data']['type_of_medicines'] = $count ? $count : 0 ;
            $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>