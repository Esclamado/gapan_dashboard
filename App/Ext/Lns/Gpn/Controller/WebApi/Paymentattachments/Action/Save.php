<?php
namespace Lns\Gpn\Controller\WebApi\Paymentattachments\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_payment_attachments;
    protected $_payment;
    protected $_upload;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Gpn\Lib\Entity\Db\Payment $Payment,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_upload = $Upload;
        $this->_payment_attachments = $PaymentAttachments;
        $this->_payment = $Payment;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $id = $this->getParam('id');
            $type = $this->getParam('type');
            $attachment_no = $this->getParam('attachment_no');
            $payment_id = $this->getParam('payment_id');
            
            $attachment = null;
            /* if ($type == 1) {
                $attachment = $this->uploadForm($payment_id, 'receipt');
            } else if ($type == 2) {
                $attachment = $this->uploadForm($payment_id, 'payment_form');
            } else if ($type == 3) {
                $attachment = $this->uploadForm($payment_id, 'credit_form');
            } else if ($type == 4) {
                $attachment = $this->uploadForm($payment_id, 'balance_form');
            } */
            $attachment = $this->uploadForm($payment_id, 'transaction_form');

            $entity = $this->_payment_attachments->getByColumn(['id' => $id], 1);
            if ($entity) {
                if ($attachment_no) {
                    $entity->setData('attachment_no', $attachment_no);
                    $payment_entity = $this->_payment->getByColumn(['id' => $payment_id], 1);
                    if ($payment_entity) {
                        $payment_entity->setData('receipt_no', $attachment_no);
                        $payment_entity->__save();
                    }
                }
                if ($attachment) {
                    $entity->setData('attachment', $attachment);
                }
                $entity->setData('payment_id', $payment_id);
                $entity->setData('type', $type);
                $entity->setData('uploaded_by', $userId);
                $save = $entity->__save();
                if ($save) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Attachment updated';
                } else {
                    $this->jsonData['message'] = 'Could not save attachment';
                }
            } else {
                $entity = $this->_payment_attachments;
                if ($attachment_no) {
                    $entity->setData('attachment_no', $attachment_no);
                    $payment_entity = $this->_payment->getByColumn(['id' => $payment_id], 1);
                    if ($payment_entity) {
                        $payment_entity->setData('receipt_no', $attachment_no);
                        $payment_entity->__save();
                    }
                }
                if ($attachment) {
                    $entity->setData('attachment', $attachment);
                }
                $entity->setData('payment_id', $payment_id);
                $entity->setData('type', $type);
                $entity->setData('uploaded_by', $userId);
                $save = $entity->__save();
                if ($save) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'Attachment saved';
                } else {
                    $this->jsonData['message'] = 'Could not save attachment';
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    protected function uploadForm($paymentId, $type) {
        $path = 'Lns' . DS . 'Gpn' . DS . 'View' . DS . 'images' . DS . 'uploads';
        if ($type) {
            $path .= DS . $type;
        }
        $path .= DS . $paymentId;

        $file = $this->getFile('attachment');

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