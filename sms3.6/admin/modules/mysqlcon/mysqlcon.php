<?php

class TMysqlCon extends TTable {

	var $name = 'mysqlcon';
	var $rights = 0;
	var $domain_selector = false;
    var $selector = false;

	######################

	function TMysqlCon() {
		TTable::TTable();

		$GLOBALS['str']['tmysqlcon']	= array(
			'title'		=> array(
				'MySQL: консоль',
				'MySQL: консоль',
			),
			'head'		=> array(
				'MySQL: консоль',
				'MySQL: консоль',
			),
			'result'	=> array(
				'Результат выполнения запроса:',
				'Результат выполнения запроса:',
			),
			'success'	=> array(
				'Запрос выполнен успешно',
				'Запрос выполнен успешно',
			),
			'exec'		=> array(
				'Выполнить',
				'Выполнить',
			),
		);

        unset($GLOBALS['actions']);
	}

	######################

	function Show() {
		if (!$this->allow(ALLOW_SELECT)) return $this->AD();
		$sql = !empty($GLOBALS['_POST']['sql']) ? $GLOBALS['_POST']['sql'] : '';
		if (get_magic_quotes_gpc()) $sql = stripslashes($sql);
		$err = '';

		if ($sql) {
			$time = time64();
			set_time_limit(0);
			$res = $this->ExecSql($sql);
			$time = sprintf("%1.1f", (time64() - $time) * 1000);
		}

		# SQL FORM

		//$ret = PageHeader($this->str('head'), 'icons/mysqlcon.gif');
		$ret = "<h3>".$this->str('head')."</h3>";

		$ret.= "
		<form method=post name=sql_form id=sql_form action=''>
		<input type=hidden name=page value=".$this->name.">
		<input type=hidden name=do value=show>
		<textarea class=fForm name=sql cols=70 rows=10 wrap=off style='width: 100%'>".stripslashes($sql)."</textarea>
		<div align=right class=note>post_max_size=".ini_get('post_max_size')."</div>
		<a class='button' href='javascript:document.getElementById(\"sql_form\").submit();'>".$this->str('exec')."</a>
		</form>
		";

		if (isset($res)) {
			$count = is_resource($res) ? @mysql_num_rows($res) : 0;
			$ret.= $this->str('result')." (Time = <b>".$time." ms</b>; Count = <b>".$count."</b>)<br>\n";
			if (false && $count) {
				$ret.= TTable::Show(array(
					'sql'		=> $sql,
					'noedit'	=> true,
				));
			} else {
				$result_str = $this->GetSqlResult($res);
				if (!$result_str) {
					$success = $this->str('success');
					$result_str = str_repeat("-", strlen($success))."\n".$success."\n".str_repeat("-", strlen($success));
					$result_str.= "\nAffected rows: ".mysql_affected_rows();
				}
                $ret .= $result_str;
			}
		}
		//$ret.= PageFooter();
		return $ret;
	}

	######################

	function Optimize() {
		if (!$this->allow(ALLOW_UPDATE)) return $this->AD();
		if ($GLOBALS['_GET']['table']) $GLOBALS['_POST']['sql'] = "OPTIMIZE TABLE `".$GLOBALS['_GET']['table']."`";
		return $this->Show();
	}

	######################

	function ExecSql($sql, $crlf = "\r\n") {
		$res = false;
		if (!$this->allow(ALLOW_DELETE)) die($this->AD()); // delete is a full access
		if (is_array($sql)) $pieces = $sql;
		else {
			# define crlf
			$pos = strpos($sql, ";");
			if ($pos !== false && strlen($sql) > $pos+1 && $sql[$pos+1] == "\n") $crlf = "\n";
			$sql = preg_replace("/^#.*/m", $crlf, $sql);
			$pieces = explode(";".$crlf, $sql);
		}
		$GLOBALS['affected_rows'] = 0;
		for ($i=0; $i<sizeof($pieces); $i++) {
			$pieces[$i] = trim($pieces[$i]);
			if ($pieces[$i]) {
				$res = sql_query($pieces[$i]);
				if ($res === FALSE) {
					if (strlen($pieces[$i]) > 1024) $pieces[$i] = 'piece #'.$i;
					$GLOBALS['last_sql_getError'] = "Error in query:\n".$pieces[$i]."\n\nmysql said:\n (".sql_getErrNo().") ".sql_getError();
					break;
				}
			}
		}
		return $res;
	}

	######################

	function GetSqlResult($res) {
		if ($res === FALSE) return $GLOBALS['last_sql_getError'];
		$counter = 0;
		$ret = "";
		while ($row = @mysql_fetch_assoc($res)) {
			if (!$counter) {
				$ret .= "<table class='ajax_table_main'>";
                $ret .= "<tr class='ajax_table_header_row'>";
                foreach (array_keys($row) as $v) $ret .= "<th class='ajax_table_header_cell'>{$v}</th>";
                $ret .= "</tr>";
			}
            $ret .= "<tr class='ajax_table_row'>";
            foreach ($row as $v) $ret .= "<td class='ajax_table_cell'>{$v}</td>";
            $ret .= "</tr>";
			$counter++;
		}
        $ret .= "</table>";
		return $ret;
	}

	######################

	function Info() {
		return array(
			'version'	=> get_revision('$Revision: 1.1 $'),
			'rights'	=> 0,
			'checked'	=> 0,
			'disabled'	=> 0,
			'type'		=> 'checkbox',
		);
	}

	######################
}

$GLOBALS['mysqlcon'] =  & Registry::get('TMysqlCon');
?>