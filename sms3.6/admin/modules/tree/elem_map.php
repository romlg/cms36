<?php
require_once module(OBJECT_EDITOR_MODULE.'/single_elem');

class TMapElement extends TSingleElem {

	######################
	var $elem_name  = "elem_map";  					//название elema
	var $elem_table = "elem_map";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_str = array(                       //строковые константы
		'map'	=> array('Карта района','',),
	);
	var $elem_where="pid";
	//поля для выборки из базы элема
	var $elem_fields = array('map'=>array(
		'type'  =>'input_image',
		),
	);
	var $elem_req_fields = array();
	var $script;
}
?>
