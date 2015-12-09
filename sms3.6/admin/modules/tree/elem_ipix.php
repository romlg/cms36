<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TIpixElement extends TElems {

	######################
	var $elem_name  = "elem_ipix";  					//название elema
	var $elem_table = "elem_ipix";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
			'flash_file'	     => array('Картинка кругового обзора','360 view picture',),
			'flash_type'        => array('Тип проигрывателя','Player type',),
			'normal' 			  => array('Обычный','Normal',),
			'lite'   			  => array('Упрощенный','Lite',),
		);
	var $elem_where="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	'columns' => array(
		 'flash_file'=>array(
			'type'  =>'input_image',
		  ),
		 'flash_type'=>array(
			 'type'  =>'select',
			 'func'  => 'types_select',
		   ),
		),
	  'id_field' => 'pid',
	);
	var $elem_req_fields = array('flash_file','flash_type',);
	var $script;

	########################
	function types_select(){
	 return $this->GetSetArray('flash_type', $this->elem_table,true);
	}
	########################
}
?>