<?php
namespace Lns\Gpn\Controller\Admin\Eggtype\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Eggtype';

	protected $_eggtype;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype
	){
		parent::__construct($Url, $Message, $Session);
		$this->_eggtype = $Eggtype;
	}

	public function run(){
		$this->requireLogin();

        $id = (int)$this->getPost('id');
		$type_shortcode = $this->getPost('type_shortcode');
        $type = $this->getPost('type');

        if($id){
			$insert = $this->_eggtype->getByColumn(['id'=>$id]);
        }else{
			$insert = $this->_eggtype;
		}
			$insert->setData('type_shortcode', $type_shortcode);
			$insert->setData('type', $type);
			$id = $insert->__save();
			$redirectTo = '/eggtype/action/add/';
			
			$this->_url->redirect($redirectTo);
	}
}
?>