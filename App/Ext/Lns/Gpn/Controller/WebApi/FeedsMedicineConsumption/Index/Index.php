<?php
namespace Lns\Gpn\Controller\WebApi\FeedsMedicineConsumption\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_medicine;
    protected $_house;
    protected $_incidentReport;
    protected $_feeds;
    protected $_userProfile;
    protected $_users;

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
        parent::__construct($Url,$Message,$Session);
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_medicine = $Medicine;
        $this->_house = $House;
        $this->_feeds = $Feeds;
        $this->_incidentReport = $IncidentReport;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
        $this->token = $Validate;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $house_harvest_id = $this->getParam('id');

            $hasRecords = $this->_dailyhouseharvest->getByColumn(['id'=>$house_harvest_id], 1);
    
            $result = [];
            if($hasRecords){
    
                $this->jsonData['error'] = 0;
    
                $house = $this->_house->getHouse($hasRecords->getData('house_id'));
                $feed = $this->_feeds->getFeed($hasRecords->getData('feed_id'));
                $feeds = $this->_feeds->convertGramsToBags($hasRecords);
                $req_feeds = $this->_feeds->convertGramsToBags($hasRecords,true);
                $mortalityrate = $hasRecords->getData('mortality') / $hasRecords->getData('bird_count') * 100;
                $end_bird_population = $hasRecords->getData('bird_count') - $hasRecords->getData('mortality');
                $productionrate = $hasRecords->getData('real_egg_count') / $hasRecords->getData('bird_count') * 100;

                if ($hasRecords->getData('medicine_ids')) {
                   
                    $medicine_ids = explode(',', $hasRecords->getData('medicine_ids'));
                    foreach ($medicine_ids as $medicine_id) {
                        $result[] = $this->_medicine->getMedicine($medicine_id);
                    }
                }       
                $this->jsonData['data'] = $hasRecords->getData();
                $this->jsonData['data']['medicine'] = $result;
                $this->jsonData['data']['house'] = $house;
                $this->jsonData['data']['feed'] = $feed;
                $this->jsonData['data']['feeds'] = $feeds ? $feeds : 0;
                $this->jsonData['data']['req_feeds'] = $req_feeds ? $req_feeds : 0 ;
                $this->jsonData['data']['mortality_rate'] = $mortalityrate ? $mortalityrate : 0;
                $this->jsonData['data']['end_bird_population'] = $end_bird_population ? $end_bird_population : 0;
                $this->jsonData['data']['production_rate'] = $productionrate ? $productionrate : 0;

            }else{
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = 'No Record Found';
            }
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}
?>