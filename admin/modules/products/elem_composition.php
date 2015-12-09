<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TCompositionElement extends TElems{

	######################
	var $elem_name  = "elem_composition";  					//название elema
	var $elem_table = "product_composition";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
			/*
			'image_large' 	 => array('Большая картинка','Large image',),
			'image_small'    => array('Маленькая картинка','Small image',),
			'name' 		 => array('Название','Title',),
			'visible'        => array('Показывать','Visible',),
			*/
			'composition_id'	=>	array('Тип продукта', 'Product type'),
			'elem_id'			=>	array('Продукт', 'Product'),
			'count' 		 	=>  array('Количество','Count',),
		); 
	var $order = " ORDER BY priority ";
	var $window_size = array(550, 290);
	var $elem_xxx = array('elem_composition'); 
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' =>  array(
			'elem_id'=>array(
				'type' => 'select',
				'func' => 'getProducts',
			),
		),
		'id_field' => 'pid',
		'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script;
	var $columns;
	var $elem_actions = false;
	var $click = ''; 
    var $dblclick = '';

	//var $debug = true;
	//var $sql = true;
	
	//----------------------------------------------------------------------------
	
	function getProducts(){
		$id = $_GET['id'];
		$ptid = $_GET['elem_id'];
		
		$sql = "SELECT id, name FROM products WHERE visible > 0 AND product_type_id = ".$ptid;
		$rows = sql_getRows($sql, true);
		return $rows;
	}
	
	//----------------------------------------------------------------------------
	
	function getWCfromDb($id){
		
		$sql = "SELECT id,pid,ptid FROM ".$this->elem_table." WHERE pid = ".$id;
		
		$sql = "
		SELECT ec.ptid as id,ec.id as composition_id,pc.elem_id, pc.count
		FROM products AS p
		RIGHT JOIN elem_composition AS ec ON ec.pid = p.solution_id
		LEFT JOIN ".$this->elem_table." AS pc ON pc.composition_id = ec.id AND pc.product_id = p.id
		WHERE p.id = ".$id;

		/*
		product_composition 
		product_id  composition_id  elem_id  
		
		elem_composition 
		id pid ptid 
		
		solutions_types 
		id  priority  name  description  
		*/
		
		
		$rows = sql_getRows($sql, true);	
		return $rows;
	}

	//----------------------------------------------------------------------------
	
	function MyCommit($row){	
    	$sql = "REPLACE INTO ".$this->elem_table." (product_id,composition_id,elem_id, count) VALUES ('".$_POST['id']."','".$row['composition_id']."','".$row['elem_id']."','".$row['count']."')";
    	sql_query($sql);
    	if (!sql_getErrNo()) return 1;
    	return 0;		
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
				'select'  => 'composition_id',
				'display' => 'composition_id',
				'type'	  => 'type'
			),
			array(
				'select'  => 'elem_id',
				'display' => 'elem_id',
				'type'	  => 'product'
			),	
			array(
				'select'  => 'count',
				'display' => 'count',
				'type'	  => 'count'
			),			
		);
		TElems::ElemInit();
	}
	
	//----------------------------------------------------------------------------
	
	function table_get_count(&$value, &$column, &$row){
		$html = "<input type='text' name='fld[".$row['id']."][count]' value='".$value."' size=8>";
		return $html;
	}
	
	//----------------------------------------------------------------------------
	
	function table_get_product(&$value, &$column, &$row){
	
		$sql = "SELECT id, name FROM products WHERE product_type_id=".$row['id'];	
		$products = array('0'=>'не указан') + sql_getRows($sql, true);
		 $html = "
			 <input type='hidden' name='fld[".$row['id']."][composition_id]' value='".$row['composition_id']."'>
			 <input type='hidden' name='fld[".$row['id']."][id]' value='".$row['id']."'>
		";
       
		$html .= "<select name='fld[".$row['id']."][elem_id]'>";
		foreach ($products as $k=>$v){
			$html .= "<option value='".$k."' ".($k == $value ? "selected" : "").">".$v."</option>	";
		}
		$html .= "</select>";	
		return $html;
		
	}
	
	//----------------------------------------------------------------------------
	
	function table_get_type(&$value){

		$sql = "
		SELECT
			pt.name 
		FROM elem_composition AS ec 
		LEFT JOIN product_types AS pt ON ec.ptid = pt.id
		WHERE ec.id=".$value;
		$type = sql_getValue($sql);
		return $type;	
		
	}
	
	//----------------------------------------------------------------------------
}
?>