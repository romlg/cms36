<?php

require_once (module('stat'));

class TErrors extends TStat {

	var $name = 'stat/stat_errors';

	########################

	function TErrors() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'pages'	=> array(
				'Запрошенные страницы',
				'Pages requested',
			),
			'status'	=> array(
				'Статус',
				'Status',
			),
			'viewed'	=> array(
				'Просмотров',
				'Viewed',
			),
			'referer'	=> array(
				'кто ссылается',
				'referer pages',
			),
			'no_referer' => array(
				'нет страниц',
				'no pages',
			),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		if ($this->show!='csv')	$this->show = 'table';
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
        if (!empty($search)) $search_state = ' AND (pag.uri LIKE "'.$search.'" OR pag.host LIKE "'.$search.'" OR ref.uri LIKE "'.$search.'" OR ref.host LIKE "'.$search.'")';

		// create temp table
		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_errors
			SELECT
				log.page_id AS page_id,
				pag.uri AS page,
				pag.host AS host,
				log.status AS status,
				log.ref_id AS ref_id,
				ref.uri AS ref_page,
				ref.host AS ref_host
			FROM ".$this->log_table." AS log
				LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
				LEFT JOIN ".STAT_PAGES_TABLE." AS ref ON ref.id=log.ref_id
			WHERE log.status IN (404, 403) $search_state");

		$count = sql_getValue("SELECT COUNT(DISTINCT(page_id)) FROM tmp_stat_errors");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->log_table." AS log
                LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
                LEFT JOIN ".STAT_PAGES_TABLE." AS ref ON ref.id=log.ref_id
            WHERE log.status IN (404, 403)");
		$data = sql_getRows("
			SELECT page, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, host, status, page_id
			FROM tmp_stat_errors
			GROUP BY page_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
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
		// заголовки
		echo $this->_str('page').';'.$this->_str('status').';'.$this->_str('viewed')."\n";

		$rows = sql_getRows("
			SELECT CONCAT('http://', pag.host, pag.uri) AS name, log.status, COUNT(*) as kol
			FROM ".$this->log_table." AS log LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=log.page_id
			WHERE log.status IN (404, 403) GROUP BY name ORDER BY kol DESC");
		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}

	}

	######################

	function table_get_page($val, $row) {
		// С какиех страниц переходят
		$referer = sql_getRows("SELECT ref_id, ref_page, ref_host FROM tmp_stat_errors WHERE page_id=".$row['page_id']." AND ref_id!=0 GROUP BY ref_id");
		$referers = $this->str('referer').':<br>';
		if ($referer) foreach ($referer AS $ref) {
			$str = substr($ref['ref_page'], 0, 20) . (strlen($ref['ref_page']) > 20 ? '...' : '');
			$referers .= '<a href="http://'.$ref['ref_host'].$ref['ref_page'].'" target="_blank">http://'.$ref['ref_host'].$str.'</a><br>';
		}
		else $referers = $this->str('no_referer');
		$str = substr($row['page'], 0, 20) . (strlen($row['page']) > 20 ? '...' : '');
		return '
			<a href="#" onclick="roll(\'referer_'.$row['page_id'].'\'); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('referer').'" align="absmiddle"></a>
			<a href="http://'.$row['host'].$row['page'].'" target="_blank">http://'.$row['host'].$str.'</a>
			<span class="tSmall">('.$row['status'].')</span>
			<div class="tSmall" style="display: none;" id="referer_'.$row['page_id'].'">'.$referers.'</div>';
	}

	######################
}

$GLOBALS['stat__stat_errors'] =  & Registry::get('TErrors');

?>