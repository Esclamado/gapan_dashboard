<?php 
namespace Lns\Gpn\Lib\Html\Datatable\Context;

class House extends \Lns\Sb\Lib\Html\Datatable\Datatable {

	protected $dataSesssion = 'house';
	protected $dataTitle = 'House/Building Management';
	protected $addButtonName = 'Add new house/building';
	protected $tableColumns = [
		[
			'name' => 'House/Building no.',
			'attr' => ''
		],
		[
			'name' => 'Chicken Capacity',
			'attr' => ''
		],
		[
			'name' => 'Action',
			'attr' => ''
		]
	];
    protected $addLink = 'house/action/add';
    protected $dataListLink = 'house/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

}
?>