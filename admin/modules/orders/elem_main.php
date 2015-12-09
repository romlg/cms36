<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//название elema
	var $elem_table = "orders";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
	'name'			=> array('ФИО',				'Name',),
	'date'			=> array('Дата',			'Date',),
	'contacts'		=> array('Контакты',		'Contacts',),
	'info'			=> array('Дополнительная информация','Info',),
	);
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'id'      => array(
				'type'    => 'hidden',
			),
			'date' => array(
				'type' => 'input_calendar',
			),
			'name'    => array(
				'type'        => 'text',
				'size'        => '50',
			),
			'contacts'    => array(
				'type'       => 'fck',
				'toolbar'    => 'Small',
				'size'       => array('100%','120'),
				'display'	 => array(
					'colspan'	=> true,
				),
			),
			'info'    => array(
				'type'       => 'fck',
				'toolbar'    => 'Small',
				'size'       => array('100%','170'),
				'display'	 => array(
					'colspan'	=> true,
				),
			),
		),
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script = "";

}
?>