<?php
require_once(elem('realty/objects/objects_elem_func'));

class TAddressElement extends TCommonObjectElement {

	//---------------------------------------------------------------------------------

	var $elem_name  = "elem_address";
	var $elem_table = "objects";
	var $elem_type  = "single";

	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'moscow'	=> array(
				'type'	=> 'select',
				'func'	=> 'getMoscow',
				'onChange' => 'changeMoscow(this.value)',
			),
			'city'		=> array(
				'type'	=> 'text',
				'size'	=> '40',
			),
			'metro_id'	=> array(
				'type' => 'select',
				'func' => 'getMetroList',
			),
			'metro_dest_value'	=> array(
				'type' 	   => 'text',
			),
			'metro_dest_text'	=> array(
				'type' 	   => 'select',
				'func'		=> 'getMetroDest',
			),
		 ),
		 'id_field' => 'id',
	 );
	var $sql = false;
	var $elem_where="";
	var $elem_req_fields = array();
	var $script = "
		{literal}
		function changeMoscow(value) {
			document.getElementById('tr_fld[city]').style.display = value=='0' ? 'block' : 'none';
		}
		window.onload = function(){
			changeMoscow('1');
		}
		{/literal}
	";
	

	//---------------------------------------------------------------------------------

	function ElemInit(){
		$this->elem_str['metro_dest_value']		= array('Удаленность от метро (минут)', 'Distance from metro');
		$this->elem_str['metro_dest_text']		= array('Удаленность от метро (способ)', 'Distance from metro');
		$this->elem_str['moscow']				= array('Регион', 'Region');
		parent::ElemInit();
	}
	
	function getMetroDest(){
		global $settings;
		return $settings['metro_dest'];
	}
	
	function getMetroList(){
		return sql_getRows('SELECT id, name FROM obj_locat_metrostations ORDER BY id', true);
	}
	
	function getMoscow() {
		return array('1' => 'Москва', '0' => 'Московская обл.');
	}
}
?>