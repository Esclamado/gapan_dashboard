<?php
namespace Lns\Gpn\Controller\WebApi\FeedManagement\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_feeds;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_feeds = $Feeds;
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

            $feeds = $this->getParam('feeds');
            /* $net_weight = $this->getParam('net_weight'); */
            $kg_per_bag = $this->getParam('kg_per_bag');
            $pieces = $this->getParam('pieces');
            $delivery_date = $this->getParam('delivery_date');
            $expiration_date = $this->getParam('expiration_date');
            $remarks = $this->getParam('remarks');
            $unit_price = $this->getParam('unit_price');

            $feedId = $this->getParam('id');

            if($feedId){
                $feed = $this->_feeds->getByColumn(['id'=> $feedId], 1);
                $message = 'Feeds has been updated';
            }else{
                $feed = $this->_feeds;
                $message = 'A new feeds has been saved';
            }
                $feed->setData('feed', $feeds);
                /* $feed->setData('net_weight', $net_weight); */
                $feed->setData('kg_per_bag', $kg_per_bag);
                $feed->setData('pieces', $pieces);
                $feed->setData('delivery_date', date("Y-m-d", strtotime($delivery_date)));
                $feed->setData('expiration_date', date("Y-m-d", strtotime($expiration_date)));
                $feed->setData('remarks', $remarks);
                $feed->setData('unit_price', $unit_price);
                $feedId = $feed->__save(); 

                if($feedId){
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $message;
                }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>