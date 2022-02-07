<?php
namespace Lns\Gpn\Controller\Api\Traytypes\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_trayTypes;
    protected $_trayinventoryreport;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes,
        \Lns\Gpn\Lib\Entity\Db\Trayinventoryreport $Trayinventoryreport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_trayTypes = $Traytypes;
        $this->_trayinventoryreport = $Trayinventoryreport;
    }
    public function run(){
		$payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $result = [];
        $this->jsonData['error'] = 1;

		if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $trayTypes = $this->_trayTypes->getCollection();
            if ($trayTypes) {
                $i = 0;
                foreach ($trayTypes as $trayType) {
                    $date = date('Y-m-d');
                    $result[$i] = $trayType->getData();
                    $result[$i]['last_data'] = $this->_trayinventoryreport->getLastEnding($trayType->getData('id'), $date);
                    $i++;
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = "No Tray Type Found";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>