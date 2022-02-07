<?php 
namespace Lns\Sb\Lib\Html\Datatable\Context;

class Roles extends \Lns\Sb\Lib\Html\Datatable\Datatable {

    protected $dataSesssion = 'roles';
    protected $dataTitle = 'Roles';
    protected $addButtonName = 'Add New Role';
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
            'name' => 'Action',
            'attr' => ''
        ]
    ];

    protected $addLink = 'roles/action/add';
    protected $dataListLink = 'roles/action/listing';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Sb\Lib\Entity\Db\Roles $Roles
    ){
        parent::__construct($Url, $Config);
        $this->_roles = $Roles;
    }

    protected function getRole(){
        $id = $this->_controller->getParam('id');
        return $this->_roles->getByColumn(['id'=>$id], 1);
    } 
}
?>