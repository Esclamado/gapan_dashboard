<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Updatemedicine extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
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

            $id = $this->getParam('daily_house_report_id');
            $medicines = $this->getParam('medicines');

            $entity = $this->_dailyhouseharvest->getByColumn(['id' => $id], 1);
            if ($entity) {
                $meds = [];
                $medVal = [];
                foreach ($medicines as $med) {
                    if ($med['value'] > 0) {
                        $meds[] = $med['med_id'];
                        $medVal[] = $med['value'];
                    }
                }
                $med_ids = implode(',', $meds);
                $medv = implode(',', $medVal);
                $entity->setData('medicine_ids', $med_ids);
                $entity->setData('medicine_values', $medv);
                $entryId = $entity->__save();
                if ($entryId) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Record updated';
                }
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>