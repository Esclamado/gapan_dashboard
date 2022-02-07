<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Lns\Gpn\Lib\Entity\Db\Eggtype;

class Price extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'price';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type_id',
        'price',
        'created_at',
        'updated_at'
    ];

    public $_egg_type;

    public function __construct(
        \Of\Http\Request $Request
      ) {
        parent::__construct($Request);
        $this->_egg_type = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Eggtype');
    }

    public function getEggPrice($id){
        $data = $this->getByColumn(['type_id'=>$id], 1);
        if($data){
            return $data;
        }else{
            return null;
        }
    }
    public function getList($param){
        $mainTable = $this->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['type'])) {
            $where = "type_id=" . $param['type'];
            $this->_select->where($where);
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        return $this->getFinalResponse($limit);
    }
    public function getLatestActivity() {
        $this->_select->order(['updated_at' => 'desc']);
        $this->_select->limit(1);
        $result = $this->getCollection();
        return $result;
    }
    public function getPriceList($param, $count = NULL) {
        $mainTable = $this->getTableName();
        $eggTypeTable = $this->_egg_type->getTableName();
        $limit = 1;
        $this->__join('egg_type_', $mainTable . '.type_id', 'egg_type', '', 'right', 'id', Eggtype::COLUMNS);
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            if ($param['order_by_column'] == 'type') {
                $this->_select->order([$eggTypeTable . "." . $param['order_by_column'] => $param['order_by']]);
            } else {
                $this->_select->order([$mainTable . "." . $param['order_by_column'] => $param['order_by']]);
            }
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`updated_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`updated_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        if (isset($param['type'])) {
            $this->_select->where($eggTypeTable . "." . "id = " . $param['type']);
        }
        if (isset($param['search'])) {
            $where = $this->likeQuery(["type_shortcode", "type"], $this->escape($param['search']), 'egg_type', true);
            $this->_select->where($where);
        }
        if ($count) {
            return $this->_egg_type->getCount($param);
        } else {
            return $this->getFinalResponse($limit);
        }
    }
}
?>
