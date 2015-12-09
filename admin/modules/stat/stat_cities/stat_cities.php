<?php

require_once (module('stat'));

class TCities extends TStat {

	var $name = 'stat/stat_cities';

	########################

	function TCities() {
		global $str, $actions;
		TStat::TStat();
		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
		);
		$str[get_class_name($this)] = $str['tstat'] + array(
			'unknown_city' => array(
				'Город неопределен',
				'Uknown city',
			),
			'city'	=> array(
				'Город (Область)',
				'Cities (Region)',
			),
			'visitors'	=> array(
				'Посетители',
				'Visitors',
			),
			### tip ###
			'tip'	=> array(
				'Отчет расскажет вам, в каких городах интересно то, о чем вы хотите рассказать на вашем сайте.',
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
        if (!empty($search)) $search_state = ' AND (city.name LIKE "'.$search.'" OR region.name LIKE "'.$search.'")';

		$q = "SELECT city.name AS city, COUNT(*) AS kol, COUNT(*) as proc, region.name as region
		FROM ".$this->sess_table." AS sess
		LEFT JOIN ".STAT_CITIES_TABLE." AS city ON sess.city = city.id
		LEFT JOIN ".STAT_REGIONS_TABLE." AS region ON city.reg = region.id
		WHERE sess.robot=0 $search_state
		GROUP BY sess.city
		ORDER BY kol DESC
		";

		$data = sql_getRows($q);
		$all_total = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess
        LEFT JOIN ".STAT_CITIES_TABLE." AS city ON sess.city = city.id
        LEFT JOIN ".STAT_REGIONS_TABLE." AS region ON city.reg = region.id
        WHERE sess.robot=0");
		# исправляем пустое имя
		foreach($data as $k=>$v) {
			if($v['city']=='') $data[$k]['city'] = $this->str('unknown_city');
			else $data[$k]['city'] = $v['city'].' ('.$v['region'].')';
		}
		# считаем проценты и записываем для каждого значения
		foreach($data as $k=>$v) {
			$data[$k]['proc'] = 100*$v['kol']/$all_total;
			unset($data[$k]['region']);
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
					'header' 	=> 'city',
					'nowrap'	=> 1,
					'type'		=> 'city',
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

$GLOBALS['stat__stat_cities'] = & Registry::get('TCities');

?>