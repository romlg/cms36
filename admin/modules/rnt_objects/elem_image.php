<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//название elema
	var $elem_table = "rnt_objects";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
		'image'			=> array('Фото (640х480)','Image (640x480)',),
		'image_small'	=> array('Фото (250х250)','Image (250x250)',),
		'image100'		=> array('Фото (156х156)','Image (156x156)',),
		'plan'			=> array('Планировка (640х480)','Plan (640х480)',),
		'plan_small'	=> array('Планировка (50х36)','Plan (50x36)',),
	);

	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'image' => array(
				'type' => 'input_image',
				'display' => array(
					'friend'=>'image_small',
					'size' => array('640', '480'),
				),
			),
			'image_small' => array(
				'type' => 'input_image',
				'friend'=>'image100',
				'display' => array(
					'size' => array('250', '250'),
				),
			),
			'image100' => array(
				'type' => 'input_image',
				'display' => array(
					'size' => array('156', '156'),
				),
			),
			'plan' => array(
				'type' => 'input_image',
				'display' => array(
					'friend'=>'plan_small',
					'size' => array('640', '480'),
				),
			),
			'plan_small' => array(
				'type' => 'input_image',
				'display' => array(
					'size' => array('50', '36'),
				),
			),
		),
		'id_field' => 'id',
		'folder'	=> 'rnt_objects',
	 );

	var $elem_where="";
	var $elem_req_fields = array();
	var $script="";
	#####################################

}
?>