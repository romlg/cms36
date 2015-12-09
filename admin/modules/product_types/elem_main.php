<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//название elema
	var $elem_table = "product_types";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
                        'name'                   => array('Название типа',              'Product type name',),
                        'description'            => array('Описание',                	'Description',),
                        'visible'                => array('Доступен для выбора и просмотра','Visible on website',),
                        'generator'              => array('Показывать в конструкторе','Visible on generator',),
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
			'type'       => 'text',
			'size'       => 30,
			'maxlength'  => 100,
		),
		'visible'=>array(
			'type'  =>'checkbox',
		),
		/*'generator'=>array(
			'type'  =>'checkbox',
		),*/
	  ),
	  'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');

}
?>