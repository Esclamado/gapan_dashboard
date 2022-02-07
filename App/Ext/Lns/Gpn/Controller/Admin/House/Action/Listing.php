<?php
namespace Lns\Gpn\Controller\Admin\House\Action;

class Listing extends \Lns\Sb\Controller\Admin\Datatable {
	
	protected $pageTitle = 'House/Building Management';

	public function run(){
		$this->requireLogin();
		
		/* DB table to use */
		$table = 'house';

		/* Table's primary key */
		$primaryKey = 'id';
		/* 
			Array of database columns which should be read and sent back to DataTables.
			The `db` parameter represents the column name in the database, while the `dt`
			parameter represents the DataTables column identifier. In this case simple
			indexes 
		*/
		$columns = array(
			array( 'db' => 'house_name', 'dt' => 0 ),
			array( 'db' => 'capacity', 'dt' => 1 ),
			array(
				'db'        => 'id',
				'dt'        => 2,
				'formatter' => function( $d, $row ) {
					$action = '<div class="btn-group table-action" role="group" aria-label="Second group">';
					
					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_url->getAdminUrl('/housebuilding/action/edit' . '/id/' . $d).'"><i class="fa fa-edit"></i></a>';
					$action .= '<a class="btn btn-outline-secondary delete-button" href="javascript:void(0)" data-id="'.$d.'" data-title="Delete Cms" data-question="Are you sure you want to delete this house/building?" data-buttontext="Delete Now" data-action="'.$this->_url->getAdminUrl('/housebuilding/action/delete').'" data-toggle="modal" data-target="#confirmModal"><i class="ti-trash"></i></a>';
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