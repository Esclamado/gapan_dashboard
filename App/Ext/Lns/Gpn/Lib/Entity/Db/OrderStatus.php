<?php
namespace Lns\Gpn\Lib\Entity\Db;

class OrderStatus extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order_status';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_id',
        'status',
        'created_at'
    ];

    public $_userprofile;
    public $_orders;

    public function __construct(
        \Of\Http\Request $Request,
        $adapter = null
    ) {
        parent::__construct($Request, $adapter);
        $this->_userprofile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_orders = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Orders');
    }

    public function getTrackingStatus($orderId){
        $name = null;
        $result = array(
            array(
                'statuslabel' => 'Order Placed',
                'created_at' => null,
                'status' => 2,
                'stop' => 0,
                'name' => null
            ),
            array(
                'statuslabel' => 'Pending for Approval',
                'created_at' => null,
                'status' => 1,
                'stop' => 0,
                'name' => null
            ),
            array(
                'statuslabel' => 'Processing Order',
                'created_at' => null,
                'status' => 0,
                'stop' => 0,
                'name' => null
            ),
            array(
                'statuslabel' => 'Ready for Pick-up',
                'created_at' => null,
                'status' => 0,
                'stop' => 0,
                'name' => null
            ),
            array(
                'statuslabel' => 'Order Completed',
                'created_at' => null,
                'status' => 0,
                'stop' => 0,
                'name' => null
            )
        );
        $datas = $this->getByColumn(['order_id'=>$orderId], 0);
        if($datas){
            foreach ($datas as $data) {

                $order_approve = '';
                $order_processed = '';
                $order_completed = '';
                $orders = $this->_orders->getByColumn(['id'=>$orderId], 1);
                if($orders){
                        $salesman = $orders->getData('approved_by');
                        if($salesman){
                            $order_approve = $this->_userprofile->getFullNameById($salesman);
                        }
                        $warehouseman = $orders->getData('prepared_by');
                        if ($warehouseman) {
                            $order_processed = $this->_userprofile->getFullNameById($warehouseman);
                        }
                        $inspector2 = $orders->getData('checked_by');
                        if($inspector2){
                            $order_completed = $this->_userprofile->getFullNameById($inspector2);
                        }
                }

                switch($data->getData('status')){
                    case 1:
                        $result[0] = array(
                            'statuslabel' => 'Order Placed',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0,
                            'name' => null
                        );
                        $result[1] = array(
                            'statuslabel' => 'Pending for Approval',
                            'created_at' => null,
                            'status' => 1,
                            'stop' => 0,
                            'name' => null
                        );
                    break;
                    /* case 7:
                        $result[0]['stop'] = 1;
                        $result[1] = array(
                            'statuslabel' => 'Cancelled',
                            'created_at' => $data->getData('created_at'),
                            'status' => 3,
                            'stop' => 0,
                            'name' => null
                        );
                    break; */
                    case 8:
                        $result[0]['stop'] = 1;
                        $result[1] = array(
                            'statuslabel' => 'Declined',
                            'created_at' => $data->getData('created_at'),
                            'status' => 3,
                            'stop' => 0,
                            'name' => null
                        );
                    break;
                    case 2:
                        $result[1] = array(
                            'statuslabel' => /* 'Pending for Approval' */'Order Approved',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0,
                            'name' => $order_approve
                        );
                        $result[2] = array(
                            'statuslabel' => 'Processing Order',
                            'created_at' => null,
                            'status' => 1,
                            'stop' => 0,
                            'name' => null
                        );
                    break;
                    case 3:
                        $result[2] = array(
                            'statuslabel' => /* 'Processing Order' */'Order Processed',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0,
                            'name' => $order_processed
                        );
                        /*                         $result[3] = array(
                            'statuslabel' => 'Ready for Pick-up',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0
                        );
                        $result[4] = array(
                            'statuslabel' => 'Order Completed',
                            'created_at' => null,
                            'status' => 1,
                            'stop' => 0
                        ); */
                        $result[3] = array(
                            'statuslabel' => 'Ready for Pick-up',
                            'created_at' => null,
                            'status' => 1,
                            'stop' => 0,
                            'name' => null
                        );
                    break;
                    case 4:
                        $result[3] = array(
                            'statuslabel' => 'Picked Up',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0,
                            'name' => $order_processed
                        );
                        $result[4] = array(
                            'statuslabel' => 'Order Completed',
                            'created_at' => $data->getData('created_at'),
                            'status' => 2,
                            'stop' => 0,
                            'name' => $order_completed
                        );
                    break;
                    case 7:
                        foreach ($result as $key => $res) {
                            if (!$res['created_at']) {
                                $result[$key-1]['stop'] = 1;
                                $result[$key] = array(
                                    'statuslabel' => 'Cancelled',
                                    'created_at' => $data->getData('created_at'),
                                    'status' => 3,
                                    'stop' => 0,
                                    'name' => null
                                );
                                break;
                            }
                        }
                        
                    break;
                }
            }
        }
        return $result;
    }
    public function updateStatus($orderId,$orderStatus){
        $entity = $this;
        $entity->setData('order_id',$orderId);
        $entity->setData('status',$orderStatus);
        $entity->__save();
    }
    public function getOrderStatus($order) {
        $return = 'Pending for Approval';

        if ($order->getData('order_status') == 1) {
            if ($order->getData('mode_of_payment') > 1) {
                if ($order->getData('balance_credit_approved') == 0) {
                    $return = 'Pending for Manager\'s Approval';
                } else {
                    $return = 'Pending for Approval';
                }
            }
        } else if ($order->getData('order_status') == 2) {
            $return = 'Processing';
        } else if ($order->getData('order_status') == 3) {
            $return = 'Ready for Pick Up';
        } else if ($order->getData('order_status') == 4) {
            $return = 'Completed';
        } else if ($order->getData('order_status') == 7) {
            $return = 'Cancelled';
        } else if ($order->getData('order_status') == 8) {
            $return = 'Declined';
        }
        return $return;
    }
}
?>
