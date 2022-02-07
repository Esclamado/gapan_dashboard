<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Notification extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const COLUMNS = [
		'id',
		'item_id',
		'owner_user_id',
		'type',
        'url',
		'data',
		'read_by_user_id',
		'isRead',
        'read_at',
		'created_at',
		'update_at',
	];
	

	protected $tablename = 'notifications';
	protected $primaryKey = 'id';
	protected $_session;

	protected $_userModel;

	public function __construct(
		\Of\Http\Request $Request
	){
		parent::__construct($Request);
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
		$this->_session = $this->_di->get('Lns\Sb\Lib\Session\Session');
	}
	
	public function saveNotification($type, $data, $url, $item_id, $sender_id){
		$this->requireLogin();
		$data['type'] = $type;
		$data['data'] = $data;
		$data['url'] = $url;
		$data['owner_user_id'] = $sender;
		$data['item_id'] = $item_id;
		$email->saveEntity($data);
	}
	public function setNotification($item_id, $owner_user_id, $type, $url, $data, $read_by_user_id) {
		/* var_dump($item_id); */
		$this->setData('item_id', $item_id);
		$this->setData('owner_user_id', $owner_user_id);
		$this->setData('type', $type);
		$this->setData('url', $url);
		$this->setData('data', $data);
		$this->setData('read_by_user_id', $read_by_user_id);
		$this->__save();

		/* $data['item_id'] = $item_id;
		$data['owner_user_id'] = $owner_user_id;
		$data['type'] = $type;
		$data['url'] = $url;
		$data['data'] = $data;
		$data['read_by_user_id'] = $read_by_user_id;
		
		$this->saveEntity($data); */
	}
	public function getNotifications(){
		$this->setOrderBy('created_at');
		$this->setIsCache(true);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$notifications = $this->getFinalResponse();

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $notifications;
	}

    public function readNotification($notif_id,$user_id){	
        $this->requireLogin();
		$data['id'] = $notif_id;

		$notification = $this->getByColumn(['id' => $notif_id]);
		$current_reader = $notification->getData('read_by_user_id');

		/*
			, -> default value
			,1, -> set another reader
			,1,2, -> set another reader
		*/
		$data['read_by_user_id'] = $current_reader.''.$user_id.',';
		$this->saveEntity($data);
	} 
	
	public function getUserNotifications($page, $limit = 10){
		$this->resetQuery();
		
		$loggedInUser = $this->_session->getLogedInUser();
		if($loggedInUser && $this->_userModel->getByColumn(['id' => $loggedInUser])){
			$this->_select->where("read_by_user_id = $loggedInUser");

			$this->_select->count();
			$count = $this->__getQuery($this->getLastSqlQuery())->getData('count');
			$this->_select->removeColumn('count');
	
			if($count){
				$this->resetQuery();
				$this->_select->where("read_by_user_id = $loggedInUser");
				$this->setOrderBy('created_at', 'DESC');
				$this->_pagination->set($page, $count, $limit);

				return [
					'pagination' => $this->_pagination,
					'totalCount' => $count,
					'notifications' => $this->getCollection($limit, $this->_pagination->offset()),
				];
			} else{
				return false;
			}
		}

		return false;
	}

	public function getUnreadNotifCount(){
		$this->resetQuery();

		$loggedInUser = $this->_session->getLogedInUser();
		if($loggedInUser && $this->_userModel->getByColumn(['id' => $loggedInUser])){
			$this->_select->where("read_by_user_id = $loggedInUser AND read_at IS NULL");

			$this->_select->count();
			$count = $this->__getQuery($this->getLastSqlQuery())->getData('count');
			$this->_select->removeColumn('count');

			return $count;
		}

		return 0;
	}
}
