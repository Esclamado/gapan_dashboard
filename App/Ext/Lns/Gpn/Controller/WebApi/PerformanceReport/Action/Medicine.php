<?php
namespace Lns\Gpn\Controller\WebApi\PerformanceReport\Action;

class Medicine extends \Lns\Sb\Controller\Controller {

    protected $_dailyhouseharvest;
    protected $_house;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest $Dailyhouseharvest,
        \Lns\Gpn\Lib\Entity\Db\House $House
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_dailyhouseharvest = $Dailyhouseharvest;
        $this->_house = $House;
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

            $param = $this->getParam();

            $dailyhouseharvests = $this->_dailyhouseharvest->getMedicineconsumptionreport($param);
            $total_count = $this->_dailyhouseharvest->getMedicineconsumptionreport($param, true);
            
            $result = [];
            $this->jsonData = $dailyhouseharvests;
            $this->jsonData['total_count'] = $total_count;
            if($dailyhouseharvests['datas']){
                $i = 0;
                foreach ($dailyhouseharvests['datas'] as $dailyhouseharvest) {
                    $result[$i] = array(
                        'id'=> $dailyhouseharvest->getData('id'),
                        'date' => $dailyhouseharvest->getData('grouped_date')
                    );
                    $houses = $this->_house->getCollection();
                    $ii = 0;
                    foreach ($houses as $house) {
                        $result[$i]['house'][$ii]['id'] = $house->getData('id');
                        $result[$i]['house'][$ii]['house_name'] = $house->getData('house_name');
                        $medicineperhouse = $this->_dailyhouseharvest->getMedPerHouse($house->getData('id'), $dailyhouseharvest->getData('grouped_date'), $param['med_id']);
                        
                        $meds_ids_arr = explode(",", $medicineperhouse['medicine_ids']);
                        $meds_val_arr = explode(",", $medicineperhouse['medicine_values']);
                        
                        $index = array_search($param['med_id'], $meds_ids_arr);
                        
                        $result[$i]['house'][$ii]['medicine']['med_id'] = $param['med_id'];
                        $result[$i]['house'][$ii]['medicine']['med_value'] = $meds_val_arr[$index];
                        $ii++;
                    }
                    /* if(isset($param['house_id'])){
                        $house = $this->_house->getByColumn(['id'=>$param['house_id']], 1);
                        if($house){
                            $result[$i]['house'] = array(
                                'id' => $house->getData('id'),
                                'house_name' => $house->getData('house_name')
                            );
                            $dailyhousemedicines = $this->_dailyhouseharvest->getMedicineperhouse($house->getData('id'), $dailyhouseharvest->getData('created_at'), $param);
                            if ($dailyhousemedicines) {
                                $med = 0;
                                if ($dailyhousemedicines) {
                                    foreach ($dailyhousemedicines as $data) {
                                        $medicine_ids = $data->getData('medicine_ids');
                                        $medicine_ids = explode(',', $medicine_ids);
                                
                                        $medicine_values = $data->getData('medicine_values');
                                        $medicine_values = explode(',', $medicine_values);
                                        if (isset($param['med_id'])) {
                                            if (in_array($param['med_id'], $medicine_ids)) {
                                                $key = array_search($param['med_id'], $medicine_ids);
                                                $med += (float) $medicine_values[$key];
                                            }
                                        }
                                    }
                                }
                                $result[$i]['house']['medicine_volume'] = $med;
                            } else {
                                $result[$i]['house']['medicine_volume'] = 0;
                            }
                        }
                    }else{
                        $houses = $this->_house->getCollection();
                        if ($houses) {
                            $a = 0;
                            foreach ($houses as $house) {
                                $result[$i]['house'][$a] = array(
                                    'id' => $house->getData('id'),
                                    'house_name' => $house->getData('house_name')
                                );
                                $dailyhousemedicines = $this->_dailyhouseharvest->getMedicineperhouse($house->getData('id'), $dailyhouseharvest->getData('created_at'), $param);
                                if ($dailyhousemedicines) {
                                    $med = 0;
                                    if ($dailyhousemedicines) {
                                        foreach ($dailyhousemedicines as $data) {
                                            $medicine_ids = $data->getData('medicine_ids');
                                            $medicine_ids = explode(',', $medicine_ids);
                                    
                                            $medicine_values = $data->getData('medicine_values');
                                            $medicine_values = explode(',', $medicine_values);
                                            if (isset($param['med_id'])) {
                                                if (in_array($param['med_id'], $medicine_ids)) {
                                                    $key = array_search($param['med_id'], $medicine_ids);
                                                    $med += (float) $medicine_values[$key];
                                                }
                                            }
                                        }
                                    }
                                    $result[$i]['house'][$a]['medicine_volume'] = $med;
                                } else {
                                    $result[$i]['house'][$a]['medicine_volume'] = 0;
                                }
                                $a++;
                            }
                        } else {
                            $this->jsonData['message'] = 'No house record found';
                        }
                    } */
                    $i++;
                }
                $this->jsonData['datas'] = $result;
                $this->jsonData['error'] = 0;
            }else{
                $this->jsonData['message'] = 'No medicine record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
