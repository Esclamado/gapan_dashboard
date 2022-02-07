<?php
namespace Lns\Gpn\Controller\WebApi\Medicineunits\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $_medicineunit;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_medicineunit = $MedicineUnit;
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

            $medicineunits = $this->_medicineunit->getCollection();
            $result = [];
            if($medicineunits){
                foreach ($medicineunits as $key => $medicineunit) {
                    $result[] = $medicineunit->getData();
                }
                $this->jsonData['data'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No medicine unit found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>