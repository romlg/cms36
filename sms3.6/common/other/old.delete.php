<?

// парсит 3х уровневый ini файл (у файла параметры могут быть представлены в виде массива)
function ini_file($file, $sections = true) {
	$ini = parse_ini_file($file, $sections);
	foreach ($ini as $k => $sec) {
		$vals = array();
		$groups = array();
		foreach ($sec as $p => $v) {
			if (strpos($p, '[') === false) {
				$vals[$p] = $v;
				continue;
			}
			list($key1, $tmp) = explode('[', $p, 2);
			list($key2, $rest) = explode(']', $tmp, 2);
			$groups[$key1][$key2] = $v;
		}
		$ini[$k] = array_merge($vals, $groups);
	}
	return $ini;
}

function my_unbuffered_query($sql) {
	return sql_unbuffered_query($sql);
}

function my_query($sql, $unbuffered = false) {
	return sql_query($sql);
}

function my_error() {
	return mysql_error().'; '.$GLOBALS['last_sql'];
}

function get_all_q() {
	return implode("\n", $GLOBALS['q']);
}




/* $Id: old.delete.php,v 1.1 2009-02-18 13:09:16 konovalova Exp $ */

Copyright('24e267762c8d7a0b85e88dd2086f5330,d08153e1c92d2fea88a9a16ad73497bb'); # @input str of md5('domain')

/*
   make query with joins
	@todo не дописана
*/

$ibfk = array(
	// field -> (link_table => link_field)
	'id'	=> array(
		/*'elem_description'	=> 'pid',
		'elem_document'		=> 'pid',
		'elem_features'		=> 'pid',
		'elem_file'				=> 'pid',
		'elem_gallery'			=> 'pid',
		'elem_image'			=> 'pid',
		'elem_image2'			=> 'pid',
		'elem_meta'				=> 'pid',
		'elem_news'				=> 'pid',
		'elem_product'			=> 'pid',
		'elem_tab'				=> 'pid',*/
	),
);

// Generate SQL query
// $fields = string: "id, pid, name"



function getRow($sql, $file = '', $line = '') {
	$res = cquery($sql, $file, $line);
	if (!$res) {
		return array();
	}

	$values = mysql_fetch_row($res);

	if (!$values) {
		return array();
	}
	for ($i = 0; $i < mysql_num_fields($res); $i++) {
		$meta = mysql_fetch_field($res, $i);
		$row[$meta->table][$meta->name] = $values[$i];
	}
	return $row;
	/*else {
		trigger_error(mysql_error(), E_USER_WARNING);
	}*/
}

function getRows($sql, $file = '', $line = '') {
	$res = cquery($sql, $file, $line);
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
	/*else {
		trigger_error(mysql_error(), E_USER_WARNING);
	}*/
}

function getSqlArray($sql, $field = '', $file = '', $line = '') {
	$res = getSQL($sql, 'number', $file, $line);
	$array = array();
	foreach($res as $row) {
		if (empty($field)) {
			$array[] = array_shift($row);
		}
		else {
			$array[] = $row[$field];
		}
	}
	return $array;
}

// alias for getSqlArray()
function getSqlColumn($sql, $field = '', $file = '', $line = '') {
	return getSqlArray($sql, $field, $file, $line);
}

// получить значение первого поля из первой строки результата выборки
function getSQlvalue($sql, $file = '', $line = ''){
	$res = getSQLrow($sql, $file, $line);
	if($res) return @array_shift($res);
}

// получить строку из базы в виде ассоц. массива
function getSQlrow($sql, $file = '', $line = ''){
	$res = getSQL($sql, 'number', $file, $line);
	if($res) return @array_shift($res);
}

// Get Results Array for Mysql query ($sql); $key - key for array
function getSQL($sql, $key = 'number', $file = '', $line = '') {
	 $res = array();
	 if ($sql) $rows = cquery($sql, $file, $line);
	 if($rows===true) Log_notice('Неправильное использование getSQL для запроса: '.$sql);
	 if ($rows && $rows!==true) {
		  if (empty($key)) {
				$field = mysql_fetch_field($rows);
				$key = $field->name;
		  }
		  while ($row = mysql_fetch_assoc($rows)) {
				if ($key == 'number') $res[] = $row;
				else $res[$row[$key]] = $row;
		  }
		  mysql_free_result($rows);
		  return $res;
	 }
	 return $res;
}

// возвращает данные в формате $key=>$value
function getSQLoptions($sql, $key = 0, $value = 0, $file = '', $line = '') {
	 $res = array();
	 if ($sql) $rows = cquery($sql, $file, $line);
	 if ($rows) {
		  while ($row = mysql_fetch_array($rows)) {
				if (!$key) $res[] = $value ? $row[$value] : $row[0];
				else $res[$row[$key]] = $value ? $row[$value] : $row[0];
		  }
		  mysql_free_result($rows);
		  return $res;
	 }
}

// Для запроса sql INSERT
function insertSQL($table, $fields, $replace = 0, $file = '', $line = '') {
	if (empty($table) || !count($fields)) return false;
	// add slashes
	foreach ($fields as $key => $val) {
		$fields[$key] = (get_magic_quotes_gpc() ? trim($val) : addslashes(trim($val)));
	}
	// generate query
	if($replace) $replace = 'REPLACE'; else $replace = 'INSERT';
	foreach ($fields as $key=>$val)
		if($val!='NULL' && $val!='null')
			$fields[$key] = '\''.$val.'\'';
	$sql = $replace.' '.$table." (".join(", ", array_keys($fields)).") VALUES (".join(', ', $fields).")";
	if (cquery($sql, $file, $line)) return mysql_insert_id();
	return false;
}

// Для запроса sql UPDATE
function updateSQL($table, $fields, $where, $file = '', $line = '') {
	if (empty($table) || !count($fields) || empty($where)) return false;
	$sql = "UPDATE ".$table." SET ";
	$counter = 0;
	foreach ($fields as $key => $val) {
		if ($counter) $sql .= ", ";
		$val = trim($val);
		if (!get_magic_quotes_gpc()) $val = addslashes($val);
		$sql .= $key."='".$val."'";
		$counter = 1;
	}
	$sql.= " WHERE ".$where;
	if (cquery($sql, $file = '', $line = ''))
	return true;
}

// Для запроса sql DELETE
function delSQL($table, $id, $file = '', $line = '') {
	$res = cquery("DELETE FROM ".$table." WHERE id='".$id."'", $file, $line);
	if ($res) return true;
	return false;
}

// Exec SQL and count
function cquery($sql, $file = '', $line = '') {
	global $cquery;
	$time = getmicrotime();
	$res = mysql_unbuffered_query($sql);
	if (mysql_errno() === 1016) {
		if (preg_match("/'(\S*)\.MYD'\. \(errno\: 145\)/", mysql_error(), $m)) {
			mysql_unbuffered_query("REPAIR TABLE ".$m[1]);
			$res = mysql_unbuffered_query($sql);
		}
	}
	$time = round((getmicrotime() - $time) * 1000, 2);
	$sql_log = $sql;
	if (!empty($file) && !empty($line)) {
		$sql_log .= "<br />\n".$file.', '.$line;
	}
	$cquery[] = array(
		'time' => $time,
		'sql' => $sql_log,
	);
	if (!$res || mysql_errno()) {
		$error_str = "SQL Error: \"".$sql."\" <br /> MySQL said: ".mysql_error();
		if (!empty($file) && !empty($line)) {
			$error_str .= "<br />\n".$file.', '.$line;
		}
		if(!defined('ENGINE_TYPE') || ENGINE_TYPE=='site') log_error($error_str, 'mysql_query()');
	}
	return $res;
}

// Для выдачи какого либо параметра из таблицы PARAMS_TABLE
function GetParam($name) {
	global $params;
	if (!$name) return;
	if (!$params) {
		$params = getSQL("SELECT name, value FROM ".PARAMS_TABLE, 'name');
	}
	return $params[$name];
}

// Для выдачи какого либо сообщения из таблицы MESSAGES_TABLE
function GetMessage($name) {
	return getSQLvalue("SELECT IF(text_".lang()."<>'', text_".lang().", text_en) as value FROM ".MESSAGES_TABLE." WHERE IF(grp_name<>'', CONCAT(grp_name,'_',name), name)='$name'");
/*static $messages;
	if (!$name) return;
	if (!isset($messages[$type])) {
		 $res = getSQL("SELECT name, IF(text_".lang()."<>'', text_".lang().", text_en) as value  FROM ".MESSAGES_TABLE." WHERE grp_name='$type'", 'name');
		 foreach($res AS $mes)
			$messages[$type][$mes['name']] = $mes['value'];
	}
//pr($messages);
	return $messages[$type][$name];
*/
}


function GetLock($table, $id) {
	global $user;
	# check locked
	$row = TTable::GetFetchedRow("select locked_till, locked_by from ".$table." where id=".$id);
	if ($row['locked_till'] > time() && $user['id'] != $row['locked_by']) {
		$locked_name = TTable::GetFetchedValue("select if(length(name)>0,name,login) from users where id=".$row['locked_by']);
		return sprintf(str('locked'), date('Y-m-d H:i', $row['locked_till']), $locked_name);
	}
}

####################

function Lock($table, $id) {
	if (!$id) {
		return false;
	}

	# check locked
	$check = GetLock($table, $id);
	if ($check) {
		cHeader('', '', str('locked_head'));
		echo '<br /><span class="check">'.$check.'</span><br /><br /><br /><br />'."\n";
		cFooter();
		return true;
	}

	session_start();
	$_SESSION['lock_table'] = $table;
	$_SESSION['lock_id'] = $id;
	session_write_close();

	# lock
	mysql_unbuffered_query("update ".$table." set locked_till=unix_timestamp(date_add(now(), interval ".$GLOBALS['params']->Get('lock_time')." minute)), locked_by='".$GLOBALS['user']['id']."' where id=".$id) or die(mysql_error());
	return false;
}

####################

function Unlock() {
	# unlock
	session_start();
	if ($_SESSION['lock_table'] && $_SESSION['lock_id']) {
		mysql_unbuffered_query("update ".$_SESSION['lock_table']." set locked_till=unix_timestamp() where id=".$_SESSION['lock_id']);
		unset($_SESSION['lock_table'], $_SESSION['lock_id']);
	}
	session_write_close();
}

####################

function ControlLink($href, $image, $text, $title = '') {
	return "<a class=control href='".$href."' title='".$title."'><img src='".BASE."images/".$image."' width=16 height=16 border=0 hspace=4 vspace=4 align=absmiddle>".$text."</a>";
}

####################

/*
Orig (c) alex at bartl dot net (29-Nov-2002 05:25)

function SendMail($From, $To, $Subject, $Text, $Html, $Charset, $AttmFiles, $AttmImages)
$From      ... sender name and mail address like "My Name <my@address.com>"
$To        ... recipient name and mail address like "Your Name <your@address.com>"
$Subject   ... subject of the mail like "This is my first testmail"
$Text      ... text version of the mail
$Html      ... html version of the mail
$Charset   ... iso-8859-1
$AttmFiles ... array containing the filenames to attach like array("file1","file2")
$AttmImages ... array containing the filenames to attach like array("file1","file2")
*/
DEFINE ('NEWLINE', "\n");
function SendMail($From, $To, $Subject, $Text, $Html='', $Charset='iso-8859-1', $AttmFiles=array(), $AttmImages=array()) {
	$OB = "----=_OuterBoundary_000";
	$IB = "----=_InnerBoundery_001";

	$headers =
	"MIME-Version: 1.0".NEWLINE
	."From: $From".NEWLINE
	."Reply-To: $From>".NEWLINE
	."X-Priority: 3".NEWLINE
	."X-MSMail-Priority: Normal".NEWLINE
	."X-Mailer: PHP Mailer".NEWLINE
	."Content-Type: multipart/mixed;".NEWLINE."\tboundary=\"".$OB."\"".NEWLINE.NEWLINE;

	//Messages start with text/html alternatives in OB
	$Msg = "This is a multi-part message in MIME format.".NEWLINE;
	$Msg.= NEWLINE."--".$OB.NEWLINE;
	$Msg.= "Content-Type: multipart/alternative;".NEWLINE."\tboundary=\"".$IB."\"".NEWLINE.NEWLINE;

	//plaintext section
	$Msg.= NEWLINE."--".$IB.NEWLINE;
	$Msg.= "Content-Type: text/plain;".NEWLINE."\tcharset=\"$Charset\"".NEWLINE;
	$Msg.= "Content-Transfer-Encoding: quoted-printable".NEWLINE.NEWLINE;

	// plaintext goes here
	$Msg.= $Text.NEWLINE.NEWLINE;

	// html section
	if ($Html) {
		$Msg.= NEWLINE."--".$IB.NEWLINE;
		$Msg.= "Content-Type: text/html;".NEWLINE."\tcharset=\"$Charset\"".NEWLINE;
		$Msg.= "Content-Transfer-Encoding: base64".NEWLINE.NEWLINE;

		// html goes here
		$Msg.= chunk_split(base64_encode($Html)).NEWLINE.NEWLINE;
	}

	// end of IB
	$Msg.= NEWLINE."--".$IB."--".NEWLINE;

	// attachments
	if ($AttmFiles) {
		foreach ($AttmFiles as $AttmFile) {
			if (!$AttmFile) continue;

			$AttmFile = FILES_PATH.'/'.$AttmFile;
			$patharray = explode("/", $AttmFile);
			$FileName = end($patharray);

			//$mime = mime_content_type($FileName);
			$mime = 'application/octetstream';

			$fd = fopen($AttmFile, "rb");
			$FileContent = fread($fd, filesize($AttmFile));
			fclose($fd);
			$FileContent = chunk_split(base64_encode($FileContent));

			$Msg.= NEWLINE."--".$OB.NEWLINE;
			$Msg.= "Content-Type: ".$mime.";".NEWLINE."\tname=\"".$FileName."\"".NEWLINE;
			$Msg.= "Content-Transfer-Encoding: base64".NEWLINE;
			$Msg.= "Content-Disposition: attachment;".NEWLINE."\tfilename=\"".$FileName."\"".NEWLINE.NEWLINE;
			$Msg.= $FileContent;
			$Msg.= NEWLINE.NEWLINE;
		}
	}

	// attachments (images)
	if ($AttmImages) {
		foreach ($AttmImages as $AttmFile) {
			if (!is_file($AttmFile)) continue;

			$patharray = explode("/", $AttmFile);
			$FileName = end($patharray);

			$size = GetImageSize($AttmFile);
			$mime = $size['mime'];

			$fd = fopen($AttmFile, "rb");
			$FileContent = fread($fd, filesize($AttmFile));
			fclose($fd);
			$FileContent = chunk_split(base64_encode($FileContent));

			$Msg.= NEWLINE."--".$OB.NEWLINE;
			$Msg.= "Content-Type: $mime;".NEWLINE."\tname=\"$FileName\"".NEWLINE;
			$Msg.= "Content-Transfer-Encoding: base64".NEWLINE;
			$Msg.= "Content-ID: <$FileName>".NEWLINE;
			$Msg.= $FileContent;
			$Msg.= NEWLINE.NEWLINE;
		}
	}

	//message ends
	$Msg.= NEWLINE."--".$OB."--".NEWLINE;

	//syslog(LOG_INFO, "Mail: Message sent to $ToName <$To>");
	return mail($To, $Subject, $Msg, $headers, '-fpostmaster@'.$_SERVER['HTTP_HOST']);
}


?>