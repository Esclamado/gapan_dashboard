<?php
namespace Lns\Gpn\Controller\WebApi\MedicineManagement\Action;

class Getall extends \Lns\Sb\Controller\Controller {

    protected $_medicine;
    protected $_medicineunit;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
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

            $medicines = $this->_medicine->getCollection();
            if($medicines){
                foreach ($medicines as $key => $medicine) {
                    $result[$key] = $medicine->getData();
                    $medicineunits = $this->_medicineunit->getbyColumn(['id'=> $medicine->getData('unit_id')], 0);
                    if($medicineunits){
                        foreach ($medicineunits as $medicineunit) {
                            $result[$key]['medicine_unit'] = $medicineunit->getData();
                        }
                    }
                }
            $this->jsonData['data'] = $result;
            $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>