<?php
namespace Lns\Sb\Controller\Admin\Users\Address;

class Getstate extends \Lns\Sb\Controller\Controller {
	
	protected $_stateEntity;
	protected $_request;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Http\Request $Request,
		\Lns\Sb\Lib\Entity\Db\State $State
	){
		parent::__construct($Url,$Message,$Session);
		$this->_request = $Request;
		$this->_stateEntity = $State;
	}

	public function run(){
		$this->requireLogin();

		$region_code = $this->getPost('region_id');
		// $current_state = $this->getPost('current');

		$states = $this->_stateEntity
		->getStateByRegionId($region_code);

		
		$list = '';
		$option = '<option value=""> Any </option>';

		foreach($states as $key => $state){
			$selected = '';
			// if(strtolower($state['name']) === strtolower($current_state)){
			// 	$selected = 'selected';
			// }
			$count = $key + 1;
			$list .= '<li class="active-result" data-option-array-index="'.$count.'">'.$state->getData('name').'</li>';
			$option .= '<option data-code="'.$state->getData('code').'" value="'.$state->getData('id').'" '.$selected.'>'.$state->getData('name').'</option>';
		}
		$result['list'] = $list;
		$result['option'] = $option;
		$this->jsonEncode($result);	
		die;
	}
}