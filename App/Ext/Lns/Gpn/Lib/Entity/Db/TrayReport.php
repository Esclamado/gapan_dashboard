<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Lns\Sb\Lib\Entity\Db\UserProfile;

class TrayReport extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'tray_report';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
      'id',
      'prepared_by',
      'prepared_by_path',
      'prepared_by_date',
      'created_at'
  ];

  public $_userProfile;

  public function __construct(
      \Of\Http\Request $Request
  ) {
      parent::__construct($Request);
      $this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
  }

  public function getList($param){
    $mainTable = $this->getTableName();
    $limit = 1;
    $order = 'desc';
    if (isset($param['order'])) {
      $order = $param['order'];
    }
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    $this->_select->order(['prepared_by_date' => $order]);
    return $this->getFinalResponse($limit);
  }
  public function getLastEnding($date = null) {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    if ($date) {
      $where = "date_format(`".$mainTable."`.`prepared_by_date`, '%Y-%m-%d') < date_format('".$date."', '%Y-%m-%d')";
      $this->_select->where($where);
    }
    $this->_select->order(['prepared_by_date' => 'desc']);
    $this->_select->limit(1);
    /* var_dump($this->getLastSqlQuery()); */
    $result = $this->getCollection();
    if ($result) {
        return $result[0]->getData();
    } else {
        return null;
    }
  }
  public function getLatest(){
    $this->resetQuery();
    $this->_select->order(['prepared_by_date' => 'desc']);
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0]->getData();
    } else {
      return null;
    }
  }
  public function getTrayreport($param)
  {
    $mainTable = $this->getTableName();
    $userprofile = $this->_userProfile->getTableName();
    $this->__join('profile_', $mainTable . '.prepared_by', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`prepared_by_date`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['search'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%m/%d/%Y') LIKE '%" . $param['search'] . "%' ) OR ";
      $where .= "((`" . $userprofile . "`.`first_name` LIKE '%" . $param['search'] . "%' ) OR (`" . $userprofile . "`.`last_name` LIKE '%" . $param['search'] . "%'))";
      $this->_select->where($where);
    }
    return $this->getFinalResponse($limit);
  }
  public function getLatestActivity() {
		$this->_select->order(['created_at' => 'desc']);
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
	}
}
?>