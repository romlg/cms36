<?php

require_once 'modules/objects_func.php';

class TObjects_Room extends TTable {

	var $name  = 'objects_room';
	var $table = 'objects';
	var $filter = array();

	function TObjects_Room(){
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Квартиры, комнаты',		'Rooms',),
			'comment'		=> array('Комментарий',				'Comment'),
			'room'			=> array('Кол-во комнат',			'Room count'),
			'visible'		=> array('Показывать',				'Visible',),
			'lot'			=> array('Лот',						'lot',),
			'address'		=> array('Адрес',					'Address',),
			'manager'		=> array('Контактное лицо',			'Manager',),
			'update_time'	=> array('Дата обновления',			'date update',),
			'url_ref'	=> array('Связанный объект',			'Reference object',),
			'credit'		=> array('Рассрочка',				'Credit',),
			'sell'		    => array('Продано',					'Sell',),
			'avance'	    => array('Аванс',					'Avance',),
			'status'		=> array('Статус',				'Status',),
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
		$actions[$this->name]['print'] = array(
			'Напечатать выбранное',
			'Print',
            'link'    => 'cnt.PrintSelected()',
			'img' => 'icon.print.gif',
			'display' => 'block',
		);
	}

	function table_get_lot(&$value) {
		return "<b>".$value."</b>";
	}

	function Show() {

		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				return $this->$action();
			}
		}

		require_once(core('ajax_table'));
		$columns = $this->getTableData();
		$client_id = get('client_id', '', 'gp');
		$cab = get('cab', '', 'gp');
		$where = $cab ? ' AND client_id='.$client_id : '';

		$ret['table'] = ajax_table(array('columns'	=> $columns,
			'from' => $this->table.' AS o
					LEFT JOIN obj_locat_metrostations m   ON ( o.metro_id = m.id )',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'where'		=> 'obj_type_id="room" AND o.visible > -1'.$where,
			'orderby'	=> 'o.priority',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			'function'	=> 'setFilterData',
			), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	function getTableData(){
		global $settings;
		$columns = array(
				array(
					'select'	=> 'o.id',
					'as'		=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'width'     => '1px',
				),
				array(
					'select'	=> 'o.lot_id',
					'display'	=> 'lot',
					'type'		=> 'lot',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'     =>'1px',
				),
				array(
					'select'	=> 'o.address',
					'as'		=> 'address',
					'display'	=> 'address',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'     => '1px',
				),
				array(
					'select'	=> 'o.room',
					'display'	=> 'room',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'o.update_time',
					'display'	=> 'update_time',
					'as'        => 'oupdtime',
					'type'		=> 'date',
				),
				array(
					'select'	=> 'o.visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'o.status',
					'display'	=> 'status',
					'type'		=> 'status',
					'flags'		=> FLAG_SORT | FLAG_FILTER,
					'filter_type' => 'array',
					'filter_display' => 'status',
					'filter_value' => array(''=>'-')+$settings['status_types'],
				),
				array(
					'select'	=> 'IF(o.credit=1, 1, 2)',
					'as'        => 'credit',
					'flags'		=> FLAG_FILTER,
					'filter_type' => 'array',
					'filter_display' => 'credit',
					'filter_value'=> array('' => '-', '2' => 'нет', '1' => 'да')
				),
				array(
					'select'	=> 'IF(o.sell=1, 1, 2)',
					'as'        => 'sell',
					'flags'		=> FLAG_FILTER,
					'filter_type' => 'array',
					'filter_display' => 'sell',
					'filter_value'=> array('' => '-', '2' => 'нет', '1' => 'да')
				),
				array(
					'select'	=> 'IF(o.avance=1, 1, 2)',
					'as'        => 'avance',
					'flags'		=> FLAG_FILTER,
					'filter_type' => 'array',
					'filter_display' => 'avance',
					'filter_value'=> array('' => '-', '2' => 'нет', '1' => 'да')
				),
		);
        return $columns;

	}

	function table_get_status(&$value, &$column, &$row) {
	    global $settings;
	    return $settings['status_types'][$value];
	}

	function setFilterData(&$data){
	    // Сохраняем $_GET['filter'] в сессию
	    session_start();
	    $_SESSION['RIEL']['print_filter'] = isset($_GET['filter']) ? $_GET['filter'] : array();
	    $_SESSION['RIEL']['find'] = isset($_GET['find']) ? $_GET['find'] : '';
	    $_SESSION['RIEL']['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 0;
	    session_write_close();
	}

	//-----------------------------------------------------------------------
	function showprintForm(){

	    $rows['objects'] = objects_getList(isset($_SESSION['RIEL']) ? $_SESSION['RIEL'] : array(), 'room');

        objects_formatList($rows['objects']);

	    $rows['fields'] = objects_getFields(isset($_SESSION['RIEL']['print_filter']['room_type']) ? $_SESSION['RIEL']['print_filter']['room_type'] : 0, isset($_SESSION['RIEL']['print_filter']['o.region']) ? $_SESSION['RIEL']['print_filter']['o.region'] : 0);
	    $rows['align'] = objects_getAlign();

	    $rows['title'] = 'Квартиры';

	    if (isset($_SESSION['RIEL']['print_filter'])) {
    	    $rows['filter'] = objects_getFilter($_SESSION['RIEL']['print_filter']);
	    } else $rows['filter'] = array();

	    return $this->Parse($rows, '../objects_print.tmpl');
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
            'where'        => 'visible<0 and obj_type_id="room"',
            'orderby'    => 'address',
            'params'    => array('page' => $this->name, 'do' => 'Show'),
        ), $this);

        return Parse($row, 'recycle.tmpl');
    }

	//-----------------------------------------------------------------------
	function editgetSellType(){
		$id = (int)get('id', 0, 'g');
		$sell_type_id = sql_getValue('SELECT sell_type_id FROM '.$this->table.' WHERE id='.$id);

		header('Content-Type: text/xml');
		$str = '<?xml version="1.0" encoding="windows-1251" standalone="yes" ?><body>';
		$str .= '<sell_type>'.$sell_type_id.'</sell_type>';
		echo $str;
		echo '</body>';
	}

	//-----------------------------------------------------------------------
}

$GLOBALS['objects_room'] = &Registry::get('TObjects_Room');

?>