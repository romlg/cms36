<?php

require_once(elem('realty/objects/objects_elem_func'));

class TMainElement extends TCommonObjectElement {

	//---------------------------------------------------------------------------------

	var $elem_table = "rnt_objects";
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
			'market'			=>	array('type'	=>	'select',		'func'		=> 'getMarket',),
			'url_ref'			=>	array('type'	=>	'text',			'size'		=> '5',),
			'hr[0]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'price_rub'			=>	array('type'	=>	'text',			'size'		=> '15',),
			'price_dollar'		=>	array('type'	=>	'text',			'size'		=> '15',    'disabled' => 'disabled'),
			'hr[1]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'room'				=>	array('type'	=>	'select',		'func'		=> 'getRoomCount',),
			'rent_room'			=>	array('type'	=>	'select',		'func'		=> 'getRoomCount',),
			'storey'			=>	array('type'	=>	'text',			'size'		=> '5',),
			'storeys_number'	=>	array('type'	=>	'text',			'size'		=> '5',),
			'house_type'		=>	array('type'	=>	'select',		'func'		=> 'getHouseType',),
			'hr[2]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'total_area'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'living_area'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'kitchen_area'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'remont'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'hr[3]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'phone'				=>	array('type'	=>	'checkbox',),
			'lavatory'			=>	array('type'	=>	'select',		'func'		=> 'getLavatory',),
			'balcony'			=>	array('type'	=>	'select',		'func'		=> 'getBalcony',),
			'furniture'			=>	array('type'	=>	'checkbox',),
			'refrigerator'		=>	array('type'	=>	'checkbox',),
			'washing_m'			=>	array('type'	=>	'checkbox',),
			'tv'				=>	array('type'	=>	'checkbox',),
			'phones'			=>	array('type'	=>	'checkbox',),
			'internet'			=>	array('type'	=>	'checkbox',),
			'chute'				=>	array('type'	=>	'checkbox',),
			'lift'				=>	array('type'	=>	'checkbox',),
			'children'			=>	array('type'	=>	'checkbox',),
			'animal'			=>	array('type'	=>	'checkbox',),
			'hr[4]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'state'				=>	array('type'	=>	'select',		'func'		=> 'getState',),
			'rnt_time'			=>	array('type'	=>	'select',		'func'		=> 'getRntTime',),
			'prepay'			=>	array('type'	=>	'select',		'func'		=> 'getPrepay',),
			'deposit'			=>	array('type'	=>	'text',			'size'		=> '10',),
			'agent_percent'		=>	array('type'	=>	'text',			'size'		=> '10',),
			'client_percent'	=>	array('type'	=>	'text',			'size'		=> '10',),
			'mobile_phone'		=>	array('type'	=>	'text',			'size'		=> '50',),
			'hr[5]'				=>	array('type'	=>	'words',		'value'		=> '<hr>'),

			'windows'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'registration'		=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'documents'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'f_release'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'short_description'	=>	array(
				'type'    => 'fck',
				'toolbar' => 'Small',
				'size'    => array('100%','140px'),
				'display' => array(
					'colspan'=>true,
				),
			),
			'contact_phone'		=>	array('type'	=>	'text',			'size'		=> '50',),
			'photos'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'visible'			=>	array('type'	=>	'checkbox',),
			'credit'			=>	array('type'	=>	'checkbox',),
			'avance'			=>	array('type'	=>	'checkbox',),
			'ipoteka'		    =>	array('type'	=>	'checkbox',),
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
		$this->elem_str['url_ref']		= array('����������� ������ (ID)',			'Reference Object ID');
		$this->elem_str['hot']			= array('���������� �����',			'Show anonce');
		$this->elem_str['house_type']	= array('��� ������',				'House type');
		$this->elem_str['remont']		= array('��� � �������� �������',	'Remont');
		$this->elem_str['market']		= array('��������',					'Market');
		$this->elem_str['windows']		= array('����',						'Windows');
		$this->elem_str['registration']	= array('��� ���������������',		'registration');
		$this->elem_str['documents']	= array('�������������������� ���������',						'Documents');
		$this->elem_str['f_release']	= array('���������� ������������',	'Release');
		$this->elem_str['short_description']		= array('���������',	'More');
		$this->elem_str['price_dollar']	= array('���� ����� (�.�.)',		'Price ($)');
		$this->elem_str['moscow']		= array('������',			        'Region');
		$this->elem_str['credit']		= array('���������',			    'Credit');
		$this->elem_str['sell']		    = array('�������',			        'Sell');
		$this->elem_str['avance']		= array('�����',			        'Avance');
		$this->elem_str['set_color']	= array('�������� ������',          'Set color');
		$this->elem_str['color']	    = array('����',                     'Color');
		$this->elem_str['ipoteka']	    = array('�������',                  'Mortgage');
		$this->elem_str['contact_phone']= array('���������� ������',		'Contact phone');
		$this->elem_str['status']		= array('������',		'Status');
		$this->elem_str['room']			= array('���-�� ������ � ��������', 'Room in flat count');
		$this->elem_str['rent_room']	= array('���-�� ������ � ������',	'Room count');
		$this->elem_str['furniture']	= array('������',					'Furniture');
		$this->elem_str['refrigerator']	= array('�����������',				'Refrigerator');
		$this->elem_str['washing_m']	= array('���������� ������',		'Washing_m');
		$this->elem_str['tv']			= array('���������',				'TV');
		$this->elem_str['agent_percent']= array('�������� ������ (%)',		'Agent comission');
		$this->elem_str['client_percent']= array('�������� ������� (%)',	'Client comission');
		$this->elem_str['mobile_phone']	= array('��������� �������',		'Mobile phone');

		$this->elem_str['phones']	= array('�������',				'Phone');
		$this->elem_str['internet']	= array('��������',				'Internet');
		$this->elem_str['chute']	= array('������������',			'Chute');
		$this->elem_str['lift']		= array('����',					'Lift');
		$this->elem_str['children']	= array('������� � ������',		'Children');
		$this->elem_str['animal']	= array('������� � ���������',	'Animal');

		$this->elem_str['rnt_time']	= array('���� ������',	'Rent time');
		$this->elem_str['prepay']	= array('����������',	'Prepay');
		$this->elem_str['deposit']	= array('�����',		'Deposit');
		$this->elem_str['state']	= array('��������� ��������', 'State of object');
		$this->elem_str['photos']	= array('����������� � ������� ����������', 'Photos');

		parent::ElemInit();
	}

	function getRoomCount(){
		global $settings;
		return array(''=>'- �� ������� -') + $settings['room_count'];
	}

    function getCurrentDate($v) {
    	return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }

    function getHouseType() {
    	return sql_getRows('SELECT id, name FROM obj_housetypes', true);
    }

    function getBalcony() {
    	return sql_getRows('SELECT id, name FROM obj_balcony', true);
    }

    function getMarket(){
		global $settings;
		return $settings['market'];
	}

	function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		$fld['obj_type_id'] = 'room';
		if ($fld['sell'] == '1') $fld['avance'] = $fld['credit'] = '0';

		if (empty($fld['lot_id'])) {
			$max_lot = (int)sql_getValue('SELECT MAX(lot_id) FROM '.$this->elem_table);
			if ($max_lot) $fld['lot_id'] = $max_lot + 1;
		}

		if (isset($fld['price_rub'])) $fld['price_rub'] = str_replace(array(" ",","), array("","."), $fld['price_rub']);
		if (isset($fld['price_metr_rub'])) $fld['price_metr_rub'] = str_replace(array(" ",","), array("","."), $fld['price_metr_rub']);
		if (isset($fld['price_rub_print'])) $fld['price_rub_print'] = str_replace(array(" ",","), array("","."), $fld['price_rub_print']);

		// �������� ���� � �.�
		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');
		if ($value) $fld['price_dollar'] = $fld['price_rub'] / $value;

		//��������� ����� � ������� ������� � ���������
		$address = e(strip_tags($fld['address']));
		$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
		if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));
		$fld['address_id'] = $address_id;

		//�������� �����������
		$current_status=sql_getValue("SELECT status FROM `rnt_objects` WHERE id=".$this->id);
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

    function getPrepay() {
		global $settings;
		return array('0'=>'- �� ������� -')+$settings['annoucement_prepays'];
    }

    function getRntTime() {
		global $settings;
		return array('0'=>'- �� ������� -')+$settings['annoucement_rnt_times'];
    }

	function getState() {
		global $settings;
		return array('0'=>'- �� ������� -')+$settings['annoucement_states'];
    }
}
?>