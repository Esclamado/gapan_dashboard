<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Lns\Sb\Lib\Entity\Db\Address;
use Lns\Sb\Lib\Entity\Db\UserProfile;

class Orders extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'orders';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'user_id',
        'transaction_id',
        'order_status',
        'payment_status',
        'mode_of_payment',
        'balance_credit_approved',
        'balance_credit_approved_by',
        'balance_credit_approved_date',
        'total_price',
        'discount',
        'note',
        'feedback',
        'date_to_pickup',
        'decline_resolved',
        'walk_in_created_by',
        'approved_by',
        'prepared_by',
        'prepared_by_path',
        'prepared_by_date',
        'checked_by',
        'checked_by_path',
        'checked_by_date',
        'for_release',
        'date_paid',
        'created_at',
        'updated_at'
    ];
    public $_userProfile;
    public $_users;
    protected $_siteConfig;
    protected $_mailer;
    protected $_paymenthistory;
    protected $_address;
    
    public function __construct(
        \Of\Http\Request $Request
    ) {
        parent::__construct($Request);
        $this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_users = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
        $this->_mailer = $this->_di->get('\Of\Std\Mailer');
        $this->_paymenthistory = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Paymenthistory');
        $this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
    }

    public function getList($param, $userId = null) {
        $mainTable = $this->getTableName();
        $limit = 1;
        $order = 'desc';
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order'])) {
            $order = $param['order'];
        }
        if ($userId) {
            $where = "user_id=" . $userId;
            /* $this->_select->where($where); */
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(" . $where . ") AND (date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            /* $this->_select->where($where); */
        }
        if (isset($param['order_status'])) {
            $where = "(" . $where . ") AND (order_status=" . $param['order_status'] . ")";
            /* $this->_select->where($where); */
            /* if ($param['order_status'] != 5 && $param['order_status'] != 6) {
                $where = "order_status=" . $param['order_status'];
                $this->_select->where($where);
            } */
        }
        if (isset($param['paymentIds'])) {
            $paymentIds = explode(',', $param['paymentIds']);
            $withPaymentId = [];
            foreach ($paymentIds as $paymentId) {
                $withPaymentId[] = "`" . $mainTable . "`.`mode_of_payment` = " . $paymentId;
            }
            $where = "(" . $where . ") AND (" . implode(" OR ", $withPaymentId) . ")";
            /* $this->_select->where($where); */
        }
        if ($where) {
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => $order]);
        return $this->getFinalResponse($limit);
    }
    public function getLatestTransactions($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => 'desc']);
        $this->_select->limit(10);
        $result = $this->getCollection();
        return $result;
    }
    public function getCount(){
        $result = $this->getCollection();
        $getCount = count($result);
        return $getCount;
    }

    public function orderNumber(){
        $this->resetQuery();
        /* $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y') = date_format(NOW(), '%Y')";
        $where = "id = 1"; */
        /* $this->_select->where($where); */
        /* var_dump('hello wins'); die;
        $data = $this->getCollection();
        var_dump($data); die;
        $result = count($data);
        if($result){
            return $result;
        } */
        $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y') = date_format(NOW(), '%Y')";
        $this->_select->where($where);
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if($count){
            $totalCount = $count->getData('count');
        }
        return $totalCount;
    }

    public function getOrdersforapproval($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("`".$mainTable."`.`order_status` = 1");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if ($count) {
            $totalCount = $count->getData('count');
        }
        return $totalCount;
        /* $where = "order_status = 1";
        $this->_select->where($where);
        $result = $this->getCollection();
        $getCount = count($result);
        if ($getCount) {
            return $getCount;
        } else {
            return null;
        } */
    }
/*     public function getCollectiblesamount($type){
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $from = date('Y') . '-01-01';
        $to = date('Y-m-d');
        $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND ";
        $where .= "order_status != 7 AND decline_resolved != 1 AND order_status != 4";

        if ($type == 1) {
            $where = "order_status != 7 AND decline_resolved != 1 AND order_status != 4 AND ";
            $where .= "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d'))";
        }
        $this->_select->where($where);
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (int) $data->getData('total_price');
            }
        }
        return $count;
    } */

    public function getCollectiblefullpayment($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`total_price`)"), 'sum_total_price');
        $this->_select->where("`" . $mainTable . "`.`mode_of_payment` = 1 AND `" . $mainTable . "`.`order_status` > 1 AND `" . $mainTable . "`.`order_status` < 4");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $result = $this->__getQuery($this->getLastSqlQuery());
        $sum_total_price = 0;
        if ($result) {
            $sum_total_price = $result->getData('sum_total_price');
        }
        return $sum_total_price;
    }
    public function getCollectiblebalance($param)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("`".$mainTable."`.`mode_of_payment` = 3 AND `".$mainTable."`.`order_status` > 1 AND `".$mainTable."`.`order_status` <= 4");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        $count = 0;
        foreach ($datas as $data) {
            if ($data->getData('order_status') < 3) {
                $count += (float) $data->getData('total_price');
            } else {
                $paymenthistorys = $this->_paymenthistory->getByColumn(['order_id'=> $data->getData('id')],0);
                if($paymenthistorys){
                    $payment = 0;
                    foreach ($paymenthistorys as $paymenthistory) {
                        $payment += (float)$paymenthistory->getData('payment');
                    }
                    $count += (float)$data->getData('total_price') - (float)$payment;
                }
            }
        }
        return $count;
    }
    public function getCollectiblecredit($type)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("`".$mainTable."`.`mode_of_payment` = 2 AND `".$mainTable."`.`order_status` > 1 AND `".$mainTable."`.`order_status` <= 4 AND `".$mainTable."`.`payment_status` = 0");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        /* var_dump($this->getLastSqlQuery()); */
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (float) $data->getData('total_price');
            }
        }
        return $count;
    }
/*     public function getFullypaidorders($type)
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $from = date('Y') . '-01-01';
        $to = date('Y-m-d');
        $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND ";
        $where .= "order_status = 4";
        if ($type == 1) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d')) AND ";
            $where .= "order_status = 4";
        }
        $this->_select->where($where);
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (int) $data->getData('total_price');
            }
        }
        return $count;
    } */
/*     public function getTotalrevenue($type)
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $from = date('Y') . '-01-01';
        $to = date('Y-m-d');
        $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND ";
        $where .= "order_status != 7 AND decline_resolved != 1";

        if ($type == 1) {
            $where = "order_status != 7 AND decline_resolved != 1 AND ";
            $where .= "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d'))";
        }
        $this->_select->where($where);
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (int) $data->getData('total_price');
            }
        }
        return $count;
    } */
    public function getPurchaseOrders($param){
        $mainTable = $this->getTableName();
        $limit = 1;
        $order = 'desc';
        $where = null;
        if (isset($param['orderstatus'])){
            $where = "order_status=" . $param['orderstatus'];
            /* $this->_select->where($where); */
        }
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order'])) {
            $order = $param['order'];
        }
        if (isset($param['for_release'])) {
            $where = "(". $where .") AND (for_release=" . $param['for_release'] . ")";
            /* $this->_select->where($where); */
        }else{
            $where = "(" . $where . ") AND (for_release=0)";
            /* $this->_select->where($where); */
        }
        /*         if (isset($param['mode_of_payment'])) {
            $where = "mode_of_payment=" . $param['mode_of_payment'];
            $this->_select->where($where);
        } */
        if (isset($param['from']) && isset($param['to'])) {
            if (isset($param['orderstatus']) && ($param['orderstatus'] == 1 || $param['orderstatus'] >= 4)) {
                $where = "(" . $where . ") AND (date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            } else {
                $where = "(" . $where . ") AND (date_format(`" . $mainTable . "`.`date_to_pickup`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`date_to_pickup`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            }
            /* $where = "(" . $where . ") AND (date_format(`" . $mainTable . "`.`date_to_pickup`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`date_to_pickup`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))"; */
        }
        if (isset($param['paymentIds'])) {
            $paymentIds = explode(',', $param['paymentIds']);
            $withPaymentId = [];
            foreach ($paymentIds as $paymentId) {
                $withPaymentId[] = "`" . $mainTable . "`.`mode_of_payment` = " . $paymentId;
            }
            $where = "(" . $where . ") AND (" . implode(" OR ", $withPaymentId) . ")";
            /* $this->_select->where($where); */
        }
        if($where){
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => $order]);
        return $this->getFinalResponse($limit);
    }
    public function cancelOrder(){
        $this->resetQuery();
        $where = "date_format(date_to_pickup, '%Y-%m-%d') < date_format(now(), '%Y-%m-%d') AND ";
        $where .= "(order_status = 2 OR order_status = 3)";
        $this->_select->where($where);
        $datas = $this->getCollection();
        if($datas){
            return $datas;
        }
    }
    public function getOrderslisting($param){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $userprofile = $this->_userProfile->getTableName();
        $limit = 1;
        $this->__join('profile_', $mainTable . '.user_id', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        if (isset($param['payment_status'])) {
                $where = "`" . $mainTable . "`.`payment_status` = " . $param['payment_status'];
                $this->_select->where($where);
        }
        if (isset($param['mode_of_payment'])) {
            $where = "order_status != 1 AND payment_status != 1 AND `" . $mainTable . "`.`mode_of_payment` = " . $param['mode_of_payment'];
            $this->_select->where($where);
        }
        if (isset($param['order_status'])) {
            if ($param['order_status']==1) {
                $where = "`" . $mainTable . "`.`order_status` = " . $param['order_status'];
                $this->_select->where($where);
            } else {
                /* $where = "payment_status = 1 AND `" . $mainTable . "`.`order_status` = " . $param['order_status']; */
                $where = "`" . $mainTable . "`.`order_status` = " . $param['order_status'];
                $this->_select->where($where);
            }
        }
        if (isset($param['amount'])) {
            $where = "`" . $mainTable . "`.`total_price` = " . $param['amount'];
            $this->_select->where($where);
        }
        if (isset($param['user_id'])) {
            $where = "`" . $mainTable . "`.`user_id` = " . $param['user_id'];
            $this->_select->where($where);
        }
        if (isset($param['search'])) {
            /*             $where = $this->likeQuery(['transaction_id', 'total_price', 'date_to_pickup'], $this->escape($param['search']), 'orders', true); */
            $where = "((`" . $mainTable . "`.`transaction_id` LIKE '%" . $param['search'] . "%' ) OR (`" . $mainTable . "`.`total_price` LIKE '%" . $param['search'] . "%') OR (`" . $mainTable . "`.`date_to_pickup` LIKE '%" . $param['search'] . "%')) OR ";
			$where .= "((`" . $userprofile . "`.`first_name` LIKE '%" . $param['search'] . "%' ) OR (`" . $userprofile . "`.`last_name` LIKE '%" . $param['search'] . "%'))";
            $this->_select->where($where);
        }
        return $this->getFinalResponse($limit);
    }
    public function getOverallsales($param, $getcount = null) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $limit = 1;
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("COUNT(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d'))"), 'total_orders');
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        /*  $this->_select->where("`" . $mainTable . "`.`order_status` = 4 AND `" . $mainTable . "`.`payment_status` = 1"); */
        $this->_select->where("`" . $mainTable . "`.`order_status` > 1 AND `" . $mainTable . "`.`order_status` <= 4");
        /* $this->_select->group('grouped_date'); */
        $this->sum('total_price');
        if ($getcount) {
            $totalCount = 0;
            $this->_select->limit(1);
            $count = $this->__getQuery($this->getLastSqlQuery());
            if ($count) {
                $totalCount = (int)$count->getData('total_orders');
            }
            return $totalCount;
        } else {
            $this->_select->group('grouped_date');
            return $this->getFinalResponse($limit);
        }
    }
    public function getCollectibleAmount($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("`" . $mainTable . "`.`order_status` > 1 AND `" . $mainTable . "`.`order_status` <= 4");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        return $this->getCollection();
    }
    public function countPendingOrders($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("`" . $mainTable . "`.`order_status` = 1");
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        return $this->getCollection();
    }
    public function getOverall(){
        $this->resetQuery();
        $where = "order_status = 1";
        $this->_select->where($where);
        $result = $this->getCollection();
        $pending_orders = count($result);

        $this->resetQuery();
        $where = "mode_of_payment = 1";
        $this->_select->where($where);
        $datas = $this->getCollection();
        $total_price = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $total_price += (int) $data->getData('total_price');
            }
        }

        $this->resetQuery();
        $where = "payment_status = 0";
        $this->_select->where($where);
        $result = $this->getCollection();
        $payment_status = count($result);

        $this->resetQuery();
        $where = "payment_status = 0";
        $this->_select->where($where);
        $datas = $this->getCollection();
        $collectibles_amount = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $collectibles_amount += (int) $data->getData('total_price');
            }
        }

        return $record = array(
            'fully_paid_orders'=> $total_price,
            'pending_orders' => $pending_orders,
            'collectibles' => $payment_status,
            'collectibles_amount' => $collectibles_amount
        );
    }
    public function getSaleseggsize($param, $getcount = null){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $limit = 1;
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
        $where = "`". $mainTable . "`.`order_status`= 4";
        $this->_select->where($where);
        /* $this->_select->group('created_at'); */
        $this->_select->group('grouped_date');

        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $data = $this->getCollection();
        if ($getcount) {
            if ($data) {
                return count($this->getCollection());
            } else {
                return 0;
            }
        } else {
            if ($data) {
                return $this->getFinalResponse($limit);
            } else {
                return null;
            }
        }
    }
    public function getOrderbydate($date){
        $this->resetQuery();
        $where = "date_format(created_at, '%Y-%m-%d') = date_format('".$date."', '%Y-%m-%d') AND order_status = 4";
        $this->_select->where($where);
        $data = $this->getCollection();
        return $data;
    }
    public function getLatestActivity($order_status = null, $mode_of_payment = null) {
        if ($order_status) {
            $this->_select->where('order_status = ' . $order_status);
        }
        if ($mode_of_payment) {
            $this->_select->where('mode_of_payment = ' . $mode_of_payment);
        }
        $this->_select->order(['updated_at' => 'desc']);
        
        $this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
    }
    public function getPendingcount(){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("order_status = 1");
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if($count){
            $totalCount = $count->getData('count');
        }
        return $totalCount;
    }
    public function getBalancecount()
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("mode_of_payment = 3 AND order_status > 1 AND payment_status = 0");
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if($count){
            $totalCount = $count->getData('count');
        }
        return $totalCount;
    }
    public function getCreditcount()
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("mode_of_payment = 2  AND order_status > 1 AND payment_status = 0");
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if($count){
            $totalCount = $count->getData('count');
        }
        return $totalCount;
    }
    public function getCompletedcount()
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->where("order_status = 4 AND payment_status = 1");
        $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $count = $this->__getQuery($this->getLastSqlQuery());
        $totalCount = 0;
        if($count){
            $totalCount = $count->getData('count');
        }
        return $totalCount;
    }
    public function getSalesByDate($date) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d') AND (`" . $mainTable . "`.`order_status` = 4 OR `" . $mainTable . "`.`date_paid` IS NOT NULL)";
        $this->_select->where($where);
        $result = $this->getCollection();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }
    public function sendEmailorderstatus($id, $siteConfig, $template_code, $orderId)
    {
        $trans_num = $this->getByColumn(['id'=> $orderId], 1);
        if($trans_num){
            $trans_id = $trans_num->getData('transaction_id');
        }
        $this->_siteConfig = $siteConfig;
        $d = $this->_users->getByColumn(['id' => $id], 1);
        $result = [
            'error' => 1,
            'message' => ''
        ];
        if ($d) {
            $email = $d->getData('email');
            $_mailTemplate = $this->_di->get('Lns\Sb\Lib\Entity\Db\MailTemplate');
            $mailTpl = $_mailTemplate->getByColumn(['template_code' => $template_code], 1);
            /* $activations = $this->_activation->getByColumn(['user_id' => $id], 1); */
            /* $activationcode = $activations->getData('activation_code'); */

            if ($mailTpl) {
                //   $fullname = $d->getData('fullname');
                $messageBody = $mailTpl->getData('template');
                $messageBody = str_replace('{{name}}', $email, $messageBody);
                $messageBody = str_replace('{{transaction_id}}', $trans_id, $messageBody);
                ob_start();
                include(ROOT . DS . 'App/Ext/Lns/Sb/View/Template/mail/email_template.phtml');
                $tplHtml = ob_get_contents();
                ob_end_clean();
                //   $mailer = $this->_di->make('\Of\Std\Mailer');
                $this->_mailer->addAddress($email, '', 'To')
                    ->setFrom($mailTpl->getData('email'), $mailTpl->getData('from_name'))
                    ->setSubject($mailTpl->getData('subject'))
                    ->setMessage($tplHtml)
                    ->send();
                $result['error'] = 0;
                $result['message'] = 'Email Sent';
            } else {
                $result['message'] = 'Email Template Not Found';
            }
        } else {
            $result['message'] = 'Email Not Found';
        }
        return $result;
    }
    public function getLocationwithhighestorders($param, $getcount = null){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $addressTable = $this->_address->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        $this->__join('location_', $mainTable . '.user_id', 'address', '', 'left', 'profile_id', Address::COLUMNS);
        
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("COUNT(`" . $addressTable . "`.`id`)"), 'counted_locations');
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`total_price`)"), 'sum_total_price');
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        /* $this->_select->count($addressTable.".id"); */
        $this->_select->group($addressTable.".id");
        $this->_select->order(['counted_locations'=>'desc']);
        if ($getcount) {
            
            /* if ($this->getCollection()) {
                return count($this->getCollection());
            } else {
                return 0;
            } */
            $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
            $this->_select->removeColumn("`" . $mainTable . "`.*");
            $count = $this->__getQuery($this->getLastSqlQuery());
            $totalCount = 0;
            if ($count) {
                $totalCount = $count->getData('count');
            }
            return $totalCount;
        } else {
            /* if ($this->getCollection()) {
                return $this->getFinalResponse($limit);
            } else {
                return null;
            } */
            return $this->getFinalResponse($limit);
        }
        /* $datas = $this->getCollection();
        if($datas){
            return $datas;
        } */
    }
    public function getWeeks($param){
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $query = "SELECT week(created_at) as week FROM  $mainTable WHERE mode_of_payment >1 GROUP BY week(created_at) ORDER BY created_at";
        if (isset($param['from']) && isset($param['to'])) {
            $query = "SELECT week(created_at) as week FROM $mainTable WHERE mode_of_payment >1 AND created_at BETWEEN date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format('" . $param['to'] . "', '%Y-%m-%d') GROUP BY WEEK(created_at) ORDER BY created_at";
        }
        $datas = $this->_adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        if($datas){
            return $datas;
        }
    }
    public function getBalanceCredit($param, $getcount = null) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $limit = 1;
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $where = "`".$mainTable."`.`mode_of_payment` = ".$param['mode_of_payment']." AND `".$mainTable."`.`order_status` = 4";
        $this->_select->where($where);
        $this->_select->group('grouped_date');
        $this->sum('total_price');
        if ($getcount) {
            if ($this->getCollection()) {
                return count($this->getCollection());
            } else {
                return 0;
            }
        } else {
            if ($this->getCollection()) {
                return $this->getFinalResponse($limit);
            } else {
                return null;
            }
        }
    }
    public function getBalance($week){
        $this->resetQuery();
        $this->sum('total_price');
        $this->_select->where('mode_of_payment = 3 AND order_status = 4 AND week(created_at)='. $this->escape($week));
        $datas = $this->getCollection();
        if ($datas) {
            return $datas;
        }
    }
    public function getCredit($week){
        $this->resetQuery();
        $this->sum('total_price');
        $this->_select->where('mode_of_payment = 2 AND order_status = 4 AND week(created_at)=' . $this->escape($week));
        $datas = $this->getCollection();
        if ($datas) {
            return $datas;
        }
    }
    public function getFullpayments($param)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "mode_of_payment = 1 AND order_status > 1 AND order_status < 4";
        $this->_select->where($where);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (float) $data->getData('total_price');
            }
        }
        return $count;
    }
    public function getBalances($param)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "mode_of_payment = 3 AND order_status > 1 AND order_status <= 4";
        $this->_select->where($where);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        $count = 0;
        foreach ($datas as $data) {
            if ($data->getData('order_status') < 3) {
                $count += (float) $data->getData('total_price');
            } else {
                $paymenthistorys = $this->_paymenthistory->getByColumn(['order_id' => $data->getData('id')], 0);
                if ($paymenthistorys) {
                    $payment = 0;
                    foreach ($paymenthistorys as $paymenthistory) {
                        $payment += (float) $paymenthistory->getData('payment');
                    }
                    $count += (float) $data->getData('total_price') - (float) $payment;
                }
            }
        }
        return $count;
    }
    public function getCredits($param)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "mode_of_payment = 2 AND order_status > 1 AND order_status <= 4 AND payment_status = 0";
        $this->_select->where($where);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (float) $data->getData('total_price');
            }
        }
        return $count;
    }
    public function Countcollectibles($param){
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $where = "order_status > 1 AND order_status <= 4 AND payment_status = 0";
        $this->_select->where($where);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $result = $this->getCollection();
        $getCount = count($result);
        return $getCount;
    }
    public function Countpendings($param){
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $where = "order_status = 1";
        $this->_select->where($where);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $result = $this->getCollection();
        $getCount = count($result);
        return $getCount;
    }
}
?>
