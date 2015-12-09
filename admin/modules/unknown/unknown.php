<?php

class TUnknown extends TTable {

	var $name = 'unknown';
	var $selector = true;

	########################

	function TUnknown() {
		global $actions, $str;

		TTable::TTable();

		$actions[$this->name] = array();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Ошибка модуля',
				'Module error',
			),
			'e_module_disabled' => array(
				'Модуль отключен',
				'Module disabled',
			),
			'e_module_forbidden' => array(
				'Модуль не разрешен к использованию',
				'Module forbidden',
			),
		));
	}

	########################

	function Show() {
		$this->AddStrings($data);
		return $this->Parse($data, $this->name.'.tmpl');
	}

	######################
}

$GLOBALS['unknown'] = & Registry::get('TUnknown');

?>