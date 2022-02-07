<?php 
namespace Lns\Sb\Lib\Entity\Db;

use Of\Std\Password;

class Activation extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const COLUMNS = [
		'id',
		'user_id',
		'activation_code',
		'created_at',
		'updated_at',
	];
	
	protected $tablename = 'user_activation_code';
	protected $primaryKey = 'id';
	
	public function __construct(
		\Of\Http\Request $Request,
		$adapter = null
	){
		parent::__construct($Request, $adapter);
    }
    public function saveActivationCode($userId) {
        $this->setData('user_id', $userId);
        return $this->setData('activation_code', Password::generate(6))->__save();
    }
    public function updateActivationCode($userId) {
        $user = $this->getByColumn(['user_id' => $userId], 1);
        return $user->setData('activation_code', Password::generate(6))->__save();
    }
    public function validateActivationCode($userId, $activationCode) {
        $isExist = $this->getByColumn([
            'user_id' => $userId,
            'activation_code' => $activationCode,
        ], 1);
        if ($isExist) {
            return true;
        } else {
            return false;
        }
    }
}
