<?php

require_once (module('stat'));

class TRefPages extends TStat {

	var $name = 'stat/stat_ref_pages';

	########################

	function TRefPages() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'page'	=> array(
				'URL страницы',
				'Page URL',
			),
			'from_page'	=> array(
				'Пришло со страницы',
				'From page',
			),
			'search_ph'	=> array(
				'Поисковые фразы',
				'Search phrases',
			),
			'total_visitors'	=> array(
				'Всего посетителей',
				'Total visitors',
			),
			'total_links'	=> array(
				'Из них по ссылке с других страниц',
				'Including refered from other sites',
			),
			'total_search'	=> array(
				'По ключевой фразе',
				'Including refered by search phrase',
			),
			'total_indefinite'	=> array(
				'Не определено',
				'Undefined referer',
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
        if (!empty($search)) $search_state = ' AND (pag.uri LIKE "'.$search.'" OR pag.host LIKE "'.$search.'" OR pag.search_ph LIKE "'.$search.'")';

        if (!empty($search)) {
            // Всего строк в таблице
            $count = sql_getValue("SELECT COUNT(DISTINCT(sess.ref_id)) FROM ".$this->sess_table." AS sess
            LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
            WHERE robot=0 AND ref_id!=0 $search_state");
        } else {
            // Всего строк в таблице
            $count = sql_getValue("SELECT COUNT(DISTINCT(ref_id)) FROM ".$this->sess_table." WHERE robot=0 AND ref_id!=0");
        }
        // Всего посетителей
        $total_visitors = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0");
        // всего по ссылке с других страниц
        $total_links = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND ref_id!=0");
		// всего неопределено
		$total_indefinite = $total_visitors - $total_links;
        // всего по поисковой фразе
        $total_search = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id WHERE pag.search_ph!=''");
		// данные для таблицы
		$data = sql_getRows("
			SELECT pag.uri AS page, pag.search_ph AS search_ph, COUNT(*) AS kol, count(*)/".$total_links."*100 AS proc, pag.host AS host
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE sess.robot=0 AND sess.ref_id!='0' $search_state GROUP BY sess.ref_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
        $total_head = array(
                '',
                $this->_str('from_page'),
                $this->_str('percent'),
        );
		$total = array(
			array(
				$this->str('total_visitors'),
				$total_visitors,
			),
			array(
				$this->str('total_links'),
				$total_links,
				$this->table_get_graph($total_links/$total_visitors*100, array()),
			),
			array(
				$this->str('total_search'),
				$total_search,
				$this->table_get_graph($total_search/$total_visitors*100, array()),
			),
			array(
				$this->str('total_indefinite'),
				$total_indefinite,
				$this->table_get_graph($total_indefinite/$total_visitors*100, array()),
			),
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
					'header' 	=> 'search_ph',
					'class'		=> 'tFirst',
					'width'		=> '10%',
					'type'      => 'search_ph',
				),
				array(
					'header' 	=> 'from_page',
					'align'		=> 'right',
					'width'		=> '10%',
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
		echo $this->_str('page').';'.$this->_str('search_ph').';'.$this->_str('viewed')."\n";

		$rows = sql_getRows("
			SELECT CONCAT('http://', pag.host, pag.uri) AS name, pag.search_ph AS search_ph, COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_PAGES_TABLE." AS pag ON pag.id=sess.ref_id
			WHERE sess.robot=0 AND sess.ref_id!='0' GROUP BY name ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
		    $ph = "";
		    if (isset($v['search_ph'])) {
		      $ph = iconv("UTF-8", "WINDOWS-1251", $v['search_ph']);
		      if (!$ph) $ph = $v['search_ph'];
		    }
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			$ph.';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
		// Выводим полученный файл
		//if (is_file($filename)) {
			//readfile($filename);
			//unlink($filename);
		//}
	}

	######################

	function table_get_page($val, $row) {
	    $href = urlencode($row['host'].$row['page']);
        if ($row['search_ph'])
			return '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[ref_page]='.$href.'\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;<a href="http://'.$row['host'].$row['page'].'" target="_blank" title="http://'.$row['host'].$row['page'].'">http://'.$row['host'].'/...</a>';
		$str = substr($row['page'], 0, 20) . (strlen($row['page']) > 20 ? '...' : '');
		return '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[ref_page]='.$href.'\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;<a href="http://'.$row['host'].$row['page'].'" target="_blank" title="http://'.$row['host'].$row['page'].'">http://'.$row['host'].$str.'</a>';
	}
	function table_get_search_ph($val, $row) {
	    $word = iconv("UTF-8", "WINDOWS-1251", $val);
	    if (empty($word)) $word = $val;
		return utf(h($word));
	}

	######################
}

$GLOBALS['stat__stat_ref_pages'] = & Registry::get('TRefPages');

?>