<?php 
namespace Lns\Sb\Lib\Lang;

class Lang {
	
	protected $type;
	protected $_lang;
	protected $langNotFound = 'Lang not found.';

	public function __construct($type='en'){
		$this->type = $type;
		$this->setLang($type);
	}

	public function setLang($type='en'){
		$targetFile = __DIR__ . '/lang/'.$type.'.php';
		if(file_exists($targetFile)){
			$this->_lang = include($targetFile);
		} else {
			$this->_lang = include(__DIR__ . '/lang/en.php');
		}
	}

	public function getLang($lang, $replacement=null){
		if(isset($this->_lang[$lang])){
			if($replacement){
				return $this->replaceString($this->_lang[$lang], $replacement);
			} else {
				return $this->_lang[$lang];
			}
		} else {
			return $langNotFound;
		}
	}

	protected function replaceString($subject, $replacement){
		return str_replace('{{var}}', $replacement, $subject);
	}
}

?>