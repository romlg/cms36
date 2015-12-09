<?php

define('DIR', '../backup');
define('TEMP_PATH','../backup');
define('BACKUP_DIR','../backup');
set_time_limit(0);

class TMysqlDump extends TTable {

	var $name = 'mysqldump';
	var $domain_selector = false;
    var $selector = false;

	######################

	function TMysqlDump() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array();

		$str[get_class_name($this)]	= array(
			'title'		=> array(
				'MySQL: дамп',
				'MySQL: dump',
			),
			'head'		=> array(
				'MySQL: экспорт и импорт данных',
				'MySQL: data export & import',
			),
			'filelist'		=> array(
				'Список файлов',
				'Backup\'s File List',
			),
			'files'		=> array(
				'Файл данных',
				'Data file',
			),
			'size'		=> array(
				'Размер, байт',
				'Size, byte',
			),
			'create'	=> array(
				'Создать дамп данных',
				'Create backup',
			),
			'as'		=> array(
				'Результат',
				'Action',
			),
			'data'		=> array(
				'Показать в броузере',
				'Show in browser',
			),
			'drop'		=> array(
				'Удалять',
				'Remove',
			),
			'nodrop'		=> array(
				'Не удалять',
				'Not remove',
			),
			'dropoptions'		=> array(
				'Если таблица существует',
				'If table exists',
			),
			'file'		=> array(
				'Записать в sql файл',
				'Save as sql',
			),
			'gzip'		=> array(
				'Записать в файл и сжать в gzip',
				'Save and gzip',
			),
			'bzip'		=> array(
				'Записать в файл и сжать в bzip2',
				'Save and bzip2',
			),

			'einsert'		=> array(
				'INSERT',
				'INSERT',
			),
			'eupdate'		=> array(
				'UPDATE',
				'UPDATE',
			),
			'ereplace'		=> array(
				'REPLACE',
				'REPLACE',
			),

			'options'	=> array(
				'Что сохранять',
				'What to save',
			),
			'exptype'	=> array(
				'Тип экспорта',
				'Export type',
			),
			'no_data'	=> array(
				'Только структура',
				'Structure only',
			),
			'all'		=> array(
				'Структура и данные',
				'Structure and data',
			),
			'no_create_info'	=> array(
				'Только данные',
				'Data only',
			),
			'xml'		=> array(
				'В XML формате',
				'as XML',
			),

			'add'		=> array(
				'Дополнительно',
				'Additionally',
			),
			'send'		=> array(
				'Выдать файл на скачивание броузеру',
				'Download file',
			),
			'submit'	=> array(
				'Создать',
				'Create',
			),

			'e_not_writable'	=> array(
				'Невозможно создать дамп: нет прав на запись в директорию "%s".',
				'Unable to create file: directory "%s" is not writable.',
			),

			'status'			=> array(
				'Общая статистика по базе данных',
				'Database summary',
			),
			'table_count'		=> array(
				'Всего таблиц',
				'Total tables',
			),
			'table_rows'		=> array(
				'Всего строк',
				'Total rows',
			),
			'table_data'		=> array(
				'Данные',
				'Data size',
			),
			'table_index'		=> array(
				'Индексы',
				'Index size',
			),
			'table_date'		=> array(
				'Последнее обновление',
				'Last update',
			),

			'import'			=> array(
				'Импорт данных',
				'Restore',
			),
			'import_head'		=> array(
				'Импорт',
				'Import',
			),
			'ask_import'		=> array(
				'Импортировать данные из этого файла?',
				'Do you really want import data from this file?',
			),
			'upload_file'		=> array(
				'Закачать файл',
				'Upload file',
			),
			'upload'			=> array(
				'Отправить',
				'Upload',
			),
			'exec'				=> array(
				'Автоматически запустить импорт данных после закачки файла',
				'Import data after uploading',
			),
			'upload_del'		=> array(
				'Удалять закачанный файл после импорта данных',
				'Delete imported data file',
			),
			'c_del'		=> array(
				'Данный файл будет удален с сервера. Продолжить?',
				'This file will be deleted. Continue?',
			),
		);
	}

	######################

	function Show() {
		global $user;
		if (!$this->allow(ALLOW_SELECT)) return $this->AD();
		$ret = '
		<link rel="stylesheet" type="text/css" href="css/table.css" />
		';

		# Таблица со списком файлов дампа
		$d = @dir(DIR);
		if ($d) {
			while ($entry = $d->read()) if ($entry != '.' && $entry != '..' && is_file(DIR.'/'.$entry) && $entry[0] != '.') {
				$files[] = $entry;
			}
			if (!empty($files)) sort($files);
			$ret.= "
			<h5>".$this->str('filelist')."</h5>
			<table cellpadding=3 cellspacing=1 class=framed width=100%>
			<tr class=thead>
			<td class=thead align=center>#</td>
			<td class=thead align=center>".$this->str('import_head')."</td>
			<td class=thead>".$this->str('files')."</td>
			<td class=thead align=right>".$this->str('size')."</td>
			<td class=thead>&nbsp;</td>
			</tr>
			<script>
			function openImport(url) {
				window.open(url,\"importwindow\",\"toolbar=no,menubar=no,location=no,height=350,width=500\")
			}
			</script>
			";
			$counter = 0;
			if (!empty($files)) foreach ($files as $entry) {
				$counter++;
				$style = " class=".($counter % 2 == 0 ? 'odd' : 'white');
				$ret.= "
				<tr>
				<td".$style." align=center class=note>".$counter."</td>
				<td".$style." align=center><a href='#' onclick='if(confirm(\"".$this->str('ask_import')."\")) openImport(\"?page=".$this->name."&do=import&fname=".$entry."\");return false;'><img src='images/icons/icon.movedown.gif' width=16 height=16 border=0></a></td>
				<td".$style."><a class=tedit href='?page=".$this->name."&do=downloadfile&name=".$entry."'>".$entry."</a></td>
				<td".$style." align=right>".number_format(filesize(DIR.'/'.$entry))."</td>
				<td".$style." align=center><a href='?page=".$this->name."&do=delete&id=".$entry."' onclick='return confirm(\"".$this->str('c_del')."\")'><img src='images/icons/icon.delete.gif' width=16 height=16 border=0></a></td>
				</tr>
				";
			}
			$d->close();
			if (!$counter) $ret.= "<tr><td class=white colspan=5>".$this->str('empty')."</td></tr>";
			$ret.= "</table>\n";
		} else {
			$ret.= "<div class=red>Error: read dir `".DIR."` failed.</div>";
		}

		# Таблица с краткой статистикой по базе
		$ret.= "
		<h5>".$this->str('status')."</h5>
		<table cellpadding=3 cellspacing=1 class=framed width=100%>
		<tr class=thead>
		<td class=thead align=center>".$this->str('table_count')."</td>
		<td class=thead align=center>".$this->str('table_rows')."</td>
		<td class=thead align=center>".$this->str('table_data')."</td>
		<td class=thead align=center>".$this->str('table_index')."</td>
		<td class=thead align=center>".$this->str('table_date')."</td>
		</tr>
		";
		$status = array(
			'count'	=> 0,
			'rows'	=> 0,
			'data'	=> 0,
			'index'	=> 0,
			'date'	=> 0,
		);
		$rows = sql_query("SHOW TABLE STATUS");
		while ($row = mysql_fetch_assoc($rows)) {
			$status['count']++;
			$status['rows']+= $row['Rows'];
			$status['data']+= $row['Data_length'];
			$status['index']+= $row['Index_length'];

			if ($row['Update_time']) $row['Update_time'] = strtotime($row['Update_time']);
			if ($row['Update_time'] > $status['date']) $status['date'] = $row['Update_time'];
		}
		$ret.= "
		<tr>
		<td class=white align=center>".$status['count']."</td>
		<td class=white align=center>".number_format($status['rows'])."</td>
		<td class=white align=center>".number_format($status['data'])."</td>
		<td class=white align=center>".number_format($status['index'])."</td>
		<td class=white align=center>".fmt_date($status['date'])."</td>
		</tr>
		</table>
		";

		$row = sql_getRow("select version() as ver");
		$mysql_version = $row["ver"];

		# Форма создания дампа
		$ret.= "
        <form id=form_export method=post action='' onsubmit='this.subm.disabled=true;window.open(\"\",\"dumpwindow\",\"toolbar=no,menubar=no,location=no,height=200,width=400\")' target='dumpwindow'>
            <input type=hidden name=server value='".MYSQL_HOST."'>
            <input type=hidden name=db value='".MYSQL_DB."'>
            <input type=hidden name=mysql_version value='$mysql_version'>
            <input type=hidden name=use_backquotes value=1>


            <input type=hidden name=drop value='1'>

            <br />
            <fieldset><legend>".$this->str('create')."</legend>
                <table cellpadding=0 cellspacing=0>
                    <tr>
                        <td valign=top>
                            <select class=fForm name='tables[]' size=5 MULTIPLE style='height: 250px;'>
                                ".$this->GetArrayOptions(sql_getRows('show tables'), '')."
                            </select>
                        </td>
                        <td valign=bottom>&nbsp;</td>
                        <td valign=top>
                            <div><label class='float margBottom' for=all>".$this->str('options').":</label></div>
                            <div class='checkBox'>
                                <input id=no_data type=radio name=what value='structure'>
                                <label class='check' for=no_data>".$this->str('no_data')."</label>
                            </div>
                            <div class='checkBox'>
                                <input id=all type=radio name=what value='data' CHECKED>
                                <label class='check' for=all>".$this->str('all')."</label>
                            </div>
                            <div class='checkBox margBottom'>
                                <input id=no_create_info type=radio name=what value='dataonly'>
                                <label class='check' for=no_create_info>".$this->str('no_create_info')."</label>
                            </div>

                            <div><label class='float margBottom' for=all>".$this->str('dropoptions').":</label></div>
                            <div class='checkBox'>
                                <input id=deltable1 type=radio name=drop value='1' CHECKED>
                                <label class='check' for=deltable1>".$this->str('drop')."</label>
                            </div>
                            <div class='checkBox'>
                                <input id=deltable0 type=radio name=drop value='0' CHECKED>
                                <label class='check' for=deltable0>".$this->str('nodrop')."</label>
                            </div>
                            ".
                            /*<div class='checkBox'>
                                <input id=xml type=checkbox name=xml value='--xml'>
                                <label class='check' for=xml>".$this->str('xml')."</label>
                            </div>*/
                        "</td>
                        <td valign=bottom>&nbsp;</td>
                        <td valign=top>
                            <div><label class='float margBottom' for=gzip>".$this->str('as').":</label></div>".
                           //"<input id=data type=radio name=as value=0> <label for=data>".$this->str('data')."</label><br />".
                           "
                            <div class='checkBox'>
                                <input id=file type=radio name=as value=file>
                                <label class='check' for=file>".$this->str('file')."</label>
                            </div>
                            <div class='checkBox'>
                                <input id=gzip type=radio name=as value=gzip CHECKED>
                                <label class='check' for=gzip>".$this->str('gzip')."</label>
                            </div>
                            <div class='checkBox margBottom'>
                                <input id=bzip type=radio name=as value=bzip>
                                <label class='check' for=bzip>".$this->str('bzip')."</label>
                            </div>
							
							
                            <div><label class='float margBottom' for=ereplace>".$this->str('exptype').":</label></div>".
                           "
                            <div class='checkBox'>
                                <input id=einsert type=radio name=exptype value=einsert CHECKED>
                                <label class='check' for=einsert>".$this->str('einsert')."</label>
                            </div>
                            <div class='checkBox'>
                                <input id=eupdate type=radio name=exptype value=eupdate>
                                <label class='check' for=eupdate>".$this->str('eupdate')."</label>
                            </div>
                            <div class='checkBox'>
                                <input id=ereplace type=radio name=exptype value=ereplace>
                                <label class='check' for=ereplace>".$this->str('ereplace')."</label>
                            </div>
							
                        </td>

                        <td valign=bottom>&nbsp;</td>
                        <td valign=top>

                        </td>

                    </tr>
                </table>".
                /*"<div><u>".$this->str('add')."</u>:</div>
                <input id=send type=checkbox name=send value=1> <label for=send>".$this->str('send')."</label><br />".*/
                "
                <a class='button' href='javascript:document.getElementById(\"form_export\").submit();'>".$this->str('submit')."</a>
                <input type=hidden name=page value=".$this->name.">
                <input type=hidden name=do value=editdump>
            </fieldset>
        </form>
		
		<script>
			function set_cb_State(elems,state) {
				if (state)
					elems.attr('disabled','disabled');
				else
					elems.removeAttr('disabled');
			};
			function updstates() {
				set_cb_State($('#eupdate, #einsert, #ereplace'),$('#no_data').is(':checked'));
				set_cb_State($('#deltable1, #deltable0'),$('#no_create_info').is(':checked'));
			};
			$('fieldset input').click(updstates);
		</script>
		
		";

		# Форма импорта данных
		$ret.= "
        <form id='form_import' method='post' action='' enctype='multipart/form-data'>
            <fieldset><legend>".$this->str('import')."</legend>
                <br />
                <input type=hidden name=page value=".$this->name.">
                <input type=hidden name=do value=upload>

                <div class='margBottom elemBox file'>
                    <label class='float' for=upload_file>".$this->str('upload_file').":</label> <input type='text' readonly='readonly' name='' value='' class='text' id='upload_file'>
                    <div class='inputBox'>
                        <span class='note floatRight'>( file_uploads=".ini_get('file_uploads')."; upload_max_filesize=".ini_get('upload_max_filesize')."; post_max_size=".ini_get('post_max_size')." )</span>
                        <a title='выберите файл' class='fileButton'><input type='file' onchange='document.getElementById(\"upload_file\").value=this.value;' name='file'>Обзор...</a>
                    </div>
                </div>
                <div class='checkBox'>
                    <input id=exec type=checkbox name=exec>
                    <label class='check' for=exec>".$this->str('exec')."</label>
                </div>
                <div class='checkBox'>
                    <input id=upload_del type=checkbox name=del>
                    <label class='check' for=upload_del>".$this->str('upload_del')."</label>
                </div>
                <a class='button' href='javascript:void(0);'>".$this->str('upload')."</a>
            </fieldset>
        </form>
		<script>
			$(document).ready(function() {
				$('#exec').click(function(){
					if ($(this).is(':checked'))
						$('#form_import').attr('target','importwindow');
					else
						$('#form_import').removeAttr('target');
				});
				$('form#form_import a.button').click(function(){
					document.getElementById('form_import').submit();
					if ($('form#form_import').attr('target')=='importwindow') 
					location.reload(true);
				});
			});
		</script>
		";

		//$ret.= PageFooter();
		return $ret;
	}

	######################

	function EditDump() {
		global $user;
		if (!$this->allow(ALLOW_DELETE)) return $this->AD();
		include_once('tbl_dump.php');
	}

	######################

	function DownloadFile() {
		if (!is_root()) {
			return ;
		}
		if (!$this->makeDir(BACKUP_DIR)) {
			return;
		}
		$filename = clearFileName($_GET['name']);
		if (!isBackupPathCorrect($filename)) exit();
		$dotpos = strrpos($filename,'.');
		switch (substr($filename,$dotpos+1,strlen($filename)-$dotpos-1)) {
			case "bz2":
				$mime_type = "application/x-bzip";
				break;
			case "gz":
				$mime_type = "application/x-gzip";
			default:
				$mime_type = "application/octet-stream";
		}
		if (!file_exists(BACKUP_DIR.'/'.$filename)) exit();
		ob_end_clean();
		Header('Content-Encoding: none');
		Header("Content-Type: " . $mime_type);

		header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
		header("Expires: 0");
		header("Pragma: no-cache");
		$fp = fopen(BACKUP_DIR.'/'.$filename,'rb');
		while(!feof($fp)) {
			echo fread($fp,10240);
		}
		fclose($fp);
		exit();
	}

	function Delete() {
		global $user;
		if (!$this->allow(ALLOW_DELETE)) return $this->AD();
		unlink(DIR.'/'.$GLOBALS['_GET']['id']);
		HeaderExit(BASE."?page=".$this->name);
	}

	######################
	/*
	function Import($fname='', $del=false) {
		global $user;
		if (!$this->allow(ALLOW_DELETE)) return $this->AD();
		if (!$fname) {
			$fname = $GLOBALS['_GET']['fname'];
			$redirect = true;
		} else {
			$redirect = false;
		}
		if (!$fname) $this->msg('error', 'Error: import failed');
		$fi = pathinfo($fname);
		$fname = DIR.'/'.$fname;

		# decompress
		if ($fi['extension'] == 'gz') {
			$res = `gzip -d $fname`;
			$fname = substr($fname, 0, -3);
		} elseif ($fi['extension'] == 'zip') {
		} elseif ($fi['extension'] == 'bz2') {
			$res = `bzip2 -d $fname`;
			$fname = substr($fname, 0, -4);
		}

		if (defined('MYSQL_BIN') && MYSQL_BIN) $basedir = MYSQL_BIN;
		else {
			$row = sql_getRow("show variables like 'basedir'");
			$basedir = escapeshellcmd($row['Value']).'bin/';
		}

		# import
		# 1. Shell usage
		# mysql database < script.sql > output.tab
		if (!isset($GLOBALS['_GET']['soft'])) {
			$cmd = $basedir."mysql --user=".MYSQL_LOGIN." --password=".MYSQL_PASSWORD." -h".MYSQL_HOST." --tee=output.tab ".MYSQL_DB." < ".($fname2 ? $fname2 : $fname);
			$res = `$cmd`;
			$cmd = str_replace(MYSQL_PASSWORD, '<hidden>', $cmd);
			StatusLog($cmd);
		} else {
			require_once(module('mysqlcon'));
			$res = $GLOBALS['mysqlcon']->ExecSql(GetFile($fname));
			if ($res === false) InternalError('mysqldump', 'import(soft)', $GLOBALS['last_sql_getError']);
		}
		if ($del) unlink($fname);
		if ($redirect) HeaderExit(BASE."?page=".$this->name);
		return $res;
	}
	*/
	######################

	function Upload() {
        $file = substr($GLOBALS['_POST']['file'], 5);
        if (!$this->allow(ALLOW_INSERT)) return $this->AD();
        if (!is_file($file)) return $this->msg(str('e_upload'));
        if (!is_writable(DIR)) return $this->msg(str('e_not_writable') . ' (' . DIR . ')');
        $fname = Pure(basename($file));
        if (!copy($file, DIR . '/' . $fname)) return $this->msg( str('e_upload') . ' (' . DIR . '/' . $fname . ')');

        if ($GLOBALS['_POST']['exec']) {
            $res = $this->Import($fname, $GLOBALS['_POST']['del']);
            pr($fname);
            pr($res);
        }
        HeaderExit(BASE . "?page=" . $this->name);
	}

	######################

	function msg($text='Неопознанная ошибка!',$isexit=true) {
	    print $text;
	    flush();
	    if ($isexit)
	       die();
	}

	/**
	 * Исполнение sql из дампа, корректная работа с комнтариями и
	 * многострочными запросами
	 */

	function ExecSql($sql, $ignoreErrors=true) {
		global $sql_queries_complete, $sql_errors_count;
	
		if (is_array($sql)) $pieces = $sql;
		else {
			# define crlf
			$crlf = "\r\n";
			$pos = strpos($sql, ";");
			if ($pos && $sql[$pos+1]=="\r") $crlf = "\r\n";
			if ($pos && $sql[$pos+1]=="\n") $crlf = "\n";
			$pieces = split(";".$crlf, $sql);
		}
		$res = 1;#
		for ($i=0; $i<sizeof($pieces); $i++) {
			$pieces[$i] = trim($pieces[$i]);
			$pieces[$i] = preg_replace("/^#.*/m", "", $pieces[$i]);
			if ($pieces[$i]) {
				$res = sql_query($pieces[$i]);
				$sql_queries_complete++;
			}
			if ($res!=TRUE || $res!=1) {
				$sql_errors_count++;
				$last_sql_getError = $res;
				$this->msg('<br />'.$last_sql_getError.'<br />SQL: <pre>'.$pieces[$i].'</pre>',false);
				if (!$ignoreErrors) break;
			}
		}
		return $res;
	}

	/**
	 * Функция ищет метки дампера.
	 * Если находит - выполняет sql куски, невыполненый остаток возвращает.
	 *
	 * @param string $buff
	 * @return string
	 */
	function _procSql($buff) {
		$tags = array( 'INSERT', 'UPDATE', 'REPLACE', 'SET', 'CREATE TABLE', 'COMMIT');
        $close_tags = array( ";\r\n", ";\n");
        $flag = true;
		while ($flag) {
		    $startpos=false;
			foreach ($tags as $tag) {
				$tmp=stripos($buff,$tag);
				if ($tmp!==false && ($startpos===false || $tmp<$startpos)) $startpos = $tmp;
			}
			$flag = $startpos!==false;
		    if ($flag) {
				$closepos=false;
				foreach ($close_tags as $ctag) {
					$tmp=stripos($buff,$ctag,$startpos);
					if ($tmp!==false && ($closepos===false || $tmp<$closepos)) $closepos = $tmp;
				}
				$flag = $closepos!==false;
				if ($flag) {
					$pos = $closepos + 1;
					$sql = substr($buff,0,$pos);
					$this->ExecSql($sql);
					$buff = substr($buff,$pos,strlen($buff)-$pos);
				}
		    }
		}
		return $buff;
	}

	/**
	 * Импортирование базы
	 *
	 */
	function Import($fname='', $del=false) {
		global $sql_queries_complete, $sql_errors_count;
		ob_end_clean();
		header('Content-Encoding: none');
		$sqlblock_size = 20480;

		// Поддержка соединения с сервером
		function _delay_connect(&$time0) {
			$time1= time();
			if ($time1 >= $time0 + 1)
			{
				$time0 = $time1;
				echo ".\n\r";
				flush();
				ob_flush();
			}
		}

		$sql_queries_complete = 0;
		$sql_errors_count = 0;
		$time0 = time();
		echo "<html><head><title>Processing database</title></head>
		<body>Importing: \r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n";
		flush();
		if ($_GET['fname']) {
			if (isBackupPathCorrect($_GET['fname'])) {
				$localfile = BACKUP_DIR.'/'.clearFileName($_GET['fname']);
			}
		}
		if (!isset($localfile) || !file_exists($localfile)) $localfile = $localfile = BACKUP_DIR.'/'.clearFileName($fname);

		$filename = $localfile;
		$name = clearFileName($_GET['fname']);
		$fInfo = pathinfo($localfile);
		
		// Обрабатываем файлы
		switch ($fInfo["extension"]) {
			case "gz":
				$zp = gzopen($filename, "rb");
				if ($zp) {
					$sql = '';
					while (!gzeof($zp)) {
						$buf = gzread($zp, $sqlblock_size);
						$sql.= $buf;
						$sql = $this->_procSql($sql);
						_delay_connect($time0);
					}
					$res = $this->ExecSql($sql);
				}
				gzclose($zp);
				break;

			case "bz2":
				$zp = bzopen($filename, "rb");
				if ($zp) {
					$sql = '';
					while (!feof($zp)) {
						$buf = bzread($zp, $sqlblock_size);
						$sql.= $buf;
						$sql = $this->_procSql($sql);
						_delay_connect($time0);
					}
					$res = $this->ExecSql($sql);
				}
				bzclose($zp);
				break;
			case "txt":
			case "sql":
				$zp = fopen($filename, "rb");
				if ($zp) {
					$sql = '';
					while (!feof($zp)) {
						$buf = fread($zp, $sqlblock_size);
						$sql.= $buf;
						$sql = $this->_procSql($sql);
						_delay_connect($time0);
					}
					$res = $this->ExecSql($sql);
                }
				fclose($zp);
			break;
		}
		if ($del) unlink($filename);
		
		if ($sql_errors_count==0)
			echo "<script>window.opener.document.location.reload();  window.close()</script>";
		echo "<br />Importing complete: $sql_queries_complete sql-queries with $sql_errors_count errors</body></html>";

		exit();
	}


	######################

	function Info() {
		return array(
			'version'	=> get_revision('$Revision: 1.1 $'),
			'checked'	=> 0,
			'disabled'	=> 0,
			'type'		=> 'checkbox',
		);
	}
	/**
	 * Создание папок, если нет
	 *
	 * @param unknown_type $dir
	 * @return unknown
	 */
	function makeDir($dir) {
		$olddir = $dir;
		$dir = realpath($dir);
		if (!is_dir($dir))
			if(!@mkdir($dir)) {
				echo $this->str('no_dir').' '.$olddir.'<br />';
				return false;
			}
		if(!is_writable($dir)) {
			echo $this->str('no_wr_dir').' '.$dir.'<br />';
			return false;
		}
		return true;
	}

	######################
}

$GLOBALS['mysqldump'] = & Registry::get('TMysqlDump');



/**
 * Проверка на directory traversal
 *
 * @param string $name
 * @return bool
 */
function isBackupPathCorrect($name) {
	$name = clearFileName($name);
	$name = realpath(BACKUP_DIR.'/'.$name);
	$backupdir = realpath(BACKUP_DIR);
	// Проверим на всякий случай
	if (substr($name,0,strlen($backupdir))!=$backupdir) {
		// Пытаются вылезти из директории, паразиты
		return false;
	}
	return true;
}

/**
 * Удаление спецсимволов из имени файла
 *
 * @param string $str
 * @return string
 */
function clearFileName($str) {
	return preg_replace('/[^a-zA-Z0-9\.\-\_]|\.{2,}/','',$str);
}

/**********

Posted by Vesa Kivistц on August 26 2004 11:06pm

Following mysqldump import example for InnoDB tables is at least 100x faster than previous examples.


1. mysqldump --opt --user=username --password database > dumbfile.sql


2. Edit the dump file and put these lines at the beginning:

SET AUTOCOMMIT = 0;
SET FOREIGN_KEY_CHECKS=0;


3. Put these lines at the end:

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
SET AUTOCOMMIT = 1;


4. mysql --user=username --password database < dumpfile.sql

*/

?>