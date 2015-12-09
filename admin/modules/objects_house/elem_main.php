<?php

require_once(elem('realty/objects/objects_elem_func'));

class TMainElement extends TCommonObjectElement {

	//---------------------------------------------------------------------------------

	var $elem_name  = "elem_main";
	var $elem_type  = "single";

	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
		'columns' => array(
			'lot_id'			=>	array('type'	=>	'text',			'size'		=> '10',),
			'hot'				=>	array('type'	=>	'checkbox',),
			'priority'			=>	array('type'	=>	'text',			'size'		=> '5'),
			'create_time'		=>	array('type'	=>	'input_calendar','display'	=> array('func'		=> 'getCurrentDate',),),
			'moscow'			=>	array('type'	=>	'select',		'func'		=> 'getMoscow',),
			'address'			=>	array('type'	=>	'text',			'size'		=> '50',),
			'set_color'			=>	array('type'	=>	'checkbox',),
			'color'			    =>	array('type'	=>	'input_color',),
			'hr[0]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'object_type'		=>	array('type'	=>	'text',			'size'		=> '30',),
			'room'				=>	array('type'	=>	'text',			'size'		=> '5',),
			'storeys_number'	=>	array('type'	=>	'text',			'size'		=> '5',),
			'phone'				=>	array('type'	=>	'checkbox',),
			'heating'			=>	array('type'	=>	'text',			'size'		=> '30',),
			'decoration'		=>	array('type'	=>	'text',			'size'		=> '30',),
			'year'				=>	array('type'	=>	'text',			'size'		=> '4',),
			'hr[1]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),
			
			'living_area'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'land_area'			=>	array('type'	=>	'text',			'size'		=> '10',),
			'hr[2]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),
			
			'price_rub'			=>	array('type'	=>	'text',			'size'		=> '15',),
			'price_dollar'		=>	array('type'	=>	'text',			'size'		=> '15',    'disabled' => 'disabled'),
			'hr[3]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),			

			'short_description'	=> array(
				'type'    => 'fck',
				'toolbar' => 'Small',
				'size'    => array('100%','140px'),
				'display' => array(
					'colspan'=>true,
				),
			),

			'visible'			=>	array('type'	=>	'checkbox',),
			'credit'			=>	array('type'	=>	'checkbox',),
			'avance'			=>	array('type'	=>	'checkbox',),
			'sell'			    =>	array('type'	=>	'checkbox',),
			'status'		    =>	array('type'	=>	'select',	'func' => 'getStatus',),
		 ),
		 'id_field' => 'id',
	 );
	var $sql = false;
	var $elem_where="";
	var $elem_req_fields = array();
	var $script = "";

	//---------------------------------------------------------------------------------

	function ElemInit(){
		$this->elem_str['hot']			= array('���������� �����',			'Show anonce');
		$this->elem_str['object_type']	= array('��� �������',				'Type');
		$this->elem_str['land_area']	= array('������ �������, ���.',		'Land area');
		$this->elem_str['living_area']	= array('������� ��������, ��.�.',	'House area');
		$this->elem_str['heating']		= array('���������',				'Heating');
		$this->elem_str['decoration']	= array('�������',					'Decoration');
		$this->elem_str['year']			= array('��� ���������',			'Year');
		$this->elem_str['short_description']	= array('���������',		'More');
		$this->elem_str['price_dollar']	= array('���� ����� (�.�.)',		'Price ($)');
		$this->elem_str['moscow']		= array('������',			        'Region');
		$this->elem_str['credit']		= array('���������',			    'Credit');
		$this->elem_str['sell']		    = array('�������',			        'Sell');
		$this->elem_str['avance']		= array('�����',			        'Avance');
		$this->elem_str['set_color']	= array('�������� ������',          'Set color');
		$this->elem_str['color']	    = array('����',                     'Color');
		$this->elem_str['status']= array('������',		'Status');
		parent::ElemInit();
	}

    function getCurrentDate($v) {
    	return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }

    function getCurrency() {
    	return sql_getRows('SELECT id, display FROM currency', true);
    }

    function getHouseTypes() {
    	global $settings;
    	return $settings['house_types2'];
    }
    
    function getRentType() {
    	return array('long' => '���������� (�� ��������)', 'short' => '������������� (�� ��������)');
    }

    function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		$fld['obj_type_id'] = 'house';
		if ($fld['sell'] == '1') $fld['avance'] = $fld['credit'] = '0';
		// �������� ���� � �.�
		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');		
		if ($value) $fld['price_dollar'] = $fld['price_rub'] / $value;

		//�������� �����������
		$current_status=sql_getValue("SELECT status FROM objects WHERE id=".$this->id);
		if($current_status==1 && $fld['status']==2) SendNotify('ANNOUNCEMENT_PUBLISHED',$fld['client_id'],$fld);
		else if($current_status==2 && $fld['status']!=2 ) SendNotify('ANNOUNCEMENT_CLOSED',$fld['client_id'],$fld);

		return $fld;
	}

	function getMoscow(){
	    return array('1' => '������', '0' => '���������� �������');
	}

    function getStatus() {
		global $settings;
		return $settings['status_types'];
    }
}
?>