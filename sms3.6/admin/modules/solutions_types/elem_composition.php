<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');
define ('USE_ED_VERSION', '1.0.2');
class TCompositionElement extends TElems{

	######################
	var $elem_name  = "elem_composition";  					//�������� elema
	var $elem_table = "elem_composition";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
			/*
			'image_large' 	 => array('������� ��������','Large image',),
			'image_small'    => array('��������� ��������','Small image',),
			'name' 		 => array('��������','Title',),
			'visible'        => array('����������','Visible',),
			*/
			'ptid'	=>	array('��� ��������', 'Product type'),
		);
	var $order = " ORDER BY priority ";
	var $window_size = array(550, 190);
	var $elem_xxx = array('elem_composition'); 
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
		'columns' =>  array(
			'ptid'=>array(
				'type' => 'select',
				'func' => 'getProductTypes',
			),
		),
		'id_field' => 'pid',
		'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script;
	var $columns;
	//var $debug = true;
	//var $sql = true;
	
	//----------------------------------------------------------------------------
	
	function getProductTypes(){
		$id = $_GET['id'];
		$elem_id = $_GET['elem_id'];
		$wc = $this->getWc($id);
		$types = array();
		foreach ($wc as $k=>$v){
			$types[$v['ptid']] = true;
		}
		if ($elem_id){
			$elem = $this->getWc($id, $elem_id);
			unset($types[$elem['ptid']]);
		}
		$sql = "SELECT id, name FROM product_types WHERE visible > 0 AND id NOT IN ('".implode("', '", array_keys($types))."')";
		$rows = sql_getRows($sql, true);
		return $rows;
	}
	
	//----------------------------------------------------------------------------
	
	function getWCfromDb($id){
		$sql = "SELECT id,pid,ptid FROM ".$this->elem_table." WHERE pid = ".$id;
		$rows = sql_getRows($sql, true);
		return $rows;
	}

	//----------------------------------------------------------------------------
	
	function MyCommit($row){
    	//�������� Commit    
    	if (is_numeric($row['id'])){
    		//update
    		$id = $row['id'];
    		unset($row['id']);
    		$str = "";
    		$delim = "";
    		foreach ($row as $k=>$v){
    			$str .= $delim." `".$k."` = '".e($v)."' ";
    			$delim = " , ";
    		}
    		$sql = "UPDATE ".$this->elem_table." SET ".$str." WHERE id = ".$id;
    	} else {
    		//insert
	    	unset($row['id']);
	    	$sql = "INSERT INTO ".$this->elem_table." (`".implode("`, `",array_keys($row))."`) VALUES ('".implode("', '", $row)."')";
    	}
    	sql_query($sql);
    	if (!sql_getErrNo()) return 1;
    	return 0;		
	}
 
 	//----------------------------------------------------------------------------
 	
	function MyDeleteItems(){
	    //�������� Deleteitems()
		//��� ���������� ������� $_POST   
		if (isset($_POST['id']) && !empty($_POST['id'])){
			$sql = "DELETE FROM ".$this->elem_table." WHERE id IN (".implode(", ", $_POST['id']).")";
			sql_query($sql);
			if (!sql_getErrNo()) return true;
		}
		return false;
	}
 
 	//----------------------------------------------------------------------------
	
	function ElemInit(){
		$this->columns = array(
			array(
				'select'  => 'id',
				'display' => 'ids',
				'type'    => 'checkbox',
				'width'	  => '1px',
			),
			array(
				'select'  => 'ptid',
				'display' => 'ptid',
				'type'	  => 'product_type'
			),
		);
		TElems::ElemInit();
	}
	
	//----------------------------------------------------------------------------
	
	function table_get_product_type(&$value){
		
		global $product_types;
		if (empty($product_types)){
			$sql = "SELECT id, name FROM product_types";
			$product_types = sql_getRows($sql, true);
		}
		return $product_types[$value];	
		
	}
	
	//----------------------------------------------------------------------------
}
?>