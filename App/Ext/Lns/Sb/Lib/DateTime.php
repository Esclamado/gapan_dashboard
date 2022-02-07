<?php 
namespace Lns\Sb\Lib;

class DateTime {
	
	protected $_time;
	
	public function __construct(){
		$this->_time = time();
	}
	
	public function setTime($time){
		$this->_time = $time;
		return $this;
	}
	
	public function getWeekStart(){
		$w = date('w', $this->_time);
		$d = date('j', $this->_time);
		
		for($i=$w; $i > 0; $i--){
			$d -= 1;
		}
		
		$day = $d;
		if($d <= 9){
			$day = "0".$d;
		}
		
		$date = date('Y-m', $this->_time).'-'.$day.' 00:00:00';
		return $date;
	}
	
	public function getWeekEnd(){
		$w = date('w', $this->_time);
		$d = date('j', $this->_time);
		
		for($i=$w; $i < 6; $i++){
			$d += 1;
		}
		
		$day = $d;
		if($d <= 9){
			$day = "0".$d;
		}
		
		$date = date('Y-m', $this->_time).'-'.$day.' 23:59:59';
		return $date;
	}
	
	public function getMonthStart(){
		$date = date('Y-m', $this->_time);
		return $date . '-00 00:00:00';
	}
	
	public function getMonthEnd(){
		$date = date('Y-m-t', $this->_time);
		return $date . ' 23:59:59';
	}
	
	public function getDayStart(){
		$date = date('Y-m-d', $this->_time);
		return $date . ' 00:00:00';
	}
	
	public function getDayEnd(){
		$date = date('Y-m-d', $this->_time);
		return $date . ' 23:59:59';
	}
	
	public function getDay($isWord=false){
		$format = 'd';
		if(!$isWord){
			$format = 'l';
		}
		$date = strtotime($this->_time);
		$string = date($format, $date);
		return $string;
	}
	public function getMonth($isShort=true){
		$format = 'M';
		if(!$isShort){
			$format = 'F';
		}
		
		$date = strtotime($this->_time);
		$string = date($format, $date);
		return $string;
	}
	public function getYear($isShort=false){
		$format = 'Y';
		if($isShort){
			$format = 'y';
		}
		
		$date = strtotime($this->_time);
		$string = date($format, $date);
		return $string;
	}
	
	public function getReadable($isHour = false){
		if($this->_time == '' || $this->_time == null || empty($this->_time)){
			return '';
		}
		
		$date = strtotime($this->_time);
		$string = date('M j, Y', $date);
		if($isHour){
			$string .= ' at ' . date('h:i A', $date);
		}
		return $string;
	}
	
	public function getReadableTime() {
		$date = strtotime($this->_time);
		return date('h:i A', $date);
	}
	
	public function getTime24Hours($time){
		$t = strtotime($time);
		return date('H:i:s', $t);
	}
	
	public function getTimestamp($date = null){
		if($date){
			return date('Y-m-d H:i:s', $date);
		}
		return date('Y-m-d H:i:s', $this->_time);
	}
	
	public function getDate($date = null){
		if($date){
			return date('Y-m-d H:i:s', $date);
		}
		return date('Y-m-d', $this->_time);
	}
	
	public function getTimeAmPmFromDecimal($TwoDecimalNUmber){
		list($hour, $min) = explode(':', (string)$TwoDecimalNUmber);
		$amPm = 'AM';
		if($hour >= 12){ $amPm = 'PM'; }	
		if($hour >= 13){ $hour -= 12; }	
		if($min > 59){ $min = 59; }	
		return $hour . ':' . $min . ' ' . $amPm;
	}
	
	public function getElapsedTime($datetime, $full = false, $mustConvert=true) {
		if($mustConvert){
			$datetime = date('Y-m-d H:i:s' ,$datetime);
		}
		
		$now = new \DateTime;
		$ago = new \DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
	
	/*
	*	this will convert date from different format
	*/
	public function convertFromToTimeStampFormat($date, $delimiter='/', $resultDelimiter='-', $currentFormat=['m','d','Y'], $expectedFormat=['Y','m','d']){
		$date = explode($delimiter, $date);
		
		$dateMergeWidthcurrentFormat = [];
		foreach($currentFormat as $key => $val) {
			$dateMergeWidthcurrentFormat[$val] = $date[$key];
		}
		
		$resultArray = [];
		foreach($expectedFormat as $key => $val) {
			$resultArray[] = $dateMergeWidthcurrentFormat[$val];
		}
		
		$resultstring = implode($resultDelimiter, $resultArray);
		
		return $resultstring;
	}
}