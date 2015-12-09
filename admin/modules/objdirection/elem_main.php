<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
    var $elem_name  = "elem_main";
	var $elem_table = "obj_direction";
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
		'name'			=> array('Название',	'Title',),
		'all'			=> array('нет',			'none'),
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
		'title'		=> 'Тип',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = "";

	#####################################
	
}
?>