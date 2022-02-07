<?php
namespace Lns\Gpn\Controller\WebApi\FeedManagement\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_feeds;
    protected $_daily_house_harvest;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_feeds = $Feeds;
        $this->_daily_house_harvest = $Dailyhouseharvest;
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

            $feeds = $this->_feeds->getFeedslist($param);
            $this->jsonData = $feeds;
            if($feeds['datas']){
                $i = 0;
                foreach ($feeds['datas'] as $feed) {
                    $result[$i] = $feed->getData();
                    $consumed = $this->_daily_house_harvest->getTotalConsumedFeeds($feed->getData('id'));
                    $result[$i]['consumed'] = $consumed;
                    $result[$i]['consumed_bag_kg'] = $this->_feeds->convertGramsToBags2($consumed, $feed->getData('kg_per_bag'));
                    $remaining = (((float)$feed->getData('kg_per_bag') * 1000) * (float)$feed->getData('pieces')) - $consumed;
                    $result[$i]['remaining'] = $remaining;
                    $result[$i]['remaining_bag_kg'] = $this->_feeds->convertGramsToBags2($remaining, $feed->getData('kg_per_bag'));
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $count = $this->_feeds->getCount();
                $this->jsonData['data']['feed_count'] = $count ? $count : 0 ;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>