<?php 
/**
*	Opoink, Inc., All rights reserved.
*	Opoink Framework. <http://opoink.com>
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Lns\Sb\Lib\Password;

class Password {

	protected $password;
	protected $passwordHash;
	
	public function setPassword($password=""){
		$this->password = $password;
		return $this;
	}
	
	public function setHash($passwordHash=""){
		$this->passwordHash =  $passwordHash;
		return $this;
	}
	
	/**
		PASSWORD_BCRYPT
		PASSWORD_BCRYPT
		PASSWORD_ARGON2_DEFAULT_MEMORY_COST
		PASSWORD_ARGON2_DEFAULT_TIME_COST
		ASSWORD_ARGON2_DEFAULT_THREADS
		PASSWORD_DEFAULT
	*/
	public function getHash($algo=PASSWORD_DEFAULT, $options=[]){
		return password_hash($this->password, $algo, $options);
	}
	
	/*
	*	return bool true | false
	*	return null if not set
	*/
	public function verify(){
		if(!$this->password || !$this->passwordHash){
			return null;
		}
		
		if (password_verify($this->password, $this->passwordHash)) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
	*	confirm password
	*	check if the password and confirm password match
	*	return bool 
	*/
	public function confirmPassword($password, $confirmPassword){
		/*
		*	set the first password to get the hashed pawword
		*/
		$this->password = $password;
		$this->passwordHash = $this->getHash();
		
		/*
		*	then set $this->password to $confirmPassword
		*	for verify() 
		*/
		$this->password = $confirmPassword;
		return $this->verify();
	}
	
	public static function generate($length=10) {
		$key = '';
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));
		
		$inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

		for($i=0; $i<$length; $i++)
		{
			$key .= $inputs{mt_rand(0,61)};
		}
		return $key;
	}



}