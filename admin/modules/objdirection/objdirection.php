<?php

class TObjDirection extends TTable {

	var $name = 'objdirection';
	var $table = 'obj_direction';

	//-----------------------------------------------------------------------

	function TObjDirection() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('�����������',				'Directions',),
			'name'			=> array('��������',				'Title',),
			'saved'			=> array('������ ������� ���������','Data has been saved successfully'),
			'all'			=> array('���',						'none'),
		));

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'�������',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
            'moveup' => &$actions['table']['moveup'],
            'movedown' => &$actions['table']['movedown'],
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

$GLOBALS['objdirection'] = & Registry::get('TObjDirection');
?>