<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Of\Db\Select;

class InflowOutflow extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

    protected $tablename = 'inflow';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type',
        'reference_id',
        'created_at'
    ];

    public $_inflow;
    public $_outflow;

    public function __construct(
        \Of\Http\Request $Request,
        $adapter=null
    ) {
        parent::__construct($Request,$adapter);
        $this->_inflow = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Inflow');
        $this->_outflow = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Outflow');
    }
    public function getReport($param) {
        $this->resetQuery();
        $inflowTable = $this->_inflow->getTableName();
        $outflowTable = $this->_outflow->getTableName();

        $page = $this->getParam('page');

		if(!$page){
			$page = 1;
        }
        
        $limit = 1;
        $order = 'desc';
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order'])) {
            $order = $param['order'];
        }
        $query = "SELECT ".$inflowTable.".* FROM ".$inflowTable." WHERE ".$inflowTable.".type = 1 UNION ALL SELECT ".$outflowTable.".* FROM ".$outflowTable." WHERE ".$outflowTable.".type = 1";
        $datas = $this->_adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        var_dump($datas);die;
        /* $datas = $this->setCollection($limit, $datas->toArray()); */
        /* var_dump($this->getLastSqlQuery());die; */

        /* return $datas; */
        
        return $this->getFinalResponse($limit);
    }
}
?>