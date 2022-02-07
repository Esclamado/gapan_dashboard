<?php
namespace Lns\Sb\Controller\Admin\Users\Action;

class Listing extends \Lns\Sb\Controller\Admin\Datatable {
	
	protected $pageTitle = 'Users List';
	protected $_userProfile;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Sb\Lib\SSP\SSP $SSP,
		\Lns\Sb\Lib\DateTime $DateTime,
		\Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile
	){
		parent::__construct($Url,$Message,$Session,$SSP,$DateTime);
		$this->_userProfile = $UserProfile;
	}

	public function run(){
		$this->requireLogin();
		
		/* DB table to use */
		$table = 'user';

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
			array(
				'db'        => 'id',
				'dt'        => 1,
				'formatter' => function( $d, $row ) {
					$up = $this->_userProfile->getByColumn(['user_id' => $d], 1);
					
					$fullName = "";
					if($up){
						$fullName = $up->getData('first_name') . ' ' . $up->getData('last_name');
					}
					return $fullName;
				}
			),
			array( 'db' => 'email', 'dt' => 2 ),
			array( 'db' => 'status', 'dt' => 3 ),
			array(
				'db'        => 'id',
				'dt'        => 4,
				'formatter' => function( $d, $row ) {
					$action = '<div class="btn-group table-action" role="group" aria-label="Second group">';
					
					$action .= '<a class="btn btn-outline-secondary" href="'.$this->_url->getAdminUrl('/users/edit?userId=' . $d).'"><i class="fa fa-edit"></i></a>';

					$action .= '<a class="btn btn-outline-secondary delete-button" href="javascript:void(0)" data-id="'.$d.'" data-title="Delete Role" data-question="Are you sure you want to delete this user?" data-buttontext="Delete Now" data-action="'.$this->_url->getAdminUrl('/users/delete?userId=' . $d).'" data-toggle="modal" data-target="#confirmModal"><i class="ti-trash"></i></a>';
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