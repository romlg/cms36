<?php

require_once (module('stat'));

class TIp extends TStat {

	var $name = 'stat/stat_ip';

	// Whois Servers
	var $whois = array(
		'whois.ripe.net',
		'whois.arin.net',
		'whois.apnic.net',
//		'whois.lapnic.net'
	);

	########################

	function TIp() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
            'ban' => array('Действие',               'Action',),
			'ips'	=> array(
				'IP-адреса',
				'IP-addresses',
			),
			'viewed'	=> array(
				'Просмотров',
				'Viewed',
			),
			'whois_server'	=> array(
				'Whois-сервер',
				'Whois-server',
			),
			'showwhois'	=> array(
				'Whois-сервер',
				'Whois-server',
			),
			'e_whois'	=> array(
				'Whois Сервер не доступен!',
				'Whois-server is not available!',
			),
            'confirm1' => array(
                'Вы уверены, что хотите поместить IP %s в черный список?',
                'Are you sure you want to ban IP %s?'
            ),
            'confirm2' => array(
                'Вы уверены, что хотите удалить IP %s из черного списка?',
                'Are you sure you want to unban IP %s?'
            ),
            'ban1' => array(
                'Забанить',
                'Ban',
            ),
            'ban2' => array(
                'Отбанить',
                'Unban',
            ),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);
		$v = sql_getValue('SELECT value FROM stat_settings WHERE name = "ip"');
        $this->ip_more = unserialize(is_string($v)?$v:"");
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
        if (!empty($search)) $search_state = ' AND (INET_NTOA(ip+4294967296) LIKE "'.$search.'" OR INET_NTOA(ip) LIKE "'.$search.'")';

        $count = sql_getValue("SELECT COUNT(DISTINCT(ip)) FROM ".$this->sess_table." WHERE robot=0 $search_state");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0");
		$data = sql_getRows("
			SELECT ip, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc FROM ".$this->sess_table."
			WHERE robot=0 $search_state GROUP BY ip ORDER BY kol DESC, time_last DESC LIMIT ".$offset.", ".$limit);

        $total_head = array(
                '',
                $this->_str('viewed'),
        );
        $total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
        $columns = array(
            array(
                'header'    => 'ips',
                'nowrap'    => 1,
                'type'        => 'ip',
                'flags'     => FLAG_SEARCH|FLAG_SORT,
            ),
            array(
                'header'     => 'viewed',
                'align'        => 'right',
                'width'        => '20%',
                'flags'     => FLAG_SORT,
            ),
            array(
                'header'     => 'percent',
                'align'        => 'right',
                'width'        => '50%',
                'type'        => 'graph',
            ),
        );
        if (isset($GLOBALS['_stat']['stat/stat_banlist']))
            $columns[] = array(
                'header'     => 'ban',
                'type'       => 'ban',
                'align'      => 'center',
            );

		$ret['table'] = $this->stat_table(array(
            'columns' => $columns,
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
		echo $this->_str('ips').';'.$this->_str('viewed')."\n";

		$rows = sql_getRows("
			SELECT IF(ip<0, INET_NTOA(ip+4294967296), INET_NTOA(ip)) as name, COUNT(*) as kol
			FROM ".$this->sess_table."
			WHERE robot=0 GROUP BY ip ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
	}

	######################

	function ShowWhois() {
		$ip		= get('whois_ip', '', 'g');
		$ip		= long2ip($ip); //real ip
		$whois	= get('whois_server', $this->whois[0], 'g');

		$this->AddStrings($data);

 		$data['whois_options'] = $this->GetArrayOptions($this->whois, $whois);
		$data['ip'] = $ip;
		if (!$ip) $data['error']['res'] = $this->str('e_whois');
		elseif (!in_array($whois, $this->whois)) $data['error']['res'] = $this->str('e_whois');
		else {
			$result = `whois -h $whois $ip`;
			if ($result)
				$data['whois']['res'] = $result;
			else
				$data['error']['res'] = $this->str('e_whois');
		}
		return Parse($data, 'stat/stat_ip/stat_whois.tmpl');
	}

	######################

	function table_get_ip($val, $row) {
//		$ced = "window.open('dialog.php?page=stat/stat_ip&do=showwhois&whois_ip=".$val."', 'whois', 'width=500, height=400, resizable=1, status=1, scrollbars=0')";
		$ced = "window.open('https://www.nic.ru/whois/?ip=".long2ip($val)."', 'whois', 'width=500, height=400, resizable=1, status=1, scrollbars=1')";
		return
			$this->table_get_advanced('ip', long2ip($val)).
			'<a href="#" onclick="'.$ced.'; return false;">'.long2ip($val).'</a> '.(isset($this->ip_more[long2ip($val)])?'( '.$this->ip_more[long2ip($val)].' )':'');
	}

    function table_get_ban($val, $row) {
        // Узнаем забаннен этот ip или нет
        $res = sql_getValue("SELECT id FROM stat_banlist WHERE ip='".$row['ip']."'");
        if (!$res) {
            // Можно забаннить
            return "<input type='image' src='/admin/images/icons/icon.rcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban1')."' onclick='if (confirm(\"".sprintf($this->str('confirm1'), long2ip($row['ip']))."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editinsertip&ip=".$row['ip']."\"; return false;'>";
        } else {
            // Можно разбаннить
            return "<input type='image' src='/admin/images/icons/icon.gcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban2')."' onclick='if (confirm(\"".sprintf($this->str('confirm2'), long2ip($row['ip']))."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editdeleteip&id=".$res."\"; return false;'>";
        }
    }

	######################
}

$GLOBALS['stat__stat_ip'] = & Registry::get('TIp');

?>