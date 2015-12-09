<?php

require_once (module('stat'));

class TGeography extends TStat {

	var $name = 'stat/stat_geography';

	########################

	function TGeography() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'countries'	=> array(
				'Страны',
				'Countries',
			),
			'country'	=> array(
				'Страна',
				'Country',
			),
			'visitors'	=> array(
				'Посетители',
				'Visitors',
			),
			### tip ###
			'tip'	=> array(
				'Отчет расскажет вам, в каких странах интересно то, о чем вы хотите рассказать на вашем сайте.',
				'',
			),
		);

		// На каком языке писать название страны
		$this->country_lang = Lang()=='ru' ? 'ru' : 'en';
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
        if (!empty($search)) $search_state = ' AND cn.name_'.$this->country_lang.' LIKE "'.$search.'"';

		$count = sql_getValue("SELECT COUNT(DISTINCT(country)) FROM ".$this->sess_table." as sess
        LEFT JOIN ".STAT_COUNTRIES_TABLE." AS cn ON cn.country_id=sess.country WHERE robot=0 $search_state");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0");
		$data = sql_getRows("
			SELECT cn.name_".$this->country_lang." AS country_name, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, sess.country AS country_id
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_COUNTRIES_TABLE." AS cn ON cn.country_id=sess.country
			WHERE sess.robot=0 $search_state GROUP BY sess.country ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
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
					'header' 	=> 'country',
					'nowrap'	=> 1,
					'type'		=> 'country',
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
		// строим темповые данные
		sql_query("
			CREATE TEMPORARY TABLE tmp_".str_replace('/', '__', $this->name)."
			SELECT cn.name_en AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_COUNTRIES_TABLE." AS cn ON cn.country_id=sess.country
			WHERE sess.robot=0");

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

	function table_get_country($val, $row) {
		return
			$this->table_get_advanced('country', $row['country_id']).
			$val.' <span class="tSmall">('.$row['country_id'].')</span>';
	}

	######################
}

$GLOBALS['stat__stat_geography'] = & Registry::get('TGeography');

?>