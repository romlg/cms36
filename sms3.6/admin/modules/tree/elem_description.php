<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TDescriptionElement extends TElems {

	######################
	var $elem_name  = "elem_description";  					//название elema
	var $elem_table = "elem_description";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
			'description'	=> array('Описание объекта','description',),
		);

	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'description'=>array(
				'type'  =>'fck',
				'toolbar'=> 'Small',
				'size'   => array('100%','350'),
				'display' => array(
					'colspan' => true,
				),
			  ),
		 ),
		 'id_field' => 'pid',
	 );

	var $elem_where="";
	var $elem_req_fields = array();
	var $script;
}
?>