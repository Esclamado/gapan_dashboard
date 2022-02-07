<?php 
namespace Lns\Sb\Lib\Html;

class Permissions extends \Of\Html\Context {

    protected $_permissions;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Sb\Lib\Entity\Db\Permissions $Permissions
    ){
        parent::__construct($Url, $Config);
        $this->_permissions = $Permissions;
    }

    protected function getPermission(){
        $id = $this->_controller->getParam('id');
        return $this->_permissions->getByColumn(['id'=>$id], 1);
    }
}
?>