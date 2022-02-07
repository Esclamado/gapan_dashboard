<?php
namespace Lns\Sb\Lib; 

class Userrole {
	
	const SUPER_ADMIN = 1;
	const ADMIN = 2;
	const USER = 3;
	
	public function getRoleText($role){
		switch($role){
			case self::SUPER_ADMIN:
				$text = 'Super Admin'; 
				break;
			case self::ADMIN:
				$text = 'Admin'; 
				break; 
			case self::USER:
				$text = 'User'; 
				break; 
		}
		return $text;
	}
}
?>