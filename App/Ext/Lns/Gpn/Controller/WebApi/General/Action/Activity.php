<?php
namespace Lns\Gpn\Controller\WebApi\General\Action;

class Activity extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;

    protected $_house;
    protected $_users;
    protected $_feeds;
    protected $_medicine;
    protected $_dailyhouseharvest;
    protected $_dailysortingreport;

    protected $_orders;
    protected $_sackinventory;
    protected $_trayreport;
    protected $_freshegginventory;
    protected $_price;
    protected $_payment_attachments;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders,
        \Lns\Gpn\Lib\Entity\Db\Sackinventory $Sackinventory,
        \Lns\Gpn\Lib\Entity\Db\TrayReport $TrayReport,
        \Lns\Gpn\Lib\Entity\Db\FresheggInventory $FresheggInventory,
        \Lns\Gpn\Lib\Entity\Db\Price $Price,
        \Lns\Gpn\Lib\Entity\Db\PaymentAttachments $PaymentAttachments,
        \Lns\Gpn\Lib\Entity\Db\Dailysortingreport $Dailysortingreport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_house = $House;
        $this->_users = $Users;
        $this->_feeds = $Feeds;
        $this->_medicine = $Medicine;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_dailysortingreport = $Dailysortingreport;
        $this->_orders = $Orders;
        $this->_sackinventory = $Sackinventory;
        $this->_trayreport = $TrayReport;
        $this->_freshegginventory = $FresheggInventory;
        $this->_price = $Price;
        $this->_payment_attachments = $PaymentAttachments;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;
        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $page = $this->getParam('page');

            if ($page == 'house_listing') {
                $entity = $this->_house->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'staff_listing') {
                $entity = $this->_users->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'customer_listing') {
                $entity = $this->_users->getLatestActivity(3);
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'feeds_listing') {
                $entity = $this->_feeds->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'medicine_listing') {
                $entity = $this->_medicine->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'daily_reports_listing') {
                $entity = $this->_dailyhouseharvest->getLatestActivity($this->getParam('house_id'));
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'daily_sorting_reports_listing') {
                $entity = $this->_dailysortingreport->getLatestActivity($this->getParam('house_id'));
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'transactions_listing') {
                $entity = $this->_orders->getLatestActivity($this->getParam('order_status'), $this->getParam('mode_of_payment'));
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'sacks_listing') {
                $entity = $this->_sackinventory->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'trays_listing') {
                $entity = $this->_trayreport->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'fresh_egg_inventory_listing') {
                $entity = $this->_freshegginventory->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'fresh_egg_inventory_view') {
                $entity = $this->_freshegginventory->getLatestActivity($this->getParam('date'));
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'price_management_listing') {
                $entity = $this->_price->getLatestActivity();
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            } else if ($page == 'sacks_view') {
                $entity = $this->_sackinventory->getByColumn(['id' => $this->getParam('id')], 1);
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity->getData();
                }
            } else if ($page == 'trays_view') {
                $entity = $this->_trayreport->getByColumn(['id' => $this->getParam('id')], 1);
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity->getData();
                }
            } else if ($page == 'payment_attachment_listing') {
                $entity = $this->_payment_attachments->getLatestActivity($this->getParam('id'));
                if ($entity) {
                    $this->jsonData['error'] = 0;
                    $this->jsonData['data'] = $entity[0]->getData();
                }
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>