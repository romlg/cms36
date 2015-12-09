<?php

// Для работы необходим модуль reklama_obj
// Как настраивать - описано в файле reklama_obj/!!readme.txt


require_once (module('stat'));

class TReklama extends TStat {

	var $name = 'stat/stat_reklama';

	########################

	function TReklama() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
            'settings' => array(
                'Настройка рекламных кампаний',
                'Option of advertising campaigns',
                'link'    => 'cnt.ReklamaSettings()',
                'img'     => 'icon.options.gif',
                'display'    => 'block',
            ),
            'refresh' => array(
                'Обновить',
                'Refresh',
                'link'    => 'cnt.location.reload()',
                'img'     => 'icon.rma.gif',
                'display'    => 'block',
            ),
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
            'visitors'          => array('Посетителей',                'Visitors',),
			'campaign'	        => array('Кампания',				'Campaign',),
			'budget'	        => array('Бюджет',               'Budget',),
            'displays_count'    => array('Кол-во показов',            'Displays count',),
            'start_date'        => array('Дата начала',            'Start date',),
            'end_date'          => array('Дата окончания',            'End date',),
            'all_count'         => array('Общее число посетителей',            'Total visitors',),
            'display_1000_cost' => array('Стоимость 1000 показов',     '1000 display cost',),
            'client_ip_slash'   => array('IP адреса (% от кликов / % от показов)',  'IP addresses (% from click / % from displays)',),
            '1TC'               => array('1TC, %',  '1TC, %',),
            '2TC'               => array('2TC, %',  '2TC, %',),
            'line1'              => array('',  '',),
            'line2'              => array('',  '',),
            'reklama_to_attendance'=> array('Доля рекламы в посещаемости',  'Share of advertising in attendance',),
            'involved_visitors_fact' => array('Привлеченных посетителей: фактическое <span style="font-size: 85%">(по данным рекламной площадки)</span>',
                                         'Involved visitors: actual <span style="font-size: 85%">(according to an advertising platform)</span>',),
            'ip_count_share' => array('IP адресов: кол-во <span style="font-size: 85%">(доля от общего)</span>',
                                'IP addresses: quantity <span style="font-size: 85%">(share)</span>',),
            'client_1_slash'     => array('2+ просмотров (кол-во / % от кликов / % от показов)',
                                    '2+ viewing (quantity / % from click / % from displays)',),
            'client_2_slash'     => array('3+ просмотров (кол-во / % от кликов / % от показов)',
                                    '3+ viewing (quantity / % from click / % from displays)',),
            'client_1_cost'     => array('Стоимость 2+', 'Cost 2+',),
            'client_2_cost'     => array('Стоимость 3+', 'Cost 3+',),
            'ip_cost'     => array('Стоимость IP адреса', 'IP address cost',),
            'CTR_fact'                => array('CTR фактический <span style="font-size: 85%">(по данным рекламной площадки)</span>',                                               'Real CTR <span style="font-size: 85%">(according to an advertising platform)</span>',),
            'click_cost'        => array('Стоимость клика расчетная <span style="font-size: 85%">(по данным рекламной площадки)</span>',
                                         'Real click cost <span style="font-size: 85%">(according to an advertising platform)</span>',),
		);

        if (STAT_EVENT_REPORT == true) {
            $row = sql_getRow('SELECT * FROM '.STAT_SETTINGS_TABLE.' WHERE name="events"', true);
            if (!empty($row['value'])) {
	            $events = unserialize($row['value']);
	            foreach ($events as $key=>$val) {
	                $this->events[] = array(
	                'name' => $key,
	                'url'  => $val,
	                );
	            }            	
            }
        }
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		if ($this->show!='diag' &&
			$this->show!='csv')
				$this->show = 'table';
		$ret = @call_user_func(array(&$this, 'Get'.$this->show));

/*		$ret['navig'] = $this->NavigForm(array(
			'hidden'	=> array('show' => $this->show),
		));
        if ($this->show == 'table') $ret['show_search'] = 1;*/

		return Parse($ret, 'stat/stat.tmpl');
	}

	######################

	function GetTable() {
		global $limit;
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));

        $reklams = sql_getRows("SELECT * FROM stat_reklama");

        if (empty($reklams)) {
            $ret['tip']['text'] = "Рекламные кампании не созданы. Для того чтобы создать рекламную кампанию, нажмите кнопку \"Настройка рекламных кампаний\".";
            return $ret;
        }

        foreach ($reklams as $key=>$reklama){
            if (!$reklama['click_count']) $reklama['click_count'] = 0;
            if (!$reklama['displays_count']) $reklama['displays_count'] = 0;
            if (!$reklama['displays_count']) $reklama['displays_count'] = 0;
            if (!$reklama['budget']) $reklama['budget'] = 0;

            $identifiers = explode(',', $reklama['identifiers']);
            foreach ($identifiers as $k=>$v)
                $identifiers[$k]= trim($v);

            $identifiers_state = "";
            if (!empty($identifiers)) {
                $identifiers_state = " AND (";
                foreach ($identifiers as $k=>$v) {
                    $identifiers_state .= " page.uri LIKE '%from=".$v."'".($k < count($identifiers)-1 ? " OR" : "");
                }
                $identifiers_state .= ") ";
            }
            // определяем дату начала рекламной кампании по первому вхождению
            if ($reklama['start_date'] == '0000-00-00 00:00:00') {
            	$reklama['start_date'] =  sql_getValue("SELECT min( time )
            	FROM ".STAT_SESSIONS_TABLE." as s LEFT JOIN stat_pages AS page ON page.id = s.first_page
				WHERE 1 ".$identifiers_state);
                if (!$reklama['start_date'])
                    $reklama['start_date'] = sql_getValue("SELECT min( time ) FROM ".STAT_SESSIONS_TABLE);
            }else
            	$reklama['start_date'] = strtotime($reklama['start_date']);

            if ($reklama['end_date'] == '0000-00-00 00:00:00') {
            	$reklama['end_date'] =  sql_getValue("SELECT max( time )
            	FROM ".STAT_SESSIONS_TABLE." as s LEFT JOIN stat_pages AS page ON page.id = s.first_page
				WHERE 1 ".$identifiers_state);
                if (!$reklama['end_date'])
                    $reklama['end_date'] = sql_getValue("SELECT max( time ) FROM ".STAT_SESSIONS_TABLE);
            }else
            	$reklama['end_date'] = strtotime($reklama['end_date']);

            $date_state = "";
            if ($reklama['start_date']) $date_state .= " AND sess.time>=".$reklama['start_date'];
            if ($reklama['end_date']) $date_state .= " AND sess.time<=".$reklama['end_date'];

            // Временные таблицы
            // Выборка из stat_sessions за время кампании
            $tmp_table = "`tmp_reklama`";
			$sql = "DROP TABLE IF EXISTS ".$tmp_table;
			sql_query($sql);
			$sql = "CREATE TEMPORARY TABLE ".$tmp_table." (
  			PRIMARY KEY  (`sess_id`),
  			KEY `ip` (`ip`,`agent_id`),
  			KEY `time` (`time`,`robot`),
  			KEY `first_page` (`first_page`)
			) SELECT * FROM ".STAT_SESSIONS_TABLE." AS sess WHERE sess.robot=0 ".$date_state;
            $res = sql_query($sql);
            if (sql_getErrNo()) { $flag = sql_getError(); break; }

            // Выборка из stat_sessions за время кампании при условии $identifiers_state
            $tmp_table_ident = "`tmp_reklama_ident`";
			$sql = "DROP TABLE IF EXISTS ".$tmp_table_ident;
			sql_query($sql);
			$sql = "CREATE TEMPORARY TABLE ".$tmp_table_ident." (
  			PRIMARY KEY  (`sess_id`),
  			KEY `ip` (`ip`,`agent_id`),
  			KEY `time` (`time`,`robot`),
  			KEY `first_page` (`first_page`)
			) SELECT * FROM ".STAT_SESSIONS_TABLE." AS sess, ".STAT_PAGES_TABLE." as page 
			WHERE sess.first_page=page.id AND sess.robot=0 ".$date_state.$identifiers_state;
			$res = sql_query($sql);
            if (sql_getErrNo()) { $flag = sql_getError(); break; }

            $cnt = sql_getRow("SELECT COUNT(*) AS cnt, COUNT(DISTINCT(sess.ip)) as cnt_ip FROM ".$tmp_table." AS sess");
            // Общее число посетителей
            $all_count = (int)$cnt['cnt'];
            // Общее число ip адресов
            $all_ip_count = (int)$cnt['cnt_ip'];

        	// Кол-во посетителей, просмотревших более 1 страницы
            $sql = "SELECT COUNT(DISTINCT(sess.sess_id)) FROM ".$tmp_table_ident." AS sess,
            ".STAT_PAGES_TABLE." AS page WHERE page.id=sess.first_page
            AND sess.path!='' AND LENGTH(sess.path)-LENGTH(REPLACE(sess.path,' ',''))+1 > 1 ";
            $count_pages[1] = (int)sql_getValue($sql);

            // Кол-во посетителей, просмотревших более 2 страниц
            $sql = "SELECT COUNT(DISTINCT(sess.sess_id)) FROM ".$tmp_table_ident." AS sess,
            ".STAT_PAGES_TABLE." AS page WHERE page.id=sess.first_page
            AND sess.path!='' AND LENGTH(sess.path)-LENGTH(REPLACE(sess.path,' ',''))+1 > 2 ";
            $count_pages[2] = (int)sql_getValue($sql);

            // Выбираем поля
            $select = array();

############################################################################################
            // название кампании
            $select[] = "'".$reklama['name']."' AS campaign";
            // дата начала кампании
            $select[] = "FROM_UNIXTIME(".$reklama['start_date'].", '%d.%m.%Y') AS start_date";
            // дата окончания кампании
            $select[] = "FROM_UNIXTIME(".$reklama['end_date'].", '%d.%m.%Y') AS end_date";
            // бюждет кампании
            $select[] = "'".$reklama['budget']."' AS budget";
            // кол-во показов
            $select[] = "'".$reklama['displays_count']."' AS displays_count";
            // Общее число посетителей
            $select[] = "'".$all_count."' AS all_count";
            // количество рекламных посетителей
            $select[] = "CONCAT(COUNT(DISTINCT(sess.sess_id)), ' <span style=\"font-size: 85%\">(', '".$reklama['click_count']."',')</span>') AS involved_visitors_fact";
            // доля рекламы в посещаемости
            $select[] = "CONCAT(IF(".$all_count."!=0,COUNT(DISTINCT(sess.sess_id))/".$all_count.",0)*100,'%') AS reklama_to_attendance";
            // ip адресов
            $select[] = "CONCAT(COUNT(DISTINCT(sess.ip)), ' <span style=\"font-size: 85%\">(', IF(".$all_ip_count."!=0,COUNT(DISTINCT(sess.ip))/".$all_ip_count.",0)*100, '%)</span>') AS ip_count_share";
            // события
            if ($this->events) {
                foreach ($this->events as $k=>$event) {
                	// Временная таблица
		            $tmp_table_event = "`tmp_reklama_event`";
					$sql = "DROP TABLE IF EXISTS ".$tmp_table_event;
					getSql($sql);
					$sql = "CREATE TEMPORARY TABLE ".$tmp_table_event." (
					PRIMARY KEY  (`id`),
  					KEY `host_uri` (`host`,`uri`)
					) SELECT * FROM ".STAT_PAGES_TABLE." AS page 
					WHERE page.uri LIKE '".str_replace("*","%",$event['url'])."'";
					$res = getSql($sql);
		            if (sql_getErrNo()) { $flag = sql_getError(); break; }
                	
                    $sql = "SELECT COUNT(DISTINCT(sess.sess_id)) AS count
                            FROM ".STAT_LOG_TABLE." AS sess,
                             ".$tmp_table_event." AS page
                             WHERE sess.page_id=page.id ".$date_state;
                    $all_event_count = (int)sql_getValue($sql); // Кол-во событий за время рекламной кампании
                    $identifiers_state2 = str_replace('page.uri', 'page2.uri', $identifiers_state);
                    
                    $sql = "SELECT COUNT(DISTINCT(sess.sess_id)) AS count FROM ".$tmp_table_ident." AS sess,
                            ".STAT_LOG_TABLE." AS log,
                            ".STAT_PAGES_TABLE." AS page2,
                            ".STAT_PAGES_TABLE." AS page
                            WHERE sess.sess_id=log.sess_id AND log.page_id=page2.id AND sess.first_page=page.id
                            AND page2.uri LIKE '".str_replace("*","%",$event['url'])."'";
                    $event_count[$k] = (int)sql_getValue($sql); // Кол-во событий по клику
                    $select[] = "CONCAT('".$event_count[$k]."', ' <span style=\"font-size: 85%\">(', IF(".$all_event_count."!=0,".$event_count[$k]."/".$all_event_count.",0)*100, '%)</span>') AS event_".$k."_share";
                }
            }
            $select[] = "'&nbsp;' as line1";
############################################################################################

            // CTR
            $select[] = "CONCAT(IF(".$reklama['displays_count']."!=0,COUNT(page.uri)/".$reklama['displays_count'].",0)*100, '% <span style=\"font-size: 85%\">(', IF(".$reklama['displays_count']."!=0,".$reklama['click_count']."/".$reklama['displays_count'].",0)*100, '%</span>)') AS CTR_fact";
            // ip адреса
            $select[] = "CONCAT(IF(COUNT(DISTINCT(sess.sess_id))!=0,COUNT(DISTINCT(sess.ip))/COUNT(DISTINCT(sess.sess_id)),0)*100, '% / ', IF(".$reklama['displays_count']."!=0,COUNT(DISTINCT(sess.ip))/".$reklama['displays_count'].",0)*100, '%') AS client_ip_slash";
            // качество просмотра (просмотревшие более 1 страницы)
            $select[] = "CONCAT('".$count_pages[1]."', ' / ', IF(COUNT(DISTINCT(sess.sess_id))!=0,".$count_pages[1]."/COUNT(DISTINCT(sess.sess_id)),0)*100, '% / ', IF(".$reklama['displays_count']."!=0,".$count_pages[1]."/".$reklama['displays_count'].",0)*100, '%') AS client_1_slash";
            // качество просмотра (просмотревшие более 2 страниц)
            $select[] = "CONCAT('".$count_pages[2]."', ' / ', IF(COUNT(DISTINCT(sess.sess_id))!=0,".$count_pages[2]."/COUNT(DISTINCT(sess.sess_id)),0)*100, '% / ', IF(".$reklama['displays_count']."!=0,".$count_pages[2]."/".$reklama['displays_count'].",0)*100, '%') AS client_2_slash";
            // события
            if ($this->events) {
                foreach ($this->events as $k=>$event) {
                    $select[] = "CONCAT('".$event_count[$k]."', ' / ', IF(COUNT(DISTINCT(sess.sess_id))!=0,".$event_count[$k]."/COUNT(DISTINCT(sess.sess_id)),0)*100, '% / ', IF(".$reklama['displays_count']."!=0,".$event_count[$k]."/".$reklama['displays_count'].",0)*100, '%') AS event_".$k."_slash";
                }
            }

            $select[] = "'&nbsp;' as line2";
############################################################################################
            // стоимость 1000 показов
            $select[] = "ROUND(IF(".$reklama['displays_count']."!=0,".$reklama['budget']."/".$reklama['displays_count'].",0)*1000,2) AS display_1000_cost";
            // стоимость клика
            $select[] = "CONCAT(ROUND(IF(COUNT(DISTINCT(sess.sess_id))!=0,".$reklama['budget']."/COUNT(DISTINCT(sess.sess_id)), 0),2), ' <span style=\"font-size: 85%\">(', ROUND(IF(".$reklama['click_count']."!=0,".$reklama['budget']."/".$reklama['click_count'].", 0),2) ,'</span>)') AS click_cost";
            // стоимость IP адреса
            $select[] = "ROUND(IF(COUNT(DISTINCT(sess.ip))!=0,".$reklama['budget']."/COUNT(DISTINCT(sess.ip)),0),2) AS ip_cost";
            // стоимость 2+
            $select[] = "ROUND(IF(".$count_pages[1]."!=0,".$reklama['budget']."/".$count_pages[1].",0),2) AS client_1_cost";
            // стоимость 3+
            $select[] = "ROUND(IF(".$count_pages[2]."!=0,".$reklama['budget']."/".$count_pages[2].",0),2) AS client_2_cost";
            // события
            if ($this->events) {
                foreach ($this->events as $k=>$event) {
                    $select[] = "ROUND(IF(".$event_count[$k]."!=0,".$reklama['budget']."/".$event_count[$k].",0),2) AS event_".$k."_cost";
                }
            }

############################################################################################

            $sql = "SELECT ".implode(", ", $select)."
                FROM ".$tmp_table_ident." AS sess,
                ".STAT_PAGES_TABLE." AS page WHERE page.id=sess.first_page";
            $data[$key] = sql_getRow($sql);

        }
/*        $num = count($data);
        if ($num > 0) {
            $num_concat_share = array();
            $num_concat_slash = array();
            foreach ($data as $k=>$v) {
                if ($k == $num) break;
                $data[$k]['start_date'] = date('Y/m/d', $data[$k]['start_date']);
                $data[$k]['end_date'] = date('Y/m/d', $data[$k]['end_date']);
                foreach ($v as $k2=>$v2) {
                    if ($k2 == 'line1' || $k2 == 'line2') {$data[$num][$k2] = '&nbsp;'; continue;}
                    if (strpos($k2,'_slash')!==false) {
                        $n = sscanf($v2, '%f/%f/%f/%f');
                        foreach ($n as $mm=>$nn) {
                            if (isset($nn)) $num_concat_slash[$num][$k2][$mm] += $nn;
                        }
                        $data[$num][$k2] = "";
                    } elseif (strpos($k2,'_share')!==false) {
                        $a = str_replace("%", "***", $v2);
                        list($n1, $n2) = sscanf($a, '%f <span style="font-size: 85***">(%f***)</span>');
                        $num_concat_share[$num][$k2][0] += $n1;
                        $num_concat_share[$num][$k2][1] += $n2;
                        $data[$num][$k2] = $num_concat_share[$num][$k2][0]." (".$num_concat_share[$num][$k2][1]."%)";
                    } elseif (strpos($k2,'_fact')!==false) {
                        $a = str_replace("%", "***", $v2);
                        list($n1, $n2) = sscanf($a, '%f <span style="font-size: 85***">(%f)</span>');
                        $num_concat_share[$num][$k2][0] += $n1;
                        $num_concat_share[$num][$k2][1] += $n2;
                        $data[$num][$k2] = $num_concat_share[$num][$k2][0]." (".$num_concat_share[$num][$k2][1].")";
                    } else $data[$num][$k2] += $v2;
                }
            }
            foreach ($num_concat_slash[$num] as $key=>$val) {
                foreach ($val as $k=>$v) {
                    $data[$num][$key] .= $v.($k<count($val)-1 ? '/':'');
                }
            }

            $data[$num]['campaign'] = $this->str('total');
            $data[$num]['start_date'] = "";
            $data[$num]['end_date'] = "";

            foreach ($data[$num] as $k2=>$v2)
                $data[$num][$k2] = '<span style="font-size: 85%; font-weight: bold;">'.$data[$num][$k2].'</span>';
        }
*/
        $keys = end($data);

        foreach($keys AS $k=>$v){
            $temp = explode('_', $k);
            if ($temp[0] == 'event' && $temp[2] == 'share') {
                $keys[$k] = $this->events[$temp[1]]['name'].(lang() == 'ru' ? ": кол-во <span style=\"font-size: 85%\">(доля от общего)</span>" : ": quantity (share)");
            } else if ($temp[0] == 'event' && $temp[2] == 'slash') {
                $keys[$k] = $this->events[$temp[1]]['name'].(lang() == 'ru' ? " (кол-во / % от кликов / % от показов)" : " (quantity / % from click / % from displays)");
            } else if ($temp[0] == 'event' && $temp[2] == 'cost') {
                $keys[$k] = (lang() == 'ru' ? "Стоимость \"" : "Cost \"").$this->events[$temp[1]]['name']."\"";
            } else $keys[$k] = $this->str($k);
        }

        array_unshift($data, $keys);

        $i=0;
        foreach($keys AS $key=>$val){
        	foreach($data AS $k=>$v){
        		$data1[$i][$k] = $v[$key];
        	}
            $i++;
        }

/*        $total_head = array(
                '',
                $this->_str('visitors'),
        );
		$total[] = array(
			$this->str('total_period'),
			(int)sql_getValue("SELECT COUNT(*) FROM ".$tmp_table),
		);*/

		// Main Table
        foreach ($data AS $k=>$v){
            $id = sql_getValue("SELECT id FROM stat_reklama WHERE name='".$v['campaign']."' LIMIT 1");
            if ($id) {
                $row = sql_getValue("SELECT identifiers FROM stat_reklama WHERE id=".$id);
                $identifiers = explode(',', $row);
                foreach ($identifiers as $key=>$val)
                    $identifiers[$key]= trim($val);
                if (!empty($identifiers))
                    $str = '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[reklama]='.implode(', ', $identifiers).'\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;'.$v['campaign'];
                else $str = $v['campaign'];
            } else {
                $str = $v['campaign'];
            }
        	$columns[$k] = array('header'     => $str, 'nowrap' => 1);
        }
        unset($data1[0]); // Убираем первую строку с названиями кампаний (т.к. она есть в шапке таблицы)

		$ret['table'] = $this->stat_table(array(
			'columns'	=> $columns,
			'data' => $data1,
			//'total' => $total,
            //'total_head' => $total_head,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));

		return $ret;
	}

    function find_in_array($value, $index, $array){
        foreach ($array as $key=>$val) {
            if ($value == $val[$index]) return $key;
        }
        return false;
    }

}

$GLOBALS['stat__stat_reklama'] = & Registry::get('TReklama');

?>