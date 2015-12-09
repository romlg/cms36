<?
/*****************************************************************************
*
* tbl_dump.php based on phpMyAdmin code
* rewritten by Dmitriy@rusoft.ru
* updated 2003-11-05
*
* изменения by xfox@rusoft.ru 2006-08:
* + дамп работает как подключаемый файл в движке
* + вывод кешируется в файл
* + соединение поддерживается передачей символов раз в секунду
* - исключен формат .zip
* + файл не отдается пользователю сразу, он сбрасывается на сервер
*   и js возвращает ссылку для его загрузки
*****************************************************************************/

/**
 * Get the variables sent or posted to this script and a core script
 */
$GLOBALS['ob_mode'] = 1;
$GLOBALS['gzip'] = false;

# check rights
if (!is_root() && $user['rights']['mysqldump'] != 15) die("Access denied.");

define("EXT_INC_COL", 100); # max allowed count of extended insert syntax

$db = $_POST["db"];
$GLOBALS['asfile'] = $asfile = $_POST["as"] != "data";
header('Content-Encoding: none');
ob_end_clean();

echo "<html><head><title>Processing database</title></head>
<body>Dumping: \n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r";
flush();


function first_replace($from, $to, $str) {
    $pos = strpos($str, $from);
    $start = substr($str, 0, $pos);
    $end = substr($str, $pos + strlen($from),(strlen($str) - $pos - strlen($from)));
    $result = $start.$to.$end;
    return $result;
};

function PMA_getTableDef($db, $table, $crlf) {
	$schema_create = "";
	if ($GLOBALS["_POST"]["drop"]==1) {
		$schema_create .= "DROP TABLE IF EXISTS " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"]) . ";" . $crlf;
	}

	// Whether to quote table and fields names or not
	if ($GLOBALS["_POST"]["use_backquotes"]) {
		sql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
	} else {
		sql_query("SET SQL_QUOTE_SHOW_CREATE = 0");
	}
	$result = sql_query("SHOW CREATE TABLE " . PMA_backquote($db) . "." . PMA_backquote($table));

	if ($result != FALSE && mysql_num_rows($result) > 0) {
		$tmpres		= mysql_fetch_array($result);
		$schema_create .= str_replace("\n", $crlf, first_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$tmpres[1]));
	}
	mysql_free_result($result);
	return $schema_create;
} // end of the "PMA_getTableDef()" function

/*
php >= 4.0.5 only : get the content of $table as a series of INSERT/UPDATE/REPLACE statements.
*/
function PMA_getTableContentFast($db, $table, $add_query = "", $handler, $exptype) {
	global $current_row, $rows_cnt, $crlf;

	// Поулучаем число записей, т.к mysql_num_rows не поддреживается с unbuffed_query
	$query = "SELECT COUNT(*) FROM " . PMA_backquote($db) . "." . PMA_backquote($table) . $add_query;
	$r = mysql_unbuffered_query($query);
	$rows_cnt = mysql_fetch_row($r);
	$rows_cnt = $rows_cnt[0];

	mysql_free_result($r);
	$local_query = "SELECT * FROM " . PMA_backquote($db) . "." . PMA_backquote($table) . $add_query;
	$result	  = mysql_unbuffered_query($local_query) or Die($local_query);
	if ($result != FALSE) {
		$fields_cnt = mysql_num_fields($result);

		//$rows_cnt   = mysql_num_rows($result);

        // Get field information
        $fields_meta = PMA_DBI_get_fields_meta($result);
        $field_flags = array();
        for ($j = 0; $j < $fields_cnt; $j++) {
            $field_flags[$j] = PMA_DBI_field_flags($result, $j);
        }

		// Checks whether the field is an integer or not
		for ($j = 0; $j < $fields_cnt; $j++) {
			$field_set[$j] = PMA_backquote(mysql_field_name($result, $j), $GLOBALS["_POST"]["use_backquotes"]);
			$type = mysql_field_type($result, $j);
			if (strpos($type, "int") !== false) {
				$field_num[$j] = TRUE;
			} else {
				$field_num[$j] = FALSE;
			}
		} // end for

        switch ($exptype) {
        	case "einsert":
        		if (isset($GLOBALS["_POST"]["showcolumns"])) {
        			$fields		= implode(", ", $field_set);
        			$schema_insert = "INSERT INTO " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"])  . " (" . $fields . ") VALUES (";
        		} else {
        			$schema_insert =  "INSERT INTO " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"]) . " VALUES (";
        		}
        		$mode = 'ins';
        	break;
        	case "ereplace":
        		if (isset($GLOBALS["_POST"]["showcolumns"])) {
        			$fields		= implode(", ", $field_set);
        			$schema_insert = "REPLACE INTO " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"])  . " (" . $fields . ") VALUES (";
        		} else {
        			$schema_insert =  "REPLACE INTO " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"]) . " VALUES (";
        		}
        		$mode = 'ins';
        	break;
        	case "eupdate":
        		$schema_insert = "UPDATE " . PMA_backquote($table, $GLOBALS["_POST"]["use_backquotes"]) . " SET ";
        		$mode = 'upd';
        		$pkeys = array();
        		$npkeys = array();
        		for ($j = 0; $j < $fields_cnt; $j++)
        		  if (strpos($field_flags[$j],'primary_key')!==false)
        		      $pkeys[$j] = $field_set[$j];
        		  else
        		      $npkeys[$j] = $field_set[$j];

        		/*///////////////////////////////////////////////////////////////////////////
        		if (count($pkeys)<1)
        		{
            		pr($pkeys);
            		pr($npkeys);
            		pr($field_flags);
            		die();
        		}
                */
        	break;
        }

		// Sets the scheme
		$search	= array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
		$replace = array("\\0", "\\n", "\\r", "\\Z");
		$current_row = 0;

		// loic1: send a fake header to bypass browser timeout if data
		//		are bufferized - part 1
		if (!empty($GLOBALS["ob_mode"])
			|| (isset($GLOBALS["_POST"]["zip"]) || isset($GLOBALS["_POST"]["bzip"]) || isset($GLOBALS["_POST"]["gzip"]))) {
			$time0	= time();
		}
		while ($row = mysql_fetch_row($result)) {
			$current_row++;
			for ($j = 0; $j < $fields_cnt; $j++) {
				if (!isset($row[$j])) {
					$values[]	 = "NULL";
				} else if ($row[$j] == "0" || $row[$j] != "") {
					// a number
					if ($field_num[$j]) {
						$values[] = $row[$j];
					}
					// a string
					else {
						$values[] = "'" . str_replace($search, $replace, PMA_sqlAddslashes($row[$j])) . "'";
					}
				} else {
					$values[] = "''";
				} // end if
			} // end for


			if ($exptype!='eupdate') {

    			// Extended inserts case
    			if (isset($GLOBALS["_POST"]["extended_ins"])) {
    				if ($current_row == 1 || !($current_row % EXT_INC_COL)) {
    					$insert_line  = $schema_insert . implode(", ", $values) . ")";
    				} else {
    					$insert_line  = "(" . implode(", ", $values) . ")";
    				}
    			}
    			// Other inserts case
    			else {
    				$insert_line = $schema_insert . implode(", ", $values) . ")";
    			}

			} else {
			    $tmpnp = array();
			    $tmpp = array();
			    foreach ($npkeys as $k=>$np)
			        $tmpnp[] = $np.' = '.$values[$k];
			    foreach ($pkeys as $k=>$pp)
			        $tmpp[] = $pp.' = '.$values[$k];
			    $insert_line=$schema_insert . implode(', ',$tmpnp) . ' WHERE ' . implode(' AND ',$tmpp);
			    unset($tmpnp);
			    unset($tmpp);
			}
			unset($values);

			// Call the handler
			$handler($insert_line);

			// loic1: send a fake header to bypass browser timeout if data
			// are bufferized - part 2
			if (isset($time0)) {
				$time1 = time();
				if ($time1 >= $time0 + 1) {
					$time0 = $time1;
					echo ".\n\r";
					flush();
					ob_flush();
				}
			} // end if
		} // end while
	} // end if ($result != FALSE)
	mysql_free_result($result);

	return TRUE;
} // end of the "PMA_getTableContentFast()" function


/*
Uses the "htmlspecialchars()" php function on databases, tables and fields
name if the dump has to be displayed on screen.
*/

function PMA_myHandler($sql_insert) {
	global $tmp_buffer, $current_row, $rows_cnt;

	// Defines the end of line delimiter to use
	$eol_dlm = (isset($GLOBALS["_POST"]["extended_ins"]) && (($current_row+1) % EXT_INC_COL && $current_row < $rows_cnt))
			 ? ","
			 : ";";
	$tmp_buffer .= (!$GLOBALS["asfile"]) ? htmlspecialchars($sql_insert) : $sql_insert;
	$tmp_buffer .= $eol_dlm . $GLOBALS["crlf"];

	/* swapfile */
	swapfile_putdata($tmp_buffer);
	/* swapfile */

} // end of the "PMA_myHandler()" function


function PMA_myCsvHandler($sql_insert)
{
	global $add_character;
	global $tmp_buffer;

	$tmp_buffer .= $sql_insert . $add_character;

	/* swapfile */
	swapfile_putdata($tmp_buffer);
	/* swapfile */

} // end of the "PMA_myCsvHandler()" function


/**
 * returns metainfo for fields in $result
 *
 * @todo add missing keys like in mysqli_query (decimals)
 * @param   resource  $result
 * @return  array  meta info for fields in $result
 */
function PMA_DBI_get_fields_meta($result)
{
    $fields       = array();
    $num_fields   = mysql_num_fields($result);
    for ($i = 0; $i < $num_fields; $i++) {
        $field = mysql_fetch_field($result, $i);
        $field->flags = mysql_field_flags($result, $i);
        $field->orgtable = mysql_field_table($result, $i);
        $field->orgname = mysql_field_name($result, $i);
        $fields[] = $field;
    }
    return $fields;
}

/**
 * return number of fields in given $result
 *
 * @param   resource  $result
 * @return  int  field count
 */
function PMA_DBI_num_fields($result)
{
    return mysql_num_fields($result);
}

/**
 * returns concatenated string of human readable field flags
 *
 * @param   resource  $result
 * @param   int       $i       field
 * @return  string  field flags
 */
function PMA_DBI_field_flags($result, $i)
{
    return mysql_field_flags($result, $i);
}

function PMA_getTableCsv($db, $table, $limit_from = 0, $limit_to = 0, $sep, $enc_by, $esc_by, $handler)
{
	// Handles the "separator" and the optionnal "enclosed by" characters
	if ($GLOBALS["_POST"]["what"] == "excel") {
		$sep	 = ",";
	} else if (!isset($sep)) {
		$sep	 = "";
	} else {
		if (get_magic_quotes_gpc()) {
			$sep = stripslashes($sep);
		}
		$sep	 = str_replace("\\t", "\011", $sep);
	}
	if ($GLOBALS["_POST"]["what"] == "excel") {
		$enc_by  = "\"";
	} else if (!isset($enc_by)) {
		$enc_by  = "";
	} else if (get_magic_quotes_gpc()) {
		$enc_by  = stripslashes($enc_by);
	}
	if ($GLOBALS["_POST"]["what"] == "excel"
		|| (empty($esc_by) && $enc_by != "")) {
		// double the "enclosed by" character
		$esc_by  = $enc_by;
	} else if (!isset($esc_by)) {
		$esc_by  = "";
	} else if (get_magic_quotes_gpc()) {
		$esc_by  = stripslashes($esc_by);
	}

	// Defines the offsets to use
	if ($limit_from > 0) {
		$limit_from--;
	} else {
		$limit_from = 0;
	}
	if ($limit_to > 0 && $limit_from >= 0) {
		$add_query  = " LIMIT $limit_from, $limit_to";
	} else {
		$add_query  = "";
	}

	// Gets the data from the database
	$local_query = "SELECT * FROM " . PMA_backquote($db) . "." . PMA_backquote($table) . $add_query;
	$result	  = sql_query($local_query) or die($local_query);
	$fields_cnt  = mysql_num_fields($result);

	// Format the data
	$i = 0;
	while ($row = mysql_fetch_row($result)) {
		$schema_insert = "";
		for ($j = 0; $j < $fields_cnt; $j++) {
			if (!isset($row[$j])) {
				$schema_insert .= "NULL";
			}
			else if ($row[$j] == "0" || $row[$j] != "") {
				// loic1 : always enclose fields
				if ($GLOBALS["_POST"]["what"] == "excel") {
					$row[$j] = ereg_replace("\015(\012)?", "\012", $row[$j]);
				}
				if ($enc_by == "") {
					$schema_insert .= $row[$j];
				} else {
					$schema_insert .= $enc_by
					.str_replace($enc_by, $esc_by . $enc_by, $row[$j])
					.$enc_by;
				}
			}
			else {
				$schema_insert .= "";
			}
			if ($j < $fields_cnt-1) {
				$schema_insert .= $sep;
			}
		} // end for
		$handler(trim($schema_insert));
		++$i;

		// loic1: send a fake header to bypass browser timeout if data are
		//		bufferized
		if (!empty($GLOBALS["ob_mode"])
			&& (isset($GLOBALS["_POST"]["zip"]) || isset($GLOBALS["_POST"]["bzip"]) || isset($GLOBALS["_POST"]["gzip"]))) {
			header("Expires: 0");
		}
	} // end while

	if (!$schema_insert) $schema_insert = "Empty";
	mysql_free_result($result);

	return TRUE;
} // end of the "PMA_getTableCsv()" function

function PMA_backquote($a_name, $do_it = TRUE) {
	if ($do_it
		&& !empty($a_name) && $a_name != "*") {
		return "`" . $a_name . "`";
	} else {
		return $a_name;
	}
} // end of the "PMA_backquote()" function


function PMA_sqlAddslashes($a_string = "", $is_like = FALSE) {
	if ($is_like) {
		$a_string = str_replace("\\", "\\\\\\\\", $a_string);
	} else {
		$a_string = str_replace("\\", "\\\\", $a_string);
	}
	$a_string = str_replace("'", "\\'", $a_string);

	return $a_string;
} // end of the "PMA_sqlAddslashes()" function


function swapfile_init() {
	//$GLOBALS['swampfile'] = tmpfile();
	$GLOBALS['swapfilename'] = TEMP_PATH.'/'.md5(microtime()).'.dump';
	$GLOBALS['swapfile'] = fopen($GLOBALS['swapfilename'],'wrb+');
}

function swapfile_putdata(&$data) {
	fwrite($GLOBALS['swapfile'],$data);
	$data = "";
}

function swapfile_flush() {
	fflush($GLOBALS['swapfile']);
}

function swapfile_close() {
	fclose($GLOBALS['swapfile']);
	unlink($GLOBALS['swapfilename']);
}

function swapfile_gethandler() {
	return $GLOBALS['swapfile'];
}

// constatns:
$strTableStructure = 'rusoft_ce3_6dump_TableSturcture:';
$strDumpingData = 'rusoft_ce3_6dump_TableData:';

/**
 * Increase time limit for script execution and initializes some variables
 */
@set_time_limit(0);
$dump_buffer = "";
// Defines the default <CR><LF> format
if (strpos($_SERVER["SERVER_SOFTWARE"], "Win") !== false)
	$crlf = "\r\n";
else
	$crlf = "\n";

$GLOBALS['crlf'] = $crlf;

if (isset($GLOBALS["_POST"]["tables"]) && is_array($GLOBALS["_POST"]["tables"]))
	$tables = $GLOBALS["_POST"]["tables"];

/**
 * Send headers depending on whether the user choosen to download a dump file
 * or not
 */

/*
if (!$asfile) {
	header("Content-Type: text/html");
} else {
	// Download

	// Defines filename and extension, and also mime types
	if (!isset($table)) {
		$filename = $db.date("_Y-m-d_H-i");
	} else {
		$filename = $table.date("_Y-m-d_H-i");
	}
	switch ($GLOBALS["_POST"]["as"]) {
	case "bzip":
		$ext	   = "bz2";
		$mime_type = "application/x-bzip";
		break;
	case "gzip":
		$ext	   = "sql.gz";
		$mime_type = "application/x-gzip";
		break;
	case "zip":
		$ext	   = "zip";
		$mime_type = "application/x-zip";
		break;
	case "file":
		if ($GLOBALS["_POST"]["what"] == "csv" || $GLOBALS["_POST"]["what"] == "excel") {
			$ext	   = "csv";
			$mime_type = "application/octetstream"; //"text/x-csv";
		} else {
			$ext	   = "sql";
			// loic1: "application/octet-stream" is the registered IANA type but
			// MSIE and Opera seems to prefer "application/octetstream"
			$mime_type = "application/octetstream";
		}
		break;
	}

	// Send headers
	Header("Content-Type: " . $mime_type);
	// lem9 & loic1: IE need specific headers
	if (false === strpos($_SERVER["HTTP_USER_AGENT"], "Opera")) {
		header("Content-Disposition: inline; filename=\"" . $filename . "." . $ext . "\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");
	} else {
		header("Content-Disposition: attachment; filename=\"" . $filename . "." . $ext . "\"");
		header("Expires: 0");
		header("Pragma: no-cache");
	}
}
// end download
*/

swapfile_init();
if (!isset($add_query)) $add_query = '';
$data = 'SET AUTOCOMMIT = 0;'.$crlf.'SET FOREIGN_KEY_CHECKS=0;'.$crlf.$crlf;
swapfile_putdata($data);

/**
 * Builds the dump
 */
// Gets the number of tables if a dump of a database has been required
if (!isset($tables)) {
	$tables	 = mysql_list_tables($db);
	$num_tables = @mysql_numrows($tables);
} else {
	$num_tables = count($tables);
	$single	 = TRUE;
}

// No table -> error message
if ($num_tables == 0) {
	$dump_buffer = "# Ошибка: не выбрана таблица для дампа";
}
// At least on table -> do the work
else {
	// No csv format -> add some comments at the top
	if ($GLOBALS["_POST"]["what"] != "csv" &&  $GLOBALS["_POST"]["what"] != "excel") {
	$dump_buffer.= "# ContentEngine MySQL-Dump @ RuSoft.ru" . $crlf
	."# (enhanced phpMyAdmin code)" . $crlf
	."#" . $crlf
	."# Host: " . $_SERVER['HTTP_HOST'];
	$formatted_db_name = (isset($GLOBALS["_POST"]["use_backquotes"]))
					   ? PMA_backquote($db)
					   : "'" . $db . "'";
	$dump_buffer.= $crlf
	."# Time: " . date("Y-m-d H:i") . $crlf
	."# MySQL Server Version: " . $GLOBALS["_POST"]["mysql_version"] . $crlf
	."# PHP Version: " . phpversion() . $crlf
	."# Database: " . $formatted_db_name . $crlf;

	$exptype = $GLOBALS["_POST"]["exptype"];

	if ($GLOBALS["_POST"]["drop"]=='')
	   $GLOBALS["_POST"]["drop"]=0;

	$i = 0;
	if (isset($table_select)) {
		$tmp_select = implode($table_select, "|");
		$tmp_select = "|" . $tmp_select . "|";
	}
	while ($i < $num_tables) {
		if (!isset($single)) {
			$table = mysql_tablename($tables, $i);
		} else {
			$table = $tables[$i];
		}
		if (isset($tmp_select) && is_int(strpos($tmp_select, "|" . $table . "|")) == FALSE) {
			$i++;
		} else {
			$formatted_table_name = (isset($GLOBALS["_POST"]["use_backquotes"]))
								  ? PMA_backquote($table)
								  : "'" . $table . "'";
			// If only datas, no need to displays table name
			if ($GLOBALS["_POST"]["what"] != "dataonly") {
				$dump_buffer .= "# --------------------------------------------------------" . $crlf
							 .  $crlf . "#" . $crlf
							 .  "# " . $strTableStructure . " " . $formatted_table_name . $crlf
							 .  "#" . $crlf . $crlf
							 .  PMA_getTableDef($db, $table, $crlf) . ";" . $crlf;
			}
			// At least data
			if (($GLOBALS["_POST"]["what"] == "data") || ($GLOBALS["_POST"]["what"] == "dataonly")) {
				$tcmt = $crlf . "#" . $crlf
							 .  "# " . $strDumpingData . " " . $formatted_table_name . $crlf
							 .  "#" . $crlf .$crlf;
				$dump_buffer .= $tcmt;
				$tmp_buffer  = "";
				/* swapfile */
				swapfile_putdata($dump_buffer);
				/* swapfile */
				PMA_getTableContentFast($db, $table, $add_query, "PMA_myHandler", $exptype);
				$dump_buffer .= $tmp_buffer;

			} // end if
			$i++;
		} // end if-else
	} // end while

	// staybyte: don't remove, it makes easier to select & copy from
	// browser
	$dump_buffer .= $crlf;
	} // end "no csv" case

	// "csv" case
	else {
		// Handles the EOL character
		if ($GLOBALS["_POST"]["what"] == "excel") {
			$add_character = "\015\012";
		} else if (empty($add_character)) {
			$add_character = $GLOBALS["crlf"];
		} else {
			if (get_magic_quotes_gpc()) {
				$add_character = stripslashes($add_character);
			}
			$add_character = str_replace("\\r", "\015", $add_character);
			$add_character = str_replace("\\n", "\012", $add_character);
			$add_character = str_replace("\\t", "\011", $add_character);
		} // end if

		$tmp_buffer = "";
		PMA_getTableCsv($db, $table, $limit_from, $limit_to, $separator, $enclosed, $escaped, "PMA_myCsvHandler");
		$dump_buffer .= $tmp_buffer;
	} // end "csv" case
} // end building the dump

/* swapfile */
// $dump_buffer = 'SET AUTOCOMMIT = 0;'.$crlf.'SET FOREIGN_KEY_CHECKS=0;'.$crlf.$crlf.$dump_buffer.$crlf.'SET FOREIGN_KEY_CHECKS=1;'.$crlf.'COMMIT;'.$crlf.'SET AUTOCOMMIT = 1;';
swapfile_putdata($dump_buffer);
$data = $crlf.'SET FOREIGN_KEY_CHECKS=1;'.$crlf.'COMMIT;'.$crlf.'SET AUTOCOMMIT = 1;';
swapfile_putdata($data);
swapfile_flush();
/* swapfile */

# Output the dump...

switch ($GLOBALS["_POST"]["as"]) {
	/*
	// 1. as a gzipped file
	case "zip":
	if (@function_exists("gzcompress")) {
		$zipfile = new zipfile();
		$zipfile->addFile($dump_buffer, $filename.".sql");
		$dump_buffer = $zipfile->file();
		Header("Content-Length: ".strlen($dump_buffer));
		echo $dump_buffer;
	}
	break;
	*/
	// 2. as a bzipped file
	case "bzip":
	if (@function_exists("bzcompress")) {
		/* swapfile */
		// Пакуем информацию
		$fp = swapfile_gethandler();
		fseek($fp,0);
		$bzname = BACKUP_DIR.'/'.$_SERVER['HTTP_HOST'].'.dbdump_'.date('Y-m-d_H-i').'.bz2';
		$bz = bzopen($bzname,'wb');
		while(!feof($fp)) {
			$data = fread($fp,10240);
			bzwrite($bz,$data);
		}
		bzclose($bz);
		swapfile_close();
		/* swapfile */
		echo "<script>window.opener.document.location.reload();  window.close();</script>";
		ob_flush();
	}
	break;

	// 3. as a gzipped file
	case "gzip":
	if (@function_exists("gzencode")) {
		// without the optional parameter level because it bug
		/* swapfile */
		// Пакуем информацию
		$fp = swapfile_gethandler();
		fseek($fp,0);
		$gzname = BACKUP_DIR.'/'.$_SERVER['HTTP_HOST'].'.dbdump_'.date('Y-m-d_H-i').'.gz';
		$gz = gzopen($gzname,'wb');
		while(!feof($fp)) {
			$data = fread($fp,10240);
			gzwrite($gz,$data);
		}
		gzclose($gz);
		swapfile_close();
		/* swapfile */
		echo "<script>window.opener.document.location.reload(); window.close()</script>";
		ob_flush();
	}
	break;
	/*
	// 4. on screen
	case "data":
	ob_start("ob_gzhandler");
	$fp = swapfile_gethandler();
	fseek($fp,0);
	while(!feof($fp)) {
		$data = fread($fp,10240);
		echo "<pre>".$data."</pre>";
	}
	swapfile_close();
	ob_end_flush();
	break;
	*/

	// 5. as a text file
	case "file":
	$fp = swapfile_gethandler();
	fseek($fp,0);
	$name = BACKUP_DIR.'/'.$_SERVER['HTTP_HOST'].'.dbdump_'.date('Y-m-d_H-i').'.sql';
	$dst = fopen($name,'wb');
	while(!feof($fp)) {
		$data = fread($fp,10240);
		fwrite($dst,$data);
	}
	fclose($dst);
	swapfile_close();
	echo "<script>window.opener.document.location.reload();window.close()</script>";
	ob_flush();
	break;
}

echo "</body></html>";
exit();
?>