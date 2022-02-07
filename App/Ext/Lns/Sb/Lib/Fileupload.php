<?php
namespace Lns\Sb\Lib;
class Fileupload extends \Lns\Sb\Controller\Controller {
	
	protected $_path;
	protected $_filename;
	protected $_filetype = array();
	protected $_ext = array();
	protected $width;
	protected $height;
	protected $savedFile;
	
	
	public function setHeight($height){
		$this->height = $height;
		return $this;
	}
	
	public function getHeight(){
		return $this->height;
	}
	
	public function setWidth($width){
		$this->width = $width;
		return $this;
	}
	
	public function getWidth(){
		return $this->width;
	}
	
	protected $fileUpoadError = [
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'The uploaded file was only partially uploaded',
		4 => 'No file was uploaded',
		6 => 'Missing a temporary folder',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.',
	];
	
	public function __construct(){
		$this->_path = __DIR__ . '/../../../../../tmpmedia';

		if(!is_dir($this->_path)){
			$this->createDir($this->_path);
		}
	}
	
	public function getFileUpoadError(){
		return $this->fileUpoadError;
	}
	
	public function createDir($path){
		$create = false;
		if(!is_dir($path)){
			if (mkdir($path, 0777, true)){
				$create = true;
			}
		}
		return $create;
	}
	
	public function setPath($path){
		$this->_path = $path;
		return $this;
	}
	
	public function setFileName($filename){
		$this->_filename = $filename;
		return $this;
	}
	
	public function setFileType($filetype){
		if(is_array($filetype)){
			$this->_filetype = $filetype;
		} else {
			$this->_filetype[] = $filetype;
		}
		return $this;
	}
	
	public function setSavedFile($savedFile){
		$this->savedFile = $savedFile;
		return $this;
	}
	
	public function __delete(){
		/*$file = __DIR__ . '/../../../../../public_html'.$this->savedFile->filepath.'/'.$this->savedFile->filename;
		if(file_exists($file)){
			unlink($file);
			$this->savedFile = null;
			return true;
		}
		return false;*/
	}
	
	public function save($postFile, $pathPrefix=""){
		$uploadResult = array(
			'msg' => '',
			'filename' => '',
			'error' => 0,
		);
		
		if(!$this->_path){
			return false;
		}

		$this->_path .= '/'.$pathPrefix;

		if($postFile['error'] == 0){ 
			$fileInfo = pathinfo($postFile['name']);
			$this->_ext = strtolower($fileInfo['extension']);
			$this->_filename = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $this->generate(17)));
			$this->_filename .= '_' . time();
			$this->_filename .= '_' . strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $this->generate(13)));
			
			if(!in_array($this->_ext, $this->_filetype)){
				$uploadResult['msg'] = 'Invalid file.';
				$uploadResult['error'] = 1;
				$uploadResult['data'] = $postFile;
			}
			
			$firstDir = strtolower(substr($this->_filename, 0, 1));
			$secondDir = strtolower(substr($this->_filename, 1, 1));

			
			if($uploadResult['error'] == 0) {
				$filename = $this->_filename;
				$targetPath = $this->_path.'/'.$firstDir.'/'.$secondDir;
				
				if(!is_dir($targetPath)){
					$this->createDir($targetPath);
				}
				/*
				* during the multi upload, generated file name
				* goes the same, make sure that it will not overide 
				* the exisisting file via concatinating incremental value
				*/
				$count = 1;
				while(file_exists( $targetPath .'/'. $filename . "." . $this->_ext ) ){
					 $filename =  $this->_filename . "_" . $count;
					 $count++;
				}
				$this->_filename = $filename;
				$targetFilePath = $targetPath .'/'. $this->_filename . "." . $this->_ext;
				
				if(move_uploaded_file($postFile['tmp_name'], $targetFilePath)){
					$this->jsonData['msg'] = 'File Successfully Uploaded.';
					$this->jsonData['filepath'] = '/tmpmedia/' . $pathPrefix . '/' . $firstDir . '/' . $secondDir;
					$this->jsonData['filename'] = $this->_filename . "." . $this->_ext;
					$this->jsonData['data'] = $postFile;
				}
			}
		} else {
			$this->jsonData['error'] = $postFile['error'];
			$this->jsonData['msg'] = $this->fileUpoadError[$postFile['error']];
		}

		return $this->jsonData;
	}

	public function moveToMedia($tempMedia, $fileName){
		$path = ltrim($tempMedia, '/');
		$path = str_replace('tmpmedia', '', $path);
		$path = ltrim($path, '/');
		
		$filePath = $path . '/' . $fileName;

		$root = __DIR__ . '/../../../../../';

		$tempFilePath = $root . 'tmpmedia/' . $filePath;
		/*$destinationPath = $root . 'media/' . $filePath;*/
		$destinationPath = $root . 'media/' . $path;

		$result = [
			'error' => 1,
			'message' => ''
		];

		$fileInfo = pathinfo($fileName);
		$ext = $fileInfo['extension'];
		$fileName = $fileInfo['filename'];

		if(file_exists($tempFilePath)){
			$mediaDir = $root.'media/'.$path;
			if(!is_dir($mediaDir)){
				$this->createDir($mediaDir);
			}

			$count = 1;
			while(file_exists( $mediaDir .'/'. $fileName . "." . $ext ) ){
				 $fileName =  $fileName . "_" . $count;
				 $count++;
			}
			$destinationPath = $mediaDir .'/'. $fileName . "." . $ext;
			
			if(rename($tempFilePath,$destinationPath)){
				$result['error'] = 0;
				$result['message'] = 'Sucess';
				$result['filename'] = $fileName . "." . $ext;
				$result['filepath'] = $path;
			} else {
				$result['message'] = "Can't move to media.";
			}
		} else {
			$result['message'] = 'Not exist.';
		}

		return $result;
	}
	
	public static function generate($length=10) {
		$key = '';
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));
		
		$inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

		for($i=0; $i<$length; $i++)
		{
			$key .= $inputs{mt_rand(0,61)};
		}
		return $key;
	}
	
	public static function create(){
		return new self();
	}
}

?>