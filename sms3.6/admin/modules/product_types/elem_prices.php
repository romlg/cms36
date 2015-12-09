<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');
class TPricesElement extends TElems{

	######################
	var $elem_name  = "elem_prices";  		
	var $elem_table = "product_type_prices";
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
			'from_price'	=> array('от','from',),
			'to_price'    => array('до','to',),
		);
	var $order = " ORDER BY priority";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
		'from_price'=>array(
			  'type'  =>'text',
			  'maxlength'  => 6,
			  'size'  => 6,
		  ),
		'to_price'=>array(
			  'type'  =>'text',
			  'maxlength'  => 6,
			  'size'  => 6,
		  ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
   'title' => 'Ценовые диапазоны',
	);
	var $elem_where="";
	var $elem_req_fields = array('from_price', 'to_price');
	var $script;
	var $columns;
	########################

	function ElemInit(){
	 $this->columns = array(
		array(
			'select'	 => 'id',
			'display' => 'ids',
			'type'    => 'checkbox',
			'width'	 => '1px',
		),
		array(
			'select'	 => 'from_price',
			'display' => 'from_price',
		),
		array(
			'select'	 => 'to_price',
			'display' => 'to_price',
		),
	);
	 TElems::ElemInit();
	}
}
?>