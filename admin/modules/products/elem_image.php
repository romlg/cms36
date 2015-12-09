<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//название elema
	var $elem_table = "products";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
			'image_descr'	=> array('Изображение <br>в описании описания','Image Description',),
			'image'			=> array('Главное изображение','Image',),
			'image_popup'	=> array('Изображение <br>в всплывающем окне','Popup Image',),
		);
	var $elem_where="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' => array(
		 'image_descr'=>array(
			'type'  =>'input_image',
                'display' => array(
                    'size'     => array('120', '100'),
                ),
		 ),
		 'image'=>array(
			'type'  =>'input_image',
                 'display' => array(
	                   'friend'   => 'image_descr',
	                   'size'     => array('120', '100'),
                 ),
		 ),
		 'image_popup'=>array(
			'type'  =>'input_image',
                'display' => array(
                     'friend'   => 'image_popup',
                     'size'     => array('200', '200'),
                ),
		 ),
	    ),
          'id_field' => 'id',
          'folder'   => 'products',
	);
	var $elem_req_fields = array();
	var $script;
	
	function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = e($v);
		}
		return $row;
	}
}
?>