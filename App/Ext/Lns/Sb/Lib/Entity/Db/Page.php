<?php 
namespace Lns\Sb\Lib\Entity\Db;

use Zend\Db\TableGateway\TableGateway;
use Lns\SB\Lib\Entity\ClassOverride\OfDbEntity;

class Page extends \Of\Db\Entity {
	
	protected $tablename = 'helppages';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id','page','pageContent','slug','image'
	];
	
	public function __construct(
		\Of\Http\Request $Request,
		$adapter=null
	){
		parent::__construct($Request,$adapter);
    }

    public function savePage($userData){
		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$this->__save();
		
		return true;
    }
    public function updatePage($id,$content,$slug){
        $data_tokens = $this->getByColumn(['id' => $id]);

        if(!$data_tokens){

            $response['error']= 1; 
            $response['message']= "None existing data!"; 
            
        }else{
            $save = $this
            ->setData('id',$id)
            ->setData('pageContent', $content)
            ->setData('slug', $slug)
            ->__save();
            $response['error']= 0; 
            $response['message'] = "Successfully updated";
            $response['data']= $this->getData();
        }
       
        return $response;
    }

    public function dispalyPage(){
        $response = $data_tokens = $this->getFinalResponse()['datas'];

        $result = [];
        foreach ($response as $key) {
            $result[] = $key->getData();
        }

        return $result;
    }
}