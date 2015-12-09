<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TTypeElement extends TElems {

	######################
	var $elem_name  = "elem_type";
	var $elem_table = "elem_type";
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
		'type'				=> array('��� ������������',	'Object type'),
		'market'			=> array('�����',	            'Market'),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		'type'=>array(
			'type'  => 'select',
			'func'  => 'getTypes',
		),
		'market'=>array(
			'type'  => 'select',
			'func'  => 'getMarkets',
		),
	  ),
	  'id_field'	=> 'pid',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script;

    //-----------------------------------------------------------------------
	function getTypes(){
		global $settings;
		return array('' => '-- ��� --') + $settings['object_types'];
	}	
	function getMarkets(){
		global $settings;
		return array('' => '-- ��� --') + $settings['market'];
	}	
}
?>