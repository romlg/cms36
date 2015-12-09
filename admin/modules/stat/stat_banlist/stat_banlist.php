<?php

require_once (module('stat'));

class TBanlist extends TStat {

	var $name = 'stat/stat_banlist';

	########################

	function TBanlist() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
			'ip'	=> array('IP адрес',				'IP address',),
            'unban' => array('Отбанить',               'Unban',),
            'error' => array('Ошибка!',               'Error!',),
            'saved' => array('Успешно сохранено',     'Save success',),
            'confirm' => array(
                'Вы уверены, что хотите удалить IP %s из черного списка?',
                'Are you sure you want to delete IP %s from banlist?'
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

		return Parse($ret, 'stat/stat.tmpl');
	}

	######################

	function GetTable() {
		global $limit;
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));

        $data = sql_getRows("SELECT id, ip FROM stat_banlist ORDER BY ip LIMIT ".$offset.", ".$limit);
	$count = sql_getValue("SELECT COUNT(DISTINCT(ip)) FROM stat_banlist");

        // Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header' 	=> 'ip',
                    'type'      => 'ip',
				),
                array(
                    'header'     => 'unban',
                    'type'       => 'unban',
                    'align'      => 'center',
                ),
			),
			'data' => $data,
			'total' => $total,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));

		return $ret;
	}

	######################

    function EditInsertIP() {
        $ip = get('ip','');
        if (empty($ip)) return "<script>alert(\"".$this->str('error')."\"); window.parent.location.reload();</script>";

        $res = sql_query("INSERT INTO stat_banlist (`ip`) VALUES('$ip')");
        if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
    }

    function EditDeleteIP() {
        $id = get('id','');
        if (empty($id)) return "<script>alert(\"".$this->str('error')."\"); window.parent.location.reload();</script>";

        $res = sql_query("DELETE FROM stat_banlist WHERE id=".$id);
        if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
    }

    function EditInsertIPS() {
        $id = get('id',''); // id клиента
        if (empty($id)) return "<script>alert(\"".$this->str('error')."\"); window.parent.location.reload();</script>";

        // Находим все ip, с которых клиент когда-либо заходил, и заносим их в черный список
        $ips = sql_getRows("SELECT DISTINCT ip FROM stat_sessions WHERE client_id=$id AND robot=0");
        foreach ($ips as $key=>$val) {
            $res = sql_query("INSERT INTO stat_banlist (`ip`) VALUES('$val')");
            if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        }
        $res = sql_query("UPDATE auth_users SET ban='1' WHERE id=$id");
        if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
    }

    function EditDeleteIPS() {
        $id = get('id',''); // id клиента
        if (empty($id)) return "<script>alert(\"".$this->str('error')."\"); window.parent.location.reload();</script>";

        // Находим все ip, с которых клиент когда-либо заходил, и удаляем их из черного списка
        $ips = sql_getRows("SELECT DISTINCT ip FROM stat_sessions WHERE client_id=$id AND robot=0");
        foreach ($ips as $key=>$val) {
            $res = sql_query("DELETE FROM stat_banlist WHERE ip='$val'");
            if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        }
        $res = sql_query("UPDATE auth_users SET ban='0' WHERE id=$id");
        if (!$res) return "<script>alert('".$this->str('error').": ".mysql_escape_string(sql_getError())."'); window.parent.location.reload();</script>";
        return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
    }
    ######################

	function table_get_unban($val, $row) {
        return "<input type='image' src='/admin/images/icons/icon.gcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('unban')."' onclick='if (confirm(\"".sprintf($this->str('confirm'), long2ip($row['ip']))."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editdeleteip&id=".$row['id']."\"; return false;'>";
	}

    function table_get_ip($val, $row) {
        return long2ip($row['ip']);
    }

	######################
}

$GLOBALS['stat__stat_banlist'] = & Registry::get('TBanlist');

?>