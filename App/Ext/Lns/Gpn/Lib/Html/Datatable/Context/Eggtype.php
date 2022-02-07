<?php 
namespace Lns\Gpn\Lib\Html\Datatable\Context;

class Eggtype extends \Lns\Sb\Lib\Html\Datatable\Datatable {

	protected $dataSesssion = 'eggtype';
	protected $dataTitle = 'Egg Type';
	protected $addButtonName = 'Add New Type';
	protected $tableColumns = [
		[
			'name' => 'Id',
			'attr' => ''
		],
		[
			'name' => 'Short Code',
			'attr' => ''
		],
		[
			'name' => 'Type',
			'attr' => ''
		],
		[
			'name' => 'Created At',
			'attr' => ''
		],
		[
			'name' => 'Action',
			'attr' => ''
		]
	];
    protected $addLink = 'eggtype/action/add';
    protected $dataListLink = 'eggtype/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

}
?>