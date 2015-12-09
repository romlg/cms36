<?php

require_once 'modules/objects_func.php';

class TObjects_Commerce extends TTable {

	var $name  = 'objects_commerce';
	var $table = 'objects';
	var $filter = array();

	function TObjects_Commerce(){
		global $str, $actions;
		
		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Коммерческая недвижимость',		'Commercial realty',),
			'lot'			=> array('Лот',						'Lot',),
			'manager'		=> array('Контактное лицо',			'Manager',),
			'sell_type'		=> array('Тип операции',			'Operation type'),
			'comment'		=> array('Комментарий',				'Comment'),
			'region'		=> array('Регион',					'Region'),
			'update_time'	=> array('Дата обновления',			'date update',),
			'visible'		=> array('Показывать',				'Visible',),
			'address'		=> array('Адрес',					'Address',),
			'type'			=> array('Тип объекта',				'Object type',),
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
		
		$ret['table'] = ajax_table(array('columns'	=> $columns,
			'from' => $this->table.' AS o
					LEFT JOIN obj_locat_metrostations m   ON ( o.metro_id = m.id )',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'where'		=> 'obj_type_id="commerce" AND o.visible > -1',
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
					'select'	=> 'CAST(o.lot_id AS UNSIGNED)',
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

	    $rows['objects'] = objects_getList(isset($_SESSION['RIEL']) ? $_SESSION['RIEL'] : array(), 'commerce');

        objects_formatList($rows['objects']);

	    $rows['fields'] = objects_getFields('commerce', isset($_SESSION['RIEL']['print_filter']['o.region']) ? $_SESSION['RIEL']['print_filter']['o.region'] : 0);
	    $rows['align'] = objects_getAlign();

	    $rows['title'] = 'Коммерческая недвижимость';

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
            'where'        => 'visible<0 and obj_type_id="commerce"',
            'orderby'    => 'address',
            'params'    => array('page' => $this->name, 'do' => 'Show'),
        ), $this);

        return Parse($row, 'recycle.tmpl');
    }

}

$GLOBALS['objects_commerce'] = &Registry::get('TObjects_Commerce');

?>