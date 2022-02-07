<?php
namespace Lns\Gpn\Controller\WebApi\FeedManagement\Index;

class Index extends \Lns\Sb\Controller\Controller {

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
        
        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $feed = $this->_feeds->getByColumn(['id' => $this->getParam('id')], 1);
            if ($feed) {
                $this->jsonData['data'] = $feed->getData();
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'No record found.';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>