<?php 
namespace Lns\Gpn\Lib\Html;

class House extends \Of\Html\Context {

    protected $_house;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url, $Config);
        $this->_house = $House;
    }
    protected function getHouse() {
        $id = $this->_controller->getParam('id');
        return $this->_house->getByColumn(['id'=>$id], 1);
    }
}
?>