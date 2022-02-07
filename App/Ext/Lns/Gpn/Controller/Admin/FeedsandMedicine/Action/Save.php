<?php
namespace Lns\Gpn\Controller\Admin\FeedsandMedicine\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_dailyHouseHarvest;

/*     protected $token;
	protected $payload; */

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ){
    parent::__construct($Url,$Message,$Session);
/*         $this->token = $Validate; */
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
    }
    public function run(){
/*         $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
		->validate($this->_request, true); */

        $this->jsonData['error'] = 1;

/*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else { */

       /*  $userId = $payload['payload']['jti']; */
        $houseId = $this->getParam('house_id');
        $bird_count = $this->getParam('bird_count');
        $feeds = $this->getParam('feeds');
        $age_week = $this->getParam('age_week');
        $age_day = $this->getParam('age_day');
        $medicines = $this->getParam('medicine');

        $med = [];
        $medVal = [];
            foreach ($medicines as $medicine) {
                if(array_key_exists('medicineId', $medicine)){
                    $med[] = $medicine['medicineId'];
                    $medVal[] = $medicine['medicineValue'];
                } 
            }
            $meds = implode(',', $med);
            $medv = implode(',', $medVal);

        $insert = $this->_dailyHouseHarvest;
        $insert->setData('house_id', $houseId);
        $insert->setData('bird_count', $bird_count);
        $insert->setData('feed_id', $feeds);
        $insert->setData('age_week', $age_week);
        $insert->setData('age_day', $age_day);
        $insert->setData('medicine_ids', $meds);
        $insert->setData('medicine_values', $medv);
        $entryId = $insert->__save();

        if($entryId){
            $this->jsonData['error'] = 0;
            $this->jsonData['message'] = 'Successfully Added New Monthly Record';

            $redirectTo = 'feedsandmedicine/action/add';
			$this->_url->redirect($redirectTo);
        }

        /* } */
        $this->jsonEncode($this->jsonData);
		die;
    }

}

?>