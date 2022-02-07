<?php 
namespace Lns\Sb\Lib\Html\Datatable;

class Datatable extends \Of\Html\Context{

	protected $dataTitle;
	protected $addButtonName;
	protected $tableColumns = [];
    protected $addLink;
    protected $dataListLink;
    protected $dataSesssion = '';

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Config $Config
    ){
        parent::__construct($Url, $Config);
    }

    protected function getDataTitle(){
    	if($this->dataTitle){
    		return $this->dataTitle;
    	} else {
    		return 'Datatable Title';
    	}
    }

    protected function getDataAddButtonName(){
    	if($this->addButtonName){
    		return $this->addButtonName;
    	} else {
    		return 'Add New Entry';
    	}
    }

    protected function getDataAddButtonLink(){
        if($this->addLink){
            return $this->getAdminUrl($this->addLink);
        } else {
            return '';
        }
    }

    protected function getDataListLink(){
        if($this->dataListLink){
            return $this->getAdminUrl($this->dataListLink);
        } else {
            return '';
        }
    }

    protected function getTableColumns(){
    	if(is_array($this->tableColumns)){
    		return $this->tableColumns;
    	} else {
    		return [];
    	}
    }

    protected function getStart(){
        $start = 0;
        if(isset($_SESSION['datatable'])){
            $datatable = $_SESSION['datatable'];
            if(isset($datatable[strtolower($this->dataSesssion)])){
                $dt = $datatable[strtolower($this->dataSesssion)];
                if(isset($dt['start'])){
                    $start = $dt['start'];
                }
            }
        }
        return $start;
    }
}
?>