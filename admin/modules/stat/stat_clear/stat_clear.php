<?php

require_once (module('stat'));

class TClear extends TStat {

	var $name = 'stat/stat_clear';

	########################

	function TClear() {
		global $str, $actions;

		TStat::TStat();

		$str[get_class_name($this)] = $str['tstat'] + array(
			'clear' 	=> array(
				'Очистка',
				'Clearing',
			),
			'period'	=> array(
				'Оставить за',
				'Saved period',
			),
			'1d'		=> array(
				'День',
				'Day',
			),
			'7d'		=> array(
				'Неделя',
				'Week',
			),
			'1m'		=> array(
				'1 месяц',
				'1 month',
			),
			'3m'		=> array(
				'3 месяца',
				'3 monthes',
			),
			'6m'		=> array(
				'Полгодa',
				'6 monthes',
			),
			'1y'		=> array(
				'Год',
				'1 Year',
			),
			'submit'	=> array(
				'Очистить',
				'Clear',
			),
			'error'		=> array(
				'Ошибка',
				'Error',
			),
			'ok'		=> array(
				'Операция успешно завершена',
				'Operation was sucesfully ended',
			),
			'info_begin'	=> array(
				'Начало сбора',
				'Collection begin date',
			),
			'info_last'	=> array(
				'Последние данные',
				'Last data',
			),
			'info_size'	=> array(
				'Размер БД',
				'Data base size',
			),
			'info_rows'	=> array(
				'Число записей',
				'Number of records',
			),
			'after'	=> array(
				'Осталось',
				'After',
			),
			'before'	=> array(
				'Было',
				'Before',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		$this->data['before'] = $this->GetStats();

		$period = get('period','none','p');
		if($period=='1d' or $period=='7d' or $period=='1m' or $period=='3m' or $period=='6m' or $period=='1y') {
			$ret['message'] = $this->DoClear($period);
		}
		$ret['navig'] = $this->NavigForm(array(
			'hidden'	=> array('show' => $this->show),
		));
		$ret['options'] = array(
		  'values' =>   array ('1d','7d','1m','3m','6m','1y'),
		  'names'  =>   array ($this->str('1d'),$this->str('7d'),$this->str('1m'),$this->str('3m'),$this->str('6m'),$this->str('1y')),
		  'selected' => $period,
		);
		$ret['data'] = $this->data;
		return Parse($ret, 'stat/stat_clear/stat.clear.tmpl');
	}

	######################

	function GetStats()	{
		$sess = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_SESSIONS_TABLE."'", 'number');
		$log = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_LOG_TABLE."'", 'number');
		$pages = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_PAGES_TABLE."'", 'number');
		$agents = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_AGENTS_TABLE."'", 'number');
		$db['Data_length'] = $sess['Data_length'] + $log['Data_length'] + $pages['Data_length'] + $agents['Data_length'];
		$db['Index_length'] = $sess['Index_length'] + $log['Index_length'] + $pages['Index_length'] + $agents['Index_length'];
		$db['Rows'] = $sess['Rows'] + $log['Rows'] + $pages['Rows'] + $agents['Rows'];
		return array(
			'STATINFO' => $this->str('info'),
			'rows' => array(
				0 => array(
					'key' => $this->str('info_size'),
					'val' => number_format( ($db['Data_length']+$db['Index_length']) / 1024, 2, ',', ' ')." KB",
				),
				1 => array(
					'key' => $this->str('info_rows'),
					'val' => $db['Rows'],
				),
			),
		);
	}


	######################
	function DoClear($period) {

// 		init
		$save = array(array());

//		время после которого надо сохранить статистику
		switch($period) {
			case '1d': $time = time() - (60*60*24*1); break;
			case '7d': $time = time() - (60*60*24*7); break;
			case '1m': $time = time() - (60*60*24*31*1); break;
			case '3m': $time = time() - (60*60*24*31*3); break;
			case '6m': $time = time() - (60*60*24*31*6); break;
			case '1y': $time = time() - (60*60*24*31*12); break;
		}
//   	выбираем id которые надо сохранить
		$res = sql_query("select sess_id, ref_id, first_page, last_page, agent_id, path from stat_sessions where time>".$time);
		while($row = mysql_fetch_array($res))
		{
			if ($row['sess_id']) $save['sess_id'][] = $row['sess_id'];
			if ($row['ref_id']) $save['page_id'][] = $row['ref_id'];
			if ($row['first_page']) $save['page_id'][] = $row['first_page'];
			if ($row['last_page']) $save['page_id'][] = $row['last_page'];
			if ($row['agent_id']) $save['agent_id'][] = $row['agent_id'];
			$path = explode(" ",$row['path']);
			foreach ($path as $value) {
				if ($value) $save['page_id'][] = $value;
			}
		}
//      делаем id уникальными
		foreach ($save as $k => $v) {
			$save[$k] = array_flip(array_flip($v));
		}
//		удаляем, которые не нужно оставлять
		sql_query("delete from stat_sessions where sess_id not in (".implode(", ",$save['sess_id']).")");
		sql_query("delete from stat_log where sess_id not in (".implode(", ",$save['sess_id']).")");
		sql_query("delete from stat_agents where id not in (".implode(", ",$save['agent_id']).")");
		sql_query("delete from stat_pages where id not in (".implode(", ",$save['page_id']).")");
		$this->data['after'] = $this->GetStats();
		return $this->str('ok');
	}

}


$GLOBALS['stat__stat_clear'] =  & Registry::get('TClear');

?>