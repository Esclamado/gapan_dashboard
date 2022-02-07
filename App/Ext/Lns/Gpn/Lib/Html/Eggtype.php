<?php 
namespace Lns\Gpn\Lib\Html;

class Eggtype extends \Of\Html\Context {

    protected $_eggtype;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype
    ){
        parent::__construct($Url, $Config);
        $this->_eggtype = $Eggtype;
    }

    protected function getEggtype(){
        $id = $this->_controller->getParam('id');
        return $this->_eggtype->getByColumn(['id'=>$id], 1);
    }

}
?>