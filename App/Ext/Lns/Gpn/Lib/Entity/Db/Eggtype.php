<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Eggtype extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'egg_type';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
      'id',
      'type_shortcode',
      'type',
      'created_at'
  ];
  public function installData() {
    $this->setDatas([
        'type_shortcode' => 'NV',
        'type' => 'No Value',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'PW',
        'type' => 'Peewee',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'XS',
        'type' => 'Extra Small',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'S',
        'type' => 'Small',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'M',
        'type' => 'Medium',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'L',
        'type' => 'Large',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'XL',
        'type' => 'Extra Large',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Jumbo',
        'type' => 'Jumbo',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Super Jumbo',
        'type' => 'Super Jumbo',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Dirty',
        'type' => 'Dirty',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Bad Crack',
        'type' => 'Bad Crack',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Soft Shell',
        'type' => 'Soft Shell',
    ])->__save();
    $this->setDatas([
        'type_shortcode' => 'Waste',
        'type' => 'Waste',
    ])->__save();
  }
  public function getEggType($id){
    $data = $this->getbyColumn(['id'=>$id], 1);
    if($data){
        return $data;
    }else{
        return null;
    }
}
    public function searchEgg($param){
        
        if (isset($param['search'])) {
            $like = $this->likeQuery(['type_shortcode', 'type'], $this->escape($param['search']), 'egg_type', true);
            $this->_select->where($like);
        }
        return $this->getCollection();
    }
    public function getList($param){
        $mainTable = $this->getTableName();
        $limit = 1;
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
        if (isset($param['type'])) {
            $where = "id=" . $param['type'];
            $this->_select->where($where);
        }
        if (isset($param['search'])) {
            $where = $this->likeQuery(['type_shortcode', 'type'], $this->escape($param['search']), 'egg_type', true);
            $this->_select->where($where);
        }
        return $this->getFinalResponse($limit);
    }
    public function getCount($param) {
        $mainTable = $this->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['type'])) {
            $this->_select->where($mainTable . "." . "id = " . $param['type']);
        }
        if (isset($param['search'])) {
            $where = $this->likeQuery(["type_shortcode", "type"], $this->escape($param['search']), 'egg_type', true);
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
}
?>