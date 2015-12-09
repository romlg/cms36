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
				'������ ������',
				'Module error',
			),
			'e_module_disabled' => array(
				'������ ��������',
				'Module disabled',
			),
			'e_module_forbidden' => array(
				'������ �� �������� � �������������',
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