<?php

require_once (module('stat'));

class TPopular extends TStat {

	var $name = 'stat/stat_popular';

	########################

	function TPopular() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'page'	=> array(
				'URL ��������',
				'Page URL',
			),
			'viewed'	=> array(
				'����������',
				'Viewed',
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

        $ret['analyze_page'] = 'http://';
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
		// create temp table
		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_popular
			SELECT
				log.page_id AS page_id,
				pag.uri AS page,
				pag.host AS host
			FROM ".$this->log_table." AS log
				LEFT JOIN ".$this->sess_table." AS sess USING (sess_id)
				LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
			WHERE log.status=200 AND sess.robot=0 $search_state");
		$count = sql_getValue("SELECT COUNT(DISTINCT(page_id)) FROM tmp_stat_popular");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->log_table." AS log
                LEFT JOIN ".$this->sess_table." AS sess USING (sess_id)
                LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
            WHERE log.status=200 AND sess.robot=0");
		$data = sql_getRows("SELECT page, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, host, page_id FROM tmp_stat_popular GROUP BY page_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);

		//$uri="http://doki/sell";
		// ���������� ���������� ����� ���� �� ����������� � uri.
		//if((substr($uri,0,-1)!='/') && (stristr($uri,'.') || stristr($uri,'?'))) {
		//	$uri .= '/';
		//} else {
			//if()
		//}
		//if (substr($uri,-1,1) != '/' && !stristr($uri,'.') && !stristr($uri,'?')){
		//	$uri .= '/';
		//}
		 //pr($uri);

        $total_head = array(
                '',
                $this->_str('viewed'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'page',
					'nowrap'	=> 1,
					'type'		=> 'page',
				),
				array(
					'header' 	=> 'viewed',
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
		// ���������
		echo $this->_str('page').';'.$this->_str('visitors')."\n";
		echo 'trabl';
		/*
		sql_query("
			SELECT CONCAT('http://', pag.host, pag.uri) AS name, COUNT(*) as kol
			INTO OUTFILE '".$filename."'
			FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'
			LINES TERMINATED BY '\n'
			FROM ".$this->log_table." AS log
				LEFT JOIN ".$this->sess_table." AS sess USING (sess_id)
				LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
			WHERE log.status=200 AND sess.robot=0 AND log.time>=".$this->from_date." AND log.time<=".$this->to_date."
			GROUP BY name ORDER BY kol DESC");*/
		$rows = sql_getRows("
			SELECT CONCAT('http://', pag.host, pag.uri) AS name, COUNT(*) as kol
			FROM ".$this->log_table." AS log
				LEFT JOIN ".$this->sess_table." AS sess USING (sess_id)
				LEFT JOIN ".MYSQL_DB.".".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
			WHERE log.status=200 AND sess.robot=0 AND log.time>=".$this->from_date." AND log.time<=".$this->to_date."
			GROUP BY name ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
		// ������� ���������� ����
		//if (is_file($filename)) {
		//	readfile($filename);
		//	unlink($filename);
		//}
	}

	######################

	function GetDiagData() {
		$res_values = $res_legends = array();
		// ������ �������� ������
		sql_query("
			CREATE TEMPORARY TABLE tmp_".str_replace('/', '__', $this->name)."
			SELECT CONCAT('http://', pag.host, pag.uri) AS name
			FROM ".$this->log_table." AS log
				LEFT JOIN ".$this->sess_table." AS sess USING (sess_id)
				LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
			WHERE log.status=200 AND sess.robot=0 AND log.time>=".$this->from_date." AND log.time<=".$this->to_date);

		$total = $others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name));
		$data = sql_getRows("SELECT name, COUNT(*) AS kol FROM tmp_".str_replace('/', '__', $this->name)." GROUP BY name HAVING kol/".$total.">0.01 ORDER BY kol DESC", true);
		if ($data) {
			$others = sql_getValue("SELECT COUNT(*) FROM tmp_".str_replace('/', '__', $this->name)." WHERE name NOT IN ('".join("', '", array_keys($data))."')");
			foreach ($data as $name=>$kol) {
				$res_values[]	= $kol;
				$res_legends[]	= '"'.str_replace('%','%%',substr($name, 0, 60)) . (strlen($name) > 60 ? '...' : '').'" ('.round($kol/$total*100).'%%)';
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

		return '<a href="act.php?page=stat/stat_attendance&page_id='.$row['page_id'].'" target="act"><img src="images/icons/icon.pages.gif" width=16 heidht=16 border=0 alt="'.$this->str('compare').'" align="absmiddle" hspace="3"></a>&nbsp;<a href="http://'.$row['host'].$row['page'].'" target="_blank" title="http://'.$row['host'].$row['page'].'">http://'.$row['host'].$str.'</a>';
	}

	######################


}

$GLOBALS['stat__stat_popular'] =  & Registry::get('TPopular');

?>