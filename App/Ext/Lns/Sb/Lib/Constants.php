<?php
namespace Lns\Sb\Lib; 

class Constants {
	
	/* defaults */
	const SITE_NAME = 'Base';
	const PAGE_LIMIT = 20;

	/* user privacy */
	const USER_PRIVACY_ACTIVE = 1;
	const USER_PRIVACY_DISABLED = 0;
	
	/* contact type */
	const CONTACT_TYPE_HOME = 'home';
	const CONTACT_TYPE_MOBILE = 'mobile';
	const CONTACT_TYPE_OFFICE = 'office';
	
	/* is sponsor */
	const IS_SPONSOR = 1; /* yes */
	const IS_NOT_SPONSOR = 0; /* no */
	
	/* yes on no */
	const YES = 'Yes';
	const NO = 'No';
	
	/* local or internatinal user */
	const LOCAL = 'local';
	const INTERNATIONAL = 'international';
	
	/* announcement satus */
	const ANNOUNCEMENT_DRAFT = 0; /* saved as draft */
	const ANNOUNCEMENT_PUBLISHED = 1; /* publish */
	
	public static function getSponsorTypeArray(){
		return [0 => 'bronze',1 => 'silver',2 => 'gold',3 => 'platinum',];
	}
	

	public static function getAnnouncementText($val){
		if($val == self::ANNOUNCEMENT_DRAFT){
			return 'Drafted';
		}
		elseif($val == self::ANNOUNCEMENT_PUBLISHED){
			return 'Published';
		} else {
			return 'Drafted';
		}
	}

}

?>