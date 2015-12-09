<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');
class TParamsElement extends TElems{

	######################
	var $elem_name  = "elem_params";  		
	var $elem_table = "product_type_params";
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
			'name'	=> array('Название','Name',),
			'url'    => array('URL','URL',),
		);
	var $order = " ORDER BY priority";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
			  'maxlength'  => 100,
			  'size'  => 30,
		  ),
		'url'=>array(
			  'type'  =>'text',
			  'maxlength'  => 255,
			  'size'  => 30,
		  ),
		 'visible'=>array(
			  'type'  =>'checkbox',
		 ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
   'title' => 'Параметры',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
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
			'select'	 => 'name',
			'display' => 'name',
		),
		array(
			'select'	 => 'visible',
			'display' => 'visible',
			'type'		=> 'visible',
		),
	);
	 TElems::ElemInit();
	}
}
?>