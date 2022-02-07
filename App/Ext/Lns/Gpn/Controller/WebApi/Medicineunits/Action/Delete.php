<?php
namespace Lns\Gpn\Controller\WebApi\Medicineunits\Action;

class Delete extends \Lns\Sb\Controller\Controller {

    protected $_medicineunit;
    protected $_medicine;

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

            $id = $this->getParam('id');
            $entity = $this->_medicine->getByColumn(['unit_id' => $id], 1);
            if ($entity) {
                $this->jsonData['message'] = 'Cannot delete medicine unit';
            } else {
                $entity = $this->_medicineunit->getByColumn(['id' => $id], 1);
                $delete = $entity->delete();
                if ($delete) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Medicine unit deleted';
                } else {
                    $this->jsonData['message'] = 'Cannot delete medicine unit';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>