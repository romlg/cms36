<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFreeElement extends TElems{

	######################
	var $elem_name  = "elem_free";
	var $elem_table = "obj_elem_free";
	var $elem_type  = 'multi';
	var $elem_str = array(
		'room'			=> array('Комнат',					'Room'),
		'total_area'	=> array('Общая площадь, кв.м.',	'Total area'),
		'living_area'	=> array('Жилая площадь, кв.м.',	'Living area'),
		'kitchen_area'	=> array('Площадь кухни, кв.м.',	'Kitchen area'),
		'storey'		=> array('Этаж',					'Storey'),
		'section'		=> array('Секция',					'Section'),
		'price_metr'	=> array('Цена в у.е.',			'Price per metr'),
		'price'			=> array('Цена в руб.',		'Price'),
		'square'		=> array('Площадь, кв.м.',			'Square'),
		'image'			=> array('Всплывающее изображение',	'Popup image'),
	);
	var $order = " ORDER BY room ";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' =>  array(
			'room'=>array(
				'type'  => 'text',
				'size'	=> '3'
			),
			'total_area'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'living_area'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'kitchen_area'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'storey'=>array(
				'type'  => 'text',
				'size'	=> '5'
			),
			'section'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'price_metr'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'price'=>array(
				'type'  => 'text',
				'size'	=> '10'
			),
			'image'=>array(
				'type'  => 'image_server',
			),
		),
		'id_field' => 'pid',
		'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array();
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
			'select'	=> 'room',
			'display'	=> 'room',
		),
		array(
			'select'	 => 'storey',
			'display' => 'storey',
			'temp'	=> true,
		),
	);
	 TElems::ElemInit();
	}


	function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		// Пересчет цены в у.е
		if ($fld['price']) {
    		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');		
    		if ($value) $fld['price_metr'] = $fld['price'] / $value;
		}
		return $fld;
	}
}
?>