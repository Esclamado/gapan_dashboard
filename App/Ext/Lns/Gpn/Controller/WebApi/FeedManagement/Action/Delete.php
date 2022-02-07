<?php
namespace Lns\Gpn\Controller\WebApi\FeedManagement\Action;

class Delete extends \Lns\Sb\Controller\Controller {

    protected $_feeds;
    protected $_dailyhouseharvest;

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
        $this->_dailyhouseharvest = $Dailyhouseharvest;
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

            $feedId = $this->getParam('id');

            $feeds = $this->_feeds->getByColumn(['id'=> $feedId], 1);
            if($feeds){
                $dailyhouseharvest = $this->_dailyhouseharvest->getByColumn(['feed_id'=> $feedId], 1);
                if($dailyhouseharvest){
                    $this->jsonData['message'] = 'Feeds can not be deleted';
                }else {
                    $feeds->delete();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = 'A feeds has been deleted';
                }
            }else{
                $this->jsonData['message'] = 'No feeds record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>