<?php

namespace Lns\Gpn\Controller\Api\Orders\Action;

class Markascomplete extends \Lns\Sb\Controller\Controller{

    protected $_orders;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_upload;
    protected $_dateTime;
    protected $_orderstatus;
    protected $_orderitems;
    protected $_orderitemdetails;
    protected $_eggtype;
    protected $_egginventory;
    protected $_outflow;
    protected $_audittrail;
    protected $_fresh_egg_inventory;
    protected $_inouteggs;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Orderitems $Orderitems,
        \Lns\Gpn\Lib\Entity\Db\Orderitemdetails $Orderitemdetails,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\EggInventory $EggInventory,
        \Lns\Gpn\Lib\Entity\Db\Outflow $Outflow,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory,
        \Lns\Gpn\Lib\Entity\Db\Inouteggs $Inouteggs
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_orders = $Orders;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_upload = $Upload;
        $this->_dateTime = $DateTime;
        $this->_orderstatus = $OrderStatus;
        $this->_orderitems = $Orderitems;
        $this->_orderitemdetails = $Orderitemdetails;
        $this->_eggtype = $Eggtype;
        $this->_egginventory = $EggInventory;
        $this->_outflow = $Outflow;
        $this->_audittrail = $AuditTrail;
        $this->_fresh_egg_inventory = $FresheggInventory;
        $this->_inouteggs = $Inouteggs;
    }

    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

            $this->jsonData['error'] = 1;

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            /* $userId = $this->getParam('userId'); */

            $update = $this->_orders->getByColumn(['id' => $this->getParam('orderId')], 1);
            if($update){
                $photo = $this->upload($userId, 'inspector');
                $update->setData('checked_by', $userId);
                $update->setData('checked_by_path', $photo);
                $update->setData('checked_by_date', $this->_dateTime->getTimestamp());
                $update->setData('order_status', 4);
                $update->setData('for_release', 0);
                $save = $update->__save();

                $this->_orderstatus->updateStatus($this->getParam('orderId'), 4);

                if($save){

                    $orderitems = $this->_orderitems->getByColumn(['order_id' => $update->getData('id')], 0);
                    if ($orderitems) {
                        $i = 0;
                        $myorders = [];
                        $egg_out = 0;
                        foreach ($orderitems as $orderitem) {
                            $eggtypes = $this->_eggtype->getByColumn(['id' => $orderitem->getData('type_id')], 0);
                            $myorders[$i]['type_id'] = $orderitem->getData('type_id');
                            if ($eggtypes) {
                                foreach ($eggtypes as $eggtype) {
                                    $this->jsonData['data']['egg_type'][] = $eggtype->getData();
                                }
                            }
                            $orderitemdetails = $this->_orderitemdetails->getByColumn(['order_item_id' => $orderitem->getData('id')], 0);
                            if ($orderitemdetails) {
                                $x = 0;
                                $pieces = 0;
                                foreach ($orderitemdetails as $orderitemdetail) {
                                    $this->jsonData['data']['egg_type'][$i]['package_type'][$x] = $orderitemdetail->getData();

                                    switch ($orderitemdetail->getData('type_id')) {
                                        case 1:
                                            $package_type = 'Case';
                                            break;
                                        case 2:
                                            $package_type = 'Tray';
                                            break;
                                        case 3:
                                            $package_type = 'Piece';
                                            break;
                                    }
                                    $this->jsonData['data']['egg_type'][$i]['package_type'][$x]['type'] = $package_type;

                                    switch ($orderitemdetail->getData('type_id')) {
                                        case 1:
                                            $pieces += 360 * (int) $orderitemdetail->getData('qty');
                                            break;
                                        case 2:
                                            $pieces += 30 * (int) $orderitemdetail->getData('qty');
                                            break;
                                        default:
                                            $pieces += (int) $orderitemdetail->getData('qty');
                                            break;
                                    }
                                    $this->jsonData['data']['egg_type'][$i]['total_eggs'] = $pieces;
                                    $myorders[$i]['pieces'] = $pieces;
                                    $egg_out += (int)$pieces;
                                    $x++;
                                }
                            }
                            $i++;
                        }
                        foreach ($myorders as $myorder) {
                            /* $egginventories = $this->_egginventory->getByColumn(['type_id' => $myorder['type_id']], 0); */
                            $egginventories = $this->_egginventory->markAsComplete($myorder['type_id']);
                            if ($egginventories) {
                                $remaining = 0;
                                foreach ($egginventories as $key => $egginventory) {
                                    if ($remaining) {
                                        $difference = (int) $egginventory->getData('egg_count') - $remaining;
                                    } else {
                                        $difference = (int) $egginventory->getData('egg_count') - (int) $myorder['pieces'];
                                    }
                                    if ($difference >= 0) {
                                        $egginventory->setData('egg_count', $difference);
                                        $egginventory->__save();
                                        break;
                                    } else {
                                        $egginventory->setData('egg_count', 0);
                                        $egginventory->__save();
                                        $remaining = abs($difference);
                                    }
                                }
                            }
                            $this->_fresh_egg_inventory->completeSales($this->_dateTime->getTimestamp(), $myorder['type_id'], $myorder['pieces']);
                        }
                    }

                    $outflow = $this->_outflow;
                    $outflow->setData('type', 1);
                    $outflow->setData('reference_id', $this->getParam('orderId'));
                    $outflow->__save();

                    /*  */
                    $date = date('Y-m-d');
                    $inouteggs = $this->_inouteggs->validate($date);
                    if ($inouteggs) {
                        $egg_out = (int)$inouteggs->getData('egg_out') + $egg_out;
                        $inouteggs->setData('egg_out', $egg_out);
                        $inouteggs->setData('updated_at', $this->_dateTime->getTimestamp());
                        $inouteggs->__save();
                    } else {
                        $inouteggs = $this->_inouteggs;
                        $inouteggs->setData('egg_out', $egg_out);
                        $inouteggs->__save();
                    }
                    /*  */

                    $pushData = $update->getData();
                    $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                    if ($customer) {
                        $pushData['customer_name'] = $customer;
                    }
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been completed.";
                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 18, null, $message, $update->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                    if ($to) {
                        $title = "Order Completed";
                        $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " has been completed.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_completed";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_completed', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $action = "<strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> has been completed.";
                    $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_completed');
                    
                    /* send notif to sales */
                    $sales = $this->_users->getUsersByRole(5);
                    if ($sales) {
                        $sender = $this->_userProfile->getFullNameById($userId);
                        foreach ($sales as $sale) {
                            $message = 'Order no. ' . $update->getData('transaction_id') . " has been completed.";
                            $this->_notification->setNotification($update->getData('id'), $userId, 18, null, $message, $sale->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($sale->getData('id'));
                            $this->_cfs->save($sale->getData('id'), $sender, $message, 'order_completed', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                    /* send notif to sales */

                    /* send notif to manager */
                    $managers = $this->_users->getUsersByRole(4);
                    if ($managers) {
                        $sender = $this->_userProfile->getFullNameById($userId);
                        foreach ($managers as $manager) {
                            $message = 'Order no. ' . $update->getData('transaction_id') . " has been completed.";
                            $this->_notification->setNotification($update->getData('id'), $userId, 18, null, $message, $manager->getData('id'));
                            $to = $this->_deviceToken->getDeviceTokenById($manager->getData('id'));
                            $this->_cfs->save($manager->getData('id'), $sender, $message, 'order_completed', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                        }
                    }
                    /* send notif to manager */

                    /* SENDING CREDENTIALS TO EMAIL AFTER REGISTRATION */
                    $this->_orders->sendEmailorderstatus($update->getData('user_id'), $this->_siteConfig, 'completed_order', $this->getParam('orderId'));
                    
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Completed.';
                }else{
                    $this->jsonData['message'] = 'Failed to update order status. Please try again.';
                }
            }else{
                $this->jsonData['message'] = 'No record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId, $type)
    {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads' . DS . 'signature';

        if ($type) {
            $path .= DS . $type;
        }

        $path .= DS . $userId;

        $file = $this->getFile('photo');

        $fileName = null;
        if ($file) {
            $_file = $this->_upload->setFile($file)
                ->setPath($path)
                ->setAcceptedFile(['ico', 'jpg', 'png', 'jpeg'])
                ->save();

            if ($_file['error'] == 0) {
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }
        }
        return $fileName;
    }
}