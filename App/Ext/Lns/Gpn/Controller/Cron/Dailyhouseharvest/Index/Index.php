<?php
namespace Lns\Gpn\Controller\Cron\Dailyhouseharvest\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_dailyHouseHarvest;
    protected $_dateTime;
    protected $_feeds;
    protected $_house;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_dateTime = $DateTime;
        $this->_feeds = $Feeds;
        $this->_house = $House;
    }
    public function run() {

        $houses = $this->_house->getHouses();
        if ($houses) {
            foreach ($houses as $house) {
                $hasRecord = $this->_dailyHouseHarvest->hasRecord($house->getData('id'), NULL, date('Y-m-d'));
                if (!$hasRecord) {
                    /* $latestRecord = $this->_dailyHouseHarvest->getLatestRecord($house->getData('id'));
                    if ($latestRecord) {
                        $age_week = (int)$latestRecord->getData('age_week');
                        $age_day = (int)$latestRecord->getData('age_day');
                        if ($age_day + 1 == 7) {
                            $age_week = $age_week + 1;
                            $age_day = 0;
                        } else {
                            $age_day = $age_day + 1;
                        }
                        $entity = $this->_dailyHouseHarvest;
                        $latestCreatedAt = date_create($latestRecord->getData('created_at'));
                        date_add($latestCreatedAt, date_interval_create_from_date_string('1 days'));
                        $rec_feed_consumption = $this->_feeds->getRecommendedConsumption($age_week);
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

                        $entity->setDatas([
                            'house_id' => $latestRecord->getData('house_id'),
                            'bird_count' => $latestRecord->getData('bird_count'),
                            'age_week' => $age_week,
                            'age_day' => $age_day,
                            'mortality' => 0,
                            'cull' => 0,
                            'feed_id' => $latestRecord->getData('feed_id'),
                            'rec_feed_consumption' => $rec_feed_consumption,
                            'feed_unit_id' => $latestRecord->getData('feed_unit_id'),
                            'egg_count' => 0,
                            'created_at' => date_format($latestCreatedAt, "Y-m-d")
                        ])->__save();
                    } */
                } else {
                    if (!$hasRecord->getData('prepared_by')) {
                        $latestRecord = $this->_dailyHouseHarvest->getLatestRecord($house->getData('id'), true);
                        $hasRecord->setData('bird_count', $latestRecord->getData('bird_count'));
                        $hasRecord->__save();
                        var_dump(date("m.d.y"));
                        var_dump($latestRecord->getData('id'));
                        var_dump($latestRecord->getData('bird_count'));
                    }
                }
            }
            
        }
        
    }
}
?>