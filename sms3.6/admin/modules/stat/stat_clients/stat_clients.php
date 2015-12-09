<?php

require_once (module('stat'));

class TClients extends TStat {

	var $name = 'stat/stat_clients';
	var $clients_table = 'auth_users';

	########################

	function TClients() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'view_table'	=> &$actions['tstat']['view_table'],
			'view_csv'		=> &$actions['tstat']['view_csv'],
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
            'ban' => array('Действие',               'Action',),
			'client'	=> array(
				'Имя пользователя (ID)',
				'Client\'s name (ID)',
			),
			'client_name'	=> array(
				'Имя пользователя',
				'Client\'s name',
			),
			'client_id'	=> array(
				'ID',
				'ID',
			),
			'sessions'	=> array(
				'Сессий',
				'Sessions',
			),
            'confirm1' => array(
                'Вы уверены, что хотите поместить клиента %s в черный список?',
                'Are you sure you want to ban client %s?'
            ),
            'confirm2' => array(
                'Вы уверены, что хотите удалить клиента %s из черного списка?',
                'Are you sure you want to unban client %s?'
            ),
            'ban1' => array(
                'Забанить клиента (все его IP адреса)',
                'Ban client (all him IP addresses)',
            ),
            'ban2' => array(
                'Отбанить клиента (все его IP адреса)',
                'Unban client (all him IP addresses)',
            ),
		);
	}

	######################

	function Show() {
		$this->Init();
		$this->AddStrings($ret);

		if ($this->show!='csv') {
			$this->show = 'table';
		}
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
        if (!empty($search)) $search_state = ' AND cl.login LIKE "'.$search.'"';

		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_clients
			SELECT sess.client_id AS client_id, cl.login AS login, cl.name AS name , cl.lname AS surname
			FROM ".$this->sess_table." AS sess LEFT JOIN ".$this->clients_table." AS cl ON cl.id=sess.client_id
			WHERE sess.client_id!=0 AND sess.robot=0".$search_state);

		$count = sql_getValue("SELECT COUNT(DISTINCT(client_id)) FROM tmp_stat_clients");
        $total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS sess LEFT JOIN ".$this->clients_table." AS cl ON cl.id=sess.client_id WHERE sess.client_id!=0 AND sess.robot=0");
		if ($count)
			$data = sql_getRows("SELECT IF(name<>'',CONCAT(login, ' (', surname, ' ', name, ')'),'n/a') AS name, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, client_id FROM tmp_stat_clients GROUP BY client_id ORDER BY kol DESC LIMIT ".$offset.", ".$limit);
		else
			$data = array();

        $total_head = array(
                '',
                $this->_str('sessions'),
        );
		$total[] = array(
			$this->str('total_period'),
			$total_value,
		);

		// Main Table
        $columns = array(
                array(
                    'header'     => 'client',
                    'nowrap'    => 1,
                    'type'        => 'name',
                ),
                array(
                    'header'     => 'sessions',
                    'align'        => 'right',
                    'width'        => '20%',
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
			'columns'	=> $columns,
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
		echo $this->_str('client_name').';'.$this->_str('sessions')."\n";

		$this->sess_table = 'stat_sessions';
		sql_query("
			CREATE TEMPORARY TABLE tmp_stat_clients
			SELECT sess.client_id AS client_id, cl.name AS name , cl.lname AS surname
			FROM ".$this->sess_table." AS sess LEFT JOIN ".$this->clients_table." AS cl ON cl.id=sess.client_id
			WHERE sess.client_id!=0 AND sess.robot=0");

		$rows = sql_getRows("SELECT IF(name<>'',name,'n/a') AS name, COUNT(*) AS kol FROM tmp_stat_clients GROUP BY client_id ORDER BY kol DESC");

		foreach ($rows as $k=>$v){
			echo
			(isset($v['name'])	? $v['name']	: 0).';'.
			(isset($v['kol'])	? $v['kol']	: 0)."\n";
		}
	}




	######################

	function table_get_name($val, $row) {
		return
			$this->table_get_advanced('client', $row['client_id']).
			$val.' ('.$row['client_id'].')';
	}

    function table_get_ban($val, $row) {
        // Узнаем забаннен этот посетитель или нет
        $res = sql_getRow("SELECT id, ban FROM auth_users WHERE id='".$row['client_id']."'");
        if (!$res['ban']) {
            // Можно забаннить
            return "<input type='image' src='/admin/images/icons/icon.rcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban1')."' onclick='if (confirm(\"".sprintf($this->str('confirm1'), $row['login'])."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editinsertips&id=".$row['client_id']."\"; return false;'>";
        } else {
            // Можно разбаннить
            return "<input type='image' src='/admin/images/icons/icon.gcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban2')."' onclick='if (confirm(\"".sprintf($this->str('confirm2'), $row['login'])."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editdeleteips&id=".$row['client_id']."\"; return false;'>";
        }
    }

	######################
}

$GLOBALS['stat__stat_clients'] = & Registry::get('TClients');

?>