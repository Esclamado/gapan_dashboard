<?php 
namespace Lns\Sb\Lib\Html;

class Emails extends \Of\Html\Context{

    protected $_emails;
    protected $_request;
    protected $template_data;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config,
        \Of\Http\Request $Request,
        \Lns\Sb\Lib\Entity\Db\Email $Email
    ){
        parent::__construct($Url, $Config);
        $this->_emails = $Email;
        $this->_request = $Request;
    }

    protected function getEmailById(){
        $emailId = $this->_request->getParam('email');
        if($emailId){
            $this->template_data = $this->_emails->getEmailTemplateById($emailId);
        }
        return $this->template_data;
    }

    protected function getEmailTemplates(){
        $templates =  $this->_emails->getEmailTemplates();
        return $templates;
    }
}
?>