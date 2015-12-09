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
			
			'price_rub_print'	=>	array('type'	=>	'text',			'size'		=> '50',),
			'price_dollar_print'=>	array('type'	=>	'text',			'size'		=> '50',    'disabled' => 'disabled'),
			'price_metr_rub'    =>	array('type'	=>	'text',			'size'		=> '50',),
			'hr[1]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),			

			'storeys_number'	=>	array('type'	=>	'text',			'size'		=> '5',),
			'ceiling_height'	=>	array('type'	=>	'text',			'size'		=> '5',),
			'house_type'		=>	array('type'	=>	'select',		'func'		=> 'getHouseType',),
			'srok'				=>	array('type'	=>	'text',			'size'		=> '50',),
			'seria'				=>	array('type'	=>	'text',			'size'		=> '50',),
			'ready'				=>	array('type'	=>	'text',			'size'		=> '50',),
			'hr[2]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),
			
			'floor'				=>	array('type'	=>	'text',			'size'		=> '50',),
			'heating'			=>	array('type'	=>	'text',			'size'		=> '50',),
			'electricity'		=>	array('type'	=>	'text',			'size'		=> '50',),
			'balcony'			=>	array('type'	=>	'text',			'size'		=> '50',),
			'hr[3]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),
			
			'square'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'decoration'		=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'windows'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'infrastructure'	=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'transport'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'short_description'	=> array(
				'type'    => 'fck',
				'toolbar' => 'Small',
				'size'    => array('100%','140px'),
				'display' => array(
					'colspan'=>true,
				),
			),
			'contact_phone'		=>	array('type'	=>	'text',			'size'		=> '50',),
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
	var $elem_req_fields = array('address');
	var $script = "";
	//---------------------------------------------------------------------------------

	function ElemInit(){
		$this->elem_str['hot']			= array('���������� �����',			'Show anonce');
		$this->elem_str['house_type']	= array('��� ������',				'House type');
		$this->elem_str['decoration']	= array('�������',					'Decoration');
		$this->elem_str['windows']		= array('����',						'Windows');
		$this->elem_str['square']		= array('������� �������',			'Square');
		$this->elem_str['floor']		= array('��������� ����',			'Floor');
		$this->elem_str['heating']		= array('���������',				'Heating');
		$this->elem_str['electricity']	= array('���������������',			'Electricity');
		$this->elem_str['balcony']		= array('���������� ��������/������','Balcony');
		$this->elem_str['price_rub_print']			= array('���� (���)','Price (rub)');
		$this->elem_str['price_dollar_print']		= array('���� (�.�.)','Price ($)');
		$this->elem_str['infrastructure']			= array('��������������','Infrastructure');
		$this->elem_str['ceiling_height']			= array('������ ��������','Ceiling height');
		$this->elem_str['short_description']		= array('���������',	'More');
		$this->elem_str['srok']			= array('���� ��',					'Srok');
		$this->elem_str['seria']		= array('����� ����',				'Seria');
		$this->elem_str['transport']	= array('������������ ���������',	'Transport');
		$this->elem_str['ready']		= array('���������� ����',			'Ready');
		$this->elem_str['moscow']		= array('������',			        'Region');
		$this->elem_str['credit']		= array('���������',			    'Credit');
		$this->elem_str['sell']		    = array('�������',			        'Sell');
		$this->elem_str['avance']		= array('�����',			        'Avance');
		$this->elem_str['price_metr_rub']			= array('���� �� ��.�. (���)','Price per metr (rub)');
		$this->elem_str['set_color']	= array('�������� ������',          'Set color');
		$this->elem_str['color']	    = array('����',                     'Color');
		$this->elem_str['contact_phone']= array('���������� ������',		'Contact phone');
		$this->elem_str['status']= array('������',		'Status');
		parent::ElemInit();
	}

	function getRoomCount(){
		global $settings;
		return $settings['room_count'];
	}

    function getCurrentDate($v) {
    	return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }
    
    function getHouseType() {
    	return sql_getRows('SELECT id, name FROM obj_housetypes', true);
    }		//�������� �����������
		$current_status=sql_getValue("SELECT status FROM objects WHERE id=".$this->id);
		if($current_status==1 && $fld['status']==2) SendNotify('ANNOUNCEMENT_PUBLISHED',$fld['client_id'],$fld);
		else if($current_status==2 && $fld['status']!=2 ) SendNotify('ANNOUNCEMENT_CLOSED',$fld['client_id'],$fld);
    
	function getMarket(){
		global $settings;
		return $settings['market'];
	}

	function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		$fld['obj_type_id'] = 'newbuild';
		if ($fld['sell'] == '1') $fld['avance'] = $fld['credit'] = '0';

		if (isset($fld['price_rub'])) $fld['price_rub'] = str_replace(array(" ",","), array("","."), $fld['price_rub']);
		if (isset($fld['price_metr_rub'])) $fld['price_metr_rub'] = str_replace(array(" ",","), array("","."), $fld['price_metr_rub']);
		if (isset($fld['price_rub_print'])) $fld['price_rub_print'] = str_replace(array(" ",","), array("","."), $fld['price_rub_print']);

		// �������� ���� � �.�
		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');		
		if ($value) $fld['price_dollar_print'] = $fld['price_rub_print'] / $value;
 
		//��������� ����� � ������� ������� � ���������
		$address = e(strip_tags($fld['address']));
		$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
		if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));		
		$fld['address_id'] = $address_id;

		//�������� �����������
		$current_status=sql_getValue("SELECT status FROM objects WHERE id=".$fld['id']);
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