<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Feeds extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'feeds';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'feed',
        'net_weight',
        'kg_per_bag',
        'pieces',
        'delivery_date',
        'expiration_date',
        'remarks',
        'unit_price',
        'created_at',
        'updated_at'
    ];
    public function installData() {
        $this->setDatas([
            'feed' => 'Layer 1',
            'kg_per_bag' => 50,
        ])->__save();
        $this->setDatas([
            'feed' => 'Layer 2',
            'kg_per_bag' => 50,
        ])->__save();
      }
    public function getFeed($id) {
        $this->resetQuery();
        $data = $this->getByColumn(['id' => $id], 1);
        if ($data) {
            return $data->getData();
        } else {
            return NULL;
        }
    }
    public function convertGramsToBags($data, $isRecommended = false) {
        $string = '';

        $bird_count = $data->getData('bird_count');
        $mortality = $data->getData('mortality');
        $cull = $data->getData('cull');

        $feed_consumption = $data->getData('feed_consumption');

        if($isRecommended){
            $feed_consumption = $data->getData('rec_feed_consumption');
        }
        
        if ($data->getData('feed_id')) {
            $feed = $this->getByColumn(['id' => $data->getData('feed_id')], 1);
            $toKg = (($bird_count + $mortality + $cull) * $feed_consumption) / 1000;
            $bags = $toKg / $feed->getData('kg_per_bag');
            $kg = round(($bags - (int)$bags) * $feed->getData('kg_per_bag'), 2);
            $bagsUnit = "bags";
            if ($bags == 1 || $bags == 0) {
                $bagsUnit = "bag";
            }
            $string = (int)$bags . ' ' . $bagsUnit . ' + ' . $kg . ' kg';
            return array('string' => $string, 'bags' => (int)$bags, 'kg' => $kg);
        } else {
            return array('string' => 0, 'bags' => 0, 'kg' => 0);
        }
        
    }
    public function convertGramsToBags2($grams, $kg_per_bag) {
        $toKg = $grams / 1000;
        $bags = $toKg / $kg_per_bag;
        $kg = round(($bags - (int)$bags) * $kg_per_bag, 2);
        $bagsUnit = "bags";
        if ($bags == 1 || $bags == 0) {
                $bagsUnit = "bag";
        }
        $string = number_format((int)$bags) . ' ' . $bagsUnit . ' + ' . $kg . ' kg';
        return array('string' => $string, 'bags' => (int)$bags, 'kg' => $kg);
    }
    public function getRecommendedConsumption($age_week) {
        $i = 0;
        if ($age_week < 19) {
            $i = 0;
        } else if ($age_week == 19) {
            $i = 81;
        } else if ($age_week == 20) {
            $i = 86;
        } else if ($age_week == 21) {
            $i = 91;
        } else if ($age_week == 22) {
            $i = 96;
        } else if ($age_week == 23) {
            $i = 101;
        } else if ($age_week == 24) {
            $i = 104;
        } else if ($age_week == 25) {
            $i = 106;
        } else if ($age_week == 26) {
            $i = 107;
        } else if ($age_week >= 27 || $age_week <= 34) {
            $i = 108;
        } else if ($age_week >= 35) {
            $i = 109;
        }
        return $i;
    }
    public function getFeedslist($param){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['feed'])) {
            $where = "feed='". $param['feed'] ."'";
            $this->_select->where($where);
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        if (isset($param['delivery_date'])) {
            $where = "date_format(delivery_date, '%Y-%m-%d') = date_format('" . $param['delivery_date'] . "', '%Y-%m-%d')";
            $this->_select->where($where);
        }
        if (isset($param['expiration_date'])) {
            $where = "date_format(expiration_date, '%Y-%m-%d') = date_format('" . $param['expiration_date'] . "', '%Y-%m-%d')";
            $this->_select->where($where);
        }
        if (isset($param['search'])) {
            $where = $this->likeQuery(['feed', 'kg_per_bag', 'pieces', 'delivery_date', 'expiration_date', 'unit_price', 'created_at'], $this->escape($param['search']), 'feeds', true);
            $this->_select->where($where);
        }
        return $this->getFinalResponse($limit);
    }
    public function getCount()
    {
        $this->resetQuery();
        $result = $this->getCollection();
        $getCount = count($result);
        return $getCount;
    }
    public function getLatestActivity() {
		$this->_select->order(['updated_at' => 'desc']);
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
	}
}
?>