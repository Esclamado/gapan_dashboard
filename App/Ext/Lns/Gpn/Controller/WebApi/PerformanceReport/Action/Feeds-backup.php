<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class Feeds extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_house;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

/*         if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti']; */

            $param = $this->getParam();

            $feeds = $this->_dailyhouseharvest->getFeeds($param);
            $this->jsonData = $feeds;
            $result = [];
            if($feeds['datas']){
                $i = 0;
                foreach ($feeds['datas'] as $feed) {
                    $result[$i] = array(
                        'age_week'=> $feed->getData('age_week'),
                        'recommended' => $feed->getData('rec_feed_consumption')
                    );
                    $houses = $this->_house->getCollection();
                    if($houses){
                        $x = 0;
                        foreach ($houses as $house) {
                            $result[$i]['house'][$x] = $house->getData();

                            $feedconsumption = $this->_dailyhouseharvest->getFeedconsumptions($feed->getData('age_week'), $house->getData('id'));
                            $result[$i]['house'][$x]['feeds_consumption'] = $feedconsumption ? $feedconsumption->getData('feed_consumption') : null ;
                            $x++;
                        }
                    }
                    $i++; 
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
        /* } */
        $this->jsonEncode($this->jsonData);
        die;
    }
}
