<?
/**
 * Класс для нахождения неиспользуемых строковых констант
 *
 */
class TCheckConstants extends TTable {

	var $name = 'checkconstants';	
	var $exclude = array(); // Среди этих констант не ищем (задается в module.ini)
	var $exclude_files = array('.', '..', 'icons', 'CVS', '.htaccess', '.svn', 'init.php', 'module.ini'); // В этих файлам/папках не ищем
	var $all_check_items = array(); // массив ранее проверенных папок и файлов
	var $constants = array();
	
	######################
	function TCheckConstants() {
		global $actions, $str;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'		 => array('Поиск неиспользуемых констант',			'Check constants',),
			'go'		 => array('Начать поиск',							'Start'),
			'delete'	 => array('Удалить выбранные',						'Delete selected'),
			'check_all'  => array('Выделить все',			                'Check all'),
			'uncheck_all'=> array('Снять выделение',		                'UnCheck all'),
		));		
		
		$ini_file = 'modules/'.$this->name.'/module.ini';
		if (!is_file($ini_file)) {
		    $ini_file = inc($this->name.'/module.ini');
		}
		$config = ini_read($ini_file);
		if (isset($config) && isset($config['exclude']) && !empty($config['exclude'])) {
            $this->exclude = array_keys($config['exclude']);
		}
	}
	
	function Show() {
		$step = isset($GLOBALS['_GET']['step']) ? $GLOBALS['_GET']['step'] : '';
		if (empty($step)) $step = isset($GLOBALS['_POST']['step']) ? $GLOBALS['_POST']['step'] : '';
		if (empty($step)) $step = 0;
		call_user_func(array(&$this, '_step'.$step));
	}

	/**
	 * Шаг 0
	 *
	 */
	function _step0() {
		echo '
		<p>Данный модуль предназначен для поиска строковых констант, которые не используются на сайте.</p>
		<iframe id="process_frame" name="process_frame" style="display:block;width:100%;height:600px;border: 1px solid black" src="" border="0" scrolling="yes" frameborder="0" marginwidth="0" marginheight="0"></iframe>
		<button onclick="window.frames[\'process_frame\'].location=\'/admin/cnt.php?page='.$this->name.'&step=1\'; return false">'.$this->str('go').'</button>
		';
	}
	
	/**
	 * Шаг 1
	 *
	 */
	function _step1() {
	    $this->_stepplay();
	}
	
	/**
	 * Закончить поиск
	 *
	 */
	function _stepstop() {
	    $this->showResults();
	}
	
	/**
	 * Поиск
	 *
	 */
	function _stepplay() {	    

	    ob_end_clean();
   		ob_start();
	    header('Content-Encoding: text/html'); // Чтобы не было вывода в gzip
   		echo str_pad(' ', 4096)."\n";
   		ob_end_flush();		
   		flush();

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
		
		$dir = dir('../templates/');
		while (false !== ($entry = $dir->read())) {
			if (in_array($entry, $this->exclude_files)) continue;
			$this->_recursive_search('../templates/'.$entry, $check_count);
		}
		closedir($dir->handle);
		
		$dir = dir('../modules/');
		while (false !== ($entry = $dir->read())) {
			if (in_array($entry, $this->exclude_files)) continue;
			$this->_recursive_search('../modules/'.$entry, $check_count);
		}
		closedir($dir->handle);

		$this->_stepstop();
	}
	
	function showResults() {
		$links = '';

		echo '
		<iframe id="tmpframe" name="tmpframe" src="about:blank" width="0" height="0" border="0" style="visibility:hidden"></iframe>
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<div id="dot"></div>
		<form action="page.php" method="post" id="files_form" name="files_form" target="tmpframe">
		<input type="hidden" name="page" value="'.$this->name.'">
		<input type="hidden" name="do" value="">
		<script>
			var dot = document.getElementById("dot");
			function del(){
				var form = document.forms.files_form;
				form.elements["do"].value = "del";
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
	
		$strings = sql_getRows('SELECT * FROM strings');
		$i = 1;
		$find = false;
		foreach ($strings as $k=>$v) {
		    $name = '';
		    if ($v['module'] == 'site') $name = $v['name'];
		    else $name = $v['module'].'_'.$v['name'];

		    if (!in_array($name, $this->constants) && !in_array($name, $this->exclude) && strpos($name, '_error_') === false && strpos($name, '_err_') === false) {
		        $find = true;
		        echo '
				<div style="width: 20px; float: left">'.$i.'</div>
				<div style="float: left"><input type="checkbox" name="fld[constant]['.$v['id'].']" value="'.$name.'"></div>
				<div style="width: 500px; float: left" id="constant'.$v['id'].'">'.$name.'</div>
				<br clear="all" />';
		        $i++;
		    }
		}

		if ($find) {
			$links = '<p><a href=\'#\' onclick=\'checkAll(true); return false;\' class=\'open\'>'.$this->str('check_all').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'#\' onclick=\'checkAll(false); return false;\' class=\'open\'>'.$this->str('uncheck_all').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'#\' onclick=\'del(); return false;\' class=\'open\'>'.$this->str('delete').'</a></p>';
		}
		echo '
		<script>
			dot.innerHTML = "<p style=\'color: red\'>Внимание!!!<br />Данный поиск не дает 100% гарантии, что найденные константы нигде не используются!<br />Необходимо дополнительно вручную проверить, прежде чем удалять!</p><p>Всего констант: '.count($strings).'<br />Найдено неиспользуемых констант: '.($i-1).'</p>'.$links.'";
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

			$content = file_get_contents($entry);
			
			preg_match_all('/{#(.*)#}/isU', $content, $matches);
			if ($matches[1]) {
			    foreach ($matches[1] as $k=>$v) {
			        if (!in_array($v, $this->constants)) $this->constants[] = $v;
			    }
			}			

			preg_match_all('/get_config_vars\(\'(.*)\'\)/isU', $content, $matches);
			if ($matches[1]) {
			    foreach ($matches[1] as $k=>$v) {
			        if (!in_array($v, $this->constants)) $this->constants[] = $v;
			    }
			}			

			preg_match_all('/get_config_vars\("(.*)"\)/isU', $content, $matches);
			if ($matches[1]) {
			    foreach ($matches[1] as $k=>$v) {
			        if (!in_array($v, $this->constants)) $this->constants[] = $v;
			    }
			}			

			preg_match_all('/msg=(.*)\'/isU', $content, $matches);
			if ($matches[1]) {
			    foreach ($matches[1] as $k=>$v) {
			        if (!in_array($v, $this->constants)) $this->constants[] = $v;
			    }
			}			
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
		}
	}

	/**
	 * Удаление констант
	 */
	function del() {
		$constants = $_POST['fld']['constant'];
		if (!$constants) return false;
		$script = "";
		$count = 0;
		sql_query('DELETE FROM strings WHERE id IN ('.implode(', ', array_keys($constants)).')');
		foreach ($constants as $id => $name) {		    
			$count++;
			$script .= "parent.document.getElementById('constant".$id."').innerHTML = '<font color=\"Silver\">".$name."</font>';";
		}
		$script .= "alert('Успешно удалено!');";
		echo "<script>".$script."</script>";
	}

}

$GLOBALS['checkconstants'] = & Registry::get('TCheckConstants');
?>