<?php
namespace Lns\Gpn\Controller\Api\Dailyhouseharvest\Action;

class Add extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;
    protected $_dailyHouseHarvest;
    protected $_feeds;
    protected $_medicine;
    protected $_medicine_unit;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_feeds = $Feeds;
        $this->_medicine = $Medicine;
        $this->_medicine_unit = $MedicineUnit;
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

            $hasRecord = $this->_dailyHouseHarvest->hasRecord($this->getParam('house_id'), NULL, date('Y-m-d'));
            if ($hasRecord) {
                /* if ($hasRecord->getData('medicine_ids') && $hasRecord->getData('feed_consumption') != 0.00) { */
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $hasRecord->getData();
                    if ($hasRecord->getData('medicine_ids')) {
                        $medicine_ids = explode(',', $hasRecord->getData('medicine_ids'));
                        $medicine_values = explode(',', $hasRecord->getData('medicine_values'));
                        $i = 0;
                        foreach ($medicine_ids as $medicine_id) {
                            $med_data = $this->_medicine->getMedicine($medicine_id);
                            $this->jsonData['data']['medicine'][$i] = $med_data;
                            $this->jsonData['data']['medicine'][$i]['value'] = $medicine_values[$i];
                            $unit_data = $this->_medicine_unit->getByColumn(['id' => $med_data['unit_id']], 1);
                            $this->jsonData['data']['medicine'][$i]['unit'] = $unit_data ? $unit_data->getData() : NULL;
                            $i++;
                        }
                    }
                    $feedBags = $this->_feeds->convertGramsToBags($hasRecord);
                    $rec_feeds = $this->_feeds->convertGramsToBags($hasRecord, true);
                    $this->jsonData['data']['feeds'] = $feedBags;
                    $this->jsonData['data']['rec_feeds'] = $rec_feeds;
                    
                    if($hasRecord->getData('checked_by')){
                        $this->jsonData['data']['cull'] = 0;
                        $this->jsonData['data']['mortality'] = 0;
                        $this->jsonData['data']['egg_count'] = 0;
                        $this->jsonData['data']['real_egg_count'] = 0;
                    }

               /*  } else {
                    $this->jsonData['message'] = 'The record for today is not yet set. Please contact your Manager.';
                } */
            } else {
                $this->jsonData['message'] = 'The record for today is not yet set. Please contact your Manager.';
            }
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}
?>