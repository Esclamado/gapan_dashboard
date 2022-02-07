<?php 
namespace Lns\Sb\Lib\Entity\Db;

class MailTemplate extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'email_templates';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
        'template_name',
		'template_code',
        'subject',
		'template',
		'from_name',
		'email',
		'created_at',
		'updated_at',
        
	];

	public function getTemplate(){
		return $this->getFinalResponse();
	}
}

 