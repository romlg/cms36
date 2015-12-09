<?php

class TBalcony extends TTable {

	var $name = 'balcony';
	var $table = 'obj_balcony';

	//-----------------------------------------------------------------------

	function TBalcony() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Балконы',					'Balcony',),
			'name'			=> array('Заголовок',				'Title',),
		));

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);
	}

	//-----------------------------------------------------------------------
	
	function Show() {

		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				return $this->$action();
			}
		}

		require_once(core('ajax_table'));

		$columns = array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SORT,
				),
		);
		
		$ret['table'] = ajax_table(array('columns'	=> $columns,
			'from' => $this->table,
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'where'		=> '',
			'orderby'	=> 'name',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	//-----------------------------------------------------------------------

}

$GLOBALS['balcony'] = &Registry::get('TBalcony');
?>