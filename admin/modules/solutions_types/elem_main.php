<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//название elema
	var $elem_table = "solutions_types";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
                        'name'                   => array('Название типа',              'Type name',),
                        'description'            => array('Описание',                	'Description',),
                        'visible'                => array('Доступен для выбора и просмотра','Visible on website',),
                        'saved'                  => array('Страница успешно сохранена',     'Page saved successfully',),
                        'loading'                => array('Загрузка...',                    'Loading...',),
	);
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' => array(
		'id' => array(
			'type'       => 'hidden',
		),
		'name'=>array(
			'type'       => 'text',
			'size'       => 30,
			'maxlength'  => 50,
		),
		'description'=>array(
			'type'       => 'textarea',
			'rows'       => 5,
			'cols' 		 => 23,
		),
		/*'visible'=>array(
			'type'  =>'checkbox',
		),*/
	  ),
	  'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');

}
?>