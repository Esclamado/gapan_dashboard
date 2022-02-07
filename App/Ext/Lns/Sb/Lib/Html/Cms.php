<?php 
namespace Lns\Sb\Lib\Html;

class Cms extends \Of\Html\Context {

    protected $_cms;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Sb\Lib\Entity\Db\Cms $Cms
    ){
        parent::__construct($Url, $Config);
        $this->_cms = $Cms;
    }

    protected function getCms(){
        $id = $this->_controller->getParam('id');
        return $this->_cms->getByColumn(['id'=>$id], 1);
    }
    protected function getByPageCode() {
        $page = $this->_controller->getParam('page');
        return $this->_cms->getByColumn(['page' => $page], 1);
    }
}
?>