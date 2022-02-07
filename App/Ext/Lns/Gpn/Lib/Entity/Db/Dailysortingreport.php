<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Dailysortingreport extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'daily_sorting_report';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'user_id',
        'house_id',
        'house_harvest_id',
        'chicken_pop_id',
        'real_egg_count',
        'egg_count',
        'prepared_by',
        'prepared_by_path',
        'prepared_by_date',
        'checked_by',
        'checked_by_path',
        'checked_by_date',
        'received_by',
        'received_by_path',
        'received_by_date',
        'production_date',
        'ir_status',
        'created_at',
        'updated_at'
    ];

    public function getSortingreport($param){
        $limit = 1;
        $this->setOrderBy('created_at', 'desc');
        
        if(isset($param['limit'])){
            $limit = $param['limit'];
        }
        if(isset($param['house_id'])){
            $this->getByColumn(['house_id'=>$param['house_id']], 1);
        }else{
            $this->getCollection();
        }
        return $this->getFinalResponse($limit);
    }
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
    
          if ($role_id == 10) {
            $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL AND `".$mainTable."`.`received_by` IS NULL";
          } else {
            $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL";
          }
        } else if ($param['type'] == 3) {
          $where = "`".$mainTable."`.`prepared_by` IS NOT NULL AND `".$mainTable."`.`checked_by` IS NOT NULL AND `".$mainTable."`.`received_by` IS NOT NULL";
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
        if (isset($param['ir_status'])) {
          $irStatuses = explode(',', $param['ir_status']);
          $withIRStatus = [];
          foreach ($irStatuses as $irStatus) {
          $withIRStatus[] = "`" . $mainTable . "`.`ir_status` = " . $irStatus;
          }
          $where = "(" . $where . ") AND (" . implode(" OR ", $withIRStatus) . ")";
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
    public function getDailysortingreport($param) {
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
      /* $where = "checked_by != 'Null'";
      $this->_select->where($where);
      $result = $this->getCollection();
      $getCount = count($result);
      if ($getCount) {
        return $getCount;
      } else {
        return null;
      } */
    }
  public function getProductionlisting($param, $getcount = null)
  {
      $this->resetQuery();
    $mainTable = $this->getTableName();
    $limit = 30;
    /* $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL AND `" . $mainTable . "`.`received_by` IS NOT NULL"; */
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL";
    $this->_select->where($where);
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['house_id'])) {
      $this->_select->where("`" . $mainTable . "`.`house_id` = " . $param['house_id']);
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
  }
  public function getProductionlistingTotalItems($param) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
    $where = "`" . $mainTable . "`.`prepared_by` IS NOT NULL AND `" . $mainTable . "`.`checked_by` IS NOT NULL";
    $this->_select->where($where);
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['house_id'])) {
      $this->_select->where("`" . $mainTable . "`.`house_id` = " . $param['house_id']);
    }
    $this->_select->group('grouped_date');
    $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
    $this->_select->removeColumn("`" . $mainTable . "`.*");
    /* var_dump($this->getLastSqlQuery());die; */
    $count = $this->__getQuery($this->getLastSqlQuery());
    $totalCount = 0;
    if($count){
        $totalCount = $count->getData('count');
    }
    return $totalCount;
  }
  public function getBeginningByDate($date) {
    $mainTable = $this->getTableName();
    $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') < date_format('" . $date . "', '%Y-%m-%d')";
    $this->_select->where($where);
    $this->_select->order(['created_at' => 'desc']);
    $this->sum('real_egg_count'); 
    $this->_select->group('created_at');
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
    }
  }
  public function getTotalHarvestedByDate($date) {
    $mainTable = $this->getTableName();
    $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
    $this->_select->where($where);
    /* $this->_select->order(['created_at' => 'desc']); */
    $this->sum('real_egg_count'); 
    $this->_select->group('created_at');
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
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
    
    /* $this->_select->where('received_by IS NOT NULL'); */
    
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
    $this->_select->group('grouped_date');
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
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
}
?>