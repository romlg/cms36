<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
    var $elem_name  = "elem_main";
	var $elem_table = "obj_balcony";
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
		'name'			=> array('Заголовок',	'Title',),
	);
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns'		=> array(
			'id'		=> array(
				'type'	=> 'hidden',
			),
			'name'		=> array(
				'type'	=>'text',
				'size'	=> '40',
			),
		),
		'id_field'	=> 'id',
		'title'		=> 'Балкон',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = "";

	#####################################
	
}
?>