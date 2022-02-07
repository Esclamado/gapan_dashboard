<?php
namespace Lns\Gpn\Schema;

use Of\Std\Status;

class Upgrade extends \Of\Db\Createtable {
	
	public function upgradeSchema($currentVersion, $newVersion){
		$vc = $this->versionCompare($currentVersion, $newVersion);

		if($vc == 1) {
			/* $tableName = $this->getTablename('test');
			$query = "ALTER TABLE `".$tableName."` ADD `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`;";
			$this->save($query); */
			
			/* $this->updateUserTable();
			$this->updateHouseTable();
			$this->updateFeedsTable();
			$this->updateMedicineTable();
			$this->updateOrdersTable();
			$this->updateDailyHouseHarvest();
			$this->updateFreshEggInventory();
			$this->updateUserstable();
			$this->updateAudittrailtable();
			$this->updatePaymentTable();
			$this->createPriceHistoryTable();
			$this->updatePriceTable();
			$this->updatePaymentAttachment(); */
			/* $this->createInOutEggs(); */
			/* $this->updateDailySortingReport(); */
			/* $this->createPaymentHistoryTable(); */
			/* $this->updateDailySortingInventoryTable(); */
			/* $this->updatePriceHistoryTable();
			$this->updatePriceTable(); */
		}
	}
	protected function updateUserTable() {
		$tableName = $this->getTablename('user');
		$query = "ALTER TABLE `".$tableName."` ADD `customer_type_id` BIGINT(20) NULL AFTER `user_role_id`;";
		$this->save($query);
	}
	protected function updateHouseTable() {
		$tableName = $this->getTablename('house');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
	protected function updateFeedsTable() {
		$tableName = $this->getTablename('feeds');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
	protected function updateMedicineTable() {
		$tableName = $this->getTablename('medicine');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
	protected function updateOrdersTable() {
		$tableName = $this->getTablename('orders');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);

		$query = "ALTER TABLE `".$tableName."` ADD `balance_credit_approved` TINYINT(1) NOT NULL DEFAULT 0 AFTER `mode_of_payment`;";
		$this->save($query);
		
		$query = "ALTER TABLE `".$tableName."` ADD `balance_credit_approved_by` BIGINT(20) NULL AFTER `balance_credit_approved`;";
		$this->save($query);

		$query = "ALTER TABLE `".$tableName."` ADD `balance_credit_approved_date` DATETIME NULL AFTER `balance_credit_approved_by`;";
		$this->save($query);

		$query = "ALTER TABLE `".$tableName."` ADD `walk_in_created_by` BIGINT(20) NULL AFTER `decline_resolved`;";
		$this->save($query);
	}
	protected function updateDailyHouseHarvest() {
		$tableName = $this->getTablename('daily_house_harvest');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
	protected function updateFreshEggInventory() {
		$tableName = $this->getTablename('freshegg_inventory');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
	protected function updateUserstable(){
		$tableName = $this->getTablename('user');
		$query = "ALTER TABLE `" . $tableName . "` ADD `real_password` VARCHAR(50) NULL AFTER `password`;";
		$this->save($query);
	}
	protected function updateAudittrailtable(){
		$tableName = $this->getTablename('audit_trail');
		$query = "ALTER TABLE `" . $tableName . "` ADD `code` VARCHAR(50) NULL AFTER `action`;";
		$this->save($query);
	}
	protected function updatePaymentTable() {
		$tableName = $this->getTablename('payment');
		$query = "ALTER TABLE `" . $tableName . "` ADD `receipt_no` VARCHAR(50) NULL AFTER `approved_date`;";
		$this->save($query);
	}
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
	protected function updatePriceTable() {
		
	}
	protected function updatePriceHistoryTable() {
		
	}
	protected function updatePaymentAttachment() {
		$tableName = $this->getTablename('payment_attachments');

		$query = "ALTER TABLE `".$tableName."` ADD `attachment_no` VARCHAR(100) NULL AFTER `id`;";
		$this->save($query);

		$query = "ALTER TABLE `".$tableName."` ADD `uploaded_by` BIGINT(20) NOT NULL AFTER `type`;";
		$this->save($query);

		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
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
			'default' => 0
		]);
		$this->addColumn([
			'name' => 'egg_out',
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
	protected function updateDailySortingReport() {
		$tableName = $this->getTablename('daily_sorting_report');
		$query = "ALTER TABLE `".$tableName."` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;";
		$this->save($query);
	}
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
	protected function updateDailySortingInventoryTable() {
		$tableName = $this->getTablename('daily_sorting_inventory');

		$query = "ALTER TABLE `".$tableName."` ADD `house_id` BIGINT(20) NOT NULL AFTER `sorted_report_id`;";
		$this->save($query);
	}
}
?>