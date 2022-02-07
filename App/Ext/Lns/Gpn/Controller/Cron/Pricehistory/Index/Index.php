<?php
namespace Lns\Gpn\Controller\Cron\Pricehistory\Index;

class Index extends \Lns\Sb\Controller\Controller {

    protected $_eggtype;
    protected $_pricehistory;
    protected $_dateTime;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\DateTime\DateTime $DateTime,
        \Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
        \Lns\Gpn\Lib\Entity\Db\Pricehistory $Pricehistory
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->_pricehistory = $Pricehistory;
        $this->_eggtype = $Eggtype;
        $this->_dateTime = $DateTime;
    }
    public function run() {
        $egg_types = $this->_eggtype->getCollection();
        if ($egg_types) {
            foreach ($egg_types as $egg_type) {
                $latestRecord = $this->_pricehistory->getLatestRecord($egg_type->getData('id'));
                if ($latestRecord) {
                    /* check if date is less than today */
                    if (date('Y-m-d', strtotime($latestRecord->getData('created_at'))) < date('Y-m-d')) {
                        $entity = $this->_pricehistory;
                        $entity->setDatas([
                            'type_id' => $latestRecord->getData('type_id'),
                            'price' => $latestRecord->getData('price')
                        ])->__save();
                    }
                }
            }
        }
    }
}
?>