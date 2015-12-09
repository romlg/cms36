<?php

class TObjects extends TTable {

	var $name  = 'objects';
	var $table = 'objects';
	var $prefix = '----';

	//-----------------------------------------------------------------------

	function TObjects() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'address'		=> array('Адрес',					'Address',),
			'all'			=> array('-- Все --',				' -- All --'),
			'basic'			=> array('Основные поля',			'Basic fields',),
			'basic_caption'	=> array('Объект недвижимости',		'Object',),
			'city'			=> array('Город',					'City',),
			'delete'		=> array('Удалить',					'Delete',),
			'district'		=> array('Район',					'District',),
			'group_str'		=> array('Групповое обновление',	'Groups',),
			'group_submit'	=> array('Обновить',				'Update',),
			'hot'			=> array('Горячий',					'Hot',),
			'visible'		=> array('Показывать',					'Visible',),
			'lot'			=> array('Лот',						'lot',),
			'ipix'			=> array('Трехмерный просмотр',		'IPIX'),
			'manager'		=> array('Менеджер',				'Manager',),
			'metrostation'	=> array('Станция метро',			'Metro station'),
			'price'			=> array('Цена',					'Price',),
			'recycle'		=> array('Корзина',					'Recycle Bin',),
			'restore'		=> array('Восстановить',			'Restore',),
			'saved'			=> array('Данные успешно сохранены','Data has been saved successfully'),
			'send'			=> array('Рассылка',				'Send',),
			'square'		=> array('Площадь',					'Square',),
			'st_prodat'		=> array('Продажи',					'To sell',),
			'st_arenda'		=> array('Аренды',					'For rent'),
			'title'			=> array('Объекты недвижимости',	'Objects',),
			'tolet'			=> array('Свободные площади',		'Free area',),
			'type'			=> array('Тип',						'Type',),
			'update_time'	=> array ('Дата обновления',		'date update',),
		));

		$actions[$this->name] = array(
			'create' => &$actions['table']['create'],
			'edit' => &$actions['table']['edit'],
			'delete' => array(
				'Удалить',
				'Delete',
                'link'    => 'cnt.deleteItems(\''.$this->name.'\', \'\', -1)',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			),
            'recycle' => array(
                'Корзина',
                'Recycle Bin',
                'link'    => 'cnt.showRecycle()',
                'img'     => 'icon.trash.gif',
                'display'    => 'block',
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
	function table_get_hidden(&$value, &$column, &$row) {
		$check = ($value) ? ' checked="checked"': '';
		$check="<input type='checkbox'".$check." disabled> ";
		return $check;
	}

	//-----------------------------------------------------------------------
	function table_get_sell_type(&$value, &$column, &$row) {
		return sql_getValue('SELECT name FROM obj_transaction WHERE id='.$value);
	}

	//-----------------------------------------------------------------------
	function table_get_date(&$value, &$column, &$row) {
		return date('d.m.Y', strtotime($value));
	}

	//-----------------------------------------------------------------------
	function setFilterData(&$data){
		$belong = isset($_GET['filter']['otr.name']) ? $_GET['filter']['otr.name'] : 0;
		$childs = $this->getChilds('obj_types', 'ORDER BY priority', null, 'belong='.$belong);
		$this->getList($childs, $types);
		unset($data['columns'][6]['filter_value']);
		$data['columns'][6]['filter_value'][0] = $this->str('-все-');
		if (!empty($types)) $data['columns'][6]['filter_value'] += $types;
	}

	//-----------------------------------------------------------------------
	function Show_base($columns, $dataset = array(), $where = '', $function = '') {
		global $directories;
		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				return $this->$action();
			}
		}

		require_once(core('ajax_table'));
		
		if (!empty($dataset))
			$ret['table'] = ajax_table(array('columns'	=> $columns,
			'dataset' => $dataset,
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			), $this);
		else 
			$ret['table'] = ajax_table(array('columns'	=> $columns,
			'from' => $this->table.' AS o
					LEFT JOIN obj_locat_districts d       ON ( o.district_id = d.id)
					LEFT JOIN obj_locat_metrostations m   ON ( o.metro_id = m.id )',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'where'		=> !empty($where) ? $where : 'visible > -1',
			'orderby'	=> 'o.priority',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			'function'	=> $function,
			), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	//-----------------------------------------------------------------------
	function getChilds($table, $orderby, $id=null, $where=null, $level=0){
		$childs = array();
    	if (!isset($id))
			$childs = sql_getRows("SELECT * FROM ".$table." WHERE ".(isset($where) ? $where.' AND ' : '')." pid IS NULL ".$orderby, true);
		else 
			$childs = sql_getRows("SELECT * FROM ".$table." WHERE ".(isset($where) ? $where.' AND ' : '')." pid=".$id." ".$orderby, true);

		if (!empty($childs)) {
    		foreach ($childs as $key=>$val) {
    			$childs[$key]['name'] = str_pad($val['name'], strlen($val['name']) + $level*strlen($this->prefix), $this->prefix, STR_PAD_LEFT);
    			$childs[$key]['items'] = $this->getChilds($table, $orderby, $val['id'], $where, $level+1);
    		}
    	}
    	return $childs;
	}

	//-----------------------------------------------------------------------
	function editgetDistrict(){

		$table = get('table', 'objects', 'g');
		$id = (int)get('id', 0, 'g');
		$city = (int)get('city', 0, 'g');
		$ret = array();
		if ($city) $childs = $this->getChilds('obj_locat_districts', 'ORDER BY name', $city);
		else $childs = $this->getChilds('obj_locat_districts', 'ORDER BY name');
		$this->getList($childs, $ret);
		
		$selected = sql_getValue('SELECT district_id FROM '.$table.' WHERE id='.$id);

		header('Content-Type: text/xml');
		$str = '<?xml version="1.0" encoding="windows-1251" standalone="yes" ?><body>';
		if ($ret) foreach ($ret as $k=>$v) $str .= '<item><id>'.$k.'</id><name>'.$v.'</name><selected>'.($k == $selected ? '1' : '0').'</selected></item>';
		echo $str;
		echo '</body>';
	}

	//-----------------------------------------------------------------------
    function getList($pids, &$res, $full = 0) {
    	foreach ($pids as $key=>$val) {
			if ($full) $res[$val['id']] = $val; else $res[$val['id']] = $val['name'];
			if (isset($val['items'])) {
				$this->getList($pids[$key]['items'], $res, $full);
			}
    	}
    }

	//-----------------------------------------------------------------------
    function editgetMetro(){
		$table = get('table', 'objects', 'g');
		$district = (int)get('district', 0, 'g');
		$id = (int)get('id', 0, 'g');
		
		// Выбираем список метро для данного района и всех его подрайонов
		$childs = $this->getChilds('obj_locat_districts', 'ORDER BY name', $district);
		$this->getList($childs, $districts);
		$districts[$district] = array();
		
		$metro[] = sql_getRow('SELECT * FROM obj_locat_metrostations WHERE id=1');
		$metro = array_merge($metro, sql_getRows('SELECT * FROM obj_locat_metrostations WHERE district IN ('.implode(',', array_keys($districts)).')'));

		if ($table == 'objects') $selected = sql_getValue('SELECT metro_id FROM '.$table.' WHERE id='.$id);

		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="windows-1251" standalone="yes" ?><body>';
		foreach ($metro as $key=>$val) {
			echo '<item><id>'.$val['id'].'</id><name>'.$val['name'].'</name><selected>'.(isset($selected) && $selected == $val['id'] ? '1' : '0').'</selected></item>';
		}
		echo '</body>';
	}

	//-----------------------------------------------------------------------
    function editgetRegion(){

		$country = get('country', '', 'g');
		$id = (int)get('id', 0, 'g');
		$ret = array();

		$ret = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid='.$country.' ORDER BY name', true);
		
		$selected = sql_getValue('SELECT region FROM objects WHERE id='.$id);
		
		header('Content-Type: text/xml');
		$str = '<?xml version="1.0" encoding="windows-1251" standalone="yes" ?><body>';
		if ($ret) foreach ($ret as $k=>$v) $str .= '<item><id>'.$k.'</id><name>'.$v.'</name><selected>'.($k == $selected ? '1' : '0').'</selected></item>';
		echo $str;
		echo '</body>';
	}

	//-----------------------------------------------------------------------
    function ShowRecycle() {
        global $limit;

        $limit = -1;
        require_once(core('ajax_table'));

        $columns = sql_getRows('SHOW columns FROM '.$this->table, 'Field');
        $name = isset($columns['name']) ? 'name' : 'address';
        
        $this->AddStrings($row);
        $row['table'] = ajax_table(array(
            'columns'    => array(
                array(
                    'select'    => 'id',
                    'display'    => 'id',
                    'type'        => 'checkbox',
                ),
                array(
                    'select'    => $name,
                    'display'    => 'name',
                ),
            ),
            'where'        => 'visible<0',
            'orderby'    => 'address',
            'params'    => array('page' => $this->name, 'do' => 'Show'),
        ), $this);

        return Parse($row, 'recycle.tmpl');
    }
}
?>