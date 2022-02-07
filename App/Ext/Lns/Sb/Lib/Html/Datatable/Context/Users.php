<?php 
namespace Lns\Sb\Lib\Html\Datatable\Context;

class Users extends \Lns\Sb\Lib\Html\Datatable\Datatable {

    protected $dataSesssion = 'user';
    protected $dataTitle = 'Users';
    protected $addButtonName = 'Add New User';
    protected $tableColumns = [
        [
            'name' => 'Id',
            'attr' => ''
        ],
        [
            'name' => 'Full Name',
            'attr' => ''
        ],
        [
            'name' => 'E-mail',
            'attr' => ''
        ],
        [
            'name' => 'Status',
            'attr' => ''
        ],
        [
            'name' => 'Action',
            'attr' => ''
        ]
    ];

    protected $addLink = 'users/create';
    protected $dataListLink = 'users/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    } 
}
?>