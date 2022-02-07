<?php 
namespace Lns\Gpn\Lib\Entity\Db;

class Trayinventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'tray_inventory';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'type_id',
        'in_return',
        'sorting',
        'marketing',
        'out_hiram',
        'prepared_by',
        'prepared_by_path',
        'prepared_by_date',
        'created_at'
    ];

    public function __construct(
		\Of\Http\Request $Request
	) {
		parent::__construct($Request);
    }
}