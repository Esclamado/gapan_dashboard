<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Lns\Gpn\Lib\Entity\Db\Dailysortingreport;
use Lns\Gpn\Lib\Entity\Db\Houseb;
use Lns\Gpn\Lib\Entity\Db\Feeds;

class Dailyhouseharvest extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'daily_house_harvest';
    protected $primaryKey = 'id';
    const COLUMNS = [
		'id',
        'user_id',
        'house_id',
        'bird_count',
        'age_week',
        'age_day',
        'mortality',
        'cull',
        'medicine_ids',
        'medicine_values',
        'feed_id',
        'feed_consumption',
        'rec_feed_consumption',
        'feed_unit_id',
        'egg_count',
        'real_egg_count',
        'schedule',
        'prepared_by',
        'prepared_by_path',
        'prepared_by_date',
        'prepared_by_isdraft',
        'checked_by',
        'checked_by_path',
        'checked_by_date',
        'checked_by_isdraft',
        'received_by',
        'received_by_path',
        'received_by_date',
        'received_by_isdraft',
        'recordStatus',
        'isSorted',
        'created_at',
        'updated_at'
  ];

  public $_dailysortingreport;
  public $_house;
  public $_feeds;
  
  public function __construct(
    \Of\Http\Request $Request,
    $adapter=null
  ) {
    parent::__construct($Request,$adapter);
    $this->_dailysortingreport = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Dailysortingreport');
    $this->_house = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Houseb');
    $this->_feeds = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Feeds');
  }

  public function hasRecord($house_id, $id = null, $date_today = null) {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    
    if ($id) {
      $where = "id = ".$this->escape($id);
    } else {
      $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND house_id = ".$this->escape($house_id);
    }
    if ($date_today) {
        $where = "date_format(created_at, '%Y-%m-%d') = date_format('".$date_today."', '%Y-%m-%d') AND house_id = ".$this->escape($house_id);
    }
    $this->_select->where($where/* "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND house_id = ".$this->escape($house_id) */);
    $this->_select->limit(1);
    $this->_select->order(['id' => 'desc']);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return false;
    }
  }
  public function hasRecordForThisMonth($house_id) {
    $mainTable = $this->getTableName();
		$this->resetQuery();
    $this->_select->where("date_format(created_at, '%Y-%m') = date_format(now(), '%Y-%m') AND house_id = ".$this->escape($house_id));
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return true;
    } else {
      return false;
    }
  }
  public function getLatestRecord($house_id, $getPrevious = false) {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $this->_select->where("house_id = ".$this->escape($house_id));
    if ($getPrevious) {
      $this->_select->where("date_format(created_at, '%Y-%m-%d') < date_format(now(), '%Y-%m-%d')");
    }
    $this->_select->limit(1);
    $this->_select->order(['created_at' => 'desc']);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return false;
    }
  }
  public function getRecordById($id) {
    return $this->getByColumn(['id' => $id], 1);
  }
  public function save($data) {
    foreach($data as $key => $value) {
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$save = $this->__save();
		return $save;
  }
  /* 1 : FOR APPROVAL NI INSPECTOR */
  /* 2 : APPROVED BY INSPECTOR, TO SORTER */
  /* 3 : ACCEPTED BY SORTER, COMPLETE THE JOURNEY */
  public function getList($param, $role_id = null) {
    $mainTable = $this->getTableName();
    $limit = 1;
    $order = 'desc';
		if (isset($param['limit'])) {
			$limit = $param['limit'];
    }
    if (isset($param['order'])) {
			$order = $param['order'];
    }
    if ($param['type'] == 1) {
      $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NULL AND `".$mainTable."`.`received_by` IS NULL";
    } else if ($param['type'] == 2) {
      /* $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL AND `".$mainTable."`.`received_by` IS NULL"; */

      if ($role_id == 9) {
        $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL AND `".$mainTable."`.`received_by` IS NULL";
      } else {
        $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL";
      }
    } else if ($param['type'] == 3) {
      $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL AND `".$mainTable."`.`received_by` IS NOT NULL AND isSorted = 0";
    } else {
      if (isset($param['prepared_by_isdraft']) && $param['prepared_by_isdraft'] == 1) {
        $where = "`".$mainTable."`.`prepared_by_isdraft` = 1";
      } else if (isset($param['checked_by_isdraft']) && $param['checked_by_isdraft'] == 1) {
        $where = "`".$mainTable."`.`checked_by_isdraft` = 1";
      } else if (isset($param['received_by_isdraft']) && $param['received_by_isdraft'] == 1) {
        $where = "`".$mainTable."`.`received_by_isdraft` = 1";
      }
    }
    if (isset($param['from']) && isset($param['to'])) {
      if ($param['type'] == 1) {
        $where = "(".$where.") AND (date_format(`".$mainTable."`.`prepared_by_date`, '%Y-%m-%d') >= date_format('".$param['from']."', '%Y-%m-%d') AND date_format(`".$mainTable."`.`prepared_by_date`, '%Y-%m-%d') <= date_format('".$param['to']."', '%Y-%m-%d'))";
      } else if ($param['type'] == 2) {
        $where = "(".$where.") AND (date_format(`".$mainTable."`.`checked_by_date`, '%Y-%m-%d') >= date_format('".$param['from']."', '%Y-%m-%d') AND date_format(`".$mainTable."`.`checked_by_date`, '%Y-%m-%d') <= date_format('".$param['to']."', '%Y-%m-%d'))";
      } else if ($param['type'] == 3) {
        $where = "(".$where.") AND (date_format(`".$mainTable."`.`received_by_date`, '%Y-%m-%d') >= date_format('".$param['from']."', '%Y-%m-%d') AND date_format(`".$mainTable."`.`received_by_date`, '%Y-%m-%d') <= date_format('".$param['to']."', '%Y-%m-%d'))";
      } else {
        $where = "(".$where.") AND (date_format(`".$mainTable."`.`created_at`, '%Y-%m-%d') >= date_format('".$param['from']."', '%Y-%m-%d') AND date_format(`".$mainTable."`.`created_at`, '%Y-%m-%d') <= date_format('".$param['to']."', '%Y-%m-%d'))";
      }
    }
    if (isset($param['houseids'])) {
      $houseIds = explode(',', $param['houseids']);
      $withHouseId = [];
      foreach ($houseIds as $houseId) {
        $withHouseId[] = "`".$mainTable."`.`house_id` = ".$houseId;
      }
      $where = "(".$where.") AND (".implode(" OR ", $withHouseId).")";
    }
    if (isset($param['userids'])) {
      $userIds = explode(',', $param['userids']);
      $withUserId = [];
      foreach ($userIds as $userId) {
        $withUserId[] = "`" . $mainTable . "`.`prepared_by` = " . $userId;
      }
      $where = "(" . $where . ") AND (" . implode(" OR ", $withUserId) . ")";
    }
    $this->_select->where($where);
    if ($param['type'] == 1) {
      $this->_select->order(['prepared_by_date' => $order]);
    } else if ($param['type'] == 2) {
      $this->_select->order(['checked_by_date' => $order]);
    } else if ($param['type'] == 3) {
      $this->_select->order(['received_by_date' => $order]);
    } else {
      $this->_select->order(['created_at' => $order]);
    }
    /* var_dump($this->getLastSqlQuery());die; */
    return $this->getFinalResponse($limit);
  }
  public function getDailyhouseharvest(){
    return $this->getCollection();
  }
  public function getSomething($id){
    $info = $this->getByColumn(['house_id'=>$id], 1);
    return $info;
  }
  public function getCull(){
    $this->resetQuery();
    $where = '';
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
    $this->_select->where($where);
    $datas = $this->getCollection();
    $count = 0;
    if($datas){
      foreach ($datas as $data) {
        $count += (int)$data->getData('cull');
      }
    }
    return $count;
  }
  public function getMortality(){
    $this->resetQuery();
    $where = '';
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
    $this->_select->where($where);
    $datas = $this->getCollection();
    $count = 0;
    if($datas){
      foreach ($datas as $data) {
        $count += (int)$data->getData('mortality');
      }
    }
    return $count;
  }
  public function getTotalCull()
  {
    $this->resetQuery();
    $datas = $this->getCollection();
    $count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $count += (int) $data->getData('cull');
      }
    }
    return $count;
  }
  public function getDailyreportsforapproval($param) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where("`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NULL");
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
    $this->_select->removeColumn("`" . $mainTable . "`.*");
    $count = $this->__getQuery($this->getLastSqlQuery());
    $totalCount = 0;
    if ($count) {
        $totalCount = $count->getData('count');
    }
    return $totalCount;
  }
  public function getDailyreportscount($param) {
    $this->resetQuery();
    if (isset($param['from']) && isset($param['to'])) {
      $date_diff = strtotime(strval($param['to'])) - strtotime(strval($param['from']));
      $count = round($date_diff / (60 * 60 * 24));
      return (int)$count + 1;
    } else {
      return NULL;
    }
    /* if ($type == 1) {
      $date_diff = strtotime(date('Y-m-d')) - strtotime(date('Y') . '-01-01');
      $newdate = round($date_diff / (60 * 60 * 24));
      $getCount = $newdate;
    } else {
      $getCount = 1;
    }
    if ($getCount) {
      return $getCount;
    } else {
      return null;
    } */
  }
  public function getprodrate($param) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where("`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL");
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $datas = $this->getCollection();
    $count = count($this->getCollection());
    $prodrateperhouse = 0;
    
    if ($datas) {
      foreach ($datas as $data) {
        $prodrateperhouse += ((float)$data->getData('real_egg_count') / (float)$data->getData('bird_count')) * 100;
       
      }
    }
    
    if($count == 0){
        return 0;
    }else{
        return $prodrateperhouse / $count;
    }
    
    /* $from = date('Y') . '-01-01';
    $to = date('Y-m-d');
    $where = "prepared_by IS NOT NULL AND checked_by IS NOT NULL";
    $this->_select->where($where);
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
    if ($type == 1) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d'))";
    }
    $this->_select->where($where);
    $datas = $this->getCollection();
    if ($datas) {
      $prodrateperhouse = 0;
      foreach ($datas as $data) {
        $prodrateperhouse += (float)$data->getData('egg_count') / (float)$data->getData('bird_count') * 100;
      }
      return $prodrateperhouse;
    } */
    
  }
  public function getTotaleggharvested($param) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`real_egg_count`)"), 'sum_egg_count');
    $this->_select->where("`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL");
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->removeColumn("`" . $mainTable . "`.*");
    $count = $this->__getQuery($this->getLastSqlQuery());
    $totalCount = 0;
    if ($count) {
        $totalCount = $count->getData('sum_egg_count');
    }
    return $totalCount;
  }
  public function getEggharvested($type)
  {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $from = date('Y') . '-01-01';
    $to = date('Y-m-d');
    $where = "prepared_by IS NOT NULL AND checked_by IS NOT NULL";
    $this->_select->where($where);
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
    if ($type == 1) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d'))";
    }
    $this->_select->where($where);
    $datas = $this->getCollection();
    /*  $count = 0; */
    if ($datas) {
      /*      foreach ($datas as $data) {
        $count += (int) $data->getData('egg_count');
      } */
      return $datas;
    }
    /*   return $count; */
  }
  public function getFeedConsumption($param, $houseId) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    
    $this->_select->where("`".$mainTable."`.`house_id` = " . $houseId);
    $this->_select->where("`" . $mainTable. "`.`prepared_by` IS NOT NULL");
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->group('grouped_date');
    /* var_dump($this->getLastSqlQuery()); */
    $datas = $this->getCollection();
    $count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        if ($data->getData('feed_consumption') > 0) {
          $count += (int) $data->getData('feed_consumption') * (int) $data->getData('bird_count');
        } else {
          $count += (int) $data->getData('rec_feed_consumption') * (int) $data->getData('bird_count');
        }
      }
    }
    return $count;
  }
  public function getMedicineConsumption($param, $medicineId) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $datas = $this->getCollection();
    if ($datas) {
      $med = 0;
      foreach ($datas as $data) {
        $medicine_ids = $data->getData('medicine_ids');
        $medicine_ids = explode(',', $medicine_ids);

        $medicine_values = $data->getData('medicine_values');
        $medicine_values = explode(',', $medicine_values);

        if (in_array($medicineId, $medicine_ids)) {
          $key = array_search($medicineId, $medicine_ids); 
          $med += (float)$medicine_values[$key];
        }
      }
      return $med;
    }
  }
  public function getProductionRate($param, $userId){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where("`".$mainTable."`.`prepared_by` = " . $userId);
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $datas = $this->getCollection();
    $real_egg_count = 0;
    $bird_count = 0;
    $egg_count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $real_egg_count += (int) $data->getData('real_egg_count');
        $bird_count += (int) $data->getData('bird_count');
        $egg_count += $data->getData('real_egg_count');
        $count = $real_egg_count / $bird_count * 100;
      }
      return array(
        'real_egg_count' => $egg_count,
        'count' => $count
      );
    }
  }
  public function getHouseProductionRate($param, $houseId) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where("`".$mainTable."`.`house_id` = " . $houseId);
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $datas = $this->getCollection();
    $real_egg_count = 0;
    $bird_count = 0;
    $egg_count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $real_egg_count += (int) $data->getData('real_egg_count');
        $bird_count += (int) $data->getData('bird_count');
        $egg_count += $data->getData('real_egg_count');
      }
      $count = $real_egg_count / $bird_count * 100;
      return  number_format((float)$count, 2, '.', '');
    }
  }
  public function getUserdailyhouserecord($param){
    $mainTable = $this->getTableName();
    $daily_sorting_report = $this->_dailysortingreport->getTableName();
    $limit = 1;
    $this->__join('daily_sorting_report_', $mainTable . '.id', 'daily_sorting_report', '', 'left', 'house_harvest_id', Dailysortingreport::COLUMNS);
    $where = "`" . $mainTable . "` . `prepared_by` IS NOT NULL";
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['user_id'])) {
      $where = "`" . $mainTable . "` . `prepared_by`=" . $param['user_id'];
      $this->_select->where($where);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['search'])) {
      $where = $this->likeQuery(['real_egg_count','bird_count','created_at'], $this->escape($param['search']), 'daily_house_harvest', true);
      $this->_select->where($where);
    }
    if (isset($param['type'])) {
      if ($param['type'] == 1) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 2) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 3) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 0";
        $this->_select->where($where);
      } else if ($param['type'] == 4) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 1 AND `" . $daily_sorting_report . "`.`prepared_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 5) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 6) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 7) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NOT NULL";
        $this->_select->where($where);
      }
    }
    return $this->getFinalResponse($limit);
  }
  public function getDailyhousereport($param){
    $mainTable = $this->getTableName();
    $daily_sorting_report = $this->_dailysortingreport->getTableName();
    $house = $this->_house->getTableName();
    $limit = 1;
    $this->__join('daily_sorting_report_', $mainTable . '.id', 'daily_sorting_report', '', 'left', 'house_harvest_id', Dailysortingreport::COLUMNS);
    $this->__join('house_', $mainTable . '.house_id', 'house', '', 'left', 'id', Houseb::COLUMNS);
    $where = "`" . $mainTable . "` . `prepared_by` IS NOT NULL";
    $this->_select->where($where);
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      if($param['order_by_column'] == 'age_week'){
        $this->_select->order([$mainTable . ".age_day" => $param['order_by']]);
      } else if ($param['order_by_column'] == 'house_name') {
        $this->_select->order([$house . "." . $param['order_by_column'] => $param['order_by']]);
      } else {
        $this->_select->order([$mainTable . "." . $param['order_by_column'] => $param['order_by']]);
      }
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['search'])) {
      $where = $this->likeQuery(['house_id', 'bird_count', 'age_week', 'age_day', 'mortality', 'cull', 'real_egg_count'], $this->escape($param['search']), 'daily_house_harvest', true);
      $this->_select->where($where);
    }
    if (isset($param['flockman_id'])) {
      $where = "`" . $mainTable . "`.`prepared_by`=" . $param['flockman_id'];
      $this->_select->where($where);
    }
    if (isset($param['type'])) {
      if ($param['type'] == 1) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 2) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 3) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 0";
        $this->_select->where($where);
      } else if ($param['type'] == 4) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 1 AND `" . $daily_sorting_report . "`.`prepared_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 5) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 6) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 7) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NOT NULL";
        $this->_select->where($where);
      }
   }
    return $this->getFinalResponse($limit);
  }
  public function getHouserecord($param){
    $this->resetQuery();  
    $mainTable = $this->getTableName();
    $daily_sorting_report = $this->_dailysortingreport->getTableName();
    $limit = 1;
    $where = "prepared_by != ''";
    $this->_select->where($where);
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
      if ($param['order_by_column'] == 'age_week') {
        $this->_select->order(["age_day" => $param['order_by']]);
      }
    }
    if (isset($param['house_id'])) {
      $where = "house_id=" . $param['house_id'];
      $this->_select->where($where);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['search'])) {
      $where = $this->likeQuery(['bird_count', 'age_week', 'age_day', 'mortality', 'cull', 'real_egg_count'], $this->escape($param['search']), 'daily_house_harvest', true);
      $this->_select->where($where);
    }
    if (isset($param['type'])) {
      if ($param['type'] == 1) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 2) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 3) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 0";
        $this->_select->where($where);
      } /* else if ($param['type'] == 4) {
        $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL AND isSorted = 1 AND `" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL";
        $this->_select->where($where);
      } */ else if ($param['type'] == 4) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 5) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`received_by` IS NULL";
        $this->_select->where($where);
      } else if ($param['type'] == 6) {
        $where = "`" . $daily_sorting_report . "`.`prepared_by` IS NOT NULL AND `" . $daily_sorting_report . "`.`checked_by` IS NULL AND `" . $daily_sorting_report . "`.`received_by` IS NOT NULL";
        $this->_select->where($where);
      }
    }
    if ($this->getCollection()) {
        return $this->getFinalResponse($limit);
    } else {
        return NULL;
    }
  }
  public function getLatestActivity($house_id = null) {
    $this->_select->where('prepared_by IS NOT NULL');
    if ($house_id) {
      $this->_select->where('house_id = ' . $house_id);
    }
		$this->_select->order(['updated_at' => 'desc']);
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
  }
  public function getBeginningByHouse($house_id) {
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->order(['id' => 'asc']);
		$this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
    }
  }
  public function getFeeds($param){
    $mainTable = $this->getTableName();
    $limit = 1;
    $this->_select->group('age_week');
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    return $this->getFinalResponse($limit);
  }
  public function getFeedconsumptions($ageweek, $houseId){
    $this->resetQuery();
    $where = "age_week = ". $ageweek ." AND house_id = ".$houseId;
    $this->sum('feed_consumption');
    $this->_select->where($where);
    $this->_select->group('age_week');
    $data = $this->getCollection();
    if($data){
      return $data[0];
    }else{
      return null;
    }
  }
  public function getFeedsandmedicineconsumption($param){
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');

    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`cull`)"), 'cull');
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`mortality`)"), 'mortality');
    $date_today = date("Y-m-d h:i:s A");
    /* $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format(now(), '%Y-%m')"; */
    $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $date_today . "', '%Y-%m')";
    $this->_select->where($where);
 /*    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    } */
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['house_id'])) {
      $where = "house_id=" . $param['house_id'];
      $this->_select->where($where);
    }
    $this->_select->group('grouped_date');
    $data = $this->getCollection();
    if($data){
      return $data;
    }else{
      return null;
    }
    /* return $this->getFinalResponse($limit); */
  }
  public function getLatestcockage($house_id, $date = NULL){
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $age_week = 0;
    $age_day = 0;
    $week = '';
    $day = '';
    
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND house_id = " . $this->escape($house_id);
    if ($date) {
        $where = "date_format(created_at, '%Y-%m-%d') = date_format('".$date."', '%Y-%m-%d') AND house_id = " . $this->escape($house_id);
    }
    $this->_select->where($where);
    $datas = $this->getCollection();
    if($datas){
      foreach ($datas as $key => $data) {
        if ($data->getData('age_week') > 1) {
          $week = 'weeks';
          $age_week = $data->getData('age_week');
        }else{
          $week = 'week';
          $age_week = $data->getData('age_week');
        }
        if ($data->getData('age_day') > 1) {
          $day = 'days';
          $age_day = $data->getData('age_day');
        }else{
          $day = 'day';
          $age_day = $data->getData('age_day');
        }
      }
    }
      return $data = array(
            'age'=> $age_week . ' ' . $week . ', ' . $age_day . ' ' . $day
      );
  }
  public function getReportcount($houseId, $date){
      $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    $where = "date_format(created_at, '%Y-%m') = date_format('".$date."', '%Y-%m') AND ";
    $where .= "house_id=" . $houseId;
    $this->_select->where($where);

    $this->_select->where('prepared_by IS NOT NULL');
    $this->_select->group('grouped_date');
    /* $this->_select->group("created_at"); */
    
    /* $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
    $this->_select->removeColumn("`" . $mainTable . "`.*");
    $count = $this->__getQuery($this->getLastSqlQuery());
    $totalCount = 0;
    if ($count) {
        $totalCount = $count->getData('count');
    }
    return $totalCount; */
    $datas = $this->getCollection();
    $totalCount = 0;
    if ($this->getCollection()) {
      $totalCount = count($this->getCollection());
    }
    return $totalCount;
  }
  public function getReportUpdatedAt($houseId, $date) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    $where = "date_format(created_at, '%Y-%m') = date_format('".$date."', '%Y-%m') AND ";
    $this->_select->where("`".$mainTable."`.`house_id` = " . $houseId);
    $this->_select->where("`".$mainTable."`.`prepared_by` IS NOT NULL");
    $this->_select->order(["updated_at" => 'DESC']);
    $this->_select->limit(1);
    //var_dump($this->getLastSqlQuery());die;
    $data = $this->__getQuery($this->getLastSqlQuery());
    $updated_at = NULL;
    if ($data) {
        $updated_at = $data->getData('updated_at');
    }
    return $updated_at;
  }
  public function getFeedConsumptionPerHouse($param, $getcount = null) {
      $this->resetQuery();
    $mainTable = $this->getTableName();
    $limit = 1;
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    /* $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM((`" . $mainTable . "`.`bird_count` + `" . $mainTable . "`.`mortality` + `" . $mainTable . "`.`cull`) * `" . $mainTable . "`.`feed_consumption`)"), 'feed_consumption'); */
    $this->_select->where('prepared_by IS NOT NULL');

    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    
    /* $this->_select->where('received_by IS NOT NULL'); */
    /* $this->sum('rec_feed_consumption'); */

    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['house_id'])) {
      $this->_select->where('house_id = ' . $param['house_id']);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    /* $this->_select->group('grouped_date'); */
    $this->_select->group('age_week');
    if ($getcount) {
      if ($this->getCollection()) {
        return count($this->getCollection());
      } else {
        return 0;
      }
    } else {
      if ($this->getCollection()) {
        return $this->getFinalResponse($limit);
      } else {
        return null;
      }
    }
  }
  public function getFeedConsumptionPerHouseAgeWeek($house_id, $age_week, $param) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->__join('feeds_', $mainTable . '.feed_id', 'feeds', '', 'left', 'id', Feeds::COLUMNS);
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->where($mainTable . '.house_id = ' . $house_id);
    $this->_select->where($mainTable . '.age_week = ' . $age_week);
    
    $result = $this->getCollection();
    if ($result) {
      $sum = 0;
      foreach ($result as $res) {
        /* get feed info first */
        $feedg = $res->getData('feed_consumption') == 0 ? (float)$res->getData('rec_feed_consumption') : (float)$res->getData('feed_consumption');
        
        $total_feed_consumption_grams = ($res->getData('bird_count') + $res->getData('mortality') + $res->getData('cull')) * $feedg;
        /* $sum += (float)$feedg; */
        $sum += (float)$total_feed_consumption_grams;
      }
      $feed_string = $this->_feeds->convertGramsToBags2($sum, 50);
      return array('sum' => $sum, 'feed_string' => $feed_string);
    } else {
      return array('sum' => 0);
    }
  }
  public function getHarvestedProductionPerHouse($param, $getcount = null) {
      $this->resetQuery();
    $mainTable = $this->getTableName();
    $limit = 1;
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');

    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    
    $this->_select->where('checked_by IS NOT NULL');
    
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['house_id'])) {
      $this->_select->where('house_id = ' . $param['house_id']);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->group('grouped_date');
    if ($getcount) {
      if ($this->getCollection()) {
        return count($this->getCollection());
      } else {
        return 0;
      }
    } else {
      if ($this->getCollection()) {
        return $this->getFinalResponse($limit);
      } else {
        return null;
      }
    }
    /* var_dump($this->getLastSqlQuery());die;
    return $this->getFinalResponse($limit); */
  }
  public function getHarvestedProductionByHouseDate($house_id, $date) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    $this->sum('real_egg_count');
    /* $this->_select->where('received_by IS NOT NULL'); */
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')");
    $this->_select->where('checked_by IS NOT NULL');
    $this->_select->group('grouped_date');
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
    }
  }
  public function validateByHouse($house_id, $year_month, $has_prepared_by = false) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $year_month . "', '%Y-%m')");
    if ($has_prepared_by) {
      $this->_select->where("prepared_by IS NOT NULL");
    }
    $this->_select->order(['created_at' => 'desc']);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return 0;
    }
  }
  /* public function getMedicineconsumptionreport($param){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->group('created_at');
    $limit = 1;
    if(isset($param['limit'])){
      $limit = $param['limit'];
    }
    if (isset($param['month'])) {
      $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $param['month'] . "', '%Y-%m')");
    }
    return $this->getFinalResponse($limit);
  } */
  public function getMedicineconsumptionreport($param, $getcount = null) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    if (isset($param['med_id'])) {
      $this->_select->where("FIND_IN_SET(".$param['med_id'].", `".$mainTable."`.`medicine_ids`)");
      /* $this->_select->where("`".$mainTable."`.`medicine_ids` IN (".$param['med_id'].")"); */
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->group('grouped_date');
    /* var_dump($this->getLastSqlQuery());die; */
    if ($getcount) {
        /* $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        var_dump($this->getLastSqlQuery());die;
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if ($count) {
            $totalCount = $count->getData('count');
        } */
        $totalCount = count($this->getCollection());
        return $totalCount;
    } else {
        $count = $this->__getQuery($this->getLastSqlQuery());
        if ($count) {
            return $this->getFinalResponse($limit);
        } else {
            return NULL;   
        }
    }
  }
  public function getMedPerHouse($house_id, $date, $med_id) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where("`".$mainTable."`.`house_id` = " . $house_id);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')");
    /* $this->_select->where("`".$mainTable."`.`medicine_ids` IN (".$med_id.")"); */
    $this->_select->where("FIND_IN_SET(".$med_id.", `".$mainTable."`.`medicine_ids`)");
    $this->_select->limit(1);
    $value = $this->__getQuery($this->getLastSqlQuery());
    $med_value = [];
    if ($value) {
        $med_value['medicine_ids'] = $value->getData('medicine_ids');
        $med_value['medicine_values'] = $value->getData('medicine_values');
    } else {
        $med_value['medicine_ids'] = $med_id;
        $med_value['medicine_values'] = 0;
    }
    return $med_value;
  }
  public function getMedicineperhouse($house_id, $created_at, $param){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $where = "house_id = $house_id";
    $this->_select->where($where);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $created_at . "', '%Y-%m-%d')");
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    return $this->getCollection();
    /* $med = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $medicine_ids = $data->getData('medicine_ids');
        $medicine_ids = explode(',', $medicine_ids);

        $medicine_values = $data->getData('medicine_values');
        $medicine_values = explode(',', $medicine_values);
        if(isset($param['med_id'])){
          if (in_array($param['med_id'], $medicine_ids)) {
            $key = array_search($param['med_id'], $medicine_ids);
            $med += (float) $medicine_values[$key];
          }
        }
      }
    }
    return $med; */
  }
  public function getRecordByDateHouse($date, $house_id) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('house_id = ' . $house_id);
    if($date){
      $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')");
    }else{
      $this->_select->order(['created_at' => 'desc']);
    }
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
    }
  }
  public function getTotalConsumedFeeds($feed_id) {
    /* $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('prepared_by IS NOT NULL AND checked_by IS NOT NULL');
    $this->_select->where('feed_id = ' . $feed_id);
    $result = $this->getCollection();
    if ($result) {
      $sum = 0;
      foreach ($result as $res) {
        $feedg = $res->getData('feed_consumption') == 0 ? (float)$res->getData('rec_feed_consumption') : (float)$res->getData('feed_consumption');
        $sum += (float)$feedg * (float)$res->getData('bird_count');
      }
      return $sum;
    } else {
      return 0;
    } */
    
    $this->resetQuery();
    $mainTable = $this->getTableName();
    /* $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`feed_consumption`)"), 'total_feed_consumption'); */
    /* $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM((`".$mainTable."`.`bird_count` + `".$mainTable."`.`mortality` + `".$mainTable."`.`cull`) * `" . $mainTable . "`.`feed_consumption`)"), 'total_feed_consumption'); */
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM((`".$mainTable."`.`bird_count` + `".$mainTable."`.`mortality` + `".$mainTable."`.`cull`) * `" . $mainTable . "`.`feed_consumption`)"), 'total_feed_consumption');
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d')"), 'prepared_by_date_formatted');
    $this->_select->where("`".$mainTable."`.`prepared_by` IS NOT NULL");
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format(NOW(), '%Y-%m-%d')");
    $this->_select->where("`" . $mainTable . "`.`feed_consumption` > 0");
    $this->_select->where("`" . $mainTable . "`.`feed_id` = " . $feed_id);
    $this->_select->group('prepared_by_date_formatted');
    $this->_select->order(['prepared_by_date_formatted' => 'asc']);
    
    $this->_select->removeColumn("*");
    /* $count = $this->__getQuery($this->getLastSqlQuery());
    $totalCount = 0;
    if ($count) {
        $totalCount = $count->getData('total_feed_consumption');
        var_dump($count->getData());
    } */
    //var_dump($this->getLastSqlQuery());
    $totalCount = 0;
    $datas = $this->getCollection();
    if ($datas) {
        foreach ($datas as $data) {
            $totalCount += $data->getData('total_feed_consumption');
        }
    }
    
    
    
    $this->resetQuery();
    $mainTable = $this->getTableName();
    /* $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM((`".$mainTable."`.`bird_count` + `".$mainTable."`.`mortality` + `".$mainTable."`.`cull`) * `" . $mainTable . "`.`rec_feed_consumption`)"), 'total_rec_feed_consumption'); */
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("(`".$mainTable."`.`bird_count` + `".$mainTable."`.`mortality` + `".$mainTable."`.`cull`) * `" . $mainTable . "`.`rec_feed_consumption`"), 'total_rec_feed_consumption');
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d')"), 'prepared_by_date_formatted');
    $this->_select->where("`".$mainTable."`.`prepared_by` IS NOT NULL");
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format(NOW(), '%Y-%m-%d')");
    $this->_select->where("`" . $mainTable . "`.`feed_consumption` = 0");
    $this->_select->where("`" . $mainTable . "`.`feed_id` = " . $feed_id);
    $this->_select->group('prepared_by_date_formatted');
    $this->_select->order(['prepared_by_date_formatted' => 'asc']);
    $this->_select->removeColumn("*");
    
 
    $count = $this->__getQuery($this->getLastSqlQuery());
   
    /* if ($count) {
        $totalCount2 = $count->getData('total_rec_feed_consumption');
    } */
    
    
    $totalCount2 = 0;
    $datas = $this->getCollection();
    if ($datas) {
        foreach ($datas as $data) {
            $totalCount2 += $data->getData('total_rec_feed_consumption');
        }
    }
  
    return $totalCount + $totalCount2;
  }
  public function getTotalConsumedMedicine($med_id) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("FIND_IN_SET($med_id, `".$mainTable."`.`medicine_ids`)"), 'index_of_medicine');
    /* $this->_select->where("`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL"); */
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d')"), 'prepared_by_date_formatted');
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("`" . $mainTable . "`.`medicine_values`"), 'medicine_values');
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("`" . $mainTable . "`.`medicine_ids`"), 'medicine_ids');
    $this->_select->where("`".$mainTable."`.`prepared_by` IS NOT NULL");
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format(NOW(), '%Y-%m-%d')");
    /* $this->_select->where("medicine_ids IN (".$med_id.")"); */
    $this->_select->where("FIND_IN_SET(".$med_id.", `".$mainTable."`.`medicine_ids`)");
    /* var_dump($this->getLastSqlQuery()); */
    $this->_select->group('prepared_by_date_formatted');
    $this->_select->order(['prepared_by_date_formatted' => 'asc']);
    $this->_select->removeColumn("*");
    $results = $this->getCollection();
    $sum = 0;
    if ($results) {
      foreach ($results as $result) {
        if ($result->getData('medicine_ids')) {
            $index_of_medicine = (int)$result->getData('index_of_medicine') - 1;
            /* $medicine_ids = explode(',',$result->getData('medicine_ids')); */
            $medicine_values = explode(',',$result->getData('medicine_values'));
            $sum += (float)$medicine_values[$index_of_medicine];
          /* if (in_array($med_id, $medicine_ids)) {
            $indexOfMedicine = array_search($med_id, $medicine_ids);
            $sum += (float)$medicine_values[$indexOfMedicine];
          } */
        }
      }
    }
    return $sum;
  }
  public function getRecordByMonth($date, $house_id) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $date . "', '%Y-%m')");
    $result = $this->getCollection();
    if ($result) {
      return $result;
    } else {
      return null;
    }
  }

  public function updateBirdcount($house_id, $date){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('house_id = ' . $house_id);
    if($date){
      $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $date . "', '%Y-%m')");
    }
    $results = $this->getCollection(1);
      if ($results) {
        return $results->getData('bird_count');
      } else {
        return null;
      }
  }
  public function checkIfMedicineExists($med_id) {
      $this->resetQuery();
      $mainTable = $this->getTableName();
      $this->_select->where("FIND_IN_SET('".$med_id."', medicine_ids) > 0");
      $this->_select->limit(1);
      $result = $this->getCollection();
      return $result ? false : true;
      /* SELECT * FROM gpn_daily_house_harvest WHERE FIND_IN_SET('27', medicine_ids) > 0 */
  }
  public function getHousesWithDailyReports($param, $count = NULL) {
      $this->resetQuery();
      $mainTable = $this->getTableName();
      if (isset($param['limit'])) {
        $limit = $param['limit'];
      }
      if (isset($param['from']) && isset($param['to'])) {
        $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
        $this->_select->where($where);
      }
      if (isset($param['order_by_column']) && isset($param['order_by'])) {
        $this->_select->order([$param['order_by_column'] => $param['order_by']]);
      }
      $this->_select->group("house_id");
      /* var_dump($this->getLastSqlQuery());die; */
      if ($count) {
        $totalCount = 0;
        if ($this->getCollection()) {
            $totalCount = count($this->getCollection());
        }
        return $totalCount;
      } else {
          if ($this->getCollection()) {
            return $this->getFinalResponse($limit);
          } else {
            return NULL;
          }
      }
      
  }

  public function getLastMonthData($house_id, $date){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->where("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m') = date_format('" . $date . "', '%Y-%m')");

    if($this->getCollection()){
       return true;
    }else{
        return false;
    }
  }
  
  
  
  
  public function getLastData($house_id){
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->where('house_id = ' . $house_id);
    $this->_select->order(['created_at' => 'desc']);
    $this->_select->limit(1);
    
    if($this->getCollection()){
        $data = $this->getCollection();
        return $data;
    }else{
        return false;
    }
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
}
?>