<?php

#### Configs ####                                                               F
// stat_tables
$and = '.';
if (!defined('STAT_DATABASE')){
	define('STAT_DATABASE', '');
	$and = '';
}
//define('STAT_SESSIONS_TABLE', STAT_DATABASE.$and.'stat_sessions'); //!!! кто-то забыл определить таблицу? скопировал строку и забыл?
define('STAT_SESSIONS_TABLE', STAT_DATABASE.$and.'stat_sessions');
define('STAT_LOG_TABLE', STAT_DATABASE.$and.'stat_log');
define('STAT_PAGES_TABLE', STAT_DATABASE.$and.'stat_pages');
define('STAT_AGENTS_TABLE', STAT_DATABASE.$and.'stat_agents');
define('STAT_SETTINGS_TABLE', STAT_DATABASE.$and.'stat_settings');
define('STAT_EVENTS_TABLE', STAT_DATABASE.$and.'stat_events');

// stat general tables
define('STAT_IPS_TABLE', 'stat.stat_ips');
define('STAT_CITIES_IP_TABLE', 'stat.stat_cities_ru_ip');
define('STAT_CITIES_TABLE', 'stat.stat_cities_ru');
define('STAT_REGIONS_TABLE', 'stat.stat_regions_ru');
define('STAT_SEARCHAGENTS_TABLE', 'stat.stat_searchagents');
define('STAT_COUNTRIES_TABLE', 'stat.stat_countries');
if(!defined('STAT_PARAMS_TABLE')) {
	define('STAT_PARAMS_TABLE', 'params');
}
if(!defined('STAT_CLIENT_REPORT')) {
	define('STAT_CLIENT_REPORT', false);
}
if(!defined('STAT_EVENT_REPORT')) {
    define('STAT_EVENT_REPORT', false);
}
if (!defined('STAT_REKLAMA_REPORT')) {
	define('STAT_REKLAMA_REPORT', false);
}
// stat templorary DB name
define('STAT_TMP_DB', 'stat-tmp');
//время через которое считается сессия (мин.)
define('STAT_SESS_TIME', 30);
//время через которое убивать темповые таблицы (мин.)
define('STAT_TMP_LIFETIME', 48*60);

class TStat extends TTable {
	// whois серверы для определения ip
	var $whois = array(
		'whois.ripe.net',
		'whois.arin.net',
		'whois.apnic.net',
		'whois.lapnic.net'
	);
	# размер графика по умолчанию (px)
	var $graph_width	= 600;
	var $graph_height	= 500;

	# избранные отчёты
	var $favorites = array('stat_popular','stat_search_ph','stat_ip');
	var $domain_selector = false;

	########################
	// конструктор
	function TStat() {
		global $str, $actions;


		TTable::TTable();

		$this->selector = false;

		// активируем менюшку слева для окна stat
		$GLOBALS['_elems'] = true;

		// Общие слова для всех файлов статистики
		$actions['tstat'] = array(
			'view_table' => array(
				'Таблица',
				'Table',
				'link'	=> 'cnt.ViewIn(\'table\')',
				'img' 	=> 'icon.table.gif',
				'display'	=> 'block',
			),
			'view_grapf' => array(
				'График',
				'Graph',
				'link'	=> 'cnt.ViewIn(\'graph\')',
				'img' 	=> 'icon.graph.gif',
				'display'	=> 'block',
			),
			'view_bargrapf' => array(
				'Гистограмма',
				'Bar graph',
				'link'	=> 'cnt.ViewIn(\'bargraph\')',
				'img' 	=> 'icon.bargraph.gif',
				'display'	=> 'block',
			),
			'view_diag' => array(
				'Диаграмма',
				'Diagram',
				'link'	=> 'cnt.ViewIn(\'diag\')',
				'img' 	=> 'icon.diag.gif',
				'display'	=> 'block',
			),
			'view_csv' => array(
				'Экспорт в CSV',
				'Export in CSV',
				'link'	=> 'cnt.ViewIn(\'csv\')',
				'img' 	=> 'xls.gif',
				'display'	=> 'block',
			),
		);

		$str['tstat'] = array(
			######## Titles ###########
			'stat'	=> array(
				'Статистика',
				'Statistics',
			),
			't_from_server'	=> array(
				'пользователи с сервера "%s"',
				'visitors from server "%s"',
			),
			't_from_search_ph'	=> array(
				'пользователи по поисковой фразе "%s"',
				'visitors from search phrase "%s"',
			),
			't_points'	=> array(
				'пользователи с входом со страницы "%s"',
				'visitors from enter page "%s"',
			),
			't_outs'	=> array(
				'пользователи с последней страницей "%s"',
				'visitors with last page "%s"',
			),
			't_ip'	=> array(
				'посетители с ip "%s"',
				'visitors with ip "%s"',
			),
			't_robot'	=> array(
				'поисковая машина: "%s"',
				'search machine: "%s"',
			),
			't_client'	=> array(
				'пользователь "#%u"',
				'client "#%u"',
			),
			't_country'	=> array(
				'пользователи из страны "%s"',
				'visitors from country "%s"',
			),
            't_path'    => array(
                'путь "%s"',
                'path "%s"',
            ),
            't_ref_page'    => array(
                'пользователи, пришедшие со страницы "%s"',
                'visitors from reference page "%s"',
            ),
            't_see_page'    => array(
                'пользователи, просмотревшие страниц "%s"',
                'visitors who viewed pages "%s"',
            ),
            't_reklama'    => array(
                'рекламная кампания "%s"',
                'advertising campaign "%s"',
            ),

			######## Table ###########
			'no_data'	=> array(
				'Нет данных',
				'No data',
			),
			'total_period' 	=> array(
				'Всего за данный период',
				'Summary for this period',
			),
			'csv_file'	=> array(
				'Скачать CSV файл',
				'Download CSV file',
			),
			'percent'	=> array(
				'Процент от всех',
				'Percent from all',
			),
			'amount'	=> array(
				'Кол-во',
				'Amount',
			),
			'more'	=> array(
				'подробнее...',
				'more...',
			),
			'compare' => array(
				'сравнить...',
				'compare...',
			),
			'reports' => array(
				'Отчеты',
				'Reports',
			),
			'settings' => array(
				'Настройки',
				'Settings',
			),
			'others' => array(
				'Остальные',
				'Others',
			),
            'count_rows' => array(
                'Всего',
                'Count rows',
            ),

			######## Navig Form ###########
			'show_from' 	=> array(
				'C',
				'From',
			),
			'show_to' 	=> array(
				'по',
				'to',
			),
			'nav_period' 	=> array(
				'Период',
				'Period',
			),
			'nav_more' 	=> array(
				'Дополнительно',
				'More',
			),
			'nav_select_period' 	=> array(
				'Выбор периода',
				'Select period',
			),
			'nav_today' 	=> array(
				'за сегодня',
				'today',
			),
			'nav_today_title' 	=> array(
				'Смотреть статистику за сегодня по часам',
				'View statistic for today by hours',
			),
			'nav_yesterday' 	=> array(
				'вчера',
				'yesterday',
			),
			'nav_yesterday_title' 	=> array(
				'Смотреть статистику за вчера по часам',
				'View statistic for yesterday by hours',
			),
			'nav_week' 	=> array(
				'за неделю',
				'last week',
			),
			'nav_week_title' 	=> array(
				'Смотреть статистику за прошедшую неделю по дням',
				'View statistic for the last week by days',
			),
			'nav_month' 	=> array(
				'за месяц',
				'last month',
			),
			'nav_month_title' 	=> array(
				'Смотреть статистику за прошедший месяц по дням',
				'View statistic for the last month by days',
			),
			'nav_all' 	=> array(
				'за все время',
				'all time',
			),
			'nav_all_title' 	=> array(
				'Смотреть статистику за все время по месяцам',
				'View statistic for all time by months',
			),
			'nav_analyze_page' => array(
				'Просмотры выбранной страницы',
				'Analyzing page',
			),
			'select_date' 	=> array(
				'Выбрать дату',
				'Select date',
			),
			'nav_use_filter' 	=> array(
				'использовать фильтр',
				'use filter',
			),
			'nav_edit_filter' 	=> array(
				'настроить',
				'edit',
			),
			'nav_show' 	=> array(
				'показать',
				'show',
			),

			######## Day & Month Names ###########
			'dayweek_0'	=> array(
				'Понедельник',
				'Monday',
			),
			'dayweek_1'	=> array(
				'Вторник',
				'Tuesday',
			),
			'dayweek_2'	=> array(
				'Среда',
				'Wednesday',
			),
			'dayweek_3'	=> array(
				'Четверг',
				'Thursday',
			),
			'dayweek_4'	=> array(
				'Пятница',
				'Friday',
			),
			'dayweek_5'	=> array(
				'Суббота',
				'Saturday',
			),
			'dayweek_6'	=> array(
				'Воскресенье',
				'Sunday',
			),
			'sm_dayweek_0'	=> array(
				'Пон',
				'Mon',
			),
			'sm_dayweek_1'	=> array(
				'Вт',
				'Tue',
			),
			'sm_dayweek_2'	=> array(
				'Ср',
				'Wed',
			),
			'sm_dayweek_3'	=> array(
				'Чет',
				'Thu',
			),
			'sm_dayweek_4'	=> array(
				'Пят',
				'Fri',
			),
			'sm_dayweek_5'	=> array(
				'Суб',
				'Sat',
			),
			'sm_dayweek_6'	=> array(
				'Вос',
				'Sun',
			),
			'month_1'	=> array(
				'Январь',
				'January',
			),
			'month_2'	=> array(
				'Февраль',
				'February',
			),
			'month_3'	=> array(
				'Март',
				'March',
			),
			'month_4'	=> array(
				'Апрель',
				'April',
			),
			'month_5'	=> array(
				'Май',
				'May',
			),
			'month_6'	=> array(
				'Июнь',
				'June',
			),
			'month_7'	=> array(
				'Июль',
				'July',
			),
			'month_8'	=> array(
				'Август',
				'August',
			),
			'month_9'	=> array(
				'Сентябрь',
				'September',
			),
			'month_10'	=> array(
				'Октябрь',
				'October',
			),
			'month_11'	=> array(
				'Ноябрь',
				'November',
			),
			'month_12'	=> array(
				'Декабрь',
				'December',
			),
			'sm_month_1'	=> array(
				'Янв',
				'Jan',
			),
			'sm_month_2'	=> array(
				'Фев',
				'Feb',
			),
			'sm_month_3'	=> array(
				'Мар',
				'Mar',
			),
			'sm_month_4'	=> array(
				'Апр',
				'Apr',
			),
			'sm_month_5'	=> array(
				'Май',
				'May',
			),
			'sm_month_6'	=> array(
				'Июн',
				'Jun',
			),
			'sm_month_7'	=> array(
				'Июл',
				'Jul',
			),
			'sm_month_8'	=> array(
				'Авг',
				'Aug',
			),
			'sm_month_9'	=> array(
				'Сен',
				'Sep',
			),
			'sm_month_10'	=> array(
				'Окт',
				'Oct',
			),
			'sm_month_11'	=> array(
				'Ноя',
				'Nov',
			),
			'sm_month_12'	=> array(
				'Дек',
				'Dec',
			),

            ######## Search form ###########
            'ok'    => array(
                '  OK  ',
                '  OK  ',
            ),
            'reset'    => array(
                'Сброс',
                'Reset',
            ),
            'find'    => array(
                'Найти',
                'Find',
            ),
            'search'    => array(
                'Поиск',
                'Search',
            ),
            'help'    => array(
                'Поиск ведется по шаблону для выражения LIKE',
                'Search by the template for LIKE statement',
            ),
            'count_find'    => array(
                'Всего найдено',
                'Find sum total',
            ),
            'not_find'    => array(
                'По Вашему запросу ничего не найдено',
                'Nothing was found',
            ),

            ######## Site selection ###########
			'all'				=> array('-- все --',			'-- all --',),
			'alias_for'			=> array('алиас для',			'alias for',),
			'site_selection'	=> array('Выбор сайта',			'Site selection',),
			'site'				=> array('Сайт',				'Site',),
		);

		// Определяем дополнительные отчеты:
        $reklama_str = "";
        if (isset($_GET['adv']['reklama'])) {
            $identifiers = explode(', ', $_GET['adv']['reklama']);
            $reklama_str = " (";
            foreach ($identifiers as $k2=>$v2) {
                $reklama_str .= "page.uri like '***from=".$v2."'";
                if ($k2<count($identifiers)-1) $reklama_str .= " OR ";
            }
            $reklama_str .= ")";
        }
        $advanced = array(
			'server' => array(
				'title' => 't_from_server',
				'exclude' => array('stat/stat_ref_server', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => "",
				'where' => "sess.host='%s'",
			),
			'search_ph' => array(
				'title' => 't_from_search_ph',
				'exclude' => array('stat/stat_search_ph', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => "LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id",
				'where' => "pag.search_ph='%s'",
			),
			'points' => array(
				'title' => 't_points',
				't_name' => "SELECT CONCAT('http://', host, uri) FROM ".STAT_PAGES_TABLE." WHERE id=%u",
				'exclude' => array('stat/stat_points', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "sess.first_page='%u'",
			),
			'outs' => array(
				'title' => 't_outs',
				't_name' => "SELECT CONCAT('http://', host, uri) FROM ".STAT_PAGES_TABLE." WHERE id=%u",
				'exclude' => array('stat/stat_outs', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "sess.last_page='%u'",
			),
			'ip' => array(
				'title' => 't_ip',
				'exclude' => array('stat/stat_ip', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "IF(ip<0, INET_NTOA(ip+4294967296), INET_NTOA(ip))='%s'",
			),
			'robot' => array(
				'title' => 't_robot',
				'exclude' => array(),
				'join' => '',
				'where' => '',
			),
			'client' => array(
				'title' => 't_client',
				'exclude' => array('stat/stat_clients', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "sess.client_id='%u'",
			),
			'country' => array(
				'title' => 't_country',
				't_name' => "SELECT name_".(Lang()=='ru' ? 'ru' : 'en')." FROM ".STAT_COUNTRIES_TABLE." WHERE country_id='%s'",
				'exclude' => array('stat/stat_geography', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "sess.country='%s'",
			),
			'robots' => array(
				'title' => 't_robots',
				't_name' => "SELECT name_".(Lang()=='ru' ? 'ru' : 'en')." FROM ".STAT_COUNTRIES_TABLE." WHERE country_id='%s'",
				'exclude' => array('stat/stat_geography', 'stat/stat_robots', 'stat/stat_settings', 'stat/stat_now'),
				'join' => '',
				'where' => "sess.country='%s'",
			),
            'path' => array(
                'title' => 't_path',
                'exclude' => array('stat/stat_popular', 'stat/stat_pathes', 'stat/stat_points', 'stat/stat_outs', 'stat/stat_robots', 'stat/stat_now'),
                'join' => "",
                'where' => "sess.path = '%s'",
            ),
            'ref_page' => array(
                'title' => 't_ref_page',
                'exclude' => array('stat/stat_ref_server', 'stat/stat_ref_pages', 'stat/stat_popular', 'stat/stat_robots', 'stat/stat_now'),
                'join' => "LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id",
                'where' => "sess.ref_id <> 0 AND CONCAT(pag.host,pag.uri) = '%s'",
            ),
            'see_page' => array(
                'title' => 't_see_page',
                'exclude' => array(),
                'join' => "",
                'where' => "LENGTH(sess.path)-LENGTH(REPLACE(sess.path,' ',''))+1 %s",
            ),
            'reklama' => array(
                'title' => 't_reklama',
                'exclude' => array('stat/stat_reklama', 'stat/stat_robots', 'stat/stat_error', 'stat/stat_popular'),
                'join' => "LEFT JOIN ".STAT_PAGES_TABLE." AS page ON page.id=sess.first_page",
                'where' => $reklama_str,
            ),
		);
		// Если есть дополнительный отчет
		$this->MakeAdvanced($advanced);
	}

	######################
	// Выводит заголовок страницы
	function GetTitle() {
		global $str, $do;
		if (isset($GLOBALS['_stat'][$this->name])) {
			$title = $this->str('stat').' - '.utf($GLOBALS['_stat'][$this->name][int_langId()]);
		}
		elseif (isset($str[$this->name][$do])) {
			$title = $this->str($do);
		}
		else {
			$title = $this->str('stat').' - '.$this->str('title');
		}
		// Для дополнительных отчетов
		if (isset($this->advanced)) {
			$title .= ' ('.$this->str($this->advanced['title']).')';
		}
		return $title;
	}

	########################
	// Создаем переменную $this->advanced если есть $_GET['adv']
	function MakeAdvanced($advanced) {
		if (!isset($_GET['adv'])) {
			return;
		}

		global $str, $intlang;
		reset($_GET['adv']);
		$this->advanced = $advanced[key($_GET['adv'])];
		$this->advanced['key'] = key($_GET['adv']);
		$this->advanced['value'] = current($_GET['adv']);
		if (isset($this->advanced['t_name'])) {
			$title_value = sql_getValue(sprintf($this->advanced['t_name'], $this->advanced['value']));
		}
		else {
			$title_value = $this->advanced['value'];
		}
        $this->advanced['where'] = str_replace("***","%",$this->advanced['where']);
		$this->advanced['title'] = sprintf($str['tstat'][$this->advanced['title']][$intlang], $title_value);
		$this->advanced['where'] = sprintf($this->advanced['where'], $this->advanced['value'], $this->advanced['value'], $this->advanced['value']);
//        pr($this->advanced);
	}

	########################
	// Делаем темповые таблицы, делаем $this->sess_table, log_table
	function MakeTemp() {
		$prefix	= '';
		$join	= array();
		$where	= array('1');
		$time_where	= array('1');

		// Имена по умолчанию
		$this->sess_table = STAT_SESSIONS_TABLE;
		$this->log_table = STAT_LOG_TABLE;

		// Если интервал больше месяца, то не создаем временные таблицы
		//if ($this->to_date - $this->from_date >= 60*60*24*32) return;

		// Не делаем временные таблицы, если у нас расширенная статистика с джойнами
		if (isset($this->advanced['join']) && !empty($this->advanced['join'])) return;

		// Формируем имя темповой таблицы
		if ($this->from_date && $this->to_date && $this->name!='stat/stat_summary' && $this->name!='stat/stat_now') {
			$prefix	.= '_'.$this->from_date.'_'.$this->to_date;
			$where[] = $time_where[] = 'time>='.$this->from_date.' AND time<='.$this->to_date;
		}
		if (isset($this->advanced)) {
		    // Расширенную статистику просматривают довольно редко, поэтому пока оставляю тут join (там только stat_pages джойнится)
			$prefix	.= '_'.$this->advanced['key'].'_'.$this->advanced['value'];
			$join[]	 = $this->advanced['join'];
			$where[] = $this->advanced['where'];
		}
		if (isset($this->filter)) { // Фильтр IP адресу
			$prefix	.= '_'.$this->filter['name'];
			$where[] = $this->filter['where'];
		}
		// Условие для выборки статистики по определенному сайту
		if ($this->site) {
			if ($this->site != '-1') {
				//$join[] = 'LEFT JOIN '.STAT_LOG_TABLE.' as log ON sess.sess_id=log.sess_id';
				//$join[] = 'LEFT JOIN '.STAT_PAGES_TABLE.' as pages ON log.page_id=pages.id';
				//$where[] = 'pages.host LIKE "'.$this->site.'"';
				$where[] = 'sess.host LIKE "'.$this->site.'"';
			}
			$prefix	.= '_'.$this->site;
		}
		if (!$prefix) return;
		// Именнуем таблицы
		$prefix = $_SERVER['HTTP_HOST'].$prefix;

		if (NEW_STAT) {
    		$this->sess_table = "`".STAT_TMP_DB.'`.`'.MYSQL_DB."_".md5(STAT_SESSIONS_TABLE.$prefix).'`';
    		$this->log_table = "`".STAT_TMP_DB.'`.`'.MYSQL_DB."_".md5(STAT_LOG_TABLE.$prefix).'`';
		}
		else {
    		$this->sess_table = "`".STAT_TMP_DB.'`.`'.md5(STAT_SESSIONS_TABLE.$prefix).'`';
    		$this->log_table = "`".STAT_TMP_DB.'`.`'.md5(STAT_LOG_TABLE.$prefix).'`';
		}
		// Смотрим какие есть таблицы
		$list_tables = sql_getRows("SHOW TABLE STATUS FROM `".STAT_TMP_DB."`");
		$drop	= array();
		$tables	= array();
		foreach ($list_tables as $t) {
			if (strtotime($t['Create_time']) < (time()-60*STAT_TMP_LIFETIME)) {
				$drop[] = "`".STAT_TMP_DB.'`.`'.$t['Name']."`";
			}
			else {
				$tables[] = $t['Name'];
			}
		}
		// Удаляем если есть старые таблицы
		if (!empty($drop)) {
			sql_query("DROP TABLE ".join(', ', $drop));
		}
		// Если наша таблица уже есть, выходим отсюда куда подальше
		if (
		    (!NEW_STAT && in_array(md5(STAT_SESSIONS_TABLE.$prefix), $tables)) ||
		    (NEW_STAT && in_array(MYSQL_DB."_".md5(STAT_SESSIONS_TABLE.$prefix), $tables))
		) {
			return;
		}

		foreach($where as $k=>$w){
			if (empty($w)) {
				unset($where[$k]);
			}
		}

		// Новый способ: длбавляем в stat_sessions колонку host, чтобы не делать join
		$columns = sql_getRows('SHOW COLUMNS FROM '.STAT_SESSIONS_TABLE, true);
		if (!isset($columns['host'])) {
		    sql_query("ALTER TABLE ".STAT_SESSIONS_TABLE." ADD `host` VARCHAR( 255 ) NOT NULL ;");
		    sql_query("UPDATE ".STAT_SESSIONS_TABLE." SET host=(SELECT p.host FROM ".STAT_PAGES_TABLE." AS p, ".STAT_LOG_TABLE." AS log WHERE log.page_id=p.id AND log.sess_id=sess_id LIMIT 1)");
		}

		// Создаем нужные "темповые" таблицы в базе данных STAT_TMP_DB
		$sql = "CREATE TABLE ".$this->sess_table." (PRIMARY KEY (`sess_id`), KEY (`robot`))
			SELECT sess.* FROM ".STAT_SESSIONS_TABLE." AS sess ".join(' ', $join)." WHERE ".join(' AND ', $where);
		sql_query($sql);

		$sql = "CREATE TABLE ".$this->log_table." (KEY (`sess_id`), KEY (`status`), KEY (`page_id`))
			SELECT * FROM ".STAT_LOG_TABLE." WHERE ".join(' AND ', $time_where);
		sql_query($sql);

		// Старый вариант
		/*sql_query("CREATE TABLE ".$this->sess_table." (PRIMARY KEY (`sess_id`), KEY (`robot`))
			SELECT sess.* FROM ".STAT_SESSIONS_TABLE." AS sess ".join(' ', $join)." WHERE ".join(' AND ', $where)." GROUP BY sess.sess_id");
		sql_query("CREATE TABLE ".$this->log_table." (KEY (`sess_id`), KEY (`status`), KEY (`page_id`))
			SELECT log.* FROM ".STAT_LOG_TABLE." AS log INNER JOIN ".$this->sess_table." AS sess USING (sess_id)");
			*/
	}

	########################
	// Строит меню для окна stat
	function GetBasicElement() {
		if (!isset($this->advanced)) {
			return array();
		}
		$menu = '';
		foreach ($GLOBALS['_stat'] as $key => $val) {
			// Убираем ненужные отчеты
			if (in_array($key, $this->advanced['exclude'])) {
				continue;
			}
			$menu .= '<a href="stat.php?page='.$key.'&adv['.$this->advanced['key'].']='.rawurlencode($this->advanced['value']).'" class="'.($GLOBALS['page']==$key ? 'open' : '').'"><img src="images/icons/icon.box.gif" width="16" height="16" align="absmiddle" hspace="4" vspace="2" border="0" alt="" /><b>'.utf($val[int_langId()]).'</b></a><br>';
		}
		return array (
			'basic_caption'	=> $this->str('reports'),
			'basic_icon'	=> 'box.stat.gif',
			'src'			=> $GLOBALS['_SERVER']['QUERY_STRING'],
			'tree'			=> $menu,
			'rows'			=> array (
			'value' 		=> $this->str($this->advanced['title']),
			),
		);
	}

	########################
	// GetValue с возможностью ограничивать по полю time (таблица stat_sessions)
	function GetValue($query, $from = 0, $to = 0) {
		if ($from) {
			$query .= ' AND time>='.$from;
		}
		if ($to) {
			$query .= ' AND time<='.$to;
		}
		return sql_getValue($query);
	}

	########################
	// Функция, которая запускается перед каждой подготовкой отчета
	// (кроме отчета summary - там своя обработка)
	function Init() {
		// Устанавливаем все необходимые переменные
		$this->SetValues();
		// Устанавливаем фильтр
		$this->SetFilter();
		// Выбор сайта
		$this->selectSite();
		// Делаем темповые таблицы
		$this->MakeTemp();
	}

	########################
	// Выбор сайта, для которого показывать статистику, с учетом прав пользователя
	function selectSite(){
		global $site_domains;
		$user = get('user', array(), 's');
		$deny = explode(",", $user['deny_ids']);
		$department_id = (int)sql_getValue('SELECT department_id FROM admins WHERE id='.$user['id']);

		foreach ($site_domains as $key=>$val) {
			if ($deny && in_array($val['root_id'], $deny)) continue;
			if ($department_id && $val['root_id'] != $department_id) continue;

			$ret['sites'][$key] = $key.' ('.(LANG_SELECT ? $val['descr_'.lang()] : $val['descr']).')';
			if (!empty($val['alias'])) {
				$alias = explode(',', $val['alias']);
				foreach ($alias as $k=>$v) {
					$ret['sites'][$v] = $v.' ('.$this->str('alias_for').' '.$key.')';
				}
			}
		}
		if ($department_id && $this->site == -1 && count($ret['sites']) == 1) {
		    $this->site = key($ret['sites']);
		}
		$ret['site'] = $this->site;

		$params['hidden']['page']	= $this->name;
		$params['hidden']['do']		= $GLOBALS['do'];
		if(isset($this->page_id)) {
			$params['hidden']['page_id'] = $this->page_id;
		}
		// Дополнительные параметры
		if (isset($this->advanced)) {
			$params['hidden']['adv['.$this->advanced['key'].']'] = $this->advanced['value'];
		}
		if ($this->name != 'stat/stat_summary') {
			$params['hidden']['from_date']		= date('Y-m-d', $this->from_date);
			$params['hidden']['to_date']		= date('Y-m-d', $this->to_date);
		}
		foreach ($params['hidden'] as $key=>$val)
			$ret['hidden'][] = array('name' => $key, 'value' => $val);

		$this->AddStrings($ret);
		return $ret;
	}

	########################
	// Дописывает к стандартной AddStrings массив чтобы выводить подсказку
	function AddStrings(&$data) {
		TTable::AddStrings($data);
		if (!empty($GLOBALS['str'][get_class_name($this)]['tip'][int_langId()])) {
			$data['tip']['text'] = $this->str('tip');
		}
	}

	function Param($name, $default = '', $value) {
		global $user, $do;
		if (is_null($value)) {
			$value = sql_getValue("SELECT value FROM ".STAT_PARAMS_TABLE." WHERE user_id=".$user['id']." AND module='".$this->name."' AND method='".$do."' AND name='".$name."'");
			return $value ? $value : $default;
		}
		return sql_query("replace ".STAT_PARAMS_TABLE." (user_id, module, method, name, value) values (".$user['id'].", '".$this->name."', '".$do."', '".$name."', '".addslashes($value)."')");
	}

	######################
	// Устанавливает основные переменные для статистики:
	// $this->group 	- группировать данные (для посещаемости)
	// $this->disp_by	- промежутки отображения (для посещаемости)
	// $this->show		- отображение ввиде таблицы, графика и др.
	// $this->from_date	- дата С
	// $this->to_date	- дата ПО
	// $this->site		- для какого сайта показывать статистику

	function SetValues() {
		// show надо взять текущего модуля
		$this->show	= get('show', $this->Param('show', ''));

		// Изменяем $this->name для записи параметров в params (для всех модулей под именем stat)
		$_name = $this->name;
		$this->name = 'stat';

		// Выбор сайта
		$this->site = get('site', $this->Param('site', -1), 'gp');
		if ($this->site == 'true') $this->site = '';
		if (!empty($this->site)) {
			$this->Param('site', '', $this->site);
		}

		 // Определение переменных
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
				$this->from_date = sql_getValue("SELECT FLOOR(time/86400)*86400 FROM ".STAT_SESSIONS_TABLE." ORDER BY time ASC LIMIT 1");
			}
			else {
				$this->from_date = mktime(0, 0, 0, date('m'), date('d')-$period, date('Y'));
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

		// Сохранение параметров
		if ($this->from_date) {
			$this->Param('from_date', '', date('Y-m-d', $this->from_date));
		}
		if ($this->to_date) {
			$this->Param('to_date', '', date('Y-m-d', $this->to_date));
		}

		// Возвращаем реальное $this->name этого модуля
		$this->name = $_name;

		// show надо сохранять для каждого субмодуля в отдельности
		if ($this->show) {
			$this->Param('show', '', $this->show);
		}
	}

	########################
	// Устанавливает переменную $this->filter для фильтрации
	function SetFilter() {
		$filter_ips = sql_getValue("SELECT value FROM ".STAT_SETTINGS_TABLE." WHERE name='filter_ips'");
		if (empty($filter_ips)) {
			return;
		}

		$ips = explode(',', $filter_ips);
		$this->filter['name'] = $filter_ips;
		$this->filter['join'] = '';
		$this->filter['where'] = '1';
		foreach ($ips as $key => $ip) {
			$ip = trim($ip);
			if (strpos($ip, '*') === false) {
				$this->filter['where'] .= " AND sess.ip!='".ip2long($ip)."'";
			}
			else {
				$this->filter['where'] .= " AND (ip & ".ip2long(str_replace('*', '255', $ip)).")!=ip";
			}
		}
	}

	######################
	// стандартная функция для показа формы скачивания CSV файла
	function GetCSV() {
		// Если была нажата кнопочка скачать файл - выдаем файл
		if (isset($_GET['show_button'])) {
			$this->GetCSVFile();
		}
		// если нет, рисуем форму с кнопочкой "скачать cvs файл"
		else {
			$GLOBALS['str'][get_class_name($this)]['nav_show'] = $GLOBALS['str'][get_class_name($this)]['csv_file'];
			$GLOBALS['str'][get_class_name($this)]['nav_show']['download_cvs'] = 'xxx';
		}
	}

	######################
	// стандартная фунция вывода CVS файла (данные берет из функции $this->GetCSVData())
	function GetCSVFile() {
		// Set English
		$GLOBALS['intlang'] = 1;
		$GLOBALS['gzip'] = false;
		// имя файла для сохранения
		$filename = str_replace('/', '__', $this->name).'_'.date('Y-m-d').'.csv';
		//ob_clean();
		header("Content-type: application/x-download");
		header("Content-Disposition: attachment; filename=".$filename.";");

		echo '
Stat Log Rusoft (c)
Report "'.$this->GetTitle().'"
Create Date '.date('Y-m-d').'

';
		$this->GetCSVData();
		echo '

Support: support@rusoft.ru
Rusoft Company http://www.rusoft.ru/
';

		exit;
	}
	// заглушка для GetCVSData
	function GetCSVData() {
		return '';
	}

	######################
	// стандартная функция вывода формы с диаграмой
	function GetDiag() {
		$img_params = array(
			'page'		=> $this->name,
			'do'		=> 'ShowDiagImg',
			'from_date'	=> date('Y-m-d', $this->from_date),
			'to_date'	=> date('Y-m-d', $this->to_date),
			'site'		=> $this->site,
		);
		// Дополнительные параметры
		if (isset($this->advanced)) {
			$img_params['adv['.$this->advanced['key'].']'] = $this->advanced['value'];
		}
		$ret['image'] = array(
			'src' => 'page.php?'.$this->query_str($img_params),
			'alt' => $this->GetTitle(),
		);
		return $ret;
	}

	######################
	// вывод стандартной диаграммы (данные берет из функции $this->GetDiagData())
	function ShowDiagImg() {
		$this->Init();

		list($values, $legends) = $this->GetDiagData();

		include ('jpgraph/jpgraph.php');
		include ('jpgraph/jpgraph_pie.php');
		include ('jpgraph/jpgraph_pie3d.php');

		$graph = &new PieGraph($this->graph_width, 300 + count($legends) * 25, 'auto');
		$graph->SetShadow();

		$graph->title->Set($this->GetTitle());
		$graph->title->SetFont(FF_VERDANA,FS_BOLD);

		$p1 = &new PiePlot3D($values);
		$p1->ExplodeSlice(0);
		$p1->SetCenter(0.5, 0.30);
		$p1->SetLegends($legends);
		$graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
		$graph->legend->Pos(0.05, 0.96, 'left', 'bottom');

		$graph->Add($p1);
		$graph->Stroke();
	}

	// заглушка для GetDiagData
	function GetDiagData() {
		return array(array(),array());
	}

	########################
	// Подготавливает данные (для шаблона) для Формы навигации
	function NavigForm($params = array()) {
		$this->AddStrings($ret);
		// Служеные поля
		$params['hidden']['page']	= $this->name;
		$params['hidden']['do']		= $GLOBALS['do'];
		if(isset($this->page_id)) {
			$params['hidden']['page_id'] = $this->page_id;
		}
		$params['hidden']['period']	= '';
		$params['hidden']['view']	= '';

		// Дополнительные параметры
		if (isset($this->advanced)) {
			$params['hidden']['adv['.$this->advanced['key'].']'] = $this->advanced['value'];
		}

		// Основные поля
/*		$ret['from_date'] = date('Y-m-d', $this->from_date);
		$ret['to_date'] = date('Y-m-d', $this->to_date);
		$ret['show_from'] = date('F j, Y (D)', $this->from_date);
		$ret['show_to'] = date('F j, Y (D)', $this->to_date);*/

        $ret['from_date'] = strftime('%Y-%m-%d', $this->from_date);
        $ret['to_date'] = strftime('%Y-%m-%d', $this->to_date);
        $ret['show_from'] = strftime('%e %B, %Y (%a)', $this->from_date);
        $ret['show_to'] = strftime('%e %B, %Y (%a)', $this->to_date);

		// Выводить интервалы для посещаемости
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
		// выводить галочки для посещаемости
		if (isset($params['attendance']) && $params['attendance']) {
			$ret['attendance'] = array(
				'show_visit'	=> ($this->show_visit	? 'CHECKED' : ''),
				'show_no_reklama'	=> ($this->show_no_reklama	? 'CHECKED' : ''),
				'str_nav_show_visit'	=> $this->str('nav_show_visit'),
				'show_clients'	=> ($this->show_clients	? 'CHECKED' : ''),
				'str_nav_show_clients'	=> $this->str('nav_show_clients'),
				'show_loads'	=> ($this->show_loads	? 'CHECKED' : ''),
				'str_nav_show_loads'	=> $this->str('nav_show_loads'),
				'show_uvisit'	=> ($this->show_uvisit	? 'CHECKED' : ''),
				'str_nav_show_uvisit'	=> $this->str('nav_show_uvisit'),
				'show_hosts'	=> ($this->show_hosts	? 'CHECKED' : ''),
				'str_nav_show_hosts'	=> $this->str('nav_show_hosts'),
			);
            if (STAT_EVENT_REPORT == true) {
                $row = sql_getRow('SELECT * FROM '.STAT_SETTINGS_TABLE.' WHERE name="events"', true);
                if ($row['value']) {
	                $events = unserialize($row['value']);
	                $l = 0;
	                foreach ($events as $key=>$val) {
	                    $temp = 'show_event_'.$l;
	                    $ret['attendance'][$temp] = $this->$temp    ? 'CHECKED' : '';
	                    $ret['attendance']['str_nav_'.$temp] = $key;
	                    $l++;
	                }
	                $ret['events'] = $events;
                }
            }
		}
		// hidden поля
		foreach ($params['hidden'] as $key=>$val)
			$ret['hidden'][] = array('name' => $key, 'value' => $val);

		return $ret;
	}

	########################
	// Строит таблицу по переданным данным
# data: array(
# 	'columns'	=> array(				# столбцы таблицы
#		array(
#			'header'	=> 'name',		# заголовок столбца
#			'type'		=> 'varchar',	# function $this->table_get_$type
#			'align'		=> 'right',		# <td align=$align>
#			'valign'	=> 'top',		# <td valign=$valign>
#			'class'		=> '',			# <td class=$class>
#			'width'		=> '100%',		# <td width=$width>
#			'nowrap'	=> false,		# true || false
# 		),
#	),
#	'data'		=> сами  по себе данные
#	'total'		=> строка "Всего"
#	'count'		=> сколько всего строк (нужно для вывода навигации по таблице)
#	'offset'	=> с какой строки выводить (нужно для вывода навигации по таблице)
#	'limit'		=> сколько строк выводить (нужно для вывода навигации по таблице)
# )
	function stat_table($data) {

		$ret = array();
		# нет данных
		if (!isset($data['columns']) || empty($data['columns'])) return $ret;

		# определяем данные
		$total	= isset($data['total'])		? $data['total']	: array();
        $total_head    = isset($data['total_head'])        ? $data['total_head']    : array();
		$count	= isset($data['count'])		? $data['count']	: 0;
		$offset	= isset($data['offset'])	? $data['offset']	: 0;
		$limit	= isset($data['limit'])		? $data['limit']	: $this->Param('limit', $GLOBALS['limit']);

		# строим заголовки
		foreach ($data['columns'] as $key=>$val) {
			$options[$key] = array(
				'align'		=> isset($val['align'])		? $val['align']		: '',
				'valign'	=> isset($val['valign'])	? $val['valign']	: '',
				'class'		=> isset($val['class'])		? $val['class']		: '',
				'width'		=> isset($val['width'])		? $val['width']		: '',
				'type'		=> isset($val['type'])		? $val['type']		: '',
				'nowrap'	=> isset($val['nowrap'])	? $val['nowrap']	: false,
                'flags'        => isset($val['flags']) ? $val['flags'] : 0,
			);
			$ret['header'][$key] = array(
				'align'	=> $options[$key]['align'],
				'width'	=> $options[$key]['width'],
				'title'	=>$this->str($val['header']),
			);
		}

		# нет данных
		if (empty($data['data'])) {
			$ret['empty'] = array('value' => $this->str('no_data'));
			return $ret;
		}

		# заполняем таблицу данными
		foreach ($data['data'] as $n=>$row) {
			foreach ($data['columns'] as $key=>$col) {
				$val = current($row);
				next($row);
				if ($options[$key]['type']) {
				  if (method_exists($this, 'table_get_'.$options[$key]['type'])) {
					$val = call_user_func(array(&$this, 'table_get_'.$options[$key]['type']), $val, $data['data'][$n]);
				  }
				}
				$ret['rows'][$n]['cells'][$key] = array(
					'align'		=> $options[$key]['align'],
					'valign'	=> $options[$key]['valign'],
					'class'		=> ($key==0 ? 'tFirst' : $options[$key]['class']),
					'nowrap'	=> $options[$key]['nowrap'] ? 'nowrap' : '',
					'value'		=> $val,
				);
			}
		}

		# Строки типа "Всего"
        $unset = array();
        if ($total && count($total)) foreach ($total as $n=>$total_row) {
            foreach ($data['columns'] as $key=>$col) {
                $val = isset($total_row[$key]) ? $total_row[$key] : '';
                if ($val == '' && !isset($total_head[$key])) { if (!isset($unset[$key])) $unset[$key] = 0; $unset[$key]++; }
				$ret['total'][$n]['cells'][$key] = array(
					'align' 	=> $options[$key]['align'],
					'class'		=> ($key==0 ? 'tFirst' : $options[$key]['class']),
					'value'		=> $val,
				);
			}
		}
        foreach ($unset as $key=>$val) {
            if (count($ret['total']) == $val)  // Удаляем пустую колонку из таблицы ВСЕГО
                foreach ($ret['total'] as $k=>$v)
                    unset($ret['total'][$k]['cells'][$key]);
        }
        if ($total_head && count($total_head)) $ret['total_head'] = $total_head;

		# Навигация
		if ($limit > 0 && $count > $limit) {
			$pageCur = floor($offset / $limit)+1;
			$pageLast = ceil($count / $limit);
			$pageFirst = 1;
			for ($i=1; $i<=$pageLast;$i++) $pages[($i-1) * $limit] = $i;
            $ret['footer'] = array(
				'page'				=> $this->str('page'),
				'total'				=> $this->str('total'),
				'limit'				=> $this->str('limit'),
				'count'				=> $count,
				'btn_prev_disabled'	=> $pageCur <= $pageFirst ? 'DISABLED' : '',
				'btn_next_disabled'	=> $pageCur >= $pageLast ? 'DISABLED' : '',
				'pages'				=> $this->GetArrayOptions($pages, $offset, true),
				'limits'			=> $this->GetArrayOptions(array(5, 10, 15, 20, 30, 40, 50), $limit, false),
			);
		}
        $ret['count'] = $count;

		// Запись параметра limit для таблицы
		$this->Param('limit', '', $limit);

		return $ret;
	}

	### Функции для таблицы

	// для вывода графика
	function table_get_graph($val, $row) {
		$proc = round($val, 2).'%';
		$width = round($val).'%';
		return '<span class="Tsmall">'.$proc.'</span><img src="images/stat/graf.gif" alt="'.$proc.'" width="'.$width.'" height="6" border="0">';
	}

	// для вывода кнопки "more.."
	function table_get_advanced($key, $val) {
		return '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv['.$key.']='.rawurlencode($val).'\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>';
	}

	########################
	########################
	// english ordinal suffix for numbers
	function ordSuffix($num) {
		if (int_langId()==0) return $num.'-е';
		$sLastChar = substr($num, -1);
		$sSecondToLastChar = substr($num, -2, 1);
		if ($sSecondToLastChar != 1) {
			switch ($sLastChar) {
				case 1:
					$suffix = 'st';
					break;
				case 2:
					$suffix = 'nd';
					break;
				case 3:
					$suffix = 'rd';
					break;
				default:
					$suffix = 'th';
					break;
			}
		} else {
			$suffix = 'th';
		}
		return $num.$suffix;
	}

	########################
	// возвращает get-строку по данным параметрам
	function query_str($params) {
		$str = '';
		foreach ($params as $key => $value) {
			$str .= (strlen($str) < 1) ? '' : '&';
			//$str .= $key.'='.$value;
			$str .= $key . '=' . rawurlencode($value);
		}
		return ($str);
	}

	########################
	// вместо array_slice, тк она, сволочь, сбрасывает ключи массива!
	function SliceArray($mas, $offset, $length=0) {
		if (!is_array($mas) || !count($mas)) return array();
/*
		раскомментируйте этот код когда будет PHP5
		$values = array_slice($mas, $offset, $length);
		$keys = array_slice($mas, $offset, $length);
		return array_combine($values, $keys);
*/
		if (empty($length)) $length = count($mas) - $offset;
		$i=0;
		foreach ($mas as $key => $val) {
			$i++;
			if ($i>$offset && $i<=$offset+$length) $arr[$key] = $val;
		}
		return $arr;
	}

	########################
}

?>