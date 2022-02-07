<?php

namespace Lns\Gpn\Controller\WebApi\Orders\Action;

class Forrelease extends \Lns\Sb\Controller\Controller{

    protected $_orderstatus;
    protected $_orders;
    protected $_cfs;
    protected $_notification;
    protected $_push;
    protected $_userProfile;
    protected $_deviceToken;
    protected $_users;
    protected $_dateTime;
    protected $_upload;
    protected $_paymentattachments;
    protected $_payment;
    protected $_audittrail;
    protected $_paymenthistory;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\OrderStatus $OrderStatus,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\CloudFirestore\CloudFirestore $CloudFirestore,
        \Lns\Sb\Lib\Entity\Db\Notification $Notification,
        \Lns\Sb\Lib\PushNotification\PushNotification $PushNotification,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Of\Std\Upload $Upload,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail,
        \Lns\Gpn\Lib\Entity\Db\Paymenthistory $Paymenthistory
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->token = $Validate;
        $this->_orderstatus = $OrderStatus;
        $this->_orders = $Orders;
        $this->_cfs = $CloudFirestore;
        $this->_notification = $Notification;
        $this->_push = $PushNotification;
        $this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_users = $Users;
        $this->_dateTime = $DateTime;
        $this->_upload = $Upload;
        $this->_paymentattachments = $PaymentAttachments;
        $this->_payment = $Payment;
        $this->_audittrail = $AuditTrail;
        $this->_paymenthistory = $Paymenthistory;
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

            $params = $this->getParam();

            $id = $this->getParam('id');

            $mode_of_payment = $this->getParam('mode_of_payment');
            $payment_status = $this->getParam('payment_status');
            $total_price = $this->getParam('total_price');
            $payment = $this->getParam('payment');
            $balance = $this->getParam('balance');
            $due_date = $this->getParam('due_date');
            $receipt_no = $this->getParam('receipt_no');

            $paymentEntity = $this->_payment->getByColumn(['order_id' => $id], 1);
            $order = $this->_orders->getByColumn(['id' => $id], 1);
            if ($paymentEntity) {
                if ($mode_of_payment == 1) {
                    $receipt_photo = $this->uploadForm($paymentEntity->getData('id'), 'receipt_photo', 'transaction_form');

                    /* update order table : start */
                    $order->setData('for_release',1);
                    if ((float)$payment == (float)$order->getData('total_price')) {
                        $order->setData('payment_status', 1);
                    } else {
                        $order->setData('payment_status', $payment_status);
                    }
                    $order->setData('date_paid', $this->_dateTime->getTimestamp());
                    $order->__save();
                    /* update order table : end */
                    
                    /* update payment : start */
                    $paymentEntity->setData('approved_by', $userId);
                    $paymentEntity->setData('approved_date', $this->_dateTime->getTimestamp());
                    $paymentEntity->setData('receipt_no', $receipt_no);
                    $paymentEntity->__save();
                    /* update payment : end */
                    
                    /* save payment attachment : start */
                    /* $payment_attachment = $this->_paymentattachments;
                    $payment_attachment->setData('attachment', $payment_photo);
                    $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                    $payment_attachment->setData('type', 2);
                    $payment_attachment->__save(); */

                    if ($receipt_photo) {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('attachment', $receipt_photo);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    } else {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    }

                    /* add payment history : start */
                    $paymentHistoryEntity = $this->_paymenthistory;
                    $paymentHistoryEntity->setData('order_id', $id);
                    $paymentHistoryEntity->setData('payment_attachments_id', $paID);
                    $paymentHistoryEntity->setData('payment', $payment);
                    $paymentHistoryEntity->setData('receipt_no', $receipt_no);
                    $paymentHistoryEntity->setData('created_by', $userId);
                    $paymentHistoryEntity->__save();
                    /* add payment history : end */

                    $inspectors2 = $this->_users->getUsersByRole(7);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $order->getData();
                            $customer = $this->_userProfile->getFullNameById($order->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }

                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "<strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                            $this->_notification->setNotification((int) $order->getData('id'), $userId, 17, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Ready for Release";
                                $message = "" . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_for_release";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $action = "<strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                        $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_forrelease');
                    }

                    $pushData = $order->getData();
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                    $this->_notification->setNotification((int) $order->getData('id'), $userId, 23, null, $message, $order->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($order->getData('user_id'));
                    if ($to) {
                        $title = "Order Ready for Release";
                        $message = "Your " . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_for_release";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($order->getData('user_id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'An order is ready for releasing';
                    /* save payment attachment : end */
                } else if ($mode_of_payment == 2) {
                    $receipt_photo = $this->uploadForm($paymentEntity->getData('id'), 'receipt_photo', 'transaction_form');

                    $order->setData('for_release', 1);
                    if ((float)$payment == (float)$order->getData('total_price')) {
                        $order->setData('payment_status', 1);
                    } else {
                        $order->setData('payment_status', $payment_status);
                    }
                    $order->setData('date_paid', $this->_dateTime->getTimestamp());
                    $order->__save();

                    /* update payment : start */
                    $paymentEntity->setData('balance', $balance);
                    $paymentEntity->setData('payment', $payment);
                    $paymentEntity->setData('due_date', date("Y-m-d", strtotime($due_date)));

                    $paymentEntity->setData('approved_by', $userId);
                    $paymentEntity->setData('approved_date', $this->_dateTime->getTimestamp());
                    $paymentEntity->setData('receipt_no', $receipt_no);
                    $paymentEntity->__save();
                    /* update payment : end */

                    /* add payment history : start */
/*                     $paymentHistoryEntity = $this->_paymenthistory;
                    $paymentHistoryEntity->setData('order_id', $id);
                    $paymentHistoryEntity->setData('payment', $payment);
                    $paymentHistoryEntity->setData('receipt_no', $receipt_no);
                    $paymentHistoryEntity->setData('created_by', $userId);
                    $paymentHistoryEntity->__save(); */
                    /* add payment history : end */

                    /* save payment attachment : start */
                    /* $payment_attachment = $this->_paymentattachments;
                    $payment_attachment->setData('attachment', $payment_photo);
                    $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                    $payment_attachment->setData('type', 2);
                    $payment_attachment->__save(); */
                    if ($receipt_photo) {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('attachment', $receipt_photo);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    }  else {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    }

                    /* add payment history : start */
                    $paymentHistoryEntity = $this->_paymenthistory;
                    $paymentHistoryEntity->setData('order_id', $id);
                    $paymentHistoryEntity->setData('payment_attachments_id', $paID);
                    $paymentHistoryEntity->setData('payment', $payment);
                    $paymentHistoryEntity->setData('receipt_no', $receipt_no);
                    $paymentHistoryEntity->setData('created_by', $userId);
                    $paymentHistoryEntity->__save();
                    /* add payment history : end */

                    /* $payment_attachment = $this->_paymentattachments;
                    $payment_attachment->setData('attachment', $credit_photo);
                    $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                    $payment_attachment->setData('type', 3);
                    $payment_attachment->__save(); */
                    /* save payment attachment : end */

                    $inspectors2 = $this->_users->getUsersByRole(7);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $order->getData();
                            $customer = $this->_userProfile->getFullNameById($order->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }

                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                            $this->_notification->setNotification((int) $order->getData('id'), $userId, 17, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Ready for Release";
                                $message = "" . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_for_release";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $action = "<strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                        $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_forrelease');
                    }

                    $pushData = $order->getData();
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                    $this->_notification->setNotification((int) $order->getData('id'), $userId, 23, null, $message, $order->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($order->getData('user_id'));
                    if ($to) {
                        $title = "Order Ready for Release";
                        $message = "Your " . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_for_release";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($order->getData('user_id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'An order is ready for releasing';
                } else if ($mode_of_payment == 3) {
                    $receipt_photo = $this->uploadForm($paymentEntity->getData('id'), 'receipt_photo', 'transaction_form');
                    $order->setData('for_release', 1);

                    $last_payment = 0;

                    $lastPaymentEntity = $this->_paymenthistory->getLastPayment($id);

                    if ($lastPaymentEntity) {
                        $last_payment = $lastPaymentEntity->getData('payment');
                    }

                    if ((float)$payment == ((float)$order->getData('total_price') - (float)$last_payment)) {
                        $order->setData('payment_status', 1);
                    } else {
                        $order->setData('payment_status', $payment_status);
                    }
                    $order->setData('date_paid', $this->_dateTime->getTimestamp());
                    $order->__save();

                    /* update payment : start */
                    $paymentEntity->setData('balance', $balance);
                    $paymentEntity->setData('payment', $payment);
                    $paymentEntity->setData('due_date', date("Y-m-d", strtotime($due_date)));

                    $paymentEntity->setData('approved_by', $userId);
                    $paymentEntity->setData('approved_date', $this->_dateTime->getTimestamp());
                    $paymentEntity->setData('receipt_no', $receipt_no);
                    $paymentEntity->__save();
                    /* update payment : end */

                    /* add payment history : start */
/*                     $paymentHistoryEntity = $this->_paymenthistory;
                    $paymentHistoryEntity->setData('order_id', $id);
                    $paymentHistoryEntity->setData('payment', $payment);
                    $paymentHistoryEntity->setData('receipt_no', $receipt_no);
                    $paymentHistoryEntity->setData('created_by', $userId);
                    $paymentHistoryEntity->__save(); */
                    /* add payment history : end */
                    
                    /* save payment attachment : start */
                    /* $payment_attachment = $this->_paymentattachments;
                    $payment_attachment->setData('attachment', $payment_photo);
                    $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                    $payment_attachment->setData('type', 2);
                    $payment_attachment->__save(); */

                    if ($receipt_photo) {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('attachment', $receipt_photo);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    }  else {
                        $payment_attachment = $this->_paymentattachments;
                        $payment_attachment->setData('attachment_no', $receipt_no);
                        $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                        $payment_attachment->setData('uploaded_by', $userId);
                        $payment_attachment->setData('type', 1);
                        $paID = $payment_attachment->__save();
                    }

                    /* add payment history : start */
                    $paymentHistoryEntity = $this->_paymenthistory;
                    $paymentHistoryEntity->setData('order_id', $id);
                    $paymentHistoryEntity->setData('payment_attachments_id', $paID);
                    $paymentHistoryEntity->setData('payment', $payment);
                    $paymentHistoryEntity->setData('receipt_no', $receipt_no);
                    $paymentHistoryEntity->setData('created_by', $userId);
                    $paymentHistoryEntity->__save();
                    /* add payment history : end */

                    /* $payment_attachment = $this->_paymentattachments;
                    $payment_attachment->setData('attachment', $balance_photo);
                    $payment_attachment->setData('payment_id', $paymentEntity->getData('id'));
                    $payment_attachment->setData('type', 4);
                    $payment_attachment->__save(); */
                    /* save payment attachment : end */

                    $inspectors2 = $this->_users->getUsersByRole(7);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $order->getData();
                            $customer = $this->_userProfile->getFullNameById($order->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }

                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                            $this->_notification->setNotification((int) $order->getData('id'), $userId, 17, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Ready for Release";
                                $message = "" . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_for_release";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $action = "<strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                        $this->_audittrail->saveAudittrail($userId, $sender, $action, 'order_forrelease');
                    }

                    $pushData = $order->getData();
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $order->getData('transaction_id') . "</strong> is ready for releasing.";
                    $this->_notification->setNotification((int) $order->getData('id'), $userId, 23, null, $message, $order->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($order->getData('user_id'));
                    if ($to) {
                        $title = "Order Ready for Release";
                        $message = "Your " . 'Order no. ' . $order->getData('transaction_id') . " is ready for releasing.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_for_release";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($order->getData('user_id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'An order is ready for releasing';
                }

            } else {
                $this->jsonData['message'] = 'No record found';
            }
            /* $orderId = $this->getParam('id');
            $attachments = $this->getParam('attachment');

            $update = $this->_orders->getByColumn(['id'=>$orderId], 1);
            $payment = $this->_payment->getByColumn(['order_id'=> $orderId], 1);
            if($update){

                $update->setData('for_release',1);
                $update->setData('date_paid', $this->_dateTime->getTimestamp());
                $save = $update->__save();

                if($save){
                    if($attachments){
                        foreach ($attachments as $key => $attachment) {
                            if ($attachment['type'] == 1) {
                                $attachment_type = 'receipt';
                            } else if ($attachment['type'] == 2) {
                                $attachment_type = 'payment_form';
                            } else if ($attachment['type'] == 3) {
                                $attachment_type = 'credit_form';
                            } else if ($attachment['type'] == 4) {
                                $attachment_type = 'balance_form';
                            }
                            $photo = $this->upload($payment->getData('id'), $attachment_type, $key);
                            $entity = $this->_paymentattachments;
                            $entity->setData('attachment', $photo);
                            $entity->setData('payment_id', $payment->getData('id'));
                            $entity->setData('type', $attachment['type']);
                            $entity->__save();
                        }
                    }

                    $inspectors2 = $this->_users->getUsersByRole(7);
                    if ($inspectors2) {
                        foreach ($inspectors2 as $inspector2) {
                            $pushData = $update->getData();
                            $customer = $this->_userProfile->getFullNameById($update->getData('user_id'));
                            if ($customer) {
                                $pushData['customer_name'] = $customer;
                            }

                            $sender = $this->_userProfile->getFullNameById($userId);
                            $message = "" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready for releasing.";
                            $this->_notification->setNotification((int) $update->getData('id'), $userId, 17, null, $message, $inspector2->getData('id'));

                            $to = $this->_deviceToken->getDeviceTokenById($inspector2->getData('id'));
                            if ($to) {
                                $title = "Order Ready for Release";
                                $message = "" . 'Order no. ' . $update->getData('transaction_id') . " is ready for releasing.";
                                $content = "Content Here";
                                $api_key = $this->_siteConfig->getData('site_fcm_key');
                                $pushAction = "order_for_release";
                                $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                                $this->_cfs->save($inspector2->getData('id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                            }
                        }
                        $this->_audittrail->saveAudittrail($userId, $sender, $message, 'order_forrelease');
                    }

                    $pushData = $update->getData();
                    $sender = $this->_userProfile->getFullNameById($userId);
                    $message = "Your <strong>" . 'Order no. ' . $update->getData('transaction_id') . "</strong> is ready for releasing.";
                    $this->_notification->setNotification((int) $update->getData('id'), $userId, 23, null, $message, $update->getData('user_id'));

                    $to = $this->_deviceToken->getDeviceTokenById($update->getData('user_id'));
                    if ($to) {
                        $title = "Order Ready for Release";
                        $message = "Your " . 'Order no. ' . $update->getData('transaction_id') . " is ready for releasing.";
                        $content = "Content Here";
                        $api_key = $this->_siteConfig->getData('site_fcm_key');
                        $pushAction = "order_for_release";
                        $this->_push->sendNotif($to->getData('token'), $title, $message, $content, $api_key, $pushAction);

                        $this->_cfs->save($update->getData('user_id'), $sender, $message, 'order_for_release', $this->_siteConfig->getData('site_firebase_project_id'), $this->_siteConfig->getData('site_firebase_web_api_key'), json_encode($pushData));
                    }
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'An order is ready for releasing';
                }
            }else{
                $this->jsonData['message'] = 'Order not found';
            } */
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function upload($userId, $type, $key)
    {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads';

        if ($type) {
            $path .= DS . $type;
        }

        $path .= DS . $userId;

        $attach = $this->getFile('attachment');

        $file = [
            'name'=> $attach['name'][$key]['photo'],
            'type' => $attach['type'][$key]['photo'],
            'tmp_name' => $attach['tmp_name'][$key]['photo'],
            'error' => $attach['error'][$key]['photo'],
            'size' => $attach['size'][$key]['photo']
        ];

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

    protected function uploadForm($paymentId, $formControlName, $type) {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads';
        if ($type) {
            $path .= DS . $type;
        }
        $path .= DS . $paymentId;

        $file = $this->getFile($formControlName);

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
?>