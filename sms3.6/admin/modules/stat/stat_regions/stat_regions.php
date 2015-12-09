<?php

require_once (module('stat'));

class TRegions extends TStat {

	var $name = 'stat/stat_regions';

	########################

	function TRegions() {
		global $str, $actions;
		TStat::TStat();
		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
		);
		$str[get_class_name($this)] = $str['tstat'] + array(
			'unknown_region' => array(
				'Область не определена',
				'Unknown region',
			),
			'region'	=> array(
				'Область',
				'Region',
			),
			'visitors'	=> array(
				'Посетители',
				'Visitors',
			),
			### tip ###
			'tip'	=> array(
				'Отчет расскажет вам, в каких областях интересно то, о чем вы хотите рассказать на вашем сайте.',
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
        if (!empty($search)) $search_state = ' AND region.name LIKE "'.$search.'"';

		$q = "SELECT region.name as region, COUNT(*) AS kol, COUNT(*) as proc
		FROM ".$this->sess_table." AS sess
		LEFT JOIN ".STAT_CITIES_TABLE." AS city ON sess.city = city.id
		LEFT JOIN ".STAT_REGIONS_TABLE." AS region ON city.reg = region.id
		WHERE sess.robot=0 $search_state
		GROUP BY region.id
		ORDER BY kol DESC
		";
		$data = sql_getRows($q);
        $all_total = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess
        LEFT JOIN ".STAT_CITIES_TABLE." AS city ON sess.city = city.id
        LEFT JOIN ".STAT_REGIONS_TABLE." AS region ON city.reg = region.id
        WHERE sess.robot=0");

		# исправляем пустое имя
		foreach($data as $k=>$v) {
			if($v['region']=='') $data[$k]['region'] = $this->str('unknown_region');
		}
		# считаем проценты и записываем для каждого значения
		foreach($data as $k=>$v) {
			$data[$k]['proc'] = 100*$v['kol']/$all_total;
		}
        $total_head = array(
            '',
            $this->str('visitors'),
        );
		$total[] = array(
			$this->str('total_period'),
			$all_total,
		);

		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'region',
					'nowrap'	=> 1,
					'type'		=> 'region',
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
			'count' => count($data),
			'offset' => $offset,
			'limit' => $limit,
		));

		return $ret;
	}
	######################
}

$GLOBALS['stat__stat_regions'] = & Registry::get('TRegions');

?>