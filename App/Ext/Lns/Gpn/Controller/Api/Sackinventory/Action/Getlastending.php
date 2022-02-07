<?php
namespace Lns\Gpn\Controller\Api\Sackinventory\Action;

class Getlastending extends \Lns\Sb\Controller\Controller {

    protected $_sackInventory;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_sackInventory = $Sackinventory;
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
            $data = $this->_sackInventory->getLatest();

            if ($data) {
                if (date('Y-m-d') == date('Y-m-d', strtotime($data['created_at']))) {
                    $this->jsonData['message'] = 'You already filed for today. Please try again tomorrow.';
                } else {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $data;
                }
            } else {
                $this->jsonData['error'] = 0;
                $this->jsonData['data']['last_ending'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>