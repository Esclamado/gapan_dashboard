<?php
namespace Lns\Sb\Controller\Api\CMS;

class Page extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_cms = $this->_di->get('Lns\Sb\Lib\Entity\Db\Page');
        $this->_attachment = $this->_di->get('Lns\Sb\Lib\Entity\Db\Attachments');
		$this->_saveAttachment = $this->_di->get('Lns\Sb\Controller\Admin\File\Upload\Index');
	}	

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        /*$cmsForm = $this->getParam();*/
        $files = $this->_request->getFile('attachment');
        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            if($this->getParam('type') != 99){
                $response = $this->_cms->updatePage($this->getParam('type'),$this->getParam('content'),$this->getParam('slug'));
                $this->jsonData = $response;
            }else{
                $image =  $this->_saveAttachment->uploadFile($files);

                /*    $attachment = array(
                    "uploader_id" => $payload['jti'],
                    "attachment_type" => "2",
                    "filename" => $image[0]->filename,
                    "filepath" => $image[0]->filepath,
                    "user_id" => $payload['jti'],
                );
                $this->_attachment->saveAttachment($attachment); */

                $cmsPage = array(
                    "page" =>  $this->getParam('page'),
                    "pageContent" => $this->getParam('content'),
                    "slug" => $this->getParam('slug'),
                    "image" => $image[0]->filepath."/".$image[0]->filename
                );

                $response = $this->_cms->savePage($cmsPage);
                if($response){
                    $this->jsonData['message'] = "Successfully added new CMS!";
                }
            }
            /* $this->jsonData = $response; */
        }
        $this->jsonEncode($this->jsonData);
        die;
       
    }
}

