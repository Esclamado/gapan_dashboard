<?php
namespace Lns\Gpn\Controller\Admin\House\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save House/Building';

	protected $_house;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Lns\Gpn\Lib\Entity\Db\House $House
	){
		parent::__construct($Url, $Message, $Session);
		$this->_house = $House;
	}

	public function run(){
		$this->requireLogin();

		$id = (int)$this->getPost('id');
		$chicken_pop_id = $this->getPost('chicken_pop_id');
        $house_name = $this->getPost('house_name');
        $capacity = $this->getPost('capacity');

		$msg = '';
		$msgtype = 'danger';

        if ($id) {
            $canUpdate = $this->checkPermission('MANAGEHOUSE', \Lns\Sb\Lib\Permission::UPDATE);
            if(!$canUpdate){
                $this->_message->setMessage('Your are not allowed to update house/building.', 'danger');
                $this->_url->redirect('/house/action/edit/id/' . $id);
            }
            $house = $this->_house->getByColumn(['id'=>$id], 1);
            if($house) {
                $entity = $house;
                $msg = 'House/building successfully updated.';
                $msgtype = 'success';
            } else {
                $msg = 'Cannot find the house/building you are trying to update.';
                $msgtype = 'danger';
            }
        } else {
            $canCreate = $this->checkPermission('MANAGEHOUSE', \Lns\Sb\Lib\Permission::CREATE);
            if (!$canCreate) {
                $this->_message->setMessage('Your are not allowed to create new house/building.', 'danger');
                $this->_url->redirect('/house');
            }
            $entity = $this->_house;
            $msg = 'House/building successfully saved.';
            $msgtype = 'success';
        }

        if ($entity) {
            $entity->setData('chicken_pop_id', $chicken_pop_id);
            $entity->setData('house_name', $house_name);
            $entity->setData('capacity', $capacity);
            $save = $entity->__save();
            if(!$save){
                $msg = 'Cannot save the house/building try again later.';
                $msgtype = 'danger';
                $redirectTo = '/house';
            } else {
                $redirectTo = '/house/action/edit/id/' . $save;
            }
        }
		$this->_message->setMessage($msg, $msgtype);
		$this->_url->redirect($redirectTo);
	}
}
?>