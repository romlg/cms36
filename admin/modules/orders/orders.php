<?php

class TOrders extends TTable {

	var $name = 'orders';
	var $table = 'orders';
    var $selector = true;

	########################

	function TOrders() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> "cnt.deleteItems('".$this->name."',null,1)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	        => array('Заявки',					'Orders'),
			'name'	        => array('ФИО',				        'Name'),
			'date'	        => array('Дата',					'Date'),
			'contacts'		=> array('Контакты',    			'Contacts'),
			'info'			=> array('Дополнительная информация','Info'),
			'lot_id'		=> array('Лот',    					'Lot'),
			'flat_id'		=> array('Квартира',				'Flat'),
		));
	}
	########################    

	function Show() {
        global $lang;
        if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$ret['thisname'] = $this->name;
		
		$ret['params'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'object_id',
					'display'	=> 'lot_id',
					'type'		=> 'lot',
				),
				array(
					'select'	=> 'flat_id',
					'display'	=> 'flat_id',
					'type'		=> 'flat',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
				),
				array(
					'select'	=> 'date',
					'display'	=> 'date',
					'type'	    => 'date',
				),
				array(
					'select'	=> 'contacts',
					'display'	=> 'contacts',
				),
			),
			'orderby'	=> 'date desc',
            'from'      => $this->table,
            'where'     => '',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'	=> 'ID=cb.value',
			'dblclick' => 'editItem(id)',
			//'_sql' => true,
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');
	}
	
	function table_get_lot(&$value) {
		$row = sql_getRow('SELECT * FROM objects WHERE id='.$value);
		if (!$row) return '';
		return '<a href="/admin/ed.php?page=objects_'.$row['obj_type_id'].'&id='.$row['id'].'" class="open" target="_blank">'.$row['lot_id'].'</a>';
	}

	function table_get_flat(&$value, &$column, &$row) {
		if (!$value) {
			$row = sql_getRow('SELECT * FROM objects WHERE id='.$row['object_id']);
		} else {
			$row = sql_getRow('SELECT * FROM obj_elem_free WHERE id='.$value);
		}
		if (!$row) return '';
		$ret = '';
		if ($row['room']) $ret .= $row['room'].'-комн., ';
		if ($row['total_area'] > 0) $ret .= $row['total_area'].'/';
		if ($row['living_area'] > 0) $ret .= $row['living_area'].'/';
		if ($row['kitchen_area'] > 0) $ret .= $row['kitchen_area'].'/';
		if ($row['storey']) $ret .= ', '.$row['storey'].' этаж';
		return $ret;
	}
}

$GLOBALS['orders'] = &Registry::get('TOrders');

?>