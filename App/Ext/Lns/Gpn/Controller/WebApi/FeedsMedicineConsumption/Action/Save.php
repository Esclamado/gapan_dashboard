<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Action;

class Save extends \Lns\Sb\Controller\Controller {

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

            $info = $this->getParam('info');
            $medicines = $this->getParam('medicines');

            $house_id = $info['house_id'];
            $bird_count = $info['bird_count'];
            $age_week = $info['age_week'];
            $age_day = $info['age_day'];
            
            $entry_count = 1;

            $birdCount = 0;
            $y = date('Y');
            $m = date('m');
            $d = 1;
            $date = $y . "-" . $m  . "-" . 1;
            $getHouse = $this->_dailyhouseharvest->getByColumn(['house_id'=>$house_id], 1);
            if($getHouse){
                $birdCount = $this->_dailyhouseharvest->updateBirdcount($house_id, $date);
            }
            $difference = $bird_count - $birdCount;


            foreach ($medicines as $medicine) {
                $meds = [];
                $medVal = [];
                foreach ($medicine['med'] as $med) {
                    if ($med['value']>0) {
                        $meds[] = $med['med_id'];
                        $medVal[] = $med['value'];
                    }
                }
                $med_ids = implode(',', $meds);
                $medv = implode(',', $medVal);
                $isNewRecord = false;
                $entity = $this->_dailyhouseharvest->getRecordByDateHouse($medicine['day'], $house_id);
                /* var_dump($entity->getData());die; */
                if (!$entity) {
                    $entity = $this->_dailyhouseharvest;
                    $isNewRecord = true;
                }
                
                if ($age_day > 6) {
                    $age_week = $age_week + 1;
                    $age_day = 0;
                }
                
                $rec_feed_consumption = 81;
                if($age_week == 20){
                    $rec_feed_consumption = 86;
                }else if($age_week == 21){
                    $rec_feed_consumption = 91;
                } else if ($age_week == 22) {
                    $rec_feed_consumption = 96;
                } else if ($age_week == 23) {
                    $rec_feed_consumption = 101;
                } else if ($age_week == 24) {
                    $rec_feed_consumption = 104;
                } else if ($age_week == 25) {
                    $rec_feed_consumption = 106;
                } else if ($age_week == 26) {
                    $rec_feed_consumption = 107;
                } else if ($age_week >= 27 && $age_week <= 34) {
                    $rec_feed_consumption = 108;
                } else if ($age_week >= 35) {
                    $rec_feed_consumption = 109;
                }
                if ($isNewRecord) {
                    /* $entity->setData('age_week', $age_week);
                    $entity->setData('age_day', $age_day); */
                    /* $entity->setData('feed_id', $medicine['feed']['feed_id']);
                    $entity->setData('feed_consumption', $medicine['feed']['feed_consumption']); */
                    $entity->setData('rec_feed_consumption', $rec_feed_consumption);
                    /* $entity->setData('bird_count', $bird_count); */
                    
                    $entity->setData('feed_unit_id', 1);
                    $entity->setData('house_id', $house_id);
                    $entity->setData('created_at', $medicine['day']);
                    $entity->setData('updated_at', $medicine['day']);
                    $entity->setData('bird_count',$bird_count);
                } else {
                    /* $entity->setData('age_week', $age_week);
                    $entity->setData('age_day', $age_day);
                    $entity->setData('bird_count', $bird_count); */
                    $sum = (int)$entity->getData('bird_count') + (int)$difference;
                    $entity->setData('bird_count', $sum);
                }

                $entity->setData('age_week', $age_week);
                $entity->setData('age_day', $age_day);
                /* $entity->setData('bird_count', $bird_count); */
                $entity->setData('feed_id', $medicine['feed']['feed_id'] ? $medicine['feed']['feed_id'] : 1);
                $entity->setData('feed_consumption', $medicine['feed']['feed_consumption'] ? $medicine['feed']['feed_consumption'] : 0);
                $entity->setData('medicine_ids', $med_ids);
                $entity->setData('medicine_values', $medv);
                $entryId = $entity->__save();
                if ($entryId) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Monthly record saved';
                } 

                /* $birdCounts = $this->_dailyhouseharvest->getByColumn(['house_id'=>$house_id], 0);
                if($birdCounts){
                    $sum = 0;
                    foreach ($birdCounts as $value) {
                        $sum = $difference + $value->getData('bird_count');
                        var_dump($sum);
                        
                        
                    }
                } */
                $age_day++;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>