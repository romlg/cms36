<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TtextElement extends TElems {

	######################
	var $elem_name  = "elem_text";  					//название elema
	var $elem_table = "rnt_objects";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
		'description'	   => array('<strong>ѕолное описание</strong>','<strong>Description</strong>',),
	);

	//пол€ дл€ выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'description'	=> array(
				'type'    => 'fck',
				'toolbar' => 'Common',
				'size'    => array('100%','340px'),
				'display' => array(
					'colspan'=>true,
				),
			),
		 ),
		 'id_field' => 'id',
	 );

	var $elem_where="";
	var $elem_req_fields = array();
	var $script="";
	#####################################

}
?>