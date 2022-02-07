<?php
namespace Lns\Gpn\Lib\Entity\Db;

class IncidentReport extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

    protected $tablename = 'incident_report';
    protected $primaryKey = 'id';
    
    const COLUMNS = [
		'id',
		'sender_id',
    'receiver_id',
    'type',
    'reference_id',
    'reason',
    'declared_qty',
    'validated_qty',
    'signature_path',
    'created_at'
  ];

  protected $_dailyhouseharvest;
  protected $_user;

  public function __construct(
		\Of\Http\Request $Request
	){
		parent::__construct($Request);
    $this->_dailyhouseharvest = $this->_di->get('Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest');
    $this->_user = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
	}

  public function getIncidentReport($userId, $prepared_by, $checked_by, $received_by, $reference_id, $type = null) {
    $userrole = 0;
    $user = $this->_user->getByColumn(['id'=> $userId], 1);
    if($user){
      $userrole = $user->getData('user_role_id');
    }
    
    if ($userId == $prepared_by) {
      $isExist = $this->getByColumn(['reference_id' => $reference_id, 'receiver_id' => $userId], 1);
      if ($isExist) {
        $this->resetQuery();
        $isExist2 = $this->getByColumn(['reference_id' => $reference_id, 'sender_id' => $userId], 1);

        $label = 'Proceed w/ Correction';
        if ($isExist2) {
          $this->resetQuery();
          $isExist3 = $this->getByColumn(['reference_id' => $reference_id, /* 'receiver_id' => $userId,  */'type' => $type], 1);
          if ($isExist3) {
            $label = 'Approved w/ Incident Report';
          } else {
            $label = 'Pending Inspector Resolve'/* 'Approve' */;
          }
        }
        return /* $isExist2 ? 'Approved w/ Incident Report' : 'Pending Incident Report' */$label;
      } else {
        $label = 'For Approval';
        if ($prepared_by && !$checked_by && !$received_by) {
          $label = 'For Approval';
        } else if ($prepared_by && $checked_by && !$received_by) {
          $label = 'Approved';
        } else if ($prepared_by && $checked_by && $received_by) {
          $label = /* 'Sorted' */'Approved';
        }
        return $label;
      }
    } else if ($userId == $checked_by) {
      $isExist = $this->getByColumn(['reference_id' => $reference_id, 'sender_id' => $userId], 1);
      if ($isExist) {
        $this->resetQuery();
        $isExist2 = $this->getByColumn(['reference_id' => $reference_id, 'receiver_id' => $userId], 1);

        $label = 'Proceed w/ Correction';
        if ($isExist2) {
          $this->resetQuery();
          $isExist3 = $this->getByColumn(['reference_id' => $reference_id, /* 'sender_id' => $userId,  */'type' => $type], 1);
          if ($isExist3) {
            $label = 'Approved w/ Incident Report';
          } else {
            $label = 'Pending Inspector Resolve'/* 'Approve' */;
          }
        }
        return $label;
      } else {
        $label = 'Pending';
        if ($prepared_by && !$checked_by && !$received_by) {
          $label = 'Pending';
        } else if ($prepared_by && $checked_by && !$received_by) {
          $label = 'Approved';
        } else if ($prepared_by && $checked_by && $received_by) {
          /* checksortingstatus */
          if($type!=6){
            $isSorted = $this->_dailyhouseharvest->getByColumn(['id' => $reference_id], 1);
            if ($isSorted->getData('isSorted') == 1) {
              $label = 'Sorted';
            } else {
              $label = 'Unsorted';
            }
          }else{
            $label = 'Received';
          }
        }
        return $label;
      }
    } else if ($userId == $received_by) {
      /* checksortingstatus */
      $isSorted = $this->_dailyhouseharvest->getByColumn(['id' => $reference_id], 1);
      if ($type == 6) {
        $label = 'Received';
      } else {
        if ($isSorted->getData('isSorted') == 1) {
          $label = 'Sorted';
        } else {
          $label = 'Unsorted';
        }
      }
      return $label;
    } else {
      if ($prepared_by && !$checked_by && !$received_by) {
        return 'Pending';
      } else if ($prepared_by && $checked_by && !$received_by) {
        return 'For Receive';
      } else if ($prepared_by && $checked_by && $received_by) {
        /* return 'Unsorted2'; */
        $isSorted = $this->_dailyhouseharvest->getByColumn(['id' => $reference_id], 1);
        if ($type == 6) {
          $label = 'Received';
        } else {
          if($userrole==8){
            $label = 'Approved';
          }else{
            if ($isSorted->getData('isSorted') == 1) {
              $label = 'Sorted';
            } else {
              $label = 'Unsorted';
            }
          }
        }
        return $label;
      }
    }
    /* $this->getByColumn(['reference_id' => $reference_id], 0);
    $datas = $this->getCollection();
    $result = [];
    if ($datas) {
      foreach ($datas as $data) {
        $result[] = $data->getData();
      }
    } */
    /* return $result; */
  }
  public function validate($type, $reference_id) {
    $result = $this->getByColumn(['type' => $type, 'reference_id' => $reference_id], 1);
    if ($result) {
      return $result;
    } else {
      return null;
    }
    /* return $this->getCollection(); */
  }
  public function getIncidentReportInfo(){
    return $this->getCollection();
  }
}
?>