<?php
namespace Lns\Gpn\Controller\WebApi\MedicineConsumption\Action;

class MedicineConsumption extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_medicine;
    protected $_medicineunit;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_medicine = $Medicine;
        $this->_medicineunit = $MedicineUnit;
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

            $medicines = $this->_medicine->getLatestMedicines();
            $i = 0;
            if($medicines){
                foreach ($medicines as $medicine) {
                    $result[$i] = $medicine->getData();
                    $medicineunits = $this->_medicineunit->getByColumn(['id'=> $medicine->getData('unit_id')], 0);
                    if($medicineunits){
                        foreach ($medicineunits as $medicineunit) {
                            $result[$i]['unit'] = $medicineunit->getData();
                        }
                    }
                    $dailyhouseharvests = $this->_dailyhouseharvest->getMedicineConsumption($param, $medicine->getData('id'));
                    $result[$i]['consumed_medicine'] = $dailyhouseharvests ? $dailyhouseharvests : 0 ;
                    $i++;
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $result;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>