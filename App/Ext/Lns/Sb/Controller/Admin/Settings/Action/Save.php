<?php
namespace Lns\Sb\Controller\Admin\Settings\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Site Settings';

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Std\Upload $Upload
	){
		parent::__construct($Url,$Message,$Session);
		$this->_upload = $Upload;
	}

	public function run(){
		$this->requireLogin();

		$canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage($this->_lang->getLang('settings_save_error'), 'danger');
			$this->_url->redirect('');
		}

		$this->upload();
		if(!$this->getPost('isAjax')){
			$this->_siteConfig->setDatas($this->getPost())->save();

			$this->_message->setMessage($this->_lang->getLang('settings_save_success'));
			$this->_url->redirect('/settings');
		}
	}

	protected function upload(){
		$path = 'Lns' . DS . 'Sb' . DS . 'View' . DS . 'images' . DS . 'uploads';

		$result = [
			'site_favicon' => '',
			'site_logo' => '',
		];

		$site_favicon = $this->getFile('site_favicon');
		if($site_favicon){
			$result['site_favicon'] = $this->uploadHelper($site_favicon, 'site_favicon', $path);
		}

		$site_logo = $this->getFile('site_logo');
		if($site_logo){
			$result['site_logo'] = $this->uploadHelper($site_logo, 'site_logo', $path);
		}
		if($result['site_favicon'] != '' || $result['site_logo'] != ''){
			$this->jsonEncode($result);
			die;
		}
	}

	protected function uploadHelper($file, $key, $path){
		$_file = $this->_upload->setFile($file)
		->setPath($path)
		->setNewName('favicon')
		->setAcceptedFile(['ico','jpg','png','jpeg'])
		->save();

		$siteSettingKey = $this->_siteConfig->getData($key);

		if(isset($_file['error']) && $_file['error'] == 0){
			if($siteSettingKey){
				$oldFile = ROOT . DS . 'App' . DS . 'Ext' . DS . $path . DS . $siteSettingKey;
				if(file_exists($oldFile)){
					unlink($oldFile);
				}
			}

			$f = $_file['file'];

			$siteSettingKey = $f['newName'] . '.' . $f['ext'];
			$this->_siteConfig->setData($key, $siteSettingKey)->save();
		}
		return $siteSettingKey;
	}
}
?>