<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFlashElement_Base extends TElems{

	######################
	var $elem_name  = "elem_flash";  					//название elema
	var $elem_table = "obj_elem_images";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
			'imagepath' 	  => array('Путь','Path',),
			'name' 			  => array('Название','Title',),
			'visible'         => array('Показывать','Visible',),
		);
	var $order = " ORDER BY priority ";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
		  ),
		'imagepath'=>array(
			  'type'  =>'input_image',
			  'display' => array(
			  	'size'	=> array('5000', '400'),
			  ),
		  ),
		 'visible'=>array(
			  'type'  =>'checkbox',
		  ),
		 'type'=>array(
			  'type'  =>'hidden',
			  'value' =>'flash',
		 ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
   'folder'=>'objects',
	);
	var $elem_where=" type='flash'";
	var $elem_req_fields = array('imagepath','name');
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
			'flags'   => FLAG_SEARCH,
		),
		array(
			'select'	 => 'imagepath',
			'type'	 => 'smallimagepath',
			'display' => 'imagepath',
			'temp'	=> true,
		),
		array(
			'select'	 => 'priority',
		),
	);
	 TElems::ElemInit();
	}
	########################
	function table_get_smallimagepath(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		return "<a href='#' onclick=\"window.open('../popup.php?img=$value[0]', 'popup', 'width=100,height=100'); return false\">$value[1]</a>";
	}

}
?>