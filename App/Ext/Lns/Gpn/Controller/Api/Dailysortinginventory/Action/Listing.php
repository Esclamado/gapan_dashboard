<?php
namespace Lns\Gpn\Controller\Api\Dailysortinginventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_dailysortinginventory;
    protected $_eggtype;
    protected $_dailysortinginventoryhistory;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventory $Dailysortinginventory,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Dailysortinginventoryhistory $Dailysortinginventoryhistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailysortinginventory = $Dailysortinginventory;
        $this->_eggtype = $Eggtype;
        $this->_dailysortinginventoryhistory = $Dailysortinginventoryhistory;
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

            $sorted_report_id = $this->getParam('sorted_report_id');

            $eggInfos = $this->_dailysortinginventory->getByColumn(['sorted_report_id'=>$sorted_report_id], 0);
    
            if($eggInfos){
    
                $this->jsonData['error'] = 0;
    
                $i = 0;
                $result = [];
                foreach ($eggInfos as $eggInfo) {
                    $result[$i] = $eggInfo->getData();
                    $eggType = $this->_eggtype->getByColumn(['id'=>$eggInfo->getData('type_id')], 1);
                    $result[$i]['type'] = $eggType->getData();
    
                    $eggHistory = $this->_dailysortinginventoryhistory->getByColumn(['sorted_inv_id'=>$eggInfo->getData('id')], 1);
                    
                    if($eggHistory){
                        $result[$i]['egg_history'] = $eggHistory->getData();
                    }else{
                        $result[$i]['egg_history'] = null;
                    }
                    $i++;
                }
                $this->jsonData['datas'] = $result;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>