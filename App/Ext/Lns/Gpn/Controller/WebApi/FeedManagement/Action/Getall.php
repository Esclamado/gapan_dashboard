<?php
namespace Lns\Gpn\Controller\WebApi\FeedManagement\Action;

class Getall extends \Lns\Sb\Controller\Controller {

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

            $feeds = $this->_feeds->getCollection();
            if($feeds){
                foreach ($feeds as $feed) {
                    $result[] = $feed->getData();
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>