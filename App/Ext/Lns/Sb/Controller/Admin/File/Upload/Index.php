<?php
namespace Lns\Sb\Controller\Admin\File\Upload;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'File Upload';
    protected $_request;
    protected $FileUpload;

    public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
        \Of\Http\Request $Request,
        \Lns\Sb\Lib\Fileupload $Fileupload
	){
		parent::__construct($Url,$Message,$Session);
        $this->_request = $Request;
        $this->FileUpload = $Fileupload;
    }	
    
	public function run(){
        $this->requireLogin();
        
        $files = $this->getFile('images');
        $this->uploadFile($files);
       
    }

    public function uploadFile($files){

        if(is_array($files)){
            $min_count = 1;
            if($this->getParam('min_upload_count')){
                $min_count = $this->getParam('min_upload_count');
            }

            $uploadCount = count($files);
            if($uploadCount >= $min_count){

                $_files = $this->sortFiles($files);

                $uploadErrorCount = 0;
                foreach($_files as $uploadedFile){
                    if($uploadedFile['error'] != 0){
                        $uploadErrorCount++;
                    }
                }

                if(($uploadCount - $uploadErrorCount) < $min_count){

                } else{
                    $user_image_path = "user";

                    $uploadToTmp = [];
                    foreach($_files as $f){

                        $this->FileUpload->setFileType([
                            'JPG', 'jpg', 'PNG', 'png', 'JPEG', 'jpeg', 'GIF', 'gif'
                        ]);
                        $uploadToTmp[] = (object)$this->FileUpload->save($f, $user_image_path);
                    }

                    return $uploadToTmp;

                }
            } else{
                return false;
            }
        } else{
            return false;
        }

    }
    
    public function sortFiles($files){
        $_files = [];
		foreach($files['name'] as $key => $val){
			$_files[] = [
				'name' => $files['name'][$key],
				'type' => $files['type'][$key],
				'tmp_name' => $files['tmp_name'][$key],
				'error' => $files['error'][$key],
				'size' => $files['size'][$key],
			];
		}
		return $_files;
    }
    
    /* NOTE: TRANSFER THESE CODES TO A CLASS THAT EXTENDS OF/HTTP/REQUEST */
    public function getFiles($param=null){
		if($param){
			if(isset($_FILES[$param])){
				return $_FILES[$param];
			} else {
				return null;
			}
		} else {
			return $_FILES;
		}
    }
    /* END: NOTE */
}
?>