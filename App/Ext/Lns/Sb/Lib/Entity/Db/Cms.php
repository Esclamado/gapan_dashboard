<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Cms extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    
  protected $tablename = 'cms';
  protected $primaryKey = 'id';
    
	const COLUMNS = [
		'id',
		'title',
		'page',
		'content',
		'created_at',
		'updated_at',
	];

  public function getTemplate() {
		return $this->getFinalResponse();
	}
	
	public function isExists($id, $page) {
		$this->resetQuery();
		$this->where('id', $id, '!=');
		$res = $this->getByColumn(['page' => $page], 1, null, false);
		return $res;
	}
}
