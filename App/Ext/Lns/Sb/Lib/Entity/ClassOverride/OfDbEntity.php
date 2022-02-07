<?php 
namespace Lns\Sb\Lib\Entity\ClassOverride;

class OfDbEntity extends \Of\Db\Entity{
	
	public function customQuery($qry){
		return $this->__getQuery($qry);
	}

	/** Note: Just added 'previous_page', 'next_page', and 'pages' for pagination purposes.
	 * 	Since di pa pwedeng galawin directly yung \Of\Db\Entity class.
	 */

	protected $param;

    protected function getFinalResponse($limit=20){
		$page = $this->getParam('page');
		$count = 0;
		if(!$page){
			$page = 1;
		}

		$this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
		$count = $this->__getQuery($this->getLastSqlQuery())->getData('count');

		$this->_select->removeColumn('count');
		$this->_pagination->set($page, $count, $limit);
		
		$datas = $this->getCollection($limit, $this->_pagination->offset());
		
		/*$next_page = $count;
		$prev_page = 1;
		if($page > 1){
			$prev_page = $page - 1;
		}

		if($page < $count){
			$next_page = $page + 1;
		}*/

		$o = [
			'total_count' => $count,
			'total_page' => $this->_pagination->total_pages(),
			'previous_page' => $this->_pagination->prevp(1),
			'current_page' => $this->_pagination->currentPage(),
			'next_page' => $this->_pagination->nextp(1),
			'pages' => $this->_pagination->pages(),
			'datas' => $datas
		];
		return $o;
    }

	public function saveEntity($attributeSet){
		foreach($attributeSet as $key => $value){
			if(in_array($key, get_called_class()::COLUMNS) && $value){
				$this->setData($key, $value);
			}
		}

		return $this->__save();
	}

	public function getEntityByPk($pk){
		return $this->getByColumn([$this->primaryKey => $pk]);
	}
	/** Where $attributes is an array of key-value pairs. 
	 * 	Attributes not specified in entity's columns are ignored. */
	public function getEntityByAttributes($attributes){
		foreach($attributes as $key => $value){
			if(!in_array($key, $this::COLUMNS)){
				unset($attributes[$key]);
			}
		}
		$entity = $this->getByColumn($attributes);
		if($entity){
			return $entity;
		} else{
			return false;
		}
	}

	public function getEntities($orderBy = '', $limit = 20, $isCache = true){
		if($orderBy != '' && in_array($key, $this::COLUMNS)){
			$this->setOrderBy($orderBy);
		}
		$this->setIsCache($isCache);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$users = $this->getFinalResponse($limit);

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $users;
	}

	public function deleteEntityByPk($id){
		$entity = $this->getEntityByPk($id);
		if($entity){
			$this->delete([$this->primaryKey => $id]);
		} else{
			return false;
		}
	}

	public function deleteEntityByAttributes($attributes){
		$entity = $this->getEntityByAttributes($attributes);
		if($entity){
			return $this->delete([$this->primaryKey => $entity->getData($this->primaryKey)]);
		} else{
			return false;
		}
	}


	public function deleteAllEntityByAttributes($attr=[]){
		if(is_array($attr) && count($attr) > 0){
			$datas = $this->getByColumn($attr, 0);
			foreach($datas as $data){
				$data->delete();
			}
		}
	}
	public function sum($col = '*'){
		$this->_select->setComlumn(new \Zend\Db\Sql\Expression('SUM(' . $col . ')'), 'sum');
	}
}