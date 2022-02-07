<?php
namespace Lns\Gpn\Controller\WebApi\RecentStaffActivities\Action;

class RecentStaffActivities extends \Lns\Sb\Controller\Controller {

    protected $_audittrail;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\AuditTrail $AuditTrail
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_audittrail = $AuditTrail;
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

            $param = $this->getParam();

            $result = [];
            $audittrails = $this->_audittrail->getList($param);
            if($audittrails['datas']){
                $this->jsonData = $audittrails;
                foreach ($audittrails['datas'] as $audittrail) {
                    $result[] = $audittrail->getData();
                }
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'Empty'; 
            }
            $this->jsonData['datas'] = $result;
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>