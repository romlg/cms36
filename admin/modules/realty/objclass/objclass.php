<?php

require_once(elem('realty/objects/objects_base'));

class TObjClass extends TObjects_base {

	var $name = 'objclass';
	var $table = 'obj_class';

	//-----------------------------------------------------------------------

	function TObjClass() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Классы объектов',			'Object classes',),
			'name'			=> array('Название',				'Title',),
			'Path'			=> array('Путь',					'Path',),
			'variety'		=> array('Принадлежит',				'Variety',),
			'transaction'	=> array('Вид сделки',				'Transaction',),
			'saved'			=> array('Данные успешно сохранены','Data has been saved successfully'),
			'all'			=> array('нет',						'none'),
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
            'moveup' => &$actions['table']['moveup'],
            'movedown' => &$actions['table']['movedown'],
		);
	}

	//-----------------------------------------------------------------------

	function Show() {

		require_once(core('ajax_table'));

		$rows = $this->getChilds($this->table, 'ORDER BY priority');
		$types = array();
		$this->getList($rows, $types, 1);

		$columns = array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'type'		=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'belong',
					'type'		=> 'sell_type',
					'display'	=> 'transaction',
				),
		);
		
		return $this->Show_base($columns, $types);	
	}

	//-----------------------------------------------------------------------

    /**
     * Функция возвращает XML-структуру типов для заданного вида сделки
     *
     */
	function editgetTypes(){

		$belong = get('belong',0,'g');
		$id = get('id',0,'g');
		$ret = $this->getChilds($this->table, 'ORDER BY priority', null, 'belong='.$belong);
		$this->getList($ret, $types, 1);

		// Выделенный пункт
		$selected = sql_getValue('SELECT pid FROM obj_class WHERE id='.$id);

		header('Content-Type: text/xml');
		$str = '<?xml version="1.0" encoding="windows-1251" standalone="yes" ?><body>';
		$str .= '<item><id>NULL</id><name>'.$this->str('all').'</name><selected>'.(!$selected ? '1' : '0').'</selected></item>';
		if ($types) foreach ($types as $k=>$v){
			$str .= '<item><id>'.$v['id'].'</id><name>'.$v['name'].'</name><selected>'.($v['id'] == $selected ? '1' : '0').'</selected></item>';
		 }
		 echo $str;
		 echo '</body>';
	}

}

$GLOBALS['objclass'] =  & Registry::get('TObjClass');
?>