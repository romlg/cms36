<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//название elema
	var $elem_table = "elem_image";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
			'image_small'	=> array('Маленькая картинка','Small Image',),
			'image_medium'	=> array('Средняя картинка','Medium Image',),
			'image_large'	=> array('Большая картинка','Large Image',),
		);
	var $elem_where="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' => array(
		 'image_small'=>array(
			'type'  =>'input_image',
		 ),
		 'image_medium'=>array(
			'type'  =>'input_image',
		 ),
		 'image_large'=>array(
			'type'  =>'input_image',
		 ),
		),
	);
	var $elem_req_fields = array('image_small','image_medium','image_large',);
	var $script;
}
?>