<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');


class TGalleryElement extends TElems{

	######################
	var $elem_name  = "elem_gallery";  					//название elema
	var $elem_table = "elem_pgallery";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
			'image_large' 	 => array('Большая картинка','Large image',),
			'image_small'    => array('Маленькая картинка','Small image',),
			'name' 		 => array('Название','Title',),
			'visible'        => array('Показывать','Visible',),
		);
	var $order = " ORDER BY priority ";
	var $window_size="Width=550, Height=190";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
		  ),
		'image_small'=>array(
			  'type'  =>'input_image',
                          'display' => array(
                                'size'     => array('150','150'),
                          ),
		  ),
		'image_large'=>array(
			  'type'     =>'input_image',
                          'display' => array(
                              'friend'   => 'image_small',
                              'size'     => array('400', '400'),
                              ),
		  ),
		 'visible'=>array(
			  'type'  =>'checkbox',
		  ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'folder'   => 'images',
   'id_field' => 'pid',
   'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array();
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
			'select'	 => 'image_small',
			'type'	 => 'imagepath',
			'display' => 'image_small',
		),
		array(
			'select'	 => 'image_large',
			'type'	 => 'imagepath',
			'display' => 'image_large',
		),
	);
	 TElems::ElemInit();
	}
	function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = e($v);
		}
		return $row;
	}
	########################
	function table_get_imagepath(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		return "<a href='#' onclick=\"window.open('../scripts/popup.php?img=files/$value', 'popup', 'width=100,height=100'); return false\">$value</a>";
	}
}
?>