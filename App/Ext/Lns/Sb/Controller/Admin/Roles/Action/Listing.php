<?php
namespace Lns\Sb\Controller\Admin\Roles\Action;

class Listing extends \Lns\Sb\Controller\Admin\Datatable {
	
	protected $pageTitle = 'Permissions List';

	public function run(){
		$this->requireLogin();
		
		/* DB table to use */
		$table = 'roles';

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
			array( 'db' => 'name', 'dt' => 1 ),
			array( 'db' => 'description', 'dt' => 2 ),
			array(
				'db'        => 'id',
				'dt'        => 3,
				'formatter' => function( $d, $row ) {
					$action = '<div class="btn-group table-action" role="group" aria-label="Second group">';
					
					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_url->getAdminUrl('/roles/permission/assign' . '/id/' . $d).'">Assign Permission</a>';

					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_url->getAdminUrl('/roles/action/edit' . '/id/' . $d).'"><i class="fa fa-edit"></i></a>';

					$action .= '<a class="btn btn-outline-secondary delete-button" href="javascript:void(0)" data-id="'.$d.'" data-title="Delete Role" data-question="Are you sure you want to delete this role?" data-buttontext="Delete Now" data-action="'.$this->_url->getAdminUrl('/roles/action/delete').'" data-toggle="modal" data-target="#confirmModal"><i class="ti-trash"></i></a>';
					$action .= '</div>';
					return $action;
				}
			),
		);

		$this->createData($table, $primaryKey, $columns);
		die;
	}
}
?>