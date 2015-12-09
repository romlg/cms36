<?php

require_once (module('stat'));

class TRefServer extends TStat {

	var $name = 'stat/stat_ref_server';

	########################

	function TRefServer() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'domain'	=> array(
				'Домен сервера',
				'Server domain',
			),
			'from_server'	=> array(
				'Пришло с сервера',
				'From server',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		if ($this->show!='diag' &&
			$this->show!='csv')
				$this->show = 'table';
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

	######################

	function GetTable() {
		global $limit;
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));
        $search = get('find','');
        $search_state = '';
        if (!empty($search)) $search_state = ' AND (pag.uri LIKE "'.$search.'" OR pag.host LIKE "'.$search.'" OR pag.search_ph LIKE "'.$search.'")';

		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_ref_servers
			SELECT pag.host AS host
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE sess.robot=0 AND sess.ref_id!='0' $search_state");

		$count = sql_getValue("SELECT COUNT(DISTINCT(host)) FROM tmp_stat_ref_servers");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id WHERE sess.robot=0 AND sess.ref_id!='0'");
		$data = sql_getRows("SELECT host, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc FROM tmp_stat_ref_servers GROUP BY host ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
        $total_head = array(
                '',
                $this->_str('from_server'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
		 	'columns'	=> array(
				array(
					'header' 	=> 'domain',
					'nowrap'	=> 1,
					'type'		=> 'server',
				),
				array(
					'header' 	=> 'from_server',
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
		echo $this->_str('domain').';'.$this->_str('visitors')."\n";
		$rows = sql_getRows("
			SELECT CONCAT('http://', pag.host, '/') AS name, COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".MYSQL_DB.".".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE sess.robot=0 AND sess.ref_id!='0' GROUP BY name ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
		//pr(sql_getError());
		// Выводим полученный файл
		//if (is_file($filename)) {
		//	readfile($filename);
		//	unlink($filename);
		//}
	}

	######################

	function GetDiagData() {
		$res_values = $res_legends = array();
		// строим темповые данные
		sql_query("
			CREATE TEMPORARY TABLE tmp_".str_replace('/', '__', $this->name)."
			SELECT pag.host AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE sess.robot=0 AND sess.ref_id!='0'");

		$total = $others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name));
		$data = sql_getRows("SELECT name, COUNT(*) AS kol FROM tmp_".str_replace('/', '__', $this->name)." GROUP BY name HAVING kol/".$total.">0.01 ORDER BY kol DESC", true);
		if ($data) {
			$others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name)." WHERE name NOT IN ('".join("', '", array_keys($data))."')");
			foreach ($data as $name=>$kol) {
				$res_values[]	= $kol;
				$res_legends[]	= '"http://'.$name.'/" ('.round($kol/$total*100).'%%)';
			}
			if ($others) {
				$res_values[]	= $others;
				$res_legends[]	= $this->str('others').' ('.round($others/$total*100).'%%)';
			}
		}
		return array($res_values, $res_legends);
	}

	######################

	function table_get_server($val, $row) {
		return
			$this->table_get_advanced('server', $row['host']).
			'<a href="http://'.$row['host'].'/" target="_blank">http://'.$row['host'].'/</a>';
	}

	######################
}

$GLOBALS['stat__stat_ref_server'] = & Registry::get('TRefServer');

?>