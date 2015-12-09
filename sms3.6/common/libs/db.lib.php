<?
//---------------------------------------------------------------------------------------
//глобальные функции для работы с бд
//---------------------------------------------------------------------------------------
function select($table, $fields, $join = '', $where = '') {
	global $ibfk;

	// joins
	if (!empty($join) && is_string($join)) {
		foreach (explode(', ', $join) as $val) {
			$j[] = $val;
			//$fields[$val] = '*';
		}
		$join = $j;
	}

	$select = explode(', ', $fields);

	// prepare joins
	$j = array();
	if (!empty($join) && is_array($join)) {
		foreach ($join as $key => $val) {
			if (!empty($ibfk['id'][$val])) {
				$j[$key] = "LEFT JOIN ".$val." ON ".$val.".".$ibfk['id'][$val]."=".$table.".id";
			}
			else {
				$j[$key] = "LEFT JOIN ".$val." ON ".$val.".pid=".$table.".id";
			}
		}
	}
	$join = $j;

	// prepare sql
	$sql = sprintf('SELECT %s FROM %s %s %s', join(', ', $select), $table, join(' ', $join), !empty($where) ? 'WHERE '.$where : '');

	return $sql;
}
//---------------------------------------------------------------------------------------
function struct($s, $table='tree') {

	if (isset($s[0])) $s = current($s);

	if (empty($s)) {
		return array();
	}
	if (isset($s[$table]) && is_array($s[$table])) {
		$s = array_merge($s[$table], $s);
		unset($s[$table]);
	}
	return $s;
}
//---------------------------------------------------------------------------------------
function sql_unbuffered_query($sql, $file = '', $line = ''){
	return $GLOBALS['db']->query($sql, $file, $line, true);
}
//---------------------------------------------------------------------------------------
function sql_query($sql, $file = '', $line = '', $unbuffered = false){
	return $GLOBALS['db']->query($sql, $file, $line, $unbuffered);
}
//---------------------------------------------------------------------------------------
function sql_getValue($sql, $file = '', $line = ''){
	return $GLOBALS['db']->getValue($sql, $file, $line);
}

//---------------------------------------------------------------------------------------

function sql_getRow($sql, $file = '', $line = ''){
	return $GLOBALS['db']->getRow($sql, $file, $line);
}

//---------------------------------------------------------------------------------------

function sql_getRows($sql, $use_key = false, $file = '', $line = '', $type = true){
	return $GLOBALS['db']->getRows($sql, $use_key, $file, $line, $type);
}

//---------------------------------------------------------------------------------------

function sql_getColumn($sql, $file = '', $line = '', $type = true){
	$rows = $GLOBALS['db']->getRows($sql, false, $file, $line, $type);
	$column = array();
	foreach ($rows as $k=>$v){
		$column[] = $v;
	}
	return $column;
}

//---------------------------------------------------------------------------------------

function sql_getTableRows($sql, $file = '', $line = '') {
	$res = $GLOBALS['db']->query($sql, $file, $line, true);
	if ($res) {
		$rows = array();
		$metas = array();
		for ($i = 0; $i < mysql_num_fields($res); $i++) {
			$metas[$i] = mysql_fetch_field($res, $i);
		}
		while($values = mysql_fetch_row($res)) {
         $row = array();
			foreach ($metas as $i => $meta) {
				$row[$meta->table][$meta->name] = $values[$i];
			}
			$rows[] = $row;
		}
		mysql_free_result($res);
		return $rows;
	}
}

//---------------------------------------------------------------------------------------

function sql_insert($table, $fields, $replace = 0, $file = '', $line = '') {
	if (empty($table) || !count($fields)) return false;
	// add slashes
	foreach ($fields as $key => $val) {
		// если $val - массив
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		$fields[$key] = trim($val);
	}
	// generate query
	if($replace) $replace = 'REPLACE'; else $replace = 'INSERT';
	foreach ($fields as $key=>$val)
		if($val!='NULL' && $val!='null')
			$fields[$key] = "'".e($val)."'";
	$sql = $replace.' '.$table." (".join(", ", array_keys($fields)).") VALUES (".join(', ', $fields).")";
	$res = $GLOBALS['db']->query($sql, $file, $line);
	if (is_numeric($res)) {
		return $res;
	} else if ($res === true) {
		return $GLOBALS['db']->getLastId();
	} else {
		return $GLOBALS['db']->getError();
	}
}

//---------------------------------------------------------------------------------------
// Для запроса sql UPDATE
function sql_updateId($table, $fields, $id, $id_field = 'id', $file = '', $line = '') {
	if (empty($table) || !count($fields)) return false;
	$sql = "UPDATE ".$table." SET ";
	$counter = 0;
	foreach ($fields as $key => $val) {
		if ($counter) $sql .= ", ";
		// если $val - массив
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		$val = trim($val);
		if ($val !== 'NULL') {
			$sql .= $key."='".e($val)."'";
		}
		else {
			$sql .= $key."=".$val;
		}
		$counter = 1;
	}
	$sql.= " WHERE ".$id_field."=".$id;
	$GLOBALS['db']->query($sql, $file, $line);
	$err = $GLOBALS['db']->getError();
	if (!empty($err)) {
		return $err;
	} else {
		return (int)$id;
	}
}
//---------------------------------------------------------------------------------------
function sql_update($table, $fields, $where, $file = '', $line = '') {
	if (empty($table) || !count($fields)) return false;
	$sql = "UPDATE ".$table." SET ";
	$counter = 0;
	foreach ($fields as $key => $val) {
		if ($counter) $sql .= ", ";
		// если $val - массив
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		$val = trim($val);
		if ($val !== 'NULL') {
			$sql .= $key."='".e($val)."'";
		}
		else {
			$sql .= $key."=".$val;
		}
		$counter = 1;
	}
	$sql.= " WHERE ".$where;
	if ($GLOBALS['db']->query($sql, $file, $line)) {
		return true;
	}
	return $GLOBALS['db']->getError();
}

//---------------------------------------------------------------------------------------
// Для запроса sql DELETE

function sql_delete($table, $id, $file = '', $line = '') {
	$res = $GLOBALS['db']->query("DELETE FROM ".$table." WHERE id='".$id."'", $file, $line);
	if ($res) return true;
	return false;
}

//---------------------------------------------------------------------------------------

function sql_getLastId() {
	return $GLOBALS['db']->getLastId();
}

//---------------------------------------------------------------------------------------
function sql_getError() {
	return $GLOBALS['db']->getError();
}
//---------------------------------------------------------------------------------------
function sql_getErrNo() {
	return $GLOBALS['db']->getErrNo();
}
//---------------------------------------------------------------------------------------

?>