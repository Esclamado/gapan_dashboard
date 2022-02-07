<?php
namespace Lns\Sb\Schema;

use Of\Std\Status;

class Upgrade extends \Of\Db\Createtable {
	
	public function upgradeSchema($currentVersion, $newVersion){
		/* $vc = $this->versionCompare($currentVersion, $newVersion);

		if($vc == 1) {
			$tableName = $this->getTablename('test');
			$query = "ALTER TABLE `".$tableName."` ADD `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`;";
			$this->save($query);
		} */
	}
}
?>