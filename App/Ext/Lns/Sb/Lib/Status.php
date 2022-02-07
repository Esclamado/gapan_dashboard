<?php
namespace Lns\Sb\Lib; 

class Status {
	
	const ZERO = 0; /* Cancelled, Deleted */
	const ONE = 1; /* Active, read, approved, published */
	const TWO = 2; /* Disabled, Inactive, Unread, Pending, draft */
	const THREE = 3; /* Completed */
	const FOUR = 4; /* Arrived */
	
	protected $status;
	
	public function setStatus($status){
		$this->status = $status;
		return $this;
	}
	
	public function getUserStatusText(){
		switch ($this->status){
			case self::ZERO:
				$statusText = 'Unverified';
				break;
			case self::ONE:
				$statusText = 'Verified';
				break;
			case self::TWO:
				$statusText = 'Normal';
				break;
			default:
				$statusText = 'Normal';
		}
		return $statusText;
	}
	
	public function getTalkStatusText(){
		switch ($this->status){
			case self::ONE:
				$statusText = 'Active';
				break;
			case self::TWO:
				$statusText = 'Disabled';
				break;
			default:
				$statusText = 'Active';
		}
		return $statusText;
	}
	
	public function getDateStatusText(){
		return $this->getTalkStatusText();
	}
	
	public function getMessageStatusText(){
		switch ($this->status){
			case self::ONE:
				$statusText = 'Read';
				break;
			case self::ZERO:
				$statusText = 'Unread';
				break;
			default:
				$statusText = 'Unread';
		}
		return $statusText;
	}
}

?>