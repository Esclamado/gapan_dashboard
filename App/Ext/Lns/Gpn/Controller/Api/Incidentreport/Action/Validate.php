<?php
namespace Lns\Gpn\Controller\Api\Incidentreport\Action;

class Validate extends \Lns\Sb\Controller\Controller {

    protected $token;
    protected $payload;
    protected $_incidentReport;
    protected $_userProfile;
    protected $_users;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Gpn\Lib\Entity\Db\IncidentReport $IncidentReport
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_incidentReport = $IncidentReport;
        $this->_userProfile = $UserProfile;
        $this->_users = $Users;
    }
    public function run() {
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->validate($this->_request, true);

        $result = [];
        $this->jsonData['error'] = 1;

		if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];
            $data = $this->_incidentReport->validate($this->getParam('type'), $this->getParam('reference_id'));
            if ($data) {
                $result = [];
                $i = 0;
                $result = $data->getData();
                $result['record_id'] = date('Y',strtotime($data->getData('created_at'))) . "-" . sprintf("%04d", $data->getData('id'));
                $result['sender'] = $this->_userProfile->getFullNameById($data->getData('sender_id'));
                $result['sender_user_role'] = $this->_users->getRoleByUserId($data->getData('sender_id'));
                $result['receiver'] = $this->_userProfile->getFullNameById($data->getData('receiver_id'));
                $result['receiver_user_role'] = $this->_users->getRoleByUserId($data->getData('receiver_id'));
                $userData = $this->_users->getByColumn(['id' => $data->getData('sender_id')], 1);
                if ($userData) {
                    $position = 'flockman';
                    switch($userData->getData('user_role_id')) {
                        case 5: $position = 'salesagent'; break;
                        case 6: $position = 'inspector'; break;
                        case 7: $position = 'inspector'; break;
                        case 8: $position = 'flockman'; break;
                        case 9: $position = 'sorter'; break;
                        case 10: $position = 'warehouseman'; break;
                        case 11: $position = 'warehouseman'; break;
                        default: $position = 'flockman'; break;
                    }
                    $result['signature_path'] = $this->getImageUrl([
                        'vendor' => 'Lns',
                        'module' => 'Gpn',
                        'path' => '/images/uploads/signature/'.$position.'/' . $data->getData('sender_id'),
                        'filename' => $data->getData('signature_path')
                    ]);
                }
                if ($this->getParam('type') == 2) {
                    $isResolved = $this->_incidentReport->validate(3, $this->getParam('reference_id'));
                    if ($isResolved) {
                        $result['receiver_signature_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $isResolved->getData('sender_id'),
                            'filename' => $isResolved->getData('signature_path')
                        ]);
                        $result['receiver_created_at'] = $isResolved->getData('created_at');
                    }
                }
                if ($this->getParam('type') == 5) {
                    $isResolved = $this->_incidentReport->validate(6, $this->getParam('reference_id'));
                    if ($isResolved) {
                        $result['receiver_signature_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/inspector/' . $isResolved->getData('sender_id'),
                            'filename' => $isResolved->getData('signature_path')
                        ]);
                        $result['receiver_created_at'] = $isResolved->getData('created_at');
                    }
                }
                /* foreach($datas as $data) {
                    $result[$i] = $data->getData();
                    $result[$i]['sender'] = $this->_userProfile->getFullNameById($data->getData('sender_id'));
                    $result[$i]['sender_user_role'] = $this->_users->getRoleByUserId($data->getData('sender_id'));
                    $result[$i]['receiver'] = $this->_userProfile->getFullNameById($data->getData('receiver_id'));
                    $result[$i]['receiver_user_role'] = $this->_users->getRoleByUserId($data->getData('receiver_id'));

                    $userData = $this->_users->getByColumn(['id' => $data->getData('sender_id')], 1);
                    if ($userData) {
                        $position = 'flockman';
                        switch($userData->getData('user_role_id')) {
                            case 5: $position = 'salesagent'; break;
                            case 6: $position = 'inspector'; break;
                            case 7: $position = 'inspector'; break;
                            case 8: $position = 'flockman'; break;
                            case 9: $position = 'sorter'; break;
                            case 10: $position = 'warehouseman'; break;
                            case 11: $position = 'warehouseman'; break;
                            default: $position = 'flockman'; break;
                        }
                        $result[$i]['signature_path'] = $this->getImageUrl([
                            'vendor' => 'Lns',
                            'module' => 'Gpn',
                            'path' => '/images/uploads/signature/'.$position.'/' . $data->getData('sender_id'),
                            'filename' => $data->getData('signature_path')
                        ]);
                    }
                    $i++;
                } */
                $this->jsonData['error'] = 0;
                $this->jsonData['data'] = $result;
            } else {
                $this->jsonData['message'] = 'No Incident Report found.';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>