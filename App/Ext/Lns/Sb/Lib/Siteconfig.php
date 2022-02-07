<?php
namespace Lns\Sb\Lib;

class Siteconfig {

	protected $file;
	protected $config = [];

	public function __construct(){
		$this->file = __DIR__ . DS . 'Site_Config.php';
		if(file_exists($this->file)){
			$this->config = include($this->file);
		}
	}

	public function getFile(){
		return $this->file;
	}

	public function setDatas($datas=[]){
		foreach($datas as $key => $val){
			$this->setData($key, $val);
		}
		return $this;
	}

	public function setData($key, $val){
		$this->config[$key] = $val;
		return $this;
	}

	public function getData($key=null, $default=null){
		if($key){
			if(isset($this->config[$key])){
				if(!empty($this->config[$key])){
					return $this->config[$key];
				}
			}
			return $default;
		} else {
			return $this->config;
		}
	}

	public function save(){
		$newConfig = '<?php' . PHP_EOL;
		$newConfig .= 'return ' . var_export($this->config, true) . PHP_EOL;
		$newConfig .= '?>';
		
		$_writer = new \Of\File\Writer();
		$_writer->setDirPath(__DIR__)
		->setData($newConfig)
		->setFilename('Site_Config')
		->setFileextension('php')
		->write();
	}
}

?>