<?php 
namespace Lns\Sb\Lib\Html\Datatable\Context;

class Cms extends \Lns\Sb\Lib\Html\Datatable\Datatable {

	protected $dataSesssion = 'cms';
	protected $dataTitle = 'Cms';
	protected $addButtonName = 'Add New Cms';
	protected $tableColumns = [
		[
			'name' => 'Id',
			'attr' => ''
		],
		[
			'name' => 'Title',
			'attr' => ''
		],
		[
			'name' => 'Page',
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
    protected $addLink = 'cms/action/add';
    protected $dataListLink = 'cms/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

}
?>