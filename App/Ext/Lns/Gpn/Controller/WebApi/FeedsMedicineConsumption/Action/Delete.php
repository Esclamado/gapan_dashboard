<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Delete extends \Lns\Sb\Controller\Controller {
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
            $house_id = $this->getParam('house_id');
            $date_today = $this->getParam('date_today');

            $hasRecord = $this->_dailyhouseharvest->validateByHouse($house_id, $date_today, true);
            if (!$hasRecord) {
                $entities = $this->_dailyhouseharvest->getRecordByMonth($date_today, $house_id);
                if ($entities) {
                    foreach ($entities as $entity) {
                        $entity->delete();
                        /* var_dump($entity->getData()); */
                    }
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = 'Monthly record removed';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>