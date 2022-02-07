<?php
namespace Lns\Gpn\Controller\WebApi\Paymentattachments\Action;

class Delete extends \Lns\Sb\Controller\Controller {

    protected $_payment_attachments;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_payment_attachments = $PaymentAttachments;
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
            
            $entity = $this->_payment_attachments->getByColumn(['id' => $id], 1);
            if ($entity) {
                $entity->delete();
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = 'Attachment deleted';
            } else {
                $this->jsonData['message'] = 'No record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>