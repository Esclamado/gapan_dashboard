<?php
namespace Lns\Sb\Controller\Admin\Users\Address;

class Getcity extends \Lns\Sb\Controller\Controller {

	protected $_request;
	protected $_cityEntity;
		
		public function __construct(
			\Of\Http\Url $Url,
			\Of\Std\Message $Message,
			\Lns\Sb\Lib\Session\Session $Session,
			\Of\Http\Request $Request,
			\Lns\Sb\Lib\Entity\Db\City $City
		){
			parent::__construct($Url,$Message,$Session);
			$this->_request = $Request;
			$this->_cityEntity = $City;
	}

	public function run(){
		$this->requireLogin();
		
		$code = $this->getPost('state_id');
		$current_city = $this->getPost('current');


		$municipalities = $this->_cityEntity->getCityByProvinceCode($code);

		$option = '<option value=""> Any </option>';

		foreach($municipalities as $key => $municipality){
			$selected = '';
			if(strtolower($municipality->getData('id')) === strtolower($current_city)){
				$selected = 'selected';
			}
			if($code === '1339' || $code === '1374' || $code === '1375' || $code === '1376'){
				$option .= '<option value="'.$municipality->getData('id').'" data-postal="'.$municipality->getData('zip_code_zip_code').'" '.$selected.'>'.$municipality->getData('zip_code_city').' - '. $municipality->getData('name') .'</option>';
			}else{
				$option .= '<option value="'.$municipality->getData('id').'" data-postal="'.$municipality->getData('zip_code_zip_code').'" '.$selected.'>'. $municipality->getData('name') .'</option>';
			}
			
		}

		$this->jsonEncode($option);	

		die;
	}
}