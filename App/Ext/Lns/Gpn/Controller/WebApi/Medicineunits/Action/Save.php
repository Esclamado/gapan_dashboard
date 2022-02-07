<?php
namespace Lns\Gpn\Controller\WebApi\Medicineunits\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_medicineunit;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
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

            $unit = $this->getParam('unit');
            $entity = $this->_medicineunit->getByColumn(['unit' => $unit], 1);
            if ($entity) {
                $this->jsonData['message'] = 'Medicine unit already exists';
            } else {
                $entity = $this->_medicineunit;
                $entity->setData('unit', $unit);
                $save = $entity->__save();
                if ($save) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Medicine unit saved';
                    $this->jsonData['data'] = $save;
                } else {
                    $this->jsonData['message'] = 'Failed to save medicine unit';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>