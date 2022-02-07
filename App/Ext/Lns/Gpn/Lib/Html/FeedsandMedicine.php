<?php 
namespace Lns\Gpn\Lib\Html;

class FeedsandMedicine extends \Of\Html\Context {

    protected $_dailyhouseharvest;
    protected $_house;
    protected $_medicine;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine
    ) {
        parent::__construct($Url, $Config);
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
        $this->_medicine = $Medicine;
    }
    
    protected function getDailyHouseHarvest() {
        $id = $this->_controller->getParam('id');
        return $this->_dailyhouseharvest->getByColumn(['id'=>$id], 0);
    }
}
?>