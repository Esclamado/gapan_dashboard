<?php
namespace Lns\Gpn\Controller\WebApi\MedicineManagement\Action;

class Delete extends \Lns\Sb\Controller\Controller {

    protected $_medicine;
    protected $_dailyhouseharvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_medicine = $Medicine;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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

            $medicineId = $this->getParam('id');
            $canDelete = true;
            $medicine = $this->_medicine->getByColumn(['id'=> $medicineId], 1);
            if($medicine){
                /* $dailyhouseharvest = $this->_dailyhouseharvest->getCollection();
                foreach ($dailyhouseharvest as $dailyhouse) {
                    $medicine_ids = explode(',', $dailyhouse->getData('medicine_ids'));
                    
                    if(in_array($medicineId, $medicine_ids)){
                        $canDelete = false;
                        break;
                    }
                }
                if ($canDelete) {
                    $medicine->delete();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'A medicine has been deleted';
                } else {
                    $this->jsonData['message'] = 'Medicine can not be deleted';
                } */
                
                $dailyhouseharvest = $this->_dailyhouseharvest->checkIfMedicineExists($medicineId);
                if ($dailyhouseharvest) {
                    $medicine->delete();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'A medicine has been deleted';
                } else {
                    $this->jsonData['message'] = 'Medicine can not be deleted';
                }
            } else {
                $this->jsonData['message'] = 'No medicine record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>