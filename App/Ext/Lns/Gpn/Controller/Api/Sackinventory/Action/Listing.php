<?php
namespace Lns\Gpn\Controller\Api\Sackinventory\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;

    protected $_sackInventory;
    protected $_sackBldgInventory;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\Sackbldginventory $Sackbldginventory
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_sackInventory = $Sackinventory;
        $this->_sackBldgInventory = $Sackbldginventory;
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
            $this->jsonData['error'] = 1;
            $param = $this->getParam();
            $date = $this->getParam('date');
            $list = $this->_sackInventory->getList($param);
            $this->jsonData = $list;
            $result = [];
            $i = 0;
            if ($list['datas']) {
                foreach ($list['datas'] as $data) {
                    $result[$i] = $data->getData();
                    $total = $this->_sackBldgInventory->getTotalBySackInvId($data->getData('id'));
                    $result[$i]['total'] = $total;
                    $date = date("Y-m-d", strtotime($data->getData('created_at')));
                    /* var_dump($date); */
                    /* $result[$i]['lastending'] = $this->_sackInventory->getLastEnding($date); */
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = "No Sack Inventory";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}