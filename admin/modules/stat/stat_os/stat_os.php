<?php

require_once (module('stat'));

class TOs extends TStat {

	var $name = 'stat/stat_os';

	########################

	function TOs() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_diag'		=> &$actions['tstat']['view_diag'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'os'	=> array(
				'Операционная система',
				'Operating system',
			),
			'viewed'	=> array(
				'Посетители',
				'Visitors',
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
        if (!empty($search)) $search_state = ' AND ag.os LIKE "'.$search.'"';

		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_os
			SELECT sess.agent_id AS agent_id, ag.os AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
			WHERE sess.robot=0 $search_state");

		$count = sql_getValue("SELECT COUNT(DISTINCT(name)) FROM tmp_stat_os");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id WHERE sess.robot=0");
		$data = sql_getRows("SELECT IF(name<>'',name,'Unknown') AS name, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc FROM tmp_stat_os GROUP BY name ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
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
					'header' 	=> 'os',
					'nowrap'	=> 1,
					'type'		=> 'agent',
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
		echo $this->_str('os').';'.$this->_str('visitors')."\n";

		$rows = sql_getRows("
			SELECT ag.os AS name, COUNT(*) as kol
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
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
			SELECT ag.os AS name
			FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_AGENTS_TABLE." AS ag ON ag.id=sess.agent_id
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

}

$GLOBALS['stat__stat_os'] = & Registry::get('TOs');

?>