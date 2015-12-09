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
			
			'price_rub'			=>	array('type'	=>	'text',			'size'		=> '15',),
			'price_dollar'		=>	array('type'	=>	'text',			'size'		=> '15',    'disabled' => 'disabled'),
			'hr[1]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),			

			'transaction_type'	=>	array('type'	=>	'text',			'size'		=> '50',),
			'purpose'			=>	array('type'	=>	'text',			'size'		=> '50',),
			'total_area'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'hr[2]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),
			
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
	var $elem_req_fields = array('address');
	var $script = "";
	//---------------------------------------------------------------------------------

	function ElemInit(){
		$this->elem_str['address']		= array('������������',				'Address');
		$this->elem_str['hot']			= array('���������� �����',			'Show anonce');
		$this->elem_str['total_area']	= array('�������',					'Square');
		$this->elem_str['purpose']		= array('����������',				'Purpose');
		$this->elem_str['transaction_type']			= array('��� ������',	'Transaction type');
		$this->elem_str['short_description']		= array('���������',	'More');
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

	function getRoomCount(){
		global $settings;
		return $settings['room_count'];
	}

    function getCurrentDate($v) {
    	return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }
    
    function getHouseType() {
    	return sql_getRows('SELECT id, name FROM obj_housetypes', true);
    }
    
	function getMarket(){
		global $settings;
		return $settings['market'];
	}

	function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		$fld['obj_type_id'] = 'commerce';
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