<?php
namespace Lns\Sb\Controller\Admin;

class Datatable extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Datatable';	
	protected $_ssp;
	protected $_dateTime;
	protected $_db;
	protected $_context;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\SSP\SSP $SSP,
		\Lns\Sb\Lib\DateTime $DateTime
	){
		parent::__construct($Url,$Message,$Session);
		$this->_ssp = $SSP;
		$this->_dateTime = $DateTime;
	}

	protected function createData($table, $primaryKey, $columns){
		$path = ROOT.DS.'etc';
		$filename = 'database.php';
		$configPath = $path.DS.$filename;
		try {
			$this->_db = include($configPath);
		} catch (\Exception $e) {
			echo 'Caught exception: ' . $e->getMessage();
			die;
		}

		$this->tablename = $this->_db['table_prefix'].$table;

		/* SQL server connection information */
		$sql_details = array(
			'user' => $this->_db['username'],
			'pass' => $this->_db['password'],
			'db'   => $this->_db['database'],
			'host' => $this->_db['host']
		);

		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		* If you just want to use the basic configuration for DataTables with PHP
		* server-side, there is no need to edit below this line.
		*/

		$_SESSION['datatable'][strtolower($table)] = $_GET;

		echo json_encode(
			$this->_ssp->simple($_GET, $sql_details, $this->tablename, $primaryKey, $columns)
		);
	}

	protected function order( $request, $columns, $_ssp)
	{
		$order = '';
		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = $_ssp::pluck( $columns, 'dt' );
			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property


				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';
					if(empty($column['ds'])){
						$orderBy[] = '`'.$column['db'].'` '.$dir;
					} else {
						$orderBy[] = '`'.$column['ds'].'`.`'.$column['db'].'` '.$dir;
					}
				}
			}
			if ( count( $orderBy ) ) {
				$order = 'ORDER BY '.implode(', ', $orderBy);
			}
		}
		return $order;
	}

	protected function filter( $request, $columns, &$bindings ) {
		$globalSearch = array();
		$columnSearch = array();

		$str = $request['search']['value'];
		foreach($columns as $key => $val){
			if($val['searchable']){
				$binding = $this->_ssp->bind( $bindings, '%'.$str.'%', \PDO::PARAM_STR );
				$globalSearch[] = "`{$val['ds']}`.`{$val['db']}` LIKE " . $binding;
			}
		}

		/*$dtColumns = $this->_ssp->pluck( $columns, 'dt' );*/
		/*// Individual column filtering
		if ( isset( $request['columns'] ) ) {
			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];
				$str = $requestColumn['search']['value'];
				if ( $requestColumn['searchable'] == 'true' &&
				 $str != '' ) {
					$binding = $this->_ssp->bind( $bindings, '%'.$str.'%', \PDO::PARAM_STR );
					$columnSearch[] = "`".$column['db']."` LIKE ".$binding;
				}
			}
		}*/
		
		// Combine the filters into a single string
		$where = '';
		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}
		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}
		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}
		return $where;
	}
}
?>