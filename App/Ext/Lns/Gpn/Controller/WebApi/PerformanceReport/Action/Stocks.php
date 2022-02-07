<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class Stocks extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_inflow;
    protected $_inouteggs;
    protected $_outflow;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Inflow $Inflow,
        \Lns\Gpn\Lib\Entity\Db\Outflow $Outflow,
        \Lns\Gpn\Lib\Entity\Db\Inouteggs $Inouteggs
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_eggtype = $Eggtype;
        $this->_inflow = $Inflow;
        $this->_outflow = $Outflow;
        $this->_inouteggs = $Inouteggs;
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

            $entities = $this->_inouteggs->getList($param);
            $total_count = $this->_inouteggs->getList($param, true);
            $result = [];
            if ($entities) {
                $this->jsonData = $entities;
                $this->jsonData['total_count'] = $total_count;
                $total_egg_inflow = 0;
                $total_egg_outflow = 0;
                foreach ($entities['datas'] as $entity) {
                    $total_egg_inflow += (int)$entity->getData('egg_in');
                    $total_egg_outflow += (int)$entity->getData('egg_out');
                    $result[] = $entity->getData();
                }
                $this->jsonData['data'] = array(
                    'total_egg_inflow' => $total_egg_inflow,
                    'total_egg_outflow' => $total_egg_outflow
                );
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No record found';
            }
            /* $outflowEntities = $this->_outflow->getReport($param); */
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>