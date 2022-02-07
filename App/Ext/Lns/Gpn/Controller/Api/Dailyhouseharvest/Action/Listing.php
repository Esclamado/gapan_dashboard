<?php
namespace Lns\Gpn\Controller\Api\Dailyhouseharvest\Action;

class Listing extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;
    protected $_dailyHouseHarvest;
    protected $_feeds;
    protected $_house;
    protected $_medicine;
    protected $_medicine_unit;
    protected $_userProfile;
    protected $_users;
    protected $_incidentReport;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\Feeds $Feeds,
        \Lns\Gpn\Lib\Entity\Db\House $House,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport,
        \Lns\Gpn\Lib\Entity\Db\Medicine $Medicine,
        \Lns\Gpn\Lib\Entity\Db\MedicineUnit $MedicineUnit
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyHouseHarvest = $Dailyhouseharvest;
        $this->_feeds = $Feeds;
        $this->_house = $House;
        $this->_incidentReport = $IncidentReport;
        $this->_medicine = $Medicine;
        $this->_medicine_unit = $MedicineUnit;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
    }
    public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $param = $this->getParam();
            $userId = $payload['payload']['jti'];

            $role = $this->_users->getByColumn(['id' => $userId], 1);

            $list = $this->_dailyHouseHarvest->getList($param, $role->getData('user_role_id'));
            $this->jsonData = $list;
            $result = [];
            $i = 0;
            if ($list['datas']) {
                foreach ($list['datas'] as $data) {
                    $result[$i] = $data->getData();
                    if ($data->getData('medicine_ids')) {
                        $medicine_ids = explode(',', $data->getData('medicine_ids'));
                        $medicine_values = explode(',', $data->getData('medicine_values'));
                        $x = 0;
                        foreach ($medicine_ids as $medicine_id) {
                            $medicine = $this->_medicine->getMedicine($medicine_id);
                            $result[$i]['medicine'][$x] = $medicine;
                            $medicine_unit = $this->_medicine_unit->getByColumn(['id' => $medicine['unit_id']], 1);
                            $result[$i]['medicine'][$x]['unit'] = $medicine_unit->getData('unit');
                            $result[$i]['medicine'][$x]['medicine_value'] = $medicine_values[$x];
                            $x++;
                        }
                    }
                    $result[$i]['house'] = $this->_house->getHouse($data->getData('house_id'));
                    $result[$i]['feed'] = $this->_feeds->getFeed($data->getData('feed_id'));
                    if ($data->getData('prepared_by')) {
                        $result[$i]['prepared_by_name'] = $this->_userProfile->getFullNameById($data->getData('prepared_by'));
                        $result[$i]['prepared_by_role'] = $this->_users->getRoleByUserId($data->getData('prepared_by'));
                        $result[$i]['prepared_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/flockman/' . $data->getData('prepared_by'),
                            'filename' => $data->getData('prepared_by_path')
                        ]);
                    }
                    if ($data->getData('checked_by')) {
                        $result[$i]['checked_by_name'] = $this->_userProfile->getFullNameById($data->getData('checked_by'));
                        $result[$i]['checked_by_role'] = $this->_users->getRoleByUserId($data->getData('checked_by'));
                        $result[$i]['checked_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $data->getData('checked_by'),
                            'filename' => $data->getData('checked_by_path')
                        ]);
                    }
                    if ($data->getData('received_by')) {
                        $result[$i]['received_by_name'] = $this->_userProfile->getFullNameById($data->getData('received_by'));
                        $result[$i]['received_by_role'] = $this->_users->getRoleByUserId($data->getData('received_by'));
                        $result[$i]['received_by_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/sorter/' . $data->getData('received_by'),
                            'filename' => $data->getData('received_by_path')
                        ]);
                    }
                    $feeds = $this->_feeds->convertGramsToBags($data);
                    $result[$i]['recordStatus'] = $this->_incidentReport->getIncidentReport((int)$userId, (int)$data->getData('prepared_by'), (int)$data->getData('checked_by'), (int)$data->getData('received_by'), (int)$data->getData('id'), 3);
                    $result[$i]['feeds'] = $feeds;
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = "No Daily House Harvest Reports";
            }
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}
?>