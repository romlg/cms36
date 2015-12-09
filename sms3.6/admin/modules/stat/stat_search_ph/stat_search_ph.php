<?php

require_once (module('stat'));

class TSearchPh extends TStat {

	var $name = 'stat/stat_search_ph';

	########################

	function TSearchPh() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'search_ph'	=> array(
				'Поисковые фразы',
				'Search phrases',
			),
			'others'	=> array(
				'Другие',
				'Others',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		if ($this->show!='table' &&
			$this->show!='diag' &&
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
        if (!empty($search)) $search_state = ' AND pag.search_ph LIKE "'.$search.'"';
		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_search_ph
			SELECT pag.search_ph AS search_ph
            FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE pag.search_ph!='' $search_state");

		$count = sql_getValue("SELECT COUNT(DISTINCT(search_ph)) FROM tmp_stat_search_ph");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id WHERE pag.search_ph!=''");
		$data = sql_getRows("SELECT search_ph, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc FROM tmp_stat_search_ph GROUP BY search_ph ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
        $total_head = array(
                '',
                $this->_str('amount'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'search_ph',
					'nowrap'	=> 1,
					'type'		=> 'search_ph',
				),
				array(
					'header' 	=> 'amount',
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
		echo $this->_str('search_ph').';'.$this->_str('amount')."\n";

		$rows = sql_getRows("
			SELECT pag.search_ph AS search_ph, COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE pag.search_ph!='' GROUP BY search_ph ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
		    $ph = iconv("UTF-8", "WINDOWS-1251", $v['search_ph']);
		    if (!$ph) $ph = $v['search_ph'];
			echo
			(isset($v['search_ph'])	? $ph	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
	}

	######################

	function GetDiagData() {
		$res_values = $res_legends = array();
		// строим темповые данные
		sql_query("
			CREATE TEMPORARY TABLE tmp_".str_replace('/', '__', $this->name)."
			SELECT pag.search_ph AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE pag.search_ph!=''");

		$total = $others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name));
		$data = sql_getRows("SELECT name, COUNT(*) AS kol FROM tmp_".str_replace('/', '__', $this->name)." GROUP BY name HAVING kol/".$total.">0.01 ORDER BY kol DESC ", true);
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

	function table_get_search_ph($val, $row) {
	    $word = iconv("UTF-8", "WINDOWS-1251", $val);
	    if (empty($word)) $word = $val;
		return $this->table_get_advanced('search_ph', $val).utf(h($word));
	}

	######################
}

$GLOBALS['stat__stat_search_ph'] =  & Registry::get('TSearchPh');

?>