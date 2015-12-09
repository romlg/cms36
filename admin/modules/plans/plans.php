<?php

class TPlans extends TTable {

	var $name  = 'plans';
	var $table = 'obj_elem_plans_items';

	//-----------------------------------------------------------------------

	function TPlans() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Планировки',		'Plans'),
			'image'			=> array('Изображение',		'Image',),
		));

		$actions[$this->name] = array(
			'create' => &$actions['table']['create'],
			'edit' => &$actions['table']['edit'],
			'delete' => array(
				'Удалить',
				'Delete',
                'link'    => 'cnt.deleteItems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			),
		);
		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link' => 'closeTab(\'save\');return false;',
				'img' => 'icon.save.gif',
				'display' => 'block',
			),
			'close' => array(
				'Закрыть',
				'Close',
				'link' => 'closeTab(\'cancel\');return false;',
				'img' => 'icon.close.gif',
				'display' => 'block',
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
					'width'     => '1px',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
				),
				array(
					'select'	=> 'image',
					'display'	=> 'image',
					'type'		=> 'image',
				),
		);
		
		$ret['table'] = ajax_table(array('columns'	=> $columns,
			'from' => $this->table,
			'where'		=> 'pid='.$_GET['pid'],
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'orderby'	=> 'priority',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	//-----------------------------------------------------------------------
	function table_get_image(&$value) {
		return "<a href='#' onclick=\"window.open('../popup.php?img=$value', 'popup', 'width=100,height=100'); return false\">$value</a>";
		
	}
}
$GLOBALS['plans'] = &Registry::get('TPlans');
?>