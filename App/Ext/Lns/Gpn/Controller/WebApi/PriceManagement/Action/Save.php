<?php
namespace Lns\Gpn\Controller\WebApi\PriceManagement\Action;

class Save extends \Lns\Sb\Controller\Controller {

    protected $_price;
    protected $_price_history;

    protected $_dateTime;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\Pricehistory $Pricehistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_price = $Price;
        $this->_price_history = $Pricehistory;
        $this->_dateTime = $DateTime;
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

            $id = $this->getParam('id');
            $current_price = $this->getParam('current_price');
            $type_id = $this->getParam('type_id');
            $price = $this->getParam('price');

            if ($current_price == $price) {
                $this->jsonData['message'] = 'Your current price and new price seems similar';
            } else {
                if ($id) {
                    $price_entity = $this->_price->getByColumn(['id' => $id], 1);

                    /* save previous price as history */
                    $price_history_entity = $this->_price_history;
                    $price_history_entity->setData('type_id', $price_entity->getData('type_id'));
                    $price_history_entity->setData('price', $price);
                    /* $price_history_entity->setData('created_at', $this->_dateTime->getTimestamp()); */
                    $price_history_entity->__save();
                    /* save previous price as history */

                    /* update price */
                    $price_entity->setData('price', $price);
                    $price_entity->setData('updated_at', $this->_dateTime->getTimestamp());
                    $price_entity->__save();
                    /* update price */
                } else {

                    $price_history_entity = $this->_price_history;
                    $price_history_entity->setData('type_id', $type_id);
                    $price_history_entity->setData('price', $price);
                    $price_history_entity->__save();

                    $price_entity = $this->_price;
                    $price_entity->setData('price', $price);
                    $price_entity->setData('type_id', $type_id);
                    $price_entity->__save();
                }
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = "New price has been saved";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>