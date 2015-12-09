<?php

require_once (module('stat'));

class TOuts extends TStat {

	var $name = 'stat/stat_outs';

	########################

	function TOuts() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'page'	=> array(
				'URL страницы',
				'Page URL',
			),
			'visitors'	=> array(
				'Посетители',
				'Visitors',
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
        if (!empty($search)) $search_state = ' AND (pag.uri LIKE "'.$search.'" OR pag.host LIKE "'.$search.'")';
		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_outs
			SELECT pag.id AS page_id, pag.uri AS page, pag.host AS host
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.last_page
			WHERE sess.robot=0 AND sess.last_page!=0 $search_state");

		$count = sql_getValue("SELECT COUNT(DISTINCT(page_id)) FROM tmp_stat_outs");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.last_page WHERE sess.robot=0 AND sess.last_page!=0");
		$data = sql_getRows("SELECT page, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, host, page_id FROM tmp_stat_outs GROUP BY page_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
        $total_head = array(
                '',
                $this->_str('visitors'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header'	=> 'page',
					'nowrap'	=> 1,
					'type'		=> 'page',
				),
				array(
					'header' 	=> 'visitors',
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
		echo $this->_str('page').';'.$this->_str('visitors')."\n";

		$rows = sql_getRows("
			SELECT CONCAT('http://', pag.host, pag.uri) AS name, COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.last_page
			WHERE sess.robot=0 AND sess.last_page!=0 GROUP BY name ORDER BY kol DESC");

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
			SELECT CONCAT('http://', pag.host, pag.uri) AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.last_page
			WHERE sess.robot=0 AND sess.last_page!=0");

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

	function table_get_page($val, $row) {
		$str = substr($row['page'], 0, 20) . (strlen($row['page']) > 20 ? '...' : '');
		return
			$this->table_get_advanced('outs', $row['page_id']).
			'<a href="http://'.$row['host'].$row['page'].'" target="_blank" title="http://'.$row['host'].$row['page'].'">http://'.$row['host'].$str.'</a>';
	}

	######################
}

$GLOBALS['stat__stat_outs'] = & Registry::get('TOuts');

?>