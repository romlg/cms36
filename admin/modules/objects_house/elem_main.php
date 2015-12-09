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
		$this->elem_str['hot']			= array('Отображать анонс',			'Show anonce');
		$this->elem_str['object_type']	= array('Тип объекта',				'Type');
		$this->elem_str['land_area']	= array('Размер участка, сот.',		'Land area');
		$this->elem_str['living_area']	= array('Площадь строения, кв.м.',	'House area');
		$this->elem_str['heating']		= array('Отопление',				'Heating');
		$this->elem_str['decoration']	= array('Отделка',					'Decoration');
		$this->elem_str['year']			= array('Год постройки',			'Year');
		$this->elem_str['short_description']	= array('Подробнее',		'More');
		$this->elem_str['price_dollar']	= array('Цена всего (у.е.)',		'Price ($)');
		$this->elem_str['moscow']		= array('Регион',			        'Region');
		$this->elem_str['credit']		= array('Рассрочка',			    'Credit');
		$this->elem_str['sell']		    = array('Продано',			        'Sell');
		$this->elem_str['avance']		= array('Аванс',			        'Avance');
		$this->elem_str['set_color']	= array('Выделить цветом',          'Set color');
		$this->elem_str['color']	    = array('Цвет',                     'Color');
		$this->elem_str['status']= array('Статус',		'Status');
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
    	return array('long' => 'Длительная (от полугода)', 'short' => 'Краткосрочная (до полугода)');
    }

    function ElemRedactB($fld){
		$fld = parent::ElemRedactB($fld);
		$fld['obj_type_id'] = 'house';
		if ($fld['sell'] == '1') $fld['avance'] = $fld['credit'] = '0';
		// Пересчет цены в у.е
		$value = sql_getValue('SELECT value FROM currencies WHERE name="USD"');		
		if ($value) $fld['price_dollar'] = $fld['price_rub'] / $value;

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