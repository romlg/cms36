<?php

require_once (module('stat'));

class TRobots extends TStat {

	var $name = 'stat/stat_robots';

	########################

	function TRobots() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'name'	=> array(
				'Название Поисковой Машины',
				'SearchRobot\'s Name',
			),
			'pages'	=> array(
				'Количество проиндексированных страниц',
				'Amount indexed pages',
			),
			'time' => array(
				'Время посещения',
				'Visit time',
			),
			'page' => array(
				'&nbsp;Адрес страницы',
				'&nbsp;Page url',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);
		if(get('show','')!='pages') {
				if ($this->show!='diag' && $this->show!='csv') {
					$this->show = 'table';
				}
				$ret = @call_user_func(array(&$this, 'Get'.$this->show));
				$ret['navig'] = $this->NavigForm(array(
					'hidden'	=> array('show' => $this->show),
				));
				$ret['site_select'] = $this->selectSite(array(
					'hidden'	=> array('show' => $this->show),
				));
                if ($this->show == 'table') $ret['show_search'] = 1;
			return Parse($ret, 'stat/stat.tmpl');
		}
		else {
			$this->GetPages();
		}
	}

	######################

	function GetTable() {
		global $limit;
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));
        $search = get('find','');
        $search_state = '';
        if (!empty($search)) $search_state = ' AND ag.name LIKE "'.$search.'"';

		$q = "
			CREATE TEMPORARY TABLE tmp_stat_robots
			SELECT sess.agent_id AS agent_id, ag.name AS name, ag.agent AS agent, COUNT(log.sess_id) AS loads
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
			LEFT JOIN ".STAT_LOG_TABLE." AS log ON log.sess_id=sess.sess_id
			WHERE sess.robot=1 $search_state
			GROUP BY sess.sess_id
		";
		sql_query($q);
        $q = "
            CREATE TEMPORARY TABLE tmp_stat_robots2
            SELECT sess.agent_id AS agent_id, ag.name AS name, ag.agent AS agent, COUNT(log.sess_id) AS loads
            FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
            LEFT JOIN ".STAT_LOG_TABLE." AS log ON log.sess_id=sess.sess_id
            WHERE sess.robot=1
            GROUP BY sess.sess_id
        ";
        sql_query($q);

		$count = sql_getValue("SELECT COUNT(DISTINCT(name)) FROM tmp_stat_robots");

        $total_value = sql_getValue("SELECT SUM(loads) FROM tmp_stat_robots2");

        $data = sql_getRows("SELECT name, SUM(loads) AS kol, SUM(loads)/".$total_value."*100 AS proc, agent, agent_id FROM tmp_stat_robots GROUP BY agent_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);

        $total_head = array(
                '',
                $this->_str('pages'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'name',
					'nowrap'	=> 1,
					'type'		=> 'name',
				),
				array(
					'header' 	=> 'pages',
					'align'		=> 'right',
					'width'		=> '20%',
				),
				array(
					'header' 	=> 'percent',
					'align'		=> 'right',
					'width'		=> '50%',
					'type'		=> 'graph',
				),
			),
			'data' => $data,
			'total' => $total,
            'total_head' => $total_head,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));

		return $ret;
	}

	######################

	function GetCSVData() {
		$filename = $_SERVER['DOCUMENT_ROOT'].BASE.'.backup/'.$this->name.'_'.date('Y-m-d').'.csv';
		// заголовки
		echo $this->_str('name').';'.$this->_str('pages')."\n";

		$rows = sql_getRows("
			SELECT ag.name AS name, COUNT(log.sess_id) AS kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
			LEFT JOIN ".STAT_LOG_TABLE." AS log ON log.sess_id=sess.sess_id
			WHERE sess.robot=1 GROUP BY sess.agent_id ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
	}

	######################

	function GetDiagData() {
		$res_values = $res_legends = array();
		// строим темповые данные
		sql_query("
			CREATE TEMPORARY TABLE tmp_".str_replace('/', '__', $this->name)."
			SELECT ag.name AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
			WHERE sess.robot=1");

		$total = $others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name));
		$data = sql_getRows("SELECT name, COUNT(*) AS kol FROM tmp_".str_replace('/', '__', $this->name)." GROUP BY name HAVING kol/".$total.">0.01 ORDER BY kol DESC", true);
		if ($data) {
			$others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name)." WHERE name NOT IN ('".join("', '", array_keys($data))."')");
			foreach ($data as $name=>$kol) {
				$res_values[]	= $kol;
				$res_legends[]	= '"'.$name.'" ('.round($kol/$total*100).'%%)';
			}
			if ($others) {
				$res_values[]	= $others;
				$res_legends[]	= $this->str('others').' ('.round($others/$total*100).'%%)';
			}
		}
		return array($res_values, $res_legends);
	}

	######################

	function table_get_name($val, $row) {
			return '<a href="#" onclick="window.open(\'cnt.php?page=stat/stat_robots&do=show&show=pages&agent_id='.$row['agent_id'].'&adv[robot]='.rawurlencode($val).'&from_date='.$this->from_date.'&to_date='.$this->to_date.'&sess='.$this->sess_table.'\', \'stat\', \'width=900, height=600, resizable=1, scrollbars=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>'.h($val).' <div class="tSmall">'.($row['agent'] ? '('.h($row['agent']).')' : '-').'</div>';
	}

	######################

	function GetPages() {
		global $limit;
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));
		$from_date	= (int)get('from_date');
		$to_date	= (int)get('to_date');
		$adv = get('adv',array(),'g');
		$this->sess_table = get('sess');
		$agent_id = get('agent_id');
		$q = "CREATE TEMPORARY TABLE tmp_stat_robot_".$agent_id." SELECT log.time, log.page_id	FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_LOG_TABLE." AS log ON log.sess_id=sess.sess_id WHERE sess.robot=1 AND sess.agent_id=".$agent_id;
		sql_query($q);
		$q = "select count(*) FROM tmp_stat_robot_".$agent_id." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS page on page.id=sess.page_id order by sess.time ASC";
		$count = sql_getValue("SELECT COUNT(*) FROM tmp_stat_robot_".$agent_id."");
		$data = sql_getRows("select FROM_UNIXTIME(sess.time), CONCAT('&nbsp;<a href=\"http://',page.host,page.uri,'\">','http://',page.host,page.uri) FROM tmp_stat_robot_".$agent_id." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS page on page.id=sess.page_id order by sess.time ASC LIMIT ".$offset.", ".$limit);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'time',
					'nowrap'	=> 1,
					'type'		=> 'time',
					'align' => 'center',
				),
				array(
					'header' 	=> 'page',
					'align'		=> 'left',
					'width'		=> '80%',
				),
			),
			'data' => $data,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));

		$ret['navig'] = $this->NavigForm(array(
			'hidden'	=> array('show' => $this->show),
		));
		$ret['navig']['hidden'][] = array (
			'name' => 'agent_id',
			'value' => $agent_id,
		);
		echo '<title>'.$this->GetTitle().'</title>';
		$ret['navig']['hidden'][] = array (
			'name' => 'sess',
			'value' => $this->sess_table,
		);

		echo Parse($ret, 'stat/stat.no_navig.tmpl');

	}
}

$GLOBALS['stat__stat_robots'] =  & Registry::get('TRobots');

?>