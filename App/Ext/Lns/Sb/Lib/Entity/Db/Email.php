<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Email extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const COLUMNS = [
		'id',
		'template_name',
		'template_code',
		'subject',
        'template',
		'from_name',
		'email',
		'created_at',
		'update_at',
	];
	

	protected $tablename = 'email_templates';
	protected $primaryKey = 'id';
	protected $_session;
	protected $_request;

	public function __construct(
		\Of\Http\Request $Request
	){
		parent::__construct($Request);
		$this->_request = $Request;
	}

	public function saveTemplate($template_id){
		$postedFormData = $this->_request->getParam();

		$template_code =  str_replace(' ','_',strtolower($postedFormData['template_name']));
        if($template_id){
			$email = $this->getByColumn(['id' => $postedFormData['id']]);
			$postedFormData['template_code'] = $email->getData('template_code');
		}else{
			$postedFormData['template_code'] = preg_replace('/[^A-Za-z0-9\_]/', '',$template_code);
		}
        $this->saveEntity($postedFormData);

		$response['error'] = 0;
		$response['message'] = 'The email template successfully saved.';
		
		return $response;
	}

	public function deleteEmailTemplate($userId){
		$user = $this->getByColumn(['id' => $userId]);
		if($user){
			return $this->delete([$this->primaryKey => $userId]);
		} else{
			return false;
		}
	}

	public function getEmailTemplates(){
		$this->setOrderBy('created_at');
		$this->setIsCache(true);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$email_templates = $this->getFinalResponse(10);

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $email_templates;
	}

    public function getEmailTemplateById($template_id){	
        $email_template = $this->getByColumn(['id' => $template_id], 1, null, false);
        return $email_template->getData();
    } 
}
