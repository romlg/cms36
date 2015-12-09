<?php

/****** —татистика по поисковым запросам на сайте ******/
/* ƒанные берутс€ из таблицы stat_search. —оздание таблицы:
DROP TABLE IF EXISTS `stat_search`;
CREATE TABLE `stat_search` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `client_id` int(10) unsigned NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `date` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='—татистика поиска по сайту';

«апрос дл€ вставки в таблицу данных (добавить на сайт в нужный модуль,
например, модуль search):
$client_id = 100; // id зарегистрированного клиента
$sql = "INSERT INTO stat_search (client_id, keyword, date)
VALUES (0, $client_id, ".time().")";
/*******************************************************/


require_once (module('stat'));

class TSearch extends TStat {

	var $name = 'stat/stat_search';

	########################

	function TSearch() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'keyword'	=> array(
				' лючевое слово',
				'Keyword',
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
        if (!empty($search)) $search_state = ' AND keyword LIKE "'.$search.'"';

		if ($this->site) {
	        $cols = sql_getRows('SHOW COLUMNS FROM stat_search', true);
    	    if (isset($cols['root_id'])) {
				global $site_domains;
				foreach($site_domains as $site=>$value){
					if ($this->site == $site || in_array($this->site, explode(',', $value['alias']))) {
						$search_state .= ' AND root_id = ' . $value['root_id'];
					}
				}
        	}
		}

        $period = " AND date BETWEEN ".$this->from_date." AND ".$this->to_date;

		$count = sql_getValue("SELECT COUNT(DISTINCT(keyword)) FROM stat_search WHERE 1 ".$search_state); //  оличество найденных соответсвий

        $total_value = sql_getValue("SELECT COUNT(*) FROM stat_search WHERE 1 ".$period);

        $sql = "SELECT keyword, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc FROM stat_search WHERE 1 ".$period.$search_state." GROUP BY keyword ORDER BY kol DESC LIMIT ".$offset.", ".$limit;

        $data = sql_getRows($sql);

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
            'columns'    => array(
                array(
                    'header'     => 'keyword',
                    'nowrap'    => 1,
                    'type'        => 'keyword',
                ),
                array(
                    'header'     => 'amount',
                    'align'        => 'right',
                    'width'        => '20%',
                ),
                array(
                    'header'     => 'percent',
                    'align'        => 'right',
                    'width'        => '50%',
                    'type'        => 'graph',
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
	function table_get_keyword(&$value) {
	    return h($value);
	}

	######################

	function GetCSVData() {
		$filename = $_SERVER['DOCUMENT_ROOT'].BASE.'.backup/'.$this->name.'_'.date('Y-m-d').'.csv';
		// заголовки
		echo $this->_str('country').';'.$this->_str('visitors')."\n";
		$rows = sql_getRows("
			SELECT cn.name_".$this->country_lang." as name,  COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_COUNTRIES_TABLE." AS cn ON cn.country_id=sess.country
			WHERE sess.robot=0 GROUP BY name ORDER BY kol DESC");
		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
	}

	######################

	function GetDiagData() {
		$res_values = $res_legends = array();

        $period = " AND date BETWEEN ".$this->from_date." AND ".$this->to_date;

        $total = sql_getValue("SELECT COUNT(*) FROM stat_search WHERE 1 ".$period);

        $sql = "SELECT keyword, COUNT(*) AS kol FROM stat_search WHERE 1 ".$period." GROUP BY keyword HAVING kol/".$total.">0.01 ORDER BY kol DESC";

        $data = sql_getRows($sql, true);
		if (!empty($data)) {
			$others = sql_getValue("SELECT COUNT(*) FROM stat_search WHERE keyword NOT IN ('".join("', '", array_keys($data))."')".$period);
			foreach ($data as $name=>$kol) {
                $res_values[]	= $kol;
				$res_legends[]	= '"'.$name.'" ('.round($kol/$total*100, 2).'%%)';
			}
			if ($others) {
				$res_values[]	= $others;
				$res_legends[]	= $this->str('others').' ('.round($others/$total*100).'%%)';
			}
		}
		return array($res_values, $res_legends);
	}

	######################

}

$GLOBALS['stat__stat_search'] =  & Registry::get('TSearch');

?>