<?php

class TLog_access extends TTable {

	var $name = 'log_access';
	var $table = 'log_access';
	var $domain_selector = false;
//------------------------------------------------------------------------------
	function TLog_access() {
		global $actions, $str;

		TTable::TTable();

        $actions[$this->name] = array(
            'Clear' => array(
                'Очистить журнал',
                'Delete',
                'link'	=> 'cnt.ClearItems()',
                'img' 	=> 'icon.delete.gif',
                'display'	=> 'none',
            ),
        );

        $actions[$this->name . '.editform'] = array(
            'cancel' => array(
                'title' => array(
                    'ru' => 'Закрыть',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );


		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Журнал посещений',
				'Access log',
			),
            'title_editform'	=> array(
                'Журнал посещений',
                'Access log',
            ),
			'login'	=> array(
				'Логин',
				'Login',
			),
			'date'	=> array(
				'Дата',
				'Date',
			),
			'ip'	=> array(
				'IP',
				'IP',
			),
		));
	}

//------------------------------------------------------------------------------
	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

        require_once (core('list_table'));
		$data['thisname'] = $this->name;

		$logins = array('' => '-') + sql_getRows('SELECT DISTINCT login FROM '.$this->table.' ORDER BY login', true);

		$data['root'] = is_root();
		$this->AddStrings($data);
		$data['table'] = list_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'login',
					'display'	=> 'login',
					'flags'     => FLAG_SORT | FLAG_SEARCH | FLAG_FILTER,
					'filter_type' => 'array',
					'filter_value' => $logins,
				),
				array(
					'select'	=> 'ip',
					'display'	=> 'ip',
					'width'		=> '1px',
					'flags'     => FLAG_SORT | FLAG_SEARCH,
				),
				array(
					'select'	=> 'date',
					'display'	=> 'date',
					'type'		=> 'datetime',
					'width'		=> '100px',
					'flags'     => FLAG_SORT,
				),
			),
			'from'		=> ' log_access ',
			'orderby'   => 'date DESC',
			'params'	=> array('page' => $this->name, 'do' => 'show',),
			'dblclick'	=> '',
			'click'		=> '',
		), $this);
        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
	}

//------------------------------------------------------------------------------

	function clear(){
		$ret = sql_query("DELETE FROM log_access");
		return '<script>parent.navigate();</script>';
	}
//------------------------------------------------------------------------------
}

$GLOBALS['log_access'] = & Registry::get('TLog_access');
?>