<?php

class TSubscribed extends TTable {

	// название модуля
	var $name = 'notify/subscribed';
	var $table = 'subscribe_users';
	// отображать ли селектор языка?
	var $selector = false;

	//-------------------------------------------------------------------------------
	
	function TSubscribed(){
		global $actions, $str;

		// обязательно вызывать
		TTable::TTable();
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title' 		=> array('Подписчики',	'Subscribers'),
			'email' 		=> array('E-mail', 		'E-mail'),
			'type' 			=> array('Тип', 		'Type'),
		));
		
	}
	
	//-------------------------------------------------------------------------------
	
	function table_get_type(&$value, &$column, &$row) {
		global $settings;
		foreach ($settings['subscribe_categories'] as $key=>$val) {
			foreach ($val['sub'] as $k=>$v) {
				if ($v['type'] == $value) return $v['title'];
			}
		}
		return $value;
	}

	function Show() {
		// обязательная фигня
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		
		global $settings;
		$types = array();
		foreach ($settings['subscribe_categories'] as $key=>$val) {
			if (isset($val['sub'])) foreach ($val['sub'] as $k=>$v) {
				$types[$v['type']] = $v['title'];
			}
		}
		
		require_once (core('ajax_table'));
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'width'		=> '1px',
				),
				array(
					'select'	=> 'email',
					'display' 	=> 'email',
					'flags'		=> FLAG_SORT | FLAG_SEARCH
				),
				array(
					'select'	=> 'type',
					'display' 	=> 'type',
					'type' 		=> 'type',
					'flags'		=> FLAG_SORT | FLAG_FILTER,
					'filter_display'	=> 'type',
					'filter_type'	=> 'array',
					'filter_value'	=> array('' => '-- Все --') + $types,
					'filter_field' => 'type',
				),
			),
			'from'		=> $this->table,
			'where'     => 'root_id='.domainRootId(),
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			//'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => true,
		), $this);
		
		$this->AddStrings($data);
		$data['thisname'] = $this->name;
		$data['thisname2'] = str_replace('/', '', $this->name);
		return Parse($data, "notify/tmpls/properties.tmpl");
	}

}

$GLOBALS['notify__subscribed'] = &Registry::get('TSubscribed');
?>
