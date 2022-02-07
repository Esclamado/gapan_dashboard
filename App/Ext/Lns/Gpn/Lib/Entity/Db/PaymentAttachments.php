<?php
namespace Lns\Gpn\Lib\Entity\Db;

use Lns\Sb\Lib\Entity\Db\UserProfile;

class PaymentAttachments extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'payment_attachments';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'attachment_no',
        'attachment',
        'payment_id',
        'type',
        'uploaded_by',
        'created_at',
        'updated_at'
    ];

    public $_user_profile;

    public function __construct(
        \Of\Http\Request $Request
    ) {
        parent::__construct($Request);
        $this->_user_profile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
    }
    public function getListByPaymentId($param, $payment_id) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $userProfileTable = $this->_user_profile->getTableName();
        $this->__join('user_profile_', $mainTable . '.uploaded_by', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        $this->_select->where($mainTable . '.payment_id = ' . $payment_id);
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            if($param['order_by_column'] == 'first_name'){
                $this->_select->order([$userProfileTable . ".first_name" => $param['order_by']]);
            } else {
                $this->_select->order([$mainTable . "." . $param['order_by_column'] => $param['order_by']]);
            }
        }
        if (isset($param['type'])) {
            $this->_select->where($mainTable . '.type = ' . $param['type']);
        } else {
            $this->_select->where($mainTable . '.type != 1');
        }
        return $this->getFinalResponse($limit);
    }
    public function getLatestActivity($payment_id = null) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        if ($payment_id) {
          $this->_select->where($mainTable . '.payment_id = ' . $payment_id);
        }
        $this->_select->order(['updated_at' => 'desc']);
        $this->_select->limit(1);
        $result = $this->getCollection();
        return $result;
    }
}
?>