<?php 
namespace Lns\Sb\Lib;

class ValidateField {

    public function validateField($field){
		if($field != '' || $field != null){
			return true;
		} else {
			return false;
		}
	}

	public function validatePrice($number,$decimal=2){
		if(!$this->validateField($number)){
			return false;
		}

		$valid = false;
		$ints = explode('.', $number);
		if(count($ints) == 1 || count($ints) == 2){
			if (is_numeric($ints[0])){
				$valid = true;
				if(isset($ints[1])){
					if (!is_numeric($ints[1])){
						$valid = false;
					} 
					elseif(strlen($ints[1]) > $decimal) {
						$valid = false;
					}
				}
			}
		}
		return $valid;
    }
    
    public function validateEmail($email){
        if(!$this->validateField($email)){
            return false;
        }

        $valid = false;
        $split = explode('@', $email);
        if(count($split) == 2){
            $split2 = explode('.', $split[1]);
            if(count($split2) == 2){
                return true;
            } else{
                return false;
            }
        } else{
            return false;
        }
    }
}
?>