<?php 
namespace Lns\Gpn\Lib\Html\Datatable\Context;

class FeedsandMedicine extends \Lns\Sb\Lib\Html\Datatable\Datatable {

	protected $dataSesssion = 'feedsandmedicine';
	protected $dataTitle = 'Feeds and medicine consumption daily report';
	protected $addButtonName = 'Column Visibility';
	protected $tableColumns = [
		[
			'name' => 'Day',
			'attr' => ''
		],
		[
			'name' => 'Age',
			'attr' => ''
		],
		[
			'name' => 'Mortality',
			'attr' => ''
		],
		[
			'name' => 'Mortality Rate %',
			'attr' => ''
		],
		[
			'name' => 'End Population',
			'attr' => ''
		],
		[
			'name' => 'Egg Production',
			'attr' => ''
		],
		[
			'name' => 'Production Rate %',
			'attr' => ''
		],
		[
			'name' => 'Feeds/Bags',
			'attr' => ''
		]
	];
    protected $addLink = 'eggtype/action/add';
    protected $dataListLink = 'dailyhouseharvest/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

}
?>