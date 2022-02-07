<?php
namespace Lns\Gpn\Controller\WebApi\Eggtype\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_egg_type;
    protected $_price;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_price = $Price;
        $this->_egg_type = $Eggtype;
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
                $type_shortcode = $this->getParam('type_shortcode');
                $type = $this->getParam('type');
                $price = $this->getParam('price');

                $egg_type_entity = $this->_egg_type;
                $egg_type_entity->setData('type_shortcode', $type_shortcode);
                $egg_type_entity->setData('type', $type);
                $type_id = $egg_type_entity->__save();

                if ($type_id) {
                    $price_entity = $this->_price;
                    $price_entity->setData('type_id', $type_id);
                    $price_entity->setData('price', $price);
                    $price_id = $price_entity->__save();
                    if ($price_id) {
                        $this->jsonData['error'] = 0;
                        $this->jsonData['message'] = "New egg size has been saved";
                    } else {
                        $this->jsonData['message'] = "New egg size could not be saved. Please try again";
                    }
                } else {
                    $this->jsonData['message'] = "New egg size could not be saved. Please try again";
                }
            }
        $this->jsonEncode($this->jsonData);
        die;
    }
}