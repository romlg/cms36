<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');


class TMapElement extends TElems{

	######################
	var $elem_name  = "elem_map";  					//название elema
	var $elem_table = "obj_elem_images";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
		'imagepath' 	  => array('Большая картинка','Large image',),
		'smallimagepath'  => array('Маленькая картинка','Small image',),
		'largeimagepath'	=> array('Картинка в новом окне', 'Popup image'),
			'name' 		 => array('Название','Title',),
			'visible'        => array('Показывать','Visible',),
		'alt'			=> array('Alt для изображения',		'Alt for image'),
		);
	var $order = " ORDER BY priority ";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
		  ),
		'smallimagepath'=>array(
			  'type'  =>'input_image',
                          'display' => array(
					'size'	=>array('120','92'),
                          ),
		  ),
		'imagepath'=>array(
			  'type'     =>'input_image',
                          'display' => array(
					'friend'=>'smallimagepath',
					'size'	=>array('375'),
				),
                              ),
		'largeimagepath'=>array(
			  'type'  =>'input_image',
			  'display' => array(
					'size'	=>array('1200','1200'),
				),
		  ),
			'alt'	=> array(
				'type' => 'text',
				'size' => '40',
		  ),
		 'visible'=>array(
			  'type'  =>'checkbox',
		  ),
		 'type'=>array(
			  'type'  =>'hidden',
			  'value' =>'maps',
		 ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
   'folder'=>'objects',
	);
	var $elem_where=" type='maps'";
	var $elem_req_fields = array('smallimagepath',);
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
			'select'	 => 'smallimagepath',
			'type'	 => 'smallimagepath',
			'display' => 'smallimagepath',
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