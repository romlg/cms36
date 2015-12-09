<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//название elema
	var $elem_table = "voting";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
	'name'          => array('Заголовок',                      'Name',),
	'date'          => array('Дата',                           'Date',),
	'visible'       => array('Показывать',                     'Visible',),
	'open'          => array('Открыто',                        'Open',),
	'ipcheck'       => array('Проверять IP',                   'Check IP',),
	'cookie'        => array('Через cookie',                   'cookie',),
	'type'          => array('Тип голосования',                'Voting type',),

	'saved'         => array('Страница успешно сохранена',     'Page saved successfully',),
	'loading'       => array('Загрузка...',                    'Loading...',),
	'description'   => array('Описание',                       'Description',),
	'available'     => array('Есть в наличии',                 'Available on stock',),
	'icon'          => array('Иконка',                         'Icon',),
	'image'         => array('Изображение',                    'Image',),
	'required'      => array('Обязательные поля',              'Required fields',),
	);
	//поля для выборки из базы элема
   	var $order = " ORDER BY priority ";
	var $elem_fields = array(
	  'columns' => array(
		'id'      => array(
		  'type'    => 'hidden',
		),
		'name'    => array(
		  'type'        => 'text',
		  'size'        => 30,
		  'maxlength'   => 99,
		),
		'date' => array(
			'type'  => 'input_calendar',
    		'display' => array(
    			'func'=>'get_Date',
    			),
		),
		'ipcheck' => array(
			'type'  => 'select',
            'func'  => 'get_ipcheck_array',
		),
		'type' => array(
			'type'  => 'select',
            'func'  => 'get_type_array',
		),
		'open'=>array(
		  'type'  =>'checkbox',
		),
		'visible'=>array(
		  'type'  =>'checkbox',
		),
        'lang'      => array(
          'type'    => 'hidden',
            'display' => array(
                'func'=>'get_lang',
             ),
        ),
        'root_id'      => array(
          'type'    => 'hidden',
            'display' => array(
                'func'=>'get_domain',
             ),
        ),
	  ),
	  'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = "";

    function get_ipcheck_array(){
        return array('none' => 'Не проверять','check' => 'Проверять','cookie' => 'Через cookie');
    }
    function get_type_array(){
        return array('radio' => 'radio','checkbox' => 'checkbox');
    }
    function get_lang(){
        return lang();
    }
    function get_domain(){
        return domainRootID();
    }
	function get_Date($v) {
		if (isset($v['value'])) return $v['value'];
		else return date("Y-m-d H:i:s");
	}

}
?>