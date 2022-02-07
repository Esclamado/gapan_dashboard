<?php
namespace Lns\Gpn\Controller\Cron\Dailyhouseharvest\Action;

class CronjobsFeedEndMonth extends \Lns\Sb\Controller\Controller {

    protected $_house;
    protected $_dailyHouseHarvest;

    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_house = $House;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
    }
    public function run() {
       $today = date("Y-m-d");
       $month = date("Y-m");
       $House = $this->_house->getCollection();
     
        foreach ($House as $house) {
            $house_id = $house->getData('id');
            $Dailyhouseharvest = $this->_dailyHouseHarvest->getLastMonthData($house_id, $today);
            
            if($Dailyhouseharvest){
                
            }else{
             $Dailyhouseharvest = $this->_dailyHouseHarvest->getLastData($house_id);
             $getLastRecord = $Dailyhouseharvest[0];
             
             $user_id = $getLastRecord->getData('user_id');
             $age_week = $getLastRecord->getData('age_week');
             $age_day = $getLastRecord->getData('age_day');
             $bird_count = $getLastRecord->getData('bird_count');
             $feed_id = $getLastRecord->getData('feed_id');
             $feed_consumption = $getLastRecord->getData('feed_consumption');
             $rec_feed_consumption = $getLastRecord->getData('rec_feed_consumption');
             $feed_unit_id = $getLastRecord->getData('feed_unit_id');
            
             $date = date('F Y');//Current Month Year
                while (strtotime($date) <= strtotime(date('Y-m') . '-' . date('t', strtotime($date)))) {
                $day_num = date('j', strtotime($date));//Day number
                $day_name = date('l', strtotime($date));//Day name
                $day_abrev = date('S', strtotime($date));//th, nd, st and rd
                $day = "$day_name $day_num $day_abrev";
                $date = date("Y-m-d", strtotime(($date)));//Adds 1 day onto current date
                
                if($age_day <= 5 ){
                    $age_day+=1;
                }else{
                    $age_day = 0;
                    $age_week+=1;
                }
                
                $created_at = $date;
                $updated_at = $date;
                
                $saveData = $this->_dailyHouseHarvest;
                $saveData->setData('house_id', $house_id);
                $saveData->setData('age_day', $age_day);
                $saveData->setData('age_week', $age_week);
                $saveData->setData('bird_count', $bird_count);
                $saveData->setData('feed_id', $feed_id);
                $saveData->setData('feed_consumption', $feed_consumption);
                $saveData->setData('rec_feed_consumption', $rec_feed_consumption);
                $saveData->setData('feed_unit_id', $feed_unit_id);
                $saveData->setData('created_at', $created_at);
                $saveData->setData('updated_at', $updated_at);
                
                if($month == date("Y-m", strtotime($date))){
                    $saveData->__save();
                }
                
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));//Adds 1 day onto current date
            
            }
                
          }
       }
    }
}
?>