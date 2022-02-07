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
namespace Lns\Sb\Lib;
class Mailer  {

	
	/*
	*	holds an array of to or Cc or Bcc
	*	for later bulk mail
	*/
	protected $To = [];
	protected $Cc = [];
	protected $Bcc = [];
	
	/*
	*	sender email and name
	*/
	protected $from = ['name'=>'', 'email'=>''];
	
	/*
	*	email subject
	*/
	protected $subject = "";
	protected $message = "";
	
	/*
	*	the header of the email
	*/
	protected $headers = [
		'mime' 			=> 'MIME-Version: 1.0',
		'contentType' 	=> 'Content-type: text/html; charset=iso-8859-1',
		'xPriority' 	=> 'X-Priority: 3 (Normal)',
		'To' 			=> '',
		'From'			=> '',
		'returnPath' 	=> '',
		'Cc'			=> '',
		'Bcc'			=> '',
	];
	
	/*
	*	set the email mime version
	*/
	public function setMime($mime=null){
		if($mime){
			$this->headers['mime'] = $mime;
		}
		return $this;
	}
	
	/*
	*	set the email Content-type
	*/
	public function setContentType($contentType=null){
		if($contentType){
			$this->headers['contentType'] = $contentType;
		}
		return $this;
	}
	
	/*
	*	add a recipient name and email
	*	@param email | the email address of recipient
	*	@param name | the name of recipient
	*	@param addressType | the type of address where to add 
	*			to or Cc or Bcc
	*/
	public function addAddress($email, $name='', $addressType='To'){
		$email = [
			'name' => $name,
			'email' => $email,
		];
		
		$this->$addressType[] = $email;
		return $this;
	}
	
	/*
	*	setsender name and email
	*	@param email | the email address of sender
	*	@param name | the name of sender
	*/
	public function setFrom($email, $name=''){
		$this->from['name'] = $name;
		$this->from['email'] = $email;
		return $this;
	}
	
	/*
	*	set the header To:
	*	return an array of email to use on mail($to)
	*/
	protected function setHeaderTo(){
		if(count($this->To) > 0){
			$headerTo = [];
			$To = [];
			foreach($this->To as $toVal){
				$headerTo[] = $toVal['name'] . " <" . $toVal['email'] . ">";
				$To[] = $toVal['email'];
			}
			$this->headers['To'] = 'To: ' . implode(", ", $headerTo);
			return $To;
		} else {
			throw new \Exception('There is no recipient email address defined');
		}
	}
	
	/*
	*	set the header Cc or Bcc:
	*	@param type | Cc or Bcc
	*/
	protected function setHeaderCcOrBcc($type = 'Cc'){
		if(count($this->$type) > 0){
			$header = [];
			foreach($this->$type as $val){
				$header[] = $val['email'];
			}
			$this->headers[$type] = $type.': ' . implode(",", $header);
		} else {
			if(isset($this->headers[$type])){
				unset($this->headers[$type]);
			}
		}
	}
	
	/*
	*	set the subject of the email
	*/
	public function setSubject($subject){
		$this->subject = $subject;
		return $this;
	}
	
	/*
	*	set the message of the email
	*/
	public function setMessage($message){
		$this->message = $message;
		return $this;
	}
	
	/*
	*	send the email to recipient
	*	return bool 
	*/
	public function send(){
		$to = $this->setHeaderTo();
		$this->setHeaderCcOrBcc('Cc');
		$this->setHeaderCcOrBcc('Bcc');
		if($this->from['email'] != ''){
			$this->headers['From'] = 'From: '.$this->from['name'].' <'.$this->from['email'].'>';
			$this->headers['peplyTo'] = 'Reply-To: '.$this->from['name'].' <'.$this->from['email'].'>';
			$this->headers['returnPath'] = 'Return-Path: '.$this->from['name'].' <'.$this->from['email'].'>';
			$this->headers['xMailer'] = 'X-Mailer: PHP'. phpversion();
		}
		
		$to = implode(", ", $to);
		
		$head = implode("\r\n", $this->headers);
		return mail($to, $this->subject, $this->message, $head);
	}
}