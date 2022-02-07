<?php
namespace Lns\Gpn\Lib\Entity\Db;

class OtherInventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

    protected $tablename = 'other_inventory';
    protected $primaryKey = 'id';

    const COLUMNS = [
        'id',
        'type',
        'last_ending',
        'created_at'
    ];

    public function installData() {
		$this->setDatas([
			'type' => 'Culled Birds',
			'last_ending' => 0,
        ])->__save();
        $this->setDatas([
			'type' => 'Sacks',
			'last_ending' => 0,
        ])->__save();
        $this->setDatas([
			'type' => 'Carton Trays',
			'last_ending' => 0,
        ])->__save();
        $this->setDatas([
			'type' => 'Plastic Trays',
			'last_ending' => 0,
		])->__save();
    }
    public function searchOtherInventory($param)
    {
        if (isset($param['search'])) {
            $like = $this->likeQuery(['type'], $this->escape($param['search']), 'other_inventory', true);
            $this->_select->where($like);
        }
        return $this->getCollection();
    }
}
?>