<?php
require_once(elem('realty/objects/objects_elem_func'));

class TAddressElement extends TCommonObjectElement {

	//---------------------------------------------------------------------------------

	var $elem_name  = "elem_address";
	var $elem_table = "objects";
	var $elem_type  = "single";

	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
		'columns' => array(
			'city_id'	=> array(
				'type'		=> 'select',
				'func'		=> 'getCity',
			),
			'district_id'	=> array(
				'type'		=> 'select',
				'func'		=> 'getDistricts',
			),
			'direction'	=> array(
				'type'	=> 'select',
				'func'	=> 'getDirection',
			),
			'distance'	=> array(
				'type' 	   => 'text',
				'size'	=> '5',
			),
		 ),
		 'id_field' => 'id',
	 );
	var $sql = false;
	var $elem_where="";
	var $elem_req_fields = array();

	//---------------------------------------------------------------------------------

	function ElemInit(){
		$this->elem_str['district_id']	= array('�����', 'District');
		$this->elem_str['direction']	= array('�����', 'Direction');
		$this->elem_str['city_id']		= array('���������� �����', 'City');
		$this->elem_str['distance']		= array('����������� �� ���� (� ��)', 'Distance from MKAD');
		parent::ElemInit();
	}

	function getDirection(){
		return array('' => '�� �������') + sql_getRows('SELECT id, name FROM obj_direction ORDER BY name', true);
	}

	function getDistricts(){
		$rows = $this->getChilds('obj_locat_districts');
		$this->getList($rows, $districts);
		return $districts;
	}

	function getCity(){
		$rows = $this->getChilds('obj_locat_districts', '', 166);
		$this->getList($rows, $districts);
		return $districts;
	}
}
?>