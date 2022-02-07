<?php
namespace Lns\Gpn\Controller\Api\Trayinventory\Action;

class Getlastending extends \Lns\Sb\Controller\Controller {

    protected $_trayReport;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayReport = $TrayReport;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $data = $this->_trayReport->getLatest();

            if ($data) {
                if (date('Y-m-d') == date('Y-m-d', strtotime($data['created_at']))) {
                    $this->jsonData['message'] = 'You already filed for today. Please try again tomorrow.';
                } else {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $data;
                }
            } else {
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $data;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>