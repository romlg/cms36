<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFreeElement extends TElems{

	######################
	var $elem_name  = "elem_free";
	var $elem_table = "obj_elem_free";
	var $elem_type  = 'multi';
	var $elem_str = array(
		'room'			=> array('������',					'Room'),
		'total_area'	=> array('����� �������, ��.�.',	'Total area'),
		'living_area'	=> array('����� �������, ��.�.',	'Living area'),
		'kitchen_area'	=> array('������� �����, ��.�.',	'Kitchen area'),
		'storey'		=> array('����',					'Storey'),
		'section'		=> array('������',					'Section'),
		'price_metr'	=> array('���� � �.�.',			'Price per metr'),
		'price'			=> array('���� � ���.',		'Price'),
		'square'		=> array('�������, ��.�.',			'Square'),
		'image'			=> array('����������� �����������',	'Popup image'),
	);
	var $order = " ORDER BY room ";
	var $window_size="";
	//���� ��� ������� �� ���� �����
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
		// �������� ���� � �.�
		if ($fld['price']) {
    		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');		
    		if ($value) $fld['price_metr'] = $fld['price'] / $value;
		}
		return $fld;
	}
}
?>