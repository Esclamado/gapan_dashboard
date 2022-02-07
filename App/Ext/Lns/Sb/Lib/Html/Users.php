<?php 
namespace Lns\Sb\Lib\Html;

class Users extends \Of\Html\Context{

    protected $_users;
    protected $_users_data;
    protected $_request;
    protected $_roles;
    protected $_regions;

    public function __construct(
        \Of\Http\Url $Url,
        \Lns\Sb\Lib\Entity\ClassOverride\OfHttpRequest $Request,
        \Of\Config $Config,
        \Lns\Sb\Lib\Entity\Db\Roles $Roles, 
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\Region $Region
    ){
        parent::__construct($Url, $Config);
        // $this->_users = new \Lns\Sb\Lib\Entity\Db\Users($Request, $UserProfile, $Address, $Contact, $Attachments, $Roles);
        $this->_users = $Users;
        $this->_request = $Request;
        $this->_roles = $Roles;
        $this->_regions = $Region;
    }

    public function getRegions(){
        return $this->_regions->getRegions();
    }

    public function getUsers(){
        return $this->_users->getEntities();
    }

    public function getSingleUser(){
        $userId = $this->_request->getParam('userId');

        if($userId){
            $this->_users_data = $this->_users->getByColumn(['id' => $userId] , 1);
        } 
        // $this->_url->redirect('/users');
        return $this->_users_data;
    }

    protected function getRoles(){
        $datas = $this->_roles->getRoles();
        return $datas;
    }

    public function getLoggedInUser(){
        return $this->_users->getLoggedInUser();
    }

    protected function getFieldValue($data, $key){
        $val = null;
        if($data){
            $val = $data->getData($key);
        }
        if(!$val){
            if(isset($_SESSION['formfield'])){
                $ses = $_SESSION['formfield'];
                if(isset($ses[$key])){
                   $val = $ses[$key];
                   unset($_SESSION['formfield'][$key]);
                }
            }
        }

        return $val;
    }
}
?>