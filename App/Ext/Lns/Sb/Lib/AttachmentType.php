<?php
namespace Lns\Sb\Lib; 

class AttachmentType {
	
	const PROFILE_PHOTO = 1;
	
	public function getAttachmentTypeText($type){
		switch($type){
			case self::PROFILE_PHOTO:
				$text = 'Profile Photo'; 
				break;
		}
		
		return $text;
	}
}

?>