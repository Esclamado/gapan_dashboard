<?php 
namespace Lns\Sb\Lib\Entity\ClassOverride;

class OfHtmlContext extends \Of\Html\Context{
	
	public function __construct(
		\Of\Http\Url $Url,
		\Of\Config $Config
	){
		parent::__construct($Url, $Config);
		
		$this->_di = \DI\ContainerBuilder::buildDevContainer();
        $this->_request = $this->_di->get('Lns\Sb\Lib\Entity\ClassOverride\OfHttpRequest');
	}

	public function getUrl($path='', $param=array()){
		return $this->_url->getUrl($path, $param);
	}

	public function getAdminUrl($path='', $param=array()){
		return $this->_url->getAdminUrl($path, $param);
	}

}