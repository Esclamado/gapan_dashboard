<?php
namespace Lns\Gpn\Schema;

use Of\Std\Status;

class Install extends \Of\Db\Createtable {
	/* PLEASE CHECK LAHAT NG MAY DEFAULT VALUE KASI MADALAS WALANG LAMAN */
	protected $_eggType;
	protected $_feeds;
	protected $_house;
	protected $_medicine;
	protected $_roles;
	protected $_vat;
	protected $_trayTypes;
	protected $_otherInventory;
	protected $_egg_cart_type;
	protected $_payment_type;

    public function __construct(
		\Of\Std\Versioncompare $Versioncompare,
		\Lns\Gpn\Lib\Entity\Db\Eggtype $Eggtype,
		\Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
		\Lns\Gpn\Lib\Entity\Db\House $House,
		\Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
		\Lns\Gpn\Lib\Entity\Db\Roles $Roles,
		\Lns\Gpn\Lib\Entity\Db\Traytypes $Traytypes,
		\Lns\Gpn\Lib\Entity\Db\Vat $Vat,
		\Lns\Gpn\Lib\Entity\Db\OtherInventory $OtherInventory,
		\Lns\Gpn\Lib\Entity\Db\EggCartType $EggCartType,
		\Lns\Gpn\Lib\Entity\Db\PaymentType $PaymentType,
		\Lns\Gpn\Lib\Entity\Db\CustomerType $CustomerType
    ) {
		parent::__construct($Versioncompare);
		$this->_eggType = $Eggtype;
		$this->_feeds = $Feeds;
		$this->_house = $House;
		$this->_medicine = $Medicine;
		$this->_roles = $Roles;
		$this->_trayTypes = $Traytypes;
		$this->_vat = $Vat;
		$this->_otherInventory = $OtherInventory;
		$this->_egg_cart_type = $EggCartType;
		$this->_payment_type = $PaymentType;
		$this->_customer_type = $CustomerType;
    }
    public function createSchema() {
        $this->createAuditTrailTable();
		$this->createCartTable();
		$this->createChickenPopulationTable();
		$this->createDailyHouseHarvestTable();
		$this->createDailyHouseTable();
		$this->createDailyHouseUserTable();
		$this->createDailySortingInventoryTable();
		$this->createDailySortingInventoryHistoryTable();
		$this->createDailySortingReportTable();
		$this->createEggInventoryTable();
		$this->createEggTypeTable();
		$this->createFeedsTable();
		$this->createFeedUnitTable();
		$this->createHouseTable();
		$this->createIncidentReportTable();
		$this->createMedicineTable();
		$this->createOrdersTable();
		$this->createOrderItems();
		$this->createOrderItemDetails();
		$this->createPaymentTable();
		$this->createPriceTable();
		$this->createPriceHistoryTable();
		$this->createVatTable();
		$this->createTrayReportTable();
		$this->createTrayInventoryReportTable();
		$this->createInflowTable();
		$this->createOutflowTable();
		$this->createOtherInventoryTable();
		$this->createEggCartTypeTable();
		$this->createCartDetailsTable();
		$this->createPaymentTypeTable();
		$this->createOrderStatusTable();
		$this->createMedicineUnitTable();
		$this->createPaymentAttachmentTable();
		$this->createFreshEggInventoryTable();
		$this->createInOutEggs();
		/* $this->createTraySackInventory(); */

		$this->createSackInventory();
		$this->createSackBldgInventory();
		$this->createTrayTypes();
		$this->createTrayInventory();
		$this->createOrderCancelDeclineTable();
		$this->createCustomerTypeTable();
		$this->createPaymentHistoryTable();

		/* ADD DATA : START */
		$this->_eggType->installData();
		$this->_feeds->installData();
		$this->_house->installData();
		$this->_medicine->installData();
		$this->_vat->installData();
		$this->_roles->installData();
		$this->_trayTypes->installData();
		$this->_otherInventory->installData();
		$this->_egg_cart_type->installData();
		$this->_payment_type->installData();
		$this->_customer_type->installData();
		/* ADD DATA : END */
    }
    /* CREATE AUDIT TRAIL TABLE : START */
    protected function createAuditTrailTable() {
        $this->setTablename('audit_trail');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
        ]);
        $this->addColumn([
			'name' => 'name',
			'type' => self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'action',
			'type' => self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'code',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
    }
    /* CREATE AUDIT TRAIL TABLE : END */
    /* CREATE CART TABLE : START */
    protected function createCartTable() {
        $this->setTablename('cart');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
        ]);
        $this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'egg type id',
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
    }
	/* CREATE CART TABLE : END */
	/* CREATE CHICKEN POPULATION TABLE : START */
    protected function createChickenPopulationTable() {
        $this->setTablename('chicken_population');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'begin_population',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'beginning population',
        ]);
        $this->addColumn([
			'name' => 'begin_date',
			'type' => self::_DATE,
			'nullable' => true,
        ]);
        $this->addColumn([
			'name' => 'end_population',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'end population',
        ]);
        $this->addColumn([
			'name' => 'end_date',
			'type' => self::_DATE,
			'nullable' => true,
        ]);
        $this->save();
    }
	/* CREATE CHICKEN POPULATION TABLE : END */
	/* CREATE DAILY HOUSE HARVEST TABLE : START */
	protected function createDailyHouseHarvestTable() {
		$this->setTablename('daily_house_harvest');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name' => 'house_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house id',
		]);
		$this->addColumn([
			'name' => 'bird_count',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'age_week',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'age_day',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'mortality',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'cull',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'medicine_ids',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'multiple comma separated ids'
		]);
		$this->addColumn([
			'name' => 'medicine_values',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'multiple comma separated ids'
		]);
		$this->addColumn([
			'name' => 'feed_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'feed_consumption',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
			'default' => 0.00
		]);
		$this->addColumn([
			'name' => 'rec_feed_consumption',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
			'default' => 0.00 /* paki check sa database kasi minsan walang default value */
		]);
		$this->addColumn([
			'name' => 'feed_unit_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'egg_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'real_egg_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'schedule',
			'type' => self::_DATE,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'prepared_by_isdraft',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'checked_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'checked_by_isdraft',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'received_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'received_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'received_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'received_by_isdraft',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'recordStatus',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'isSorted',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE DAILY HOUSE HARVEST TABLE : END */
	/* CREATE DAILY HOUSE TABLE : START */
	protected function createDailyHouseTable() {
		$this->setTablename('daily_house');
		$this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'daily_house_harvest_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'daily house harvest id',
		]);
		$this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'user id',
		]);
		$this->save();
	}
	/* CREATE DAILY HOUSE TABLE : END */
	/* CREATE DAILY HOUSE USER TABLE : START */
	protected function createDailyHouseUserTable() {
		$this->setTablename('daily_house_user');
		$this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name' => 'house_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house id',
		]);
		$this->save();
	}
	/* CREATE DAILY HOUSE USER TABLE : END */
	/* CREATE DAILY SORTING INVENTORY TABLE : START */
	protected function createDailySortingInventoryTable() {
		$this->setTablename('daily_sorting_inventory');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'sorted_report_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'house_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'egg_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE DAILY SORTING INVENTORY TABLE : END */
	/* CREATE DAILY SORTING INVENTORY HISTORY TABLE : START */
	protected function createDailySortingInventoryHistoryTable() {
		$this->setTablename('daily_sorting_inventory_history');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'sorted_inv_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'original_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'updated_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0
		]);
		$this->save();
	}
	/* CREATE DAILY SORTING INVENTORY HISTORY TABLE : END */
	/* CREATE DAILY SORTING REPORT TABLE : START */
	protected function createDailySortingReportTable() {
		$this->setTablename('daily_sorting_report');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name' => 'house_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house id',
		]);
		$this->addColumn([
			'name' => 'house_harvest_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house harvest id',
		]);
		$this->addColumn([
			'name' => 'chicken_pop_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'chicken population id',
		]);
		$this->addColumn([
			'name' => 'real_egg_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0,
			'comment' => 'egg count received',
			/* 'default' => 0 */
		]);
		$this->addColumn([
			'name' => 'egg_count',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'default' => 0,
			'comment' => 'final egg count',
			/* 'default' => 0 */
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'checked_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'received_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'received_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'received_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'production_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'ir_status',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE DAILY SORTING REPORT TABLE : END */
	/* CREATE EGG INVENTORY TABLE : START */
    protected function createEggInventoryTable() {
        $this->setTablename('egg_inventory');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'egg type id',
        ]);
        $this->addColumn([
			'name' => 'house_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house id',
        ]);
        $this->addColumn([
			'name' => 'egg_count',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'egg count',
        ]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
    }
	/* CREATE EGG INVENTORY TABLE : END */
	/* CREATE EGG TYPE TABLE : START */
    protected function createEggTypeTable() {
        $this->setTablename('egg_type');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'type_shortcode',
			'type' => self::_VARCHAR,
			'length' => 20,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
        $this->save();
    }
	/* CREATE EGG TYPE TABLE : END */
	/* CREATE FEEDS TABLE : START */
    protected function createFeedsTable() {
        $this->setTablename('feeds');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'feed',
			'type' => self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'net_weight',
			'type' => self::_INT,
			'length' => 11,
			'default' => 0,
			'nullable' => false,
		]);
        $this->addColumn([
			'name' => 'kg_per_bag',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'pieces',
			'type' => self::_INT,
			'length' => 11,
			'default' => 0,
			'nullable' => false,
			'comment' => 'quantity',
		]);
		$this->addColumn([
			'name' => 'delivery_date',
			'type' => self::_DATE,
			'nullable' => false,
			'comment' => 'delivery date',
		]);
		$this->addColumn([
			'name' => 'expiration_date',
			'type' => self::_DATE,
			'nullable' => false,
			'comment' => 'expiration date',
		]);
		$this->addColumn([
			'name' => 'remarks',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'remarks',
		]);
		$this->addColumn([
			'name' => 'unit_price',
			'type' => self::_INT,
			'length' => 11,
			'default' => 0,
			'nullable' => false,
			'comment' => 'price',
		]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
        $this->save();
    }
	/* CREATE FEEDS TABLE : END */
	/* CREATE FEED UNIT TABLE : START */
    protected function createFeedUnitTable() {
        $this->setTablename('feed_unit');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'unit',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
        $this->save();
    }
	/* CREATE FEED UNIT TABLE : END */
	/* CREATE HOUSE TABLE : START */
    protected function createHouseTable() {
        $this->setTablename('house');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'chicken_pop_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'chicken population id',
        ]);
        $this->addColumn([
			'name' => 'house_name',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'capacity',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
    }
	/* CREATE HOUSE TABLE : END */
	/* CREATE INCIDENT REPORT TABLE : START */
	protected function createIncidentReportTable() {
		$this->setTablename('incident_report');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'sender_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'sender id',
		]);
		$this->addColumn([
			'name' => 'receiver_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'receiver id',
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'comment' => 'type of incident',
		]);
		$this->addColumn([
			'name' => 'reference_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'reference id',
		]);
		$this->addColumn([
			'name' => 'reason',
			'type' => self::_TEXT,
			'nullable' => false,
			'comment' => 'reason for incident',
		]);
		$this->addColumn([
			'name' => 'declared_qty',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'validated_qty',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'signature_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
	}
	/* CREATE INCIDENT REPORT TABLE : END */
    /* CREATE MEDICINE TABLE : START */
    protected function createMedicineTable() {
        $this->setTablename('medicine');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'medicine',
			'type' => self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'unit_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'net_weight',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'pieces',
			'type' => self::_INT,
			'default' => 0,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'delivery_date',
			'type' => self::_DATE,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'expiration_date',
			'type' => self::_DATE,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'unit_price',
			'type' => self::_INT,
			'default' => 0,
			'length' => 10,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'remarks',
			'type' => self::_TEXT,
			'nullable' => true,
		]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
        $this->save();
    }
	/* CREATE MEDICINE TABLE : END */
	/* CREATE NEW ORDERS TABLE : START */
	protected function createOrdersTable() {
		$this->setTablename('orders');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'user_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
        ]);
        $this->addColumn([
			'name' => 'transaction_id',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'order_status',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
        ]);
        $this->addColumn([
			'name' => 'payment_status',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'mode_of_payment',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'total_price',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'default' => 0.00
		]);
		$this->addColumn([
			'name' => 'discount',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'default' => 0.00,
		]);
        $this->addColumn([
			'name' => 'note',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'note',
		]);
		$this->addColumn([
			'name' => 'feedback',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'feedback',
		]);
		$this->addColumn([
			'name' => 'date_to_pickup',
			'type' => self::_DATETIME,
			'nullable' => true,
			'comment' => 'date to pickup',
		]);
		$this->addColumn([
			'name' => 'decline_resolved',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0,
		]);
		$this->addColumn([
			'name' => 'walk_in_created_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
        ]);
		$this->addColumn([
			'name' => 'approved_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'checked_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'checked_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'for_release',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0,
		]);
		$this->addColumn([
			'name' => 'date_paid',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE NEW ORDERS TABLE : END */
	/* CREATE ORDER ITEMS TABLE : START */
	protected function createOrderItems() {
		$this->setTablename('order_items');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'order_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'order id',
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'egg type id',
		]);
		$this->save();
	}
	/* CREATE ORDER ITEMS TABLE : END */
	protected function createOrderItemDetails() {
		$this->setTablename('order_item_details');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'order_item_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'order item id',
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'comment' => 'case, tray, pieces',
		]);
		$this->addColumn([
			'name' => 'qty',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'price',
			'type' => self::_DECIMAL,
			'length' => '10,2',
            'nullable' => false,
            'default' => 0.00,
		]);
		$this->save();
	}
	/* CREATE PAYMENT TABLE : START */
    protected function createPaymentTable() {
        $this->setTablename('payment');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'order_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'order id',
        ]);
        $this->addColumn([
			'name' => 'payment',
			'type' => self::_DECIMAL,
			'length' => '10,2',
            'nullable' => false,
            'default' => 0.00,
        ]);
        $this->addColumn([
			'name' => 'balance',
			'type' => self::_DECIMAL,
			'length' => '10,2',
            'nullable' => false,
            'default' => 0.00,
		]);
		$this->addColumn([
			'name' => 'reason',
			'type' => self::_TEXT,
			'nullable' => true,
		]);
        $this->addColumn([
			'name' => 'due_date',
			'type' => self::_DATE,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'approved_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'approved_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'approved_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'receipt_no',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
    }
    /* CREATE PAYMENT TABLE : END */
    /* CREATE PRICE TABLE : START */
    protected function createPriceTable() {
        $this->setTablename('price');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'egg type id',
        ]);
        $this->addColumn([
			'name' => 'price',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
			'comment' => 'price',
        ]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
    }
	/* CREATE PRICE TABLE : END */
	/* CREATE PRICE HISTORY TABLE : START */
	protected function createPriceHistoryTable() {
		$this->setTablename('price_history');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'egg type id',
        ]);
        $this->addColumn([
			'name' => 'price',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
			'comment' => 'price',
        ]);
        $this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE PRICE HISTORY TABLE : END */
	/* CREATE VAT TABLE : START */
	protected function createVatTable() {
		$this->setTablename('vat');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'vat',
			'type' => self::_DECIMAL,
			'length' => '10,2',
			'nullable' => false,
			'default' => 0.00,
			'comment' => 'vat percentage',
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
	}
	/* CREATE VAT TABLE : END */
	/* CREATE TRAY AND SACK INVENTORY : START */
		protected function createTraySackInventory() {
			$this->setTablename('tray_sack_inventory');
			$this->setPrimarykey('id');
			$this->addColumn([
				'name' => 'id',
				'type' => self::_INT,
				'length' => 10,
				'ai' => self::_AI,
			]);
			$this->addColumn([
				'name' => 'item',
				'type' => self::_VARCHAR,
				'length' => 255,
				'nullable' => false,
			]);
			$this->addColumn([
				'name' => 'quantity',
				'type' => self::_BIGINT,
				'length' => 20,
				'nullable' => false
			]);
			$this->save();
		}
	/* CREATE TRAY AND SACK INVENTORY : END */
	/* CREATE SACK INVENTORY : START */
	protected function createSackInventory() {
		$this->setTablename('sack_inventory');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'total_out',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'sales',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'total_in',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'last_ending',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'remarks',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'remarks_out',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
	}
	/* CREATE SACK INVENTORY : END */
	/* CREATE SACK BUILDING INVENTORY : START */
	protected function createSackBldgInventory() {
		$this->setTablename('sack_bldg_inventory');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'sack_inv_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'house_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false,
			'comment' => 'house id',
		]);
		$this->addColumn([
			'name' => 'count',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE SACK BUILDING INVENTORY : END */
	/* CREATE TRAY TYPES : START */
	protected function createTrayTypes() {
		$this->setTablename('tray_types');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE TRAY TYPES : END */
	/* CREATE TRAY INVENTORY : START */
	protected function createTrayInventory() {
		$this->setTablename('tray_inventory');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'in_return',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'sorting',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'marketing',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'out_hiram',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE TRAY INVENTORY : END */
	/* CREATE TRAY REPORT : START */
	protected function createTrayReportTable(){
		$this->setTablename('tray_report');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'prepared_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_path',
			'type' => self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'prepared_by_date',
			'type' => self::_DATETIME,
			'nullable' => true,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE TRAY REPORT : END */
	/* CREATE TRAY INVENTORY REPORT : START */
	protected function createTrayInventoryReportTable(){
		$this->setTablename('tray_inventory_report');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'in_return',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'sorting',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'marketing',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'out_hiram',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'total_end',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'tray_report_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE TRAY INVENTORY REPORT : END */
	/* CREATE INFLOW : START */
	protected function createInflowTable(){
		$this->setTablename('inflow');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'reference_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE INFLOW : END */
	/* CREATE OUTFLOW : START */
	protected function createOutflowTable()
	{
		$this->setTablename('outflow');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'reference_id',
			'type' => self::_INT,
			'length' => 10,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE OUTFLOW : END */
	/* CREATE OTHER INVENTORY : START */
	protected function createOtherInventoryTable() {
		$this->setTablename('other_inventory');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'last_ending',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE OTHER INVENTORY : END */
	/* CREATE EGG CART TYPE : START */
	protected function createEggCartTypeTable(){
		$this->setTablename('egg_cart_type');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE EGG CART TYPE : END */
	/* CREATE EGG CART DETAILS TABLE : START */
	protected function createCartDetailsTable(){
		$this->setTablename('cart_details');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'cart_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'cart_id',
		]);
		$this->addColumn([
			'name' => 'type_id',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'comment' => 'type',
		]);
		$this->addColumn([
			'name' => 'qty',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'quantity',
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE EGG CART DETAILS TABLE : END */
	/* CREATE PAYMENT TYPE TABLE : START */
	protected function createPaymentTypeTable(){
		$this->setTablename('payment_type');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE PAYMENT TYPE TABLE : END */
	/* CREATE ORDER STATUS TABLE : END */
	protected function createOrderStatusTable(){
		$this->setTablename('order_status');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'order_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'status',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE ORDER STATUS TABLE : END */
	/* CREATE ORDER CANCELLED DECLINED TABLE : START */
	protected function createOrderCancelDeclineTable() {
		$this->setTablename('order_cancel_decline');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'order_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'comment' => '1 = Cancel; 2 = Decline'
		]);
		$this->addColumn([
			'name' => 'decline_cause',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'message',
			'type' => self::_VARCHAR,
			'length' => 250,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE ORDER CANCELLED DECLINED TABLE : END */
	/* CREATE MEDICINE UNIT TABLE : START */
	protected function createMedicineUnitTable()
	{
		$this->setTablename('medicine_unit');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'unit',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE MEDICINE UNIT TABLE : END */
	/* CREATE PAYMENT ATTACHMENT TABLE : START */
	protected function createPaymentAttachmentTable()
	{
		$this->setTablename('payment_attachments');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'attachment_no',
			'length' => 100,
			'type' => self::_VARCHAR,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'attachment',
			'type' => self::_TEXT,
			'nullable' => true,
			'comment' => 'file path',
		]);
		$this->addColumn([
			'name' => 'payment_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'payment id',
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'comment' => '1 = receipt, 2 = payment form, 3 = credit form, 4 = balance form',
		]);
		$this->addColumn([
			'name' => 'uploaded_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE PAYMENT ATTACHMENT TABLE : END */
	/* CREATE FRESHEGGINVENTORY TABLE : START */
	protected function createFreshEggInventoryTable(){
		$this->setTablename('freshegg_inventory');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'beginning_stocks',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0,
			'comment' => 'beginning stocks',
		]);
		$this->addColumn([
			'name' => 'total_harvested',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0,
			'comment' => 'total harvested',
		]);
		$this->addColumn([
			'name' => 'waste_sales',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0,
			'comment' => 'waste and sales',
		]);
		$this->addColumn([
			'name' => 'total_remaining_stocks',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0,
			'comment' => 'remaining stocks',
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE FRESHEGGINVENTORY TABLE : END */
	/* CREATE CUSTOMER PROFILE : START */
	protected function createCustomerTypeTable(){
		$this->setTablename('customer_type');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'type',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->save();
	}
	/* CREATE CUSTOMER PROFILE : END */

	/* CREATE IN OUT EGGS : START */
	protected function createInOutEggs() {
		$this->setTablename('in_out_eggs');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name' => 'egg_in',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0 /* paki check sa database kasi minsan walang default value */
		]);
		$this->addColumn([
			'name' => 'egg_out',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'default' => 0 /* paki check sa database kasi minsan walang default value */
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	/* CREATE IN OUT EGGS : END */
	/* CREATE PAYMENT HISTORY TABLE : START */
    protected function createPaymentHistoryTable() {
        $this->setTablename('payment_history');
        $this->setPrimarykey('id');
        $this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
        ]);
        $this->addColumn([
			'name' => 'order_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'order id',
		]);
		$this->addColumn([
			'name' => 'payment_attachments_id',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'order id',
		]);
        $this->addColumn([
			'name' => 'payment',
			'type' => self::_DECIMAL,
			'length' => '10,2',
            'nullable' => false,
            'default' => 0.00,
		]);
		$this->addColumn([
			'name' => 'receipt_no',
			'type' => self::_VARCHAR,
			'length' => 50,
			'nullable' => true
		]);
		$this->addColumn([
			'name' => 'created_by',
			'type' => self::_BIGINT,
			'length' => 20,
			'nullable' => false
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
        ]);
		$this->save();
    }
    /* CREATE PAYMENT HISTORY TABLE : END */
}
?>