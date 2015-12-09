<?php

require_once(elem('realty/objects/objects_elem_func'));

class TMainElement extends TCommonObjectElement {

	//---------------------------------------------------------------------------------

	var $elem_name  = "elem_main";
	var $elem_type  = "single";

	//поля для выборки из базы элема
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

			'windows'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'registration'		=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'documents'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'f_release'			=>	array('type'	=>	'textarea',		'cols'		=> '50',	'rows'	=> '3'),
			'short_description'	=> array(
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
		$this->elem_str['url_ref']			= array('Привязанный объект (ID)',			'Reference Object ID');
		$this->elem_str['hot']			= array('Отображать анонс',			'Show anonce');
		$this->elem_str['house_type']	= array('Тип здания',				'House type');
		$this->elem_str['remont']		= array('Вид и качество ремонта',	'Remont');
		$this->elem_str['market']		= array('Квартира',					'Market');
		$this->elem_str['windows']		= array('Окна',						'Windows');
		$this->elem_str['registration']	= array('Кто зарегистрирован',		'registration');
		$this->elem_str['documents']	= array('Правоустанавливающие документы',						'Documents');
		$this->elem_str['f_release']	= array('Физическое освобождение',	'Release');
		$this->elem_str['short_description']		= array('Подробнее',	'More');
		$this->elem_str['price_dollar']	= array('Цена всего (у.е.)',		'Price ($)');
		$this->elem_str['moscow']		= array('Регион',			        'Region');
		$this->elem_str['credit']		= array('Рассрочка',			    'Credit');
		$this->elem_str['sell']		    = array('Продано',			        'Sell');
		$this->elem_str['avance']		= array('Аванс',			        'Avance');
		$this->elem_str['set_color']	= array('Выделить цветом',          'Set color');
		$this->elem_str['color']	    = array('Цвет',                     'Color');
		$this->elem_str['ipoteka']	    = array('Ипотека',                  'Mortgage');
		$this->elem_str['contact_phone']= array('Контактные данные',		'Contact phone');
		$this->elem_str['status']= array('Статус',		'Status');
		$this->elem_str['photos']	= array('Изображения с внешних источников', 'Photos');
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

		if (isset($fld['price_rub'])) $fld['price_rub'] = str_replace(array(" ",","), array("","."), $fld['price_rub']);
		if (isset($fld['price_metr_rub'])) $fld['price_metr_rub'] = str_replace(array(" ",","), array("","."), $fld['price_metr_rub']);
		if (isset($fld['price_rub_print'])) $fld['price_rub_print'] = str_replace(array(" ",","), array("","."), $fld['price_rub_print']);

		// Пересчет цены в у.е
		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');
		if ($value) $fld['price_dollar'] = $fld['price_rub'] / $value;

		//Проверяем адрес в таблице адресов и координат
		$address = e(strip_tags($fld['address']));
		$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
		if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));
		$fld['address_id'] = $address_id;

		//отправка уведомления
		$current_status=sql_getValue("SELECT status FROM objects WHERE id=".$this->id);
		if($current_status==1 && $fld['status']==2) SendNotify('ANNOUNCEMENT_PUBLISHED',$fld['client_id'],$fld);
		else if($current_status==2 && $fld['status']!=2 ) SendNotify('ANNOUNCEMENT_CLOSED',$fld['client_id'],$fld);
		return $fld;
	}

	function getMoscow(){
	    return array('1' => 'Москва', '0' => 'Московская область');
	}

    function getStatus() {
		global $settings;
		return $settings['status_types'];
    }
}
?>