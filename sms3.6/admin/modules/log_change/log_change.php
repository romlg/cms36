<?php

class TLog_change extends TTable {

	var $name = 'log_change';
	var $table = 'log_change';
	var $domain_selector = false;
//------------------------------------------------------------------------------
	function TLog_change() {
		global $actions, $str;

		TTable::TTable();

		$actions[$this->name] = array(
			'Clear' => array(
				'�������� ������',
				'Delete',
				'link'	=> 'cnt.ClearItems()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'				=> array('������ ���������',				'Change log',),
			'user'				=> array('�����',							'Login',),
			'date'				=> array('����',							'Date',),
			'object'			=> array('�������',							'Table',),
			'action'			=> array('���',								'Type',),
			'description'		=> array('��������',						'Description',),

	        //--- Navigation ----------------------------
	        'show_from' 			=> array('C',								'From',),
	        'show_to' 				=> array('��',								'to',),
	        'nav_period' 			=> array('������',							'Period',),
	        'nav_more' 				=> array('�������������',					'More',),
	        'nav_select_period'		=> array('����� �������',					'Select period',),
	        'nav_today' 			=> array('�� �������',						'today',),
	        'nav_today_title' 		=> array('�������� ���������� �� �������',	'View statistic for today',),
	        'nav_yesterday' 		=> array('�����',							'yesterday',),
	        'nav_yesterday_title'	=> array('�������� ���������� �� �����',	'View statistic for yesterday',	),
	        'nav_week'				=> array('�� ������',						'last week',),
	        'nav_week_title'		=> array('�������� ���������� �� ������',	'View statistic for the last week',),
	        'nav_month' 			=> array('�� �����',						'last month',),
	        'nav_month_title' 		=> array('�������� ���������� �� �����',	'View statistic for the last month',),
	        'nav_all' 				=> array('�� ��� �����',					'all time',),
	        'nav_all_title' 		=> array('�������� ���������� �� ��� �����','View statistic for all time',),
	        'select_date'			=> array('������� ����',					'Select date',),
	        'nav_show'				=> array('��������',						'show',),
		));
	}

//------------------------------------------------------------------------------

	function table_get_description(&$value, &$column, &$row) {
		$str = h($value);
		if (strlen($str) <= 200) return $str;
		$ret = '<div><div style="display: none">'.$str.'</div>'.substr($str, 0, 200).'... <a href="#" class="open" onclick="cut_text(this); return false;">�����</a></div>';
		return $ret;
	}

//------------------------------------------------------------------------------

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
	    $this->SetValues();

		require_once(core('ajax_table'));
		$data['thisname'] = $this->name;

		$data['root'] = is_root();
		$this->AddStrings($data);

		$_tables = sql_getRows('SHOW tables');
		foreach ($_tables as $key=>$val) $tables[$val] = $val;

		$_transactions = sql_getColumn('SELECT distinct action FROM '.$this->table.' ORDER BY action');
		foreach ($_transactions as $key=>$val) $transactions[$val] = $val;

		$where = '';
        if ($this->from_date && $this->to_date) {
            $where = ' UNIX_TIMESTAMP(date)>='.$this->from_date.' AND UNIX_TIMESTAMP(date)<='.$this->to_date;
        }

        $data['table'] = ajax_table(array(

			'columns'	=> array(
				array(
					'select'	=> 'user',
					'display'	=> 'user',
					'width'		=> '1px',
					'flags'		=> FLAG_SORT | FLAG_SEARCH,
				),
				array(
					'select'	=> 'object',
					'display'	=> 'object',
					'width'		=> '1px',
					'flags'		=> FLAG_SORT | FLAG_FILTER | FLAG_SEARCH,
					'filter_type'	=> 'array',
					'filter_value'	=> array('' => '-- ��� --') + $tables,
					'filter_rule'	=> 'find_in_set',
				),
				array(
					'select'	=> 'action',
					'display'	=> 'action',
					'width'		=> '1px',
					'flags'		=> FLAG_SORT | FLAG_FILTER | FLAG_SEARCH,
					'filter_type'	=> 'array',
					'filter_value'	=> array('' => '-- ��� --') + $transactions,
				),
				array(
					'select'	=> 'description',
					'display'	=> 'description',
					'type'		=> 'description',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'date',
					'display'	=> 'date',
					'type'		=> 'datetime',
					'width'		=> '100px',
					'flags'		=> FLAG_SORT,
				),
			),
			'from'		=> $this->table,
			'where'		=> $where,
			'orderby'   => 'date DESC',
			'params'	=> array('page' => $this->name, 'do' => 'show',),
			'dblclick'	=> '',
			'click'		=> '',
			//'_sql'		=> true,
		), $this);

        $data['navig'] = $this->NavigForm();

		return $this->parse($data, $this->name.'.tmpl');
	}

//------------------------------------------------------------------------------
	######################
	// ������������� �������� ���������� ��� ����������:
	// $this->group 	- ������������ ������ (��� ������������)
	// $this->disp_by	- ���������� ����������� (��� ������������)
	// $this->from_date	- ���� �
	// $this->to_date	- ���� ��

	function SetValues() {

		 // ����������� ����������
		$period		= get('period','');
		$from		= get('from_date', $this->Param('from_date', ''));
		$to 		= get('to_date', $this->Param('to_date', ''));

		// Set From_date and To_date
		if ($from && $to) {
			$from = explode('-', $from);
			$to = explode('-', $to);
			if(count($to)>2 && count($from)>2) {
				$this->from_date = mktime(0, 0, 0, $from[1], $from[2], $from[0]);
				$this->to_date = mktime(23, 59, 59, $to[1], $to[2], $to[0]);
			}
		}
		// Set From_date and To_date if period set
		if ($period != '') {
			if ($period == 'all') {
				$this->from_date = sql_getValue("SELECT FLOOR(UNIX_TIMESTAMP(date)/86400)*86400 FROM ".$this->table." ORDER BY UNIX_TIMESTAMP(date) ASC LIMIT 1");
			}
			else {
				$this->from_date = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $period*86400;
			}
			if ($period == 1) {
				$this->to_date = mktime(23, 59, 59, date('m'), date('d') - 1, date('Y'));
			}
			else {
				$this->to_date = time();
			}
		}
		// Set Default From_date and To_date
		if (empty($this->from_date)) {
			$this->from_date = mktime(0,0,0,date('m') ,date('d')-7 ,date('Y'));
		}
		if (empty($this->to_date)) {
			$this->to_date = mktime(23, 59, 59, date('m'), date('d') ,date('Y'));
		}

		// ���������� ����������
		if ($this->from_date) {
			$this->Param('from_date', '', date('Y-m-d', $this->from_date));
		}
		if ($this->to_date) {
			$this->Param('to_date', '', date('Y-m-d', $this->to_date));
		}
	}

//------------------------------------------------------------------------------

    // �������������� ������ (��� �������) ��� ����� ���������
    function NavigForm() {
        $this->AddStrings($ret);
        // �������� ����
        $params['hidden']['page']	= $this->name;
        $params['hidden']['do']		= $GLOBALS['do'];
        $params['hidden']['period']	= '';
        $params['hidden']['view']	= '';

        $ret['from_date'] = strftime('%Y-%m-%d', $this->from_date);
        $ret['to_date'] = strftime('%Y-%m-%d', $this->to_date);
        $ret['show_from'] = strftime('%e %B, %Y (%a)', $this->from_date);
        $ret['show_to'] = strftime('%e %B, %Y (%a)', $this->to_date);

        // �������� ��������� ��� ������������
        if (isset($params['interval']) && $params['interval']) {
            $intervals = array(
            'hour'			=> 'nav_by_hour',
            'dayofmonth'	=> 'nav_by_days',
            'weekday'		=> 'nav_by_weeks',
            'month'			=> 'nav_by_month',
            );
            $ret['interval'] = array(
            'select_interval'	=> $this->GetArrayOptions($intervals, $this->disp_by, true, true),
            'group_checked'		=> $this->group ? 'checked' : '',
            'str_nav_interval'	=> $this->str('nav_interval'),
            'str_nav_sum'		=> $this->str('nav_sum'),
            );
        }

        // hidden ����
        foreach ($params['hidden'] as $key=>$val)
        $ret['hidden'][] = array('name' => $key, 'value' => $val);

        return $ret;
    }

//------------------------------------------------------------------------------

	function clear(){
		$ret = sql_query("TRUNCATE TABLE log_change");
		return '<script>parent.navigate();</script>';
	}
//------------------------------------------------------------------------------
}

$GLOBALS['log_change'] = & Registry::get('TLog_change');

?>