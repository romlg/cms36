<?
/**
 * Класс для нахождения неиспользуемых файлов и картинок из папки FILES_DIR
 *
 */
class TCheckFiles extends TTable {

	var $name = 'checkfiles';
	var $exclude_tables = array(); // В этих таблицах не ищем (задается в module.ini)
	var $exclude_files = array('.', '..', 'icons', 'CVS', '.htaccess', '.svn'); // В этих файлам/папках не ищем
	var $all_check_items = array(); // массив ранее проверенных папок и файлов
	var $_sql = array();
	var $table = 'checkfiles';

	######################
	function TCheckFiles() {
		global $actions, $str;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'		 => array('Поиск неиспользуемых файлов',				'Check files',),
			'go'		 => array('Начать поиск',							'Start'),
			'delete'	 => array('Удалить выбранные',						'Delete selected'),
			'move'		 => array('Переместить выбранные в папку ',			'Move selected into'),
			'check_all'  => array('Выделить все',			                'Check all'),
			'uncheck_all'=> array('Снять выделение',		                'UnCheck all'),
			'pause'      => array('Остановить поиск',		                'Pause'),
			'play'       => array('Продолжить поиск',		                'Play'),
			'clear_cache'=> array('Сбросить кеш',		                    'Clear cache'),
		));

		$ini_file = 'modules/'.$this->name.'/module.ini';
		if (!is_file($ini_file)) {
		    $ini_file = inc($this->name.'/module.ini');
		}
		$config = ini_read($ini_file);
		if (isset($config) && isset($config['exclude_tables']) && !empty($config['exclude_tables'])) {
            $this->exclude_tables = array_keys($config['exclude_tables']);
		}

        $actions[$this->name] = array();
	}

	function Show() {
		$step = isset($GLOBALS['_GET']['step']) ? $GLOBALS['_GET']['step'] : '';
		if (empty($step)) $step = isset($GLOBALS['_POST']['step']) ? $GLOBALS['_POST']['step'] : '';
		if (empty($step)) $step = 0;
		$ret = call_user_func(array(&$this, '_step'.$step));
		return $ret;
	}

	/**
	 * Шаг 0
	 *
	 */
	function _step0() {
		$data = array(
            'name'  => $this->name,
            'go'    => $this->str('go'),
            'pause' => $this->str('pause'),
            'clear_cache' => $this->str('clear_cache'),
		);

	    return $this->Parse(array('step1' => $data), 'checkfiles.tmpl');
	}

	function makeSql() {
		$sql = 'SHOW TABLES';
		$res = mysql_query($sql);
		while($table = mysql_fetch_assoc($res)) {
			$t = current($table);
			if (in_array($t, $this->exclude_tables)) continue;
    		// Fields to select
    		$res2 = mysql_query('SHOW FULL FIELDS FROM `' . $t . '`;');
    		while ($current = mysql_fetch_assoc($res2)) {
    			list($current['Charset']) = explode('_', $current['Collation']);
    			$current['Field'] = '`'.$current['Field'].'`';
    			$this->_tablesfields[$t][]      = $current;
    		} // while
    		mysql_free_result($res2);
    		unset($res2, $current);
  			$this->_sql[] = $this->getSearchSqls($t);
		}
  		mysql_free_result($res);
	}

	/**
	 * Шаг 1
	 *
	 */
	function _step1() {
	    // Очистка результатов прошлого поиска
	    session_start();
	    $_SESSION['checkfiles'] = array();
	    $_SESSION['checkfiles_time'] = 0;
	    session_write_close();

	    // Создаем таблицу для хранения проверенных файлов (если еще не создана)
	    sql_query(
	    "CREATE TABLE IF NOT EXISTS `".$this->table."` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `filename` varchar(255) NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=cp1251;"
	    );

	    $this->all_check_items = sql_getRows('SELECT id, filename FROM '.$this->table, true);

	    $this->_stepplay();
	}

	/**
	 * Очистка "кеша" - списка проверенных файлов
	 *
	 */
	function _stepclear_cache() {
	    sql_query('TRUNCATE TABLE '.$this->table);
	    echo "<script>alert('Кеш очищен!');</script>";
	    die();
	}

	/**
	 * Закончить поиск
	 *
	 */
	function _stepstop() {
	    echo "<script>
	       var button = window.parent.document.getElementById('pause_button');
	       button.innerHTML = '".$this->str('pause')."';
	       button.onclick = function() {
	           document.location='/admin/cnt.php?page=".$this->name."&step=pause';
	           return false;
	       }
	    </script>";
	    $this->showResults();
	}

	/**
	 * Приостановить поиск и показать результаты
	 *
	 */
	function _steppause() {
	    echo "<script>
	       var button = window.parent.document.getElementById('pause_button');
	       button.innerHTML = '".$this->str('play')."';
	       button.onclick = function() {
	           document.location='/admin/cnt.php?page=".$this->name."&step=play';
	           return false;
	       }
	    </script>";
	    $this->showResults();
	}

	/**
	 * Продолжить поиск с последнего места поиска
	 *
	 */
	function _stepplay() {
	    $this->makeSql();

	    ob_end_clean();
   		ob_start();
	    header('Content-Encoding: text/html'); // Чтобы не было вывода в gzip
   		echo str_pad(' ', 4096)."\n";
   		ob_end_flush();
   		flush();

   		echo "<script>
	       var button = window.parent.document.getElementById('pause_button');
	       button.innerHTML = '".$this->str('pause')."';
	       button.onclick = function() {
	           document.location='/admin/cnt.php?page=".$this->name."&step=pause';
	           return false;
	       }
	    </script>";

	    session_start();
	    $_SESSION['checkfiles'] = array();
	    $_SESSION['checkfiles_time'] = 0;
	    session_write_close();

	    $this->_files_show();
	}

	function _files_show() {
		set_time_limit(0);

		echo '
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<div id="dot">Идет поиск</div>
		<script>
			var dot = document.getElementById("dot");
		</script>';

		$check_count = sql_getValue('SELECT COUNT(*) FROM '.$this->table); // проверенное кол-во
		session_start();
		$_SESSION['checkfiles_time'] = time();
		session_write_close();


		$dir = dir(FILES_DIR);
		while (false !== ($entry = $dir->read())) {
			if (in_array($entry, $this->exclude_files)) continue;
			$this->_recursive_search(FILES_DIR.$entry, $check_count);
		}
		closedir($dir->handle);

		echo '<script>location.href = "/admin/cnt.php?page='.$this->name.'&step=stop";</script>';
	}

	function showResults() {
		$links = '';
		session_start();
		$files = $_SESSION['checkfiles'];
		$time = $_SESSION['checkfiles_time'];
		$all_count = sql_getValue('SELECT COUNT(*) FROM '.$this->table);

		echo '&nbsp;
		<iframe id="tmpframe" name="tmpframe" src="about:blank" width="0" height="0" border="0" style="visibility:hidden"></iframe>
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<div id="dot">Идет поиск</div>
		<form action="page.php" method="post" id="files_form" name="files_form" target="tmpframe">
		<input type="hidden" name="page" value="'.$this->name.'">
		<input type="hidden" name="do" value="">
		<input type="hidden" name="move_folder" value="">
		<script>
			var dot = document.getElementById("dot");
			function delfiles(){
				var form = document.forms.files_form;
				form.elements["do"].value = "delfiles";
				form.submit();
				return false;
			}
			function movefiles(){
				var form = document.forms.files_form;
				form.elements["do"].value = "movefiles";
				form.elements["move_folder"].value = document.getElementById("move_id").value;
				form.submit();
				return false;
			}
			function checkAll(check) {
                var elements = document.forms.files_form.elements;
                for (var i=0; i<elements.length; i++) {
                    if (elements[i].tagName == "INPUT") {
                        elements[i].checked = check;
                    }
                }
			}
	    </script>
		';

		if (count($files) > 0) {
			$links = '<p><a href=\'#\' onclick=\'checkAll(true); return false;\' class=\'open\'>'.$this->str('check_all').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'#\' onclick=\'checkAll(false); return false;\' class=\'open\'>'.$this->str('uncheck_all').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'#\' onclick=\'delfiles(); return false;\' class=\'open\'>'.$this->str('delete').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'#\' onclick=\'movefiles(); return false\' class=\'open\'>'.$this->str('move').'</a><input id=\'move_id\' type=\'text\' name=\'move_folder\' value=\'\'></p>';
		}
		$i = 1;
		foreach ($files as $key=>$entry) {
		    echo '
				<div style="width: 20px; float: left">'.$i.'</div>
				<div style="float: left"><input type="checkbox" name="fld[file]['.$key.']" value="'.$entry.'"></div>
				<div style="width: 500px; float: left" id="file'.$key.'"><a href="'.$entry.'" target="_blank">'.$entry.'</a></div>
				<br clear="all" />';
		    $i++;
		}
		echo '
		<script>
			dot.innerHTML = "<p>Всего обработано файлов и папок: '.$all_count.'<br />Найдено неиспользуемых файлов: '.count($files).'</p>'.$links.' <p>Время = '.(time()-$time).' c</p>";
		</script>';
		echo '</form>';
		//ob_end_clean();
	}

	/**
	 * Рекурсивный поиск по всем вложенным папкам
	 *
	 */
	function _recursive_search($entry, &$all_count) {
	    if (in_array($entry, $this->all_check_items)) return;

	    if ($all_count%10 == 0) {
		    echo '<script>dot.innerHTML += ". ";</script>'; ob_end_flush(); flush();
		}

		if (is_file($entry)) {

			$all_count++;

			$find = false;
			foreach ($this->_sql as $sql) {
				if (mysql_fetch_assoc(mysql_query(str_replace('$$$', str_replace(FILES_DIR, '', $entry), $sql)))) {
					$find = true;
					break;
				}
			}

			if (!$find) {
			    session_start();
				$_SESSION['checkfiles'][$all_count] = $entry;
				session_write_close();
				echo '
				<div style="width: 20px; float: left">'.count($_SESSION['checkfiles']).'</div>
				<div style="float: left"><input type="checkbox" name="fld[file]['.$all_count.']" value="'.$entry.'"></div>
				<div style="width: 500px; float: left" id="file'.$all_count.'"><a href="'.$entry.'" target="_blank">'.$entry.'</a></div>
				<br clear="all" />';
				ob_end_flush();
				flush();
			}
    		// Записываем файл в проверенные
    		sql_query('INSERT INTO '.$this->table.' (filename) VALUES ("'.$entry.'")');
		}
		elseif (is_dir($entry)) {
			$entry_dir = dir($entry);
			$count = 0;
			while (false !== ($entry2 = $entry_dir->read())) {
				if (in_array($entry2, $this->exclude_files)) continue;
				$count++;
				$this->_recursive_search($entry.'/'.$entry2, $all_count);
			}
			closedir($entry_dir->handle);
			if (!$count) @rmdir($entry);
    		// Записываем папку в проверенные
    		sql_query('INSERT INTO '.$this->table.' (filename) VALUES ("'.$entry.'")');
		}
	}

	/**
	 * Функция вычисляет размер файла
	 *
	 * @param string $file - путь к файлу
	 * @return массив с размером и единицей измерения
	 */
	function getFileSize($file) {  // Узнаем размер файла с единицей измерения
		$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
		$size = filesize($file);
		if (!$size) $size = 0;
		else {
			$pass = 0;
			while( $size >= 1024 )
			{
				$size /= 1024;
				$pass++;
			}
			$size = round($size, 2);
			$unit = $units[$pass];
		}
		return array('size'=>$size,'unit'=>$unit);
	}

	/**
	 * Удаление файлов
	 */
	function delFiles() {
		$files = $_POST['fld']['file'];
		if (!$files) return false;
		$script = "";
		$count = 0;
		foreach ($files as $num => $path) {
			if (unlink($path) === true) {
				$count++;
			    // Если папка пустая, то она тоже удалится
			    @rmdir(dirname($path));
				$script .= "parent.document.getElementById('file".$num."').innerHTML = '<font color=\"Silver\">".$path."</font>';";
			}
			echo $num." ";
		}
		if ($count == count($files)) $script .= "alert('Успешно удалено!');";
		else $script .= "alert('Некоторые файлы не могут быть удалены - не найден путь!');";
		echo "<script>".$script."</script>";
	}

	/**
	 * Премещение файлов
	 */
	function moveFiles() {
		$folder = $_POST['move_folder'];
		if (!$folder) return false;
		$files = $_POST['fld']['file'];
		if (!$files) return false;
		$script = "";
		$count = 0;
		$folder = FILES_DIR.$folder;
		if (!is_dir($folder)) {
			mkdir($folder);
			chmod($folder, 0775);
		}
		foreach ($files as $num => $path) {
		    // Надо сохранить структуру папок, чтобы не пересеклись файлы с одинаковыми названиями
		    $new_folder = $folder;
		    $dirs = explode('/', $path);
		    foreach ($dirs as $key=>$val) {
		        if (!in_array($val, array('..', '', 'files')) && $key != count($dirs)-1) {
		            $new_folder .= '/'.$val;
            		if (!is_dir($new_folder)) {
            			mkdir($new_folder);
            			chmod($new_folder, 0775);
            		}
		        }
		    }
			if (rename($path, $new_folder.'/'.basename($path)) === true) {
				$count++;
			    // Если папка пустая, то она тоже удалится
			    @rmdir(dirname($path));
				$script .= "parent.document.getElementById('file".$num."').innerHTML = '<font color=\"Red\">".$path."</font>';";
			}
			echo $num." ";
		}
		if ($count == count($files)) $script .= "alert('Успешно перемещено!');";
		else $script .= "alert('Некоторые файлы не могут быть перемещены - не найден путь или такие файлы уже есть в папке!');";
		echo "<script>".$script."</script>";
	}

	/**
	 * Генерация поискового запроса
	 *
	 * @param string $table
	 * @return string
	 */
	function getSearchSqls($table)
	{
		$tblfields = $this->_tablesfields[$table];

		// Table to use
		$sqlstr_from = ' FROM `' . $table . '`';

		$like_or_regex   = 'LIKE';
		$automatic_wildcard   = '%';

		$fieldslikevalues = array();

		$thefieldlikevalue = array();
		foreach ($tblfields as $tblfield) {
		    if (strpos($tblfield['Type'], 'text') === false && strpos($tblfield['Type'], 'varchar') === false) continue;
		    if ($tblfield['Charset'] != 'NULL' && $tblfield['Charset'] != '') {
		        $prefix = 'CONVERT(_utf8 ';
		        $suffix = ' USING ' . $tblfield['Charset'] . ') COLLATE ' . $tblfield['Collation'];
		    } else {
		        $prefix = $suffix = '';
		    }
		    $thefieldlikevalue[] = $tblfield['Field']
		    . ' ' . $like_or_regex . ' '
		    . $prefix
		    . "'"
		    . $automatic_wildcard
		    . "$$$"
		    . $automatic_wildcard . "'"
		    . $suffix;
		} // end for

		$fieldslikevalues[]      = implode(' OR ', $thefieldlikevalue);

		$implode_str  = ' OR ';
		$sqlstr_where = ' WHERE (' . implode(') ' . $implode_str . ' (', $fieldslikevalues) . ')';
		unset($fieldslikevalues);

		// Builds complete queries
		$sql = 'SELECT * ' . $sqlstr_from . $sqlstr_where;

		unset($tblfields);
		return $sql;
	} // end of the "PMA_getSearchSqls()" function
}

$GLOBALS['checkfiles'] = & Registry::get('TCheckFiles');
?>