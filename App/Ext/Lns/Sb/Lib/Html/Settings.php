<?php 
namespace Lns\Sb\Lib\Html;

class Settings extends \Of\Html\Context {

    protected $_roles;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

    protected function getConfig($key=null){
    	return $this->_controller->_siteConfig->getData($key);
    }
}
?>