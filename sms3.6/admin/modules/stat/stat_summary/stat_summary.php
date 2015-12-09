<?php

require_once (module('stat'));

class TSummary extends TStat {

	var $name = 'stat/stat_summary';

	########################

	function TSummary() {
		global $str;

		TStat::TStat();
		$str[get_class_name($this)] = $str['tstat'] + array(
			'attendance'	=> array(
				'Посещаемость',
				'Attendance',
			),
			'today'	=> array(
				'За сегодня',
				'Today',
			),
			'yesterday'	=> array(
				'Вчера',
				'Yesterday',
			),
			'week'	=> array(
				'За последнюю неделю',
				'Last week',
			),
			'month'	=> array(
				'За последний месяц',
				'Last month',
			),
			'all_time'	=> array(
				'За все время',
				'All time',
			),
			'avg_day'	=> array(
				'Относительно среднего количества в день за неделю',
				'Changes corresponding to dayly average',
			),
			'avg_week'	=> array(
				'Относительно среднего количества в неделю за последний месяц',
				'Changes corresponding to weekly average',
			),
			'avg_month'	=> array(
				'Относительно среднего количества в месяц за все время',
				'Changes corresponding to monthly average',
			),
			'visitors'	=> array(
				'Посетители',
				'Visitors',
			),
			'reg_clients'	=> array(
				'Заходы зарегистрированных клиентов<br>(уникальные клиенты)',
				'Register clients',
			),
			'pages'	=> array(
				'Просмотренные страницы',
				'Pages viewed',
			),
			'new_visitors'	=> array(
				'Новые посетители',
				'New visitors',
			),
			'ips'	=> array(
				'IP-адреса',
				'IP-addresses',
			),
			'errors'	=> array(
				'Ошибочные страницы',
				'Error pages',
			),
			'errors'	=> array(
				'Ошибочные страницы',
				'Error pages',
			),
			'robots'	=> array(
				'Роботы (страницы с роботов)',
				'Searchrobots (pages from robots)',
			),
			'info'	=> array(
				'Данные статистики',
				'Statistic info',
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
			'total'	=> array(
				'Всего',
				'Total'
			),
			### tip ###
			'tip'	=> array(
				'Компактный отчет, показывающий основные цифры посещаемости вашего сайта. Привлекательный отчет для беглой оценки текущего положения вещей.',
				'',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($data);

		// Вспомогательные данные
		$this->today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$diff = time() - sql_getValue("SELECT MIN(time) FROM ".STAT_SESSIONS_TABLE);
		$this->all_monthes = 12 *(date('Y', $diff) - 1970) + date('n', $diff);

		// Количество посетителей (%s - нужно чтобы туда вставить выборку по времени)
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_attendance&period=0&disp_by=hour">'.$this->str('visitors').'</a>',
			"SELECT COUNT(*) FROM ".$this->sess_table." WHERE %s robot='0'"
		);

		// Количество зарегистированных клиентов на сайте
/*		if(STAT_CLIENT_REPORT)
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_clients&period=0">'.$this->str('reg_clients').'</a>',
			"SELECT COUNT(*) FROM ".$this->sess_table." WHERE %s robot='0' AND client_id!=0"
		);*/
        if(STAT_CLIENT_REPORT)
        $stat[] = $this->GetRobotsRow(
            '<a href="?page=stat/stat_clients&period=0">'.$this->str('reg_clients').'</a>',
            "SELECT CONCAT(COUNT(*), ' (', COUNT(DISTINCT(client_id)), ')') FROM ".$this->sess_table." WHERE %s robot='0' AND client_id!=0"
        );

		// Количество просмотренных страниц
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_popular&period=0">'.$this->str('pages').'</a>',
			"SELECT SUM(loads) FROM ".$this->sess_table." WHERE %s robot='0'"
		);

		// Количество новых посетителей
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_attendance&period=0&disp_by=hour">'.$this->str('new_visitors').'</a>',
			"SELECT COUNT(*) FROM ".$this->sess_table." WHERE %s robot='0' AND new_visitor='1'"
		);

		// Количество ip-адресов
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_ip&period=0">'.$this->str('ips').'</a>',
			"SELECT COUNT(DISTINCT(ip)) FROM ".$this->sess_table." WHERE %s robot='0'"
		);

		// Количество ошибочных страниц
		$stat[] = $this->GetSummaryRow(
			'<a href="?page=stat/stat_errors&period=0">'.$this->str('errors').'</a>',
			"SELECT COUNT(*) FROM ".$this->log_table." WHERE %s status IN (404, 403)"
		);

		// Количество проиндексированных страниц с них
		$stat[] = $this->GetRobotsRow(
			'<a href="?page=stat/stat_robots&period=0">'.$this->str('robots').'</a>',
			"SELECT CONCAT(COUNT(DISTINCT(agent_id)), ' (', COUNT(*), ')') FROM ".$this->sess_table." WHERE %s robot='1'"
		);


		// Main Table
		$data['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'attendance',
					'nowrap'	=> 1,
					'valign'	=> 'top',
				),
				array(
					'header' 	=> 'today',
					'align'		=> 'right',
					'width'		=> '15%',
				),
				array(
					'header' 	=> 'yesterday',
					'align'		=> 'right',
					'width'		=> '15%',
				),
				array(
					'header' 	=> 'week',
					'align'		=> 'right',
					'width'		=> '15%',
				),
				array(
					'header' 	=> 'month',
					'align'		=> 'right',
					'width'		=> '15%',
				),
				/*array(
					'header'	=> 'total',
					'valign'	=> 'top',
					'align'		=> 'right',
					'width'		=> '15%',
				),*/
			),
			'data' => $stat,
		));

		// Stat Info Table
		$sess = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_SESSIONS_TABLE."'", 'number');
		$log = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_LOG_TABLE."'", 'number');
		$pages = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_PAGES_TABLE."'", 'number');
		$agents = sql_getRow("SHOW TABLE STATUS LIKE '".STAT_AGENTS_TABLE."'", 'number');
		$db['Data_length'] = $sess['Data_length'] + $log['Data_length'] + $pages['Data_length'] + $agents['Data_length'];
		$db['Index_length'] = $sess['Index_length'] + $log['Index_length'] + $pages['Index_length'] + $agents['Index_length'];
		$db['Rows'] = $sess['Rows'] + $log['Rows'] + $pages['Rows'] + $agents['Rows'];
		$data['info'] = array(
			'STATINFO' => $this->str('info'),
			'rows' => array(
				0 => array(
					'key' => $this->str('info_begin'),
					'val' => $sess['Create_time'],
				),
				1 => array(
					'key' => $this->str('info_last'),
					'val' => $log['Update_time'],
				),
				2 => array(
					'key' => $this->str('info_size'),
					'val' => number_format( ($db['Data_length']+$db['Index_length']) / 1024, 2, ',', ' ')." KB",
				),
				3 => array(
					'key' => $this->str('info_rows'),
					'val' => $db['Rows'],
				),
			),
		);

		$data['site_select'] = $this->selectSite(array(
			'hidden'	=> array('show' => $this->show),
		));
		return Parse($data, 'stat/stat.tmpl');
	}

	######################

	function GetSummaryRow($title, $query) {
        // Подсчет значений за день, неделю, месяц и всего
		$stat_today = (int)sql_getValue(sprintf($query, 'time>='.$this->today.' AND'));

		$yesterday = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 day');
		$stat_yesterday = (int)sql_getValue(sprintf($query, 'time>='.$yesterday.' AND time<='.$this->today.' AND'));

		$week = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 week');
		$stat_week = (int)sql_getValue(sprintf($query, 'time>='.$week.' AND'));

		$month = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 month');
		$stat_month = (int)sql_getValue(sprintf($query, 'time>='.$month.' AND'));

		//$stat_all = (int)sql_getValue(sprintf($query, ''));

		// Подсчет среднего
		//$avg_today = $this->GetAvg($stat_today, $stat_week/7, 'avg_day');
		$avg_yesterday = $this->GetAvg($stat_yesterday, $stat_week/7, 'avg_day');
		$avg_week = $this->GetAvg($stat_week, $stat_month/4.3, 'avg_week');
		//$avg_month = $this->GetAvg($stat_week, $stat_all/$this->all_monthes, 'avg_month');

		$row = array(
			$title,
			$stat_today/*.$avg_today*/,
			$stat_yesterday.$avg_yesterday,
			$stat_week.$avg_week,
			$stat_month/*.$avg_month*/,
			//$stat_all,
		);
		return $row;
	}

	######################

	function GetAvg($val, $avg, $title) {
		if ($val == $avg) {
			return '';
		}

		return '
			<div class="tSmall" title="'.$this->str($title).'" style="white-space: nowrap;">
			(<img src="images/stat/'.($val>$avg?'up' : 'down').'.gif" width="12" height="12" alt="'.$this->str($title).'" border="0" />
			'.($val>$avg?'+':'').round($val-$avg, 2).')
			</div>';
	}

	######################

	function GetRobotsRow($title, $query) {
		// Подсчет значений за день, неделю, месяц и всего
		$yesterday = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 day');
		$week = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 week');
		$month = strtotime(strftime('%Y-%m-%d %H:%M:%S', $this->today) . ' -1 month');
		return array(
			$title,
			sql_getValue(sprintf($query, 'time>='.$this->today.' AND')),
			sql_getValue(sprintf($query, 'time>='.$yesterday.' AND time<='.$this->today.' AND')),
			sql_getValue(sprintf($query, 'time>='.$week.' AND')),
			sql_getValue(sprintf($query, 'time>='.$month.' AND')),
			//sql_getValue(sprintf($query, '')),
		);
	}

	######################
}

$GLOBALS['stat__stat_summary'] = & Registry::get('TSummary');

?>