<?php

require_once (module('stat'));
require_once (elem_inc('csv_tools'));

class TExport extends TStat {

	var $name = 'stat/stat_export';

	########################

	function TExport() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array();

		$str[get_class_name($this)] = $str['tstat'] + array(
			'date'		=> array('Дата',		'Date'),
			'time'		=> array('Время',		'Time'),
			'ip'		=> array('IP',			'IP'),
			'address1'	=> array('Адрес запрошенной страницы',	'Request page'),
			'address2'	=> array('Адрес ссылаемой страницы',	'Reference page'),
            'host1'    => array('Хост запрошенный',    'Request page'),
            'host2'    => array('Хост ссылаемый',    'Reference page'),
			'agent'		=> array('Агент',	'Agent'),
			'country'	=> array('Страна',	'Country'),
			'search'	=> array('Поисковый запрос',	'Search request'),
            'client'    => array('Клиент',    'Client'),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

        $this->show = 'csv';
        $ret = @call_user_func(array(&$this, 'Get'.$this->show));

        $ret['navig'] = $this->NavigForm(array(
			'hidden'	=> array('show' => $this->show),
		));

		return Parse($ret, 'stat/stat.tmpl');
	}

	######################

    function GetCSVFile() {
        // имя файла для сохранения
        $from_date = get('from_date','','g');
        $to_date = get('to_date','','g');
        $filename = $this->name.'_'.$from_date.' - '.$to_date.'.csv';

        $data = $this->GetCSVData();

		$GLOBALS['gzip'] = false;
        ob_end_clean();
        $zip= new ss_zip('',6);
        $zip->add_data($filename, $data);
        $zip->save($filename.".zip",'d');
        exit;
    }

	function GetCSVData() {
		// заголовки
		$data = $this->_str('date').';'.$this->_str('time').';'.$this->_str('ip').';'.$this->_str('host1').';'.$this->_str('address1').';'.$this->_str('host2').';'.$this->_str('address2').';'.$this->_str('agent').';'.$this->_str('country').';'.$this->_str('search').';'.$this->_str('client')."\n";


        $sql = "SELECT temp.time, IF(s.ip<0, INET_NTOA(s.ip+4294967296), INET_NTOA(s.ip)) as ip, p1.uri as address1, p2.uri as address2, p2.search_ph as search, p1.host as host1, p2.host as host2, a.name as agent, c.name_".lang()." as country, CONCAT(auth.login,' <',auth.lname,' ',auth.name,' ',auth.tname,'> ') as client FROM ".$this->log_table. " AS temp
        LEFT JOIN stat_sessions AS s ON s.sess_id=temp.sess_id
        LEFT JOIN stat_pages AS p1 ON p1.id=temp.page_id
        LEFT JOIN stat_pages AS p2 ON p2.id=temp.ref_id
        LEFT JOIN stat_agents AS a ON a.id=s.agent_id
        LEFT JOIN auth_users AS auth ON auth.id=s.client_id
        LEFT JOIN ".STAT_COUNTRIES_TABLE." AS c ON c.country_id=s.country";
        $rows = sql_getRows($sql);

        foreach ($rows as $k=>$v){
			$data .=
			(isset($v['time'])	? date("d-m-Y", $v['time'])	: 0).';'.
			(isset($v['time'])	? date("H:i:s", $v['time'])	: 0).';'.
            (isset($v['ip'])    ? $v['ip'] : 0).';'.
            (isset($v['host1'])    ? $v['host1'] : '').';'.
            (isset($v['address1'])    ? $v['address1'] : '').';'.
            (isset($v['host2'])    ? $v['host2'] : '').';'.
            (isset($v['address2'])    ? $v['address2'] : '').';'.
            (isset($v['agent'])    ? $v['agent'] : '').';'.
            (isset($v['country'])    ? $v['country'] : '').';'.
            (isset($v['search'])    ? $v['search'] : '').';'.
            (isset($v['client'])    ? $v['client'] : '').
            "\n";
		}
        return $data;
	}

}

$GLOBALS['stat__stat_export'] = &Registry::get('TExport');

?>