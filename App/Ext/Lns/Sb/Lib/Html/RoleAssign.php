<?php 
namespace Lns\Sb\Lib\Html;

class RoleAssign extends \Of\Html\Context {

    protected $_permissions;
    protected $_permission;
    protected $_roles;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Sb\Lib\Entity\Db\Roles $Roles,
        \Lns\Sb\Lib\Entity\Db\Permissions $Permissions,
        \Lns\Sb\Lib\Entity\Db\Permission $Permission
    ){
        parent::__construct($Url, $Config);
        $this->_roles = $Roles;
        $this->_permissions = $Permissions;
        $this->_permission = $Permission;
    }

    protected function getRole(){
        $id = $this->_controller->getParam('id');
        return $this->_roles->getByColumn(['id'=>$id], 1);
    }

    protected function getPermissions(){
        return $this->_permissions->getByColumn([], 0);
    }

    protected function getPermission($roleId, $permissionsId){
        return $this->_permission->getByColumn([
            'role_id' => $roleId,
            'permissions_id' => $permissionsId
        ], 1);
    }

    protected function isChecked($permission, $key){
        if($permission){
            $per = (int)$permission->getData($key);
            if($per == 1){
                return 'checked="checked"';
            }
        }
        return '';
    }
}
?>