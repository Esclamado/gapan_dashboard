<?php
namespace Lns\Gpn\Controller\WebApi\MedicineManagement\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_medicine;
    protected $_medicineunit;
    protected $_dailyhouseharvest;

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
        $this->_dailyhouseharvest = $Dailyhouseharvest;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;
        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $medicineId = $this->getParam('id');
            
            $medicine = $this->_medicine->getByColumn(['id' => $this->getParam('id')], 1);
            if ($medicine) {
                $canDelete = true;
                $result = $medicine->getData();
                
                /* $dailyhouseharvest = $this->_dailyhouseharvest->getCollection();
                var_dump("do this");
                die;
                if ($dailyhouseharvest) {
                    foreach ($dailyhouseharvest as $dailyhouse) {
                        $medicine_ids = explode(',', $dailyhouse->getData('medicine_ids'));

                        if (in_array($medicineId, $medicine_ids)) {
                            $canDelete = false;
                            break;
                        }
                    }
                    $result['isDeleteable'] = $canDelete;
                } */
                $canDelete = $this->_dailyhouseharvest->checkIfMedicineExists($medicineId);
                $result['isDeleteable'] = $canDelete;
                $medicineunit = $this->_medicineunit->getByColumn(['id' => $medicine->getData('unit_id')], 1);
                $result['medicine_unit'] = $medicineunit->getData();

                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No record found.';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>