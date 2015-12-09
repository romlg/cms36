<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TPlanElement_Base extends TElems{

	######################
	var $elem_name  = "elem_plan";
	var $elem_table = "obj_elem_plans";
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
		'name'				=> array('Название',		'Title',),
		'image'				=> array('Изображение',		'Image',),
		'visible'			=> array('Показывать',		'Visible',),
		'items'				=> array('Секции',			'Sections',),
	);
	var $order = " ORDER BY priority ";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' =>  array(
			'name'=>array(
				'type'  =>'text',
			),
			'image'=>array(
				'type'  =>'input_image',
				'display' => array(
					'size'	=>array('500','500'),
				),
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
		'folder'=>'objects',
	);
	var $elem_where="";
	var $elem_req_fields = array('image',);
	var $script;
	var $columns;
	########################
	function ElemInit(){
		$this->columns = array(
			array(
				'select'	=> 'id',
				'display'	=> 'ids',
				'type'		=> 'checkbox',
				'width'		=> '1px',
			),
			array(
				'select'	=> 'name',
				'display'	=> 'name',
			),
			array(
				'select'	=> 'image',
				'type'		=> 'image',
				'display'	=> 'image',
				'temp'		=> true,
			),
			array(
				'select'	=> 'id',
				'display'	=> 'items',
				'type'		=> 'items'
			),
    		array(
    			'select'	 => 'priority',
    		),
		);
		TElems::ElemInit();
	}
	########################
	function table_get_image(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		return "<a href='#' onclick=\"window.open('../popup.php?img=$value[0]', 'popup', 'width=100,height=100'); return false\">$value[1]</a>";
	}

	function table_get_items(&$value, &$column, &$row) {
		return "<a href='#' onclick=\"window.open('/admin/act.php?page=plans&pid=".$value."', 'sections', 'width=500,height=500'); return false\" class='open'>Изменить</a>";
	}
}
?>