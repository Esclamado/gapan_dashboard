<?php 
namespace Lns\Sb\Lib\Html\Datatable\Context;

class Permissions extends \Lns\Sb\Lib\Html\Datatable\Datatable {

	protected $dataSesssion = 'permissions';
	protected $dataTitle = 'Permissions';
	protected $addButtonName = 'Add New Permission';
	protected $tableColumns = [
		[
			'name' => 'Id',
			'attr' => ''
		],
		[
			'name' => 'Name',
			'attr' => ''
		],
		[
			'name' => 'Description',
			'attr' => ''
		],
		[
			'name' => 'Code',
			'attr' => ''
		],
		[
			'name' => 'Created At',
			'attr' => ''
		],
		[
			'name' => 'Updated At',
			'attr' => ''
		],
		[
			'name' => 'Action',
			'attr' => ''
		]
	];
    protected $addLink = 'permissions/action/add';
    protected $dataListLink = 'permissions/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

}
?>