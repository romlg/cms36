<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TDocumentElement extends TElems {
	######################
	var $elem_name  = "elem_document";  					//название elema
	var $elem_table = "elem_document";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
		'add'	 	=> array('Добавить','Add',),
		'title'	    => array('Документ','Document',),
		'name'	    => array('Название','name',),
		'hot'	    => array('Горячий','hot',),
		'caption'	=> array('Документы','Documents',),
		'h_add'	    => array('Новый документ','New Document',),
		'h_edit'	=> array('Документ','Document',),
		'name'	    => array('Заголовок','Title',),
		'image'	    => array('Изображение','Image',),
		'date'	    => array('Дата','Date',),
		'description'=> array('Краткое описание','Description',),
		'text'		 => array('Текст',	'Text',),
		'visible'	 => array('Показывать','Visible',),
	);
	
	var $order = " ORDER BY priority ";
	var $window_size="Width=500, Height=400";
	//поля для выборки из базы элема
var $elem_fields = array(
'columns' =>  array(
	'name'=>array(
	  'type'    => 'text',
	//'name'    => 'qwer',
	//'display' => array(
	//    'value' => значение checkboxa когда он  выбран-default =1
	//    'value2' => значение checkboxa когда он не выбран-default =0
	//    'colspan'=>true данное поле будет в две строчки
	//    'name'=> имя для стрингов
	//    'ckeacked' =>true, -  при создании объекта, будет ли cheched cheackbox
	//),
	 ),
	 'visible'=>array(
	   'type'  =>'checkbox',
	   ),
	 'description'=>array(
	   'type'    =>'textarea',
	   'rows'    => '4',
	   'cols'    => '54',
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'text'=>array(
	   'type'    =>'fck',
	   'toolbar' => 'Common',
	   'size'    => array('100%','150'),
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'priority'=>array(
	   'type'  =>'hidden',
	  ),

   ),
   'id_field' => 'pid',
   'type' => 'multi',
);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script;
	var $columns;

	########################
	
	function ElemInit(){
	 $this->columns = array(
		array(
			'select'	 => 'id',
			'display' => 'ids',
			'type'    => 'checkbox',
			'width'	 => '1px',
		),
		array(
			'select'	 => 'name',
			'display' => 'name',
			'flags'   => FLAG_SEARCH,
		),
	);
	 TElems::ElemInit();
	}

	########################
	//Вызывается перед сохранением в базу
	function ElemRedactB($fld){
		if (empty($_POST['ctime'])) $_POST['ctime'] = date('YmdHis'); # timestamp (14)
		 return $fld;
	}
	######################
	
}
?>