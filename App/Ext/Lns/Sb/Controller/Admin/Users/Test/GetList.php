<?php
namespace Lns\Sb\Controller\Admin\Users\Test;

class GetList extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Emails';	
	protected $_ssp;
	protected $_db;
	protected $_context;

	public function run(){
		$this->requireLogin();
		$this->_context = $this->_di->get('Lns\Sb\Lib\Entity\ClassOverride\OfHtmlContext');
		$this->_ssp = $this->_di->get('Lns\Sb\Lib\SSP\SSP');

		/* DB table to use */
		$table = 'email_templates';

		/* Table's primary key */
		$primaryKey = 'id';
		/* 
			Array of database columns which should be read and sent back to DataTables.
			The `db` parameter represents the column name in the database, while the `dt`
			parameter represents the DataTables column identifier. In this case simple
			indexes 
		*/
		$columns = array(
			array( 'db' => 'id', 'dt' => 0 ),
			array( 'db' => 'template_name', 'dt' => 1 ),
			array( 'db' => 'created_at',  'dt' => 2 ),
			array( 'db' => 'updated_at',   'dt' => 3 ),
			array(
				'db'        => 'id',
				'dt'        => 4,
				'formatter' => function( $d, $row ) {
					$action = '<div class="btn-group table-action" role="group" aria-label="Second group">';
					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_context->getAdminUrl('/settings/templates/preview' . '/?email=' . $d).'"><i class="fa fa-eye"></i></a>';
					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_context->getAdminUrl('/settings/templates/edit' . '/?email=' . $d).'"><i class="fa fa-edit"></i></a>';
					$action .= '</div>';
					return $action;
				}
			),
		);


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

		echo json_encode(
			$this->_ssp->simple($_GET, $sql_details, $this->tablename, $primaryKey, $columns)
		);
	}
}
?>