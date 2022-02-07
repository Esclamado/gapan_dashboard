<?php
namespace Lns\Gpn\Controller\WebApi\MedicineManagement\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_medicine;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_medicine = $Medicine;
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

            $medicine = $this->getParam('medicine');
            $unit_id = $this->getParam('unit_id');
            $net_weight = $this->getParam('net_weight');
            $pieces = $this->getParam('pieces');
            $delivery_date = $this->getParam('delivery_date');
            $expiration_date = $this->getParam('expiration_date');
            $unit_price = $this->getParam('unit_price');
            $remarks = $this->getParam('remarks');

            $medicineId = $this->getParam('id');

            if($medicineId){
                $med = $this->_medicine->getByColumn(['id'=>$medicineId], 1);
                $message = 'Medicine has been updated';
            }else{
                $med = $this->_medicine;
                $message = 'A new medicine has been saved';
            }
                $med->setData('medicine', $medicine);
                $med->setData('unit_id', $unit_id);
                $med->setData('net_weight', $net_weight);
                $med->setData('pieces', $pieces);
                $med->setData('delivery_date', date("Y-m-d", strtotime($delivery_date)));
                $med->setData('expiration_date', date("Y-m-d", strtotime($expiration_date)));
                $med->setData('unit_price', $unit_price);
                $med->setData('remarks', $remarks);
                $medId = $med->__save(); 

            if($medId){
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = $message;
            }
            
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>