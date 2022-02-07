<?php 
namespace Lns\Sb\Lib\Entity\ClassOverride;

class OfHttpRequest extends \Of\Http\Request{
	
	public function getIsAjax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}

	public function getUrlReferer(){
        if(isset($this->server['HTTP_REFERER'])){
            return $this->server['HTTP_REFERER'];
        } else{
            return null;
        }
    }

    public function getFile($param=null){
		if($param){
			if(isset($_FILES[$param])){
				return $_FILES[$param];
			} else {
				return null;
			}
		} else {
			return $_FILES;
		}
	}
}