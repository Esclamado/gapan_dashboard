<?php 
namespace Lns\Sb\Lib\Html\Datatable\Context;

class Emailtemplate extends \Lns\Sb\Lib\Html\Datatable\Datatable {

    protected $dataSesssion = 'email_templates';
    protected $dataTitle = 'Email Templates';
    protected $addButtonName = 'Add New Template';
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
            'name' => 'Code',
            'attr' => ''
        ],
        [
            'name' => 'Subject',
            'attr' => ''
        ],
        [
            'name' => 'From Name',
            'attr' => ''
        ],
        [
            'name' => 'From Email',
            'attr' => ''
        ],
        [
            'name' => 'Action',
            'attr' => ''
        ]
    ];

    protected $addLink = 'settings/templates/create';
    protected $dataListLink = 'settings/templates/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    } 
}
?>