<?php
/**
 * Отправляет уведомление по заданному событитю (старая функция)
 *
 * @param string $event
 * @param int $user_id - идентификтаор пользователя
 * @param array $data - массив данных для парса в шаблон
 * @param string $attach - вложение
*/
function SendNotify($event, $user_id, $data = array(), $attach = ''){
	// event - событие для нотификации
	// user_id - идентификатор пользователя из таблицы auth_users
	// data - массив для парса в шаблон
	switch (ENGINE_TYPE) {
		case 'site':
			require_once ('sms3.6/admin/lib/table.phpe');
			if (is_file('admin/modules/notify/notify.php')) include_once('admin/modules/notify/notify.php');
			else include_once('sms3.6/admin/modules/notify/notify.php');
			break;
		case 'admin':
			require_once(call_user_func('core', 'table'));
			require_once(call_user_func('module', 'notify'));
			break;
	}
	//-----------------------------------------------
	$notify = new TNotify();
	$ret = $notify->Send($event, $user_id, $data, $attach);
	return $ret;
}

/**
 * Отправляет уведомление по заданному событию (новая функция)
 *
 * @param string $event - код события
 * @param string $emails - адреса для отправки через запятую (если пусто - будут взяты из настроек события)
 * @param array $data - массив данных для парса в шаблон
 * @param string $attach - вложение
 * @return true|array
*/
function Notify($event, $emails = '', $data = array(), $attach = ''){
    require_once PATH_COMMON_CLASSES . 'notify.class.php';
	$notify = new RusoftNotify();
	$ret = $notify->Send($event, $emails, $data, $attach);
    if ($ret === true) return true;
	else return $notify->getErrors();
}

/**
 * Функция рекурсивно мерджит массив 1 с массивом 2
 * Если значение в массиве 1 не создано, оно добавляется из второго массива,
 * иначе ничего не происходит.
 * @param array() &$array
 * @param array() &$default
 */
function array_verify(&$array, &$default){
	if (!empty($default) && is_array($default)){
		foreach ($default as $k=>$v){
			if (!isset($array[$k])){
				$array[$k] = $v;
			} else {
				if (is_array($v)){
					array_verify($array[$k], $default[$k]);
				}
			}
		}
	}
}
//-----------------------------------------------------------------------------------------------------------
// функция поиска файла в Include_path
function eis_file(&$filename){
	if (defined('INCLUDE_PATH') && constant('INCLUDE_PATH')) {
		return array('.', INCLUDE_PATH);
	}
	$inc_path_delim = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
	$path_delim = (substr(PHP_OS, 0, 3) == 'WIN') ? "\\" : '/';

	$ipath = explode($inc_path_delim, ini_get('include_path'));


	$file = implode($path_delim, explode("/", $filename));
	//ищем файл
	$flag = false;
	foreach ($ipath as $path){
		if ($path != "."){
			$path = implode($path_delim, explode($path_delim, $path));
			//обрезаем последний слэш
			$path = implode($path_delim, explode($path_delim, $path)) . $path_delim . ENGINE_VERSION . $path_delim . ENGINE_TYPE . $path_delim;
		}
		if (is_file($path. $path_delim .$file)) {
			$flag = true;
			break;
		}
	}
	if ($flag) $filename = $path . $path_delim . $file;
	return $flag;
}

//-----------------------------------------------------------------------------------------------------------
// функции работы со строками
function h($str){
	return htmlspecialchars($str);
}
//-----------------------------------------------------------------------------------------------------------
function e($str){
	//аналог mysql_escape_string
	//return mysql_escape_string($str);
	return str_replace("'", "\'", $str);
}
//-----------------------------------------------------------------------------------------------------------

//учим работать с ini файлами
//-----------------------------------------------------------------------------------------------------------
// учим работать с файлами настроек
// работа c ini
//-----------------------------------------------------------------------------------------------------------
// функция чтения ini файла
// возвращает ассоциативный массив

/**
 * так как функция parse_ini_file почему-то по разному работает в разных
 * версиях php, вводим свою функцию, выполняющую аналогичные действия
 *
 * @param string $filename имя файла для чтения
 * @param string $commentchar переменная комментария
 * @return array()
 */
function _parse_ini_file ($filename, $commentchar = "#") {
	$array = file($filename);
	$section = '';
	for ($line_num = 0; $line_num < sizeof($array); $line_num++) {
		$filedata = $array[$line_num];
		$dataline = trim($filedata);
		$firstchar = substr($dataline, 0, 1);
		if ($firstchar!=$commentchar && $dataline!='') {
     		//It's an entry (not a comment and not a blank line)
			if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
				//It's a section
			$section = strtolower(substr($dataline, 1, -1));
			} else {
				//It's a key...
				$delimiter = strpos($dataline, '=');
				if ($delimiter > 0) {
					//...with a value
		         	$key = strtolower(trim(substr($dataline, 0, $delimiter)));
		         	if (strpos($key, '[') !== false) {
						list($key1, $tmp) = explode('[', $key, 2);
						list($key2, $rest) = explode(']', $tmp, 2);
						$element = &$ret[$section][$key1][$key2];
		         	} else {
		         		$element = &$ret[$section][$key];
		         	}
		         	$element = '';
		         	$value = trim(substr($dataline, $delimiter + 1));
		         	while (substr($value, -1, 1) == '\\') {
		            	//...value continues on the next line
			            $value = substr($value, 0, strlen($value)-1);
		    	        $element .= stripcslashes($value);
		        	    $line_num++;
		            	$value = trim($array[$line_num]);
		         	}
		         	$element .= stripcslashes($value);
			       	$element = trim($element);
		    	    if (substr($element, 0, 1) == '"' && substr($element, -1, 1) == '"') {
		           		$element = substr($element, 1, -1);
		         	}
				} else {
		        	//...without a value
		        	$ret[$section][strtolower(trim($dataline))]='';
		       	}
		   }
		} else {
	    	//It's a comment or blank line.  Ignore.
	   	}
	}
	return $ret;
}
//-----------------------------------------------------------------------------------------------------------
function ini_read($filename){
	$ini = _parse_ini_file($filename);
	return $ini;
}

//-----------------------------------------------------------------------------------------------------------
//фунция записи в ini файл
function ini_write($filename, $data){

   if (!is_file($filename)){
        return false;
   }

   $file = fopen($filename, "w");
   if (!is_resource($file)) return false;

   $delimiter = "\n";
   //сначала пишем все данные не принадлежащие секциям(появляется глюк, елси мы запишим данные после секции, они прикрепляются секции)
   foreach($data as $key => $value) {
        if (!is_array($value)){
            fwrite($file, $key.' = '.$value.$delimiter);
        }
   }
   fwrite($file,$delimiter);
   // запишем секции
   foreach($data as $key => $value) {
        if (is_array($value)){
            fwrite($file, '['.$key.']'.$delimiter);
            foreach ($value as $k => $v){
                 fwrite($file, $k.' = '.$v.$delimiter);
            }
            fwrite($file, $delimiter);
        }
   }

   fclose($file);
	return true;
}

//-----------------------------------------------------------------------------------------------------------

//фунция изменении данных в ini файле
function ini_change($filename, $data){

     $ini = ini_read($filename);
     return ini_write($filename, array_merge($ini,$data));

}

//-----------------------------------------------------------------------------------------------------------

// генерирует случайную строку длиной 32 символа
function getUniqueId() {
	return md5(uniqid(rand(), true));
}

//-----------------------------------------------------------------------------------------------------------

function get($name, $def = '', $order = 'gpcs') { # order may be gpcsu
	$ret = $def;
	for ($i = 0; $i < strlen($order); $i++) {
		switch ($order{$i}) {
			case 'g': $type = '_GET'; break;
			case 'p': $type = '_POST'; break;
			case 'c': $type = '_COOKIE'; break;
			case 's': $type = '_SESSION'; break;
			default: $type = '';
		}
		if (!$type) {
			continue;
		}
		if ($type == '_SESSION') {
			if (!session_id() && isset($_COOKIE[session_name()]))
				session_start();
		}
		if (!isset($GLOBALS[$type][$name])) {
			if ($type == '_SESSION') {
				if (session_id()) session_write_close();
			}
			continue;
		}
		$ret = $GLOBALS[$type][$name];
		if ($type == '_SESSION') {
			if (session_id()) session_write_close();
		}
		break;
	}
	return $ret ? $ret : $def;
}

//-----------------------------------------------------------------------------------------------------------
# аналог get_class, из-за разницы работы в php4/php5
function get_class_name($object){
	return strtolower(get_class($object));
}

//-----------------------------------------------------------------------------------------------------------
function getSiteByRootID($root_id) {
	$commonCfg = & Registry::get('TCommonCfg');
	return $commonCfg->getSiteByRootID($root_id);
}
function getLangByRootID($root_id) {
    $commonCfg = & Registry::get('TCommonCfg');
   	return $commonCfg->getLangByRootID($root_id);
}
function getMainRootID() {
    $commonCfg = & Registry::get('TCommonCfg');
   	return $commonCfg->getMainRootID();
}
//-----------------------------------------------------------------------------------------------------------
/**
 * Устанавливает время последнего изменения таблицы
 *
 * @param string $table
 */
function touch_cache($table){
	//if (isset($GLOBALS['cache_tables']) && false !== in_array($table, $GLOBALS['cache_tables']) ){
	touch(PATH_CACHE_TABLES.".".$table);
	//}

	touch(PATH_CACHE_TABLES.'.cache');
}
//-----------------------------------------------------------------------------------------------------------
/**
 * просматривает папку с сессиями и очищает все папки в указанной директории, для которых нет сессии
 * название папки ==  названию сессии без "sess_"
 *
 * @param string $dir
 * @return bool
 */
function rmTempDir($dir){

	//получаем список доступных сессий
	$sessions = array();
	if (is_dir(SESSION_SAVE_PATH)) {
	    if ($dh = opendir(SESSION_SAVE_PATH)) {
	        while (($file = readdir($dh)) !== false) {
	        	if ($file == "." || $file==".." || is_dir(SESSION_SAVE_PATH."/".$file)) continue;
	        	if (substr($file, 0, 5) == 'sess_') $sessions[substr($file, 5)] = true;
	        }
	        closedir($dh);
	    } else return false;
	} else return false;

	$rmDirs = array();
	//теперь собираем список доступных папок
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if ($file == "." || $file==".." || is_file($dir."/".$file) || strpos($file, 'cvs')!==false || strpos($file, '.svn')!==false) continue;
	        	$rmDirs[] = $file;
	        }
	        closedir($dh);
	    } else return false;
	} else return false;

	foreach ($rmDirs as $k=>$name){
		if (!isset($sessions[$name])) {
			if (!rmdir($dir . "/" . $name)){
				if ($dh = opendir($dir . "/" . $name)) {
			        while (($file = readdir($dh)) !== false) {
			        	if ($file == "." || $file=="..") continue;
			        	unlink($dir . "/" . $name . "/" . $file);
			        }
			        closedir($dh);
			    } else return false;
			    rmdir($dir . "/" . $name);
			}
		}
	}
	return true;
}

//-----------------------------------------------------------------------------------------------------------
/**
 * Проверяет папку, если нет, то создает и устанавливает права
 *
 * @param string $dir
 * @return bool
 */
function verifyDir($dir){
	//проверяем папку
	if (!is_dir($dir)) {
		if (!mkdir($dir, DIRS_MOD)) {
			$files_log['fatal'][] = "Do not create directory '".$dir."'";
			return false;
		}
	}
	//проверяем права на запись в нее
	clearstatcache();
	$permisions = file_GetPermsNum($dir);
	if ((int)$permisions != (int)DIRS_MOD){
		if (!chmod($dir, DIRS_MOD)){
			$files_log['warning'][] = "Do not change permission to directory '".$dir."', current permissions is ".$permisions;
		}
		chown($dir, fileowner($_SERVER['SCRIPT_FILENAME']));
		chgrp($dir, filegroup($_SERVER['SCRIPT_FILENAME']));
	}
	return true;
}
//-----------------------------------------------------------------------------------------------------------
/**
 * Возвращает в строковом виде ошибку закачки файла
 *
 * @param int $error
 * @return string
 */
function file_uploder_error($error){
	switch ($error){
		case 0: return "There is no error, the file uploaded with success.";
		case 1: return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
		case 2: return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
		case 3: return "The uploaded file was only partially uploaded.";
		case 4: return "No file was uploaded.";
	}
}
//-----------------------------------------------------------------------------------------------------------
/**
 * Закачивает файлы в указанную папку
 *
 * @param string $dir
 * @return bool
 */
function download_files($dir = TEMP_FILE_PATH)  {
	if (isset($_POST['page']) && ($_POST['page'] == 'fm2' || $_POST['page'] == 'fmr')) return;
	// лог файлов
	global $files_log;
	//проверяем, нужно ли что-нить закачать
	if (empty($_FILES)) {
		$files_log['notice'][] = "There are no files to load...";
		return false;
	}

	//название темповой папки
	$temp_dirname = $dir."/".$_COOKIE[session_name()];
	path($temp_dirname);

	//создаем коневую папку
	if (!verifyDir($dir)) return false;
	//создаем временную папку для закачивания == названию сессии
	if (!verifyDir($temp_dirname)) return false;

	if (!is_file($temp_dirname.'/.htaccess')){
		$handle = fopen($temp_dirname.'/.htaccess', 'w');
		fwrite($handle, 'Allow from all');
		fclose($handle);
	}

	$files = array();
	//определяем массив файлов для закачивания
	foreach ($_FILES as $name=>$data){
		if (is_array($data['name'])){
			foreach ($data['name'] as $k=>$v){
				if (is_array($v)) {
					foreach ($v as $name2=>$value){
						if (!empty($value)){
							if (array_key_exists($name2, $_POST[$name][$k])){
								$files[] = array(
									'post' 		=> & $_POST[$name][$k][$name2],
									'name' 		=> current($value),
									'size'		=> current($data['size'][$k][$name2]),
									'type'		=> current($data['type'][$k][$name2]),
									'tmp_name'	=> current($data['tmp_name'][$k][$name2]),
									'error'		=> current($data['error'][$k][$name2]),
									'error_str' => file_uploder_error(current($data['error'][$k][$name2])),
								);
							} else {
								if (count($data['size'][$k]) == 1){
									$files[] = array(
										'post' 		=> & $_POST[$name][$k],
										'name' 		=> $value,
										'size'		=> current($data['size'][$k]),
										'type'		=> current($data['type'][$k]),
										'tmp_name'	=> current($data['tmp_name'][$k]),
										'error'		=> current($data['error'][$k]),
										'error_str' => file_uploder_error(current($data['error'][$k])),
									);
								} else {
									$files[] = array(
										'post' 		=> & $_POST[$name][$k][$name2],
										'name' 		=> $value,
										'size'		=> $data['size'][$k][$name2],
										'type'		=> $data['type'][$k][$name2],
										'tmp_name'	=> $data['tmp_name'][$k][$name2],
										'error'		=> $data['error'][$k][$name2],
										'error_str' => file_uploder_error($data['error'][$k][$name2]),
									);
								}
							}

						}
					}
				} else {
					$files[] = array(
						'post' 		=> & $_POST[$name][$k],
						'name' 		=> $data['name'][$k],
						'size'		=> $data['size'][$k],
						'type'		=> $data['type'][$k],
						'tmp_name'	=> $data['tmp_name'][$k],
						'error'		=> $data['error'][$k],
						'error_str' => file_uploder_error($data['error'][$k]),
					);
				}
			}
		} else {
			if (!empty($data['name'])){
				$files[] = array(
					'post' 		=> & $_POST[$name],
					'name' 		=> $data['name'],
					'size'		=> $data['size'],
					'type'		=> $data['type'],
					'tmp_name'	=> $data['tmp_name'],
					'error'		=> $data['error'],
					'error_str' => file_uploder_error($data['error']),
				);
			}
		}
	}
	if (empty($files)){
		$files_log['fatal'][] = "There are no files to load...";
		return false;
	}

	//закачиваем
	foreach ($files as $k=>$v){
		if ($v['error'] == 0){
			$files[$k]['path'] = file_getUniName($temp_dirname."/".GetPureName($v['name']));
			//проверяем на совпадение имен файла в кэше
			if (!move_uploaded_file($v['tmp_name'], $files[$k]['path'])) {
				unset($files[$k]['path']);
				$files_log['warning'][] = "Cann't upload the file '".$v['name']."' in function 'move_uploaded_file'";
	        } else {
	        	 chmod($files[$k]['path'], FILES_MOD);
        		 chown($files[$k]['path'], fileowner($_SERVER['SCRIPT_FILENAME']));
       			 chgrp($files[$k]['path'], filegroup($_SERVER['SCRIPT_FILENAME']));
       			 //возвращаем пути на файлы в указанной папке
       			 $files[$k]['post'] = "@temp".$files[$k]['path'];
	        }
		}
	}
	$_FILES = null;
    return true;
}
//-----------------------------------------------------------------------------------------------------------
function file_getUniName($path, $pref = ""){
	if (substr($path, 0, 5) == "@temp") $path = substr($path,5);
	if (is_file($path)){
		$dot = strrpos($path ,".");
		$file = substr($path, 0, $dot);
		$ext = substr($path, $dot);
		for ($num = 1; $num< 1000000; $num++){
			$new_path = $file.$pref."[".$num."]".$ext;
			if (!is_file($new_path)) break;
		}
		return $new_path;
	}
	return $path;
}
//-----------------------------------------------------------------------------------------------------------
function file_GetPermsNum($perms) {
	# Source: http://www.php.net/fileperms
	$info = array(0,0,0);
	// Owner
	$info[0] += (($perms & 0x0100) ? '4' : '0');
	$info[0] += (($perms & 0x0080) ? '2' : '0');
	$info[0] += (($perms & 0x0040) ?
		(($perms & 0x0800) ? '0' : '1' ) :
		(($perms & 0x0800) ? '1' : '0'));

	// Group
	$info[1] += (($perms & 0x0020) ? '4' : '0');
	$info[1] += (($perms & 0x0010) ? '2' : '0');
	$info[1] += (($perms & 0x0008) ?
		(($perms & 0x0400) ? '0' : '1' ) :
		(($perms & 0x0400) ? '1' : '0'));

	// World
	$info[2] += (($perms & 0x0004) ? '4' : '0');
	$info[2] += (($perms & 0x0002) ? '2' : '0');
	$info[2] += (($perms & 0x0001) ?
		(($perms & 0x0200) ? '0' : '1' ) :
		(($perms & 0x0200) ? '1' : '0'));

	return implode($info);
}

//----------------------------------------------------

function file_GetPermsInfo($perms) {
	# Source: http://www.php.net/fileperms
	$info = array();
	if (($perms & 0xC000) == 0xC000) {
		// Socket
		$info['type'] = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		// Symbolic Link
		$info['type'] = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		// Regular
		$info['type'] = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		// Block special
		$info['type'] = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		// Directory
		$info['type'] = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		// Character special
		$info['type'] = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		// FIFO pipe
		$info['type'] = 'p';
	} else {
		// Unknown
		$info['type'] = 'u';
	}

	// Owner
	$info['u']['r']= (($perms & 0x0100) ? '1' : '0');
	$info['u']['w']= (($perms & 0x0080) ? '1' : '0');
	$info['u']['x']= (($perms & 0x0040) ?
		(($perms & 0x0800) ? '0' : '1' ) :
		(($perms & 0x0800) ? '1' : '0'));

	// Group
	$info['g']['r']= (($perms & 0x0020) ? '1' : '0');
	$info['g']['w']= (($perms & 0x0010) ? '1' : '0');
	$info['g']['x']= (($perms & 0x0008) ?
		(($perms & 0x0400) ? '0' : '1' ) :
		(($perms & 0x0400) ? '1' : '0'));

	// World
	$info['o']['r']= (($perms & 0x0004) ? '1' : '0');
	$info['o']['w']= (($perms & 0x0002) ? '1' : '0');
	$info['o']['x']= (($perms & 0x0001) ?
		(($perms & 0x0200) ? '0' : '1' ) :
		(($perms & 0x0200) ? '1' : '0'));

	return $info;
}
//-----------------------------------------------------------------------------------------------------------

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

/**
 * Кодирование урла
 *
 * @param string $url
 * @return string
 */
function set_encode_url_base($url) {

    $url_start = '';
    if (stripos($url,'http://')!==false) {
        $url_start = 'http://';
        $url = substr($url,strlen($url_start));
        $slash_pos = strpos($url,'/');
        if ($slash_pos!==false) {
            $url_start .= substr($url,0,$slash_pos);
            $url = substr($url,$slash_pos);
        } else {
            $url_start .= $url;
            $url = '';
        }
    }

    $from = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
    $to = array('%D0%B0','%D0%B1','%D0%B2','%D0%B3','%D0%B4','%D0%B5','%D1%91','%D0%B6','%D0%B7','%D0%B8','%D0%B9','%D0%BA','%D0%BB','%D0%BC','%D0%BD','%D0%BE','%D0%BF','%D1%80','%D1%81','%D1%82','%D1%83','%D1%84','%D1%85','%D1%86','%D1%87','%D1%88','%D1%89','%D1%8A','%D1%8B','%D1%8C','%D1%8D','%D1%8E','%D1%8F','%D0%90','%D0%91','%D0%92','%D0%93','%D0%94','%D0%95','%D0%81','%D0%96','%D0%97','%D0%98','%D0%99','%D0%9A','%D0%9B','%D0%9C','%D0%9D','%D0%9E','%D0%9F','%D0%A0','%D0%A1','%D0%A2','%D0%A3','%D0%A4','%D0%A5','%D0%A6','%D0%A7','%D0%A8','%D0%A9','%D0%AA','%D0%AB','%D0%AC','%D0%AD','%D0%AE','%D0%AF');
    return $url_start . str_replace($from, $to, $url);
}


/**
 * Кодирование урла
 *
 * @param string $a
 * @return string
 */
function set_encode_url($a){
    if (strpos($a[1], 'javascript:') !== false) return 'href="'.$a[1].'"';
    return 'href="'. set_encode_url_base($a[1]) .'"';
}

/**
 * Кодирование урла
 *
 * @param string $a
 * @return string
 */
function set_encode_url2($a){
    if (strpos($a[1], 'javascript:') !== false) return "href='".$a[1]."'";
    return "href='".set_encode_url_base($a[1])."'";
}


/**
 * Кодирование строки
 *
 * @param string $a
 * @return string
 */
function set_encode_str($a){
    $from = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
    $to = array('%D0%B0','%D0%B1','%D0%B2','%D0%B3','%D0%B4','%D0%B5','%D1%91','%D0%B6','%D0%B7','%D0%B8','%D0%B9','%D0%BA','%D0%BB','%D0%BC','%D0%BD','%D0%BE','%D0%BF','%D1%80','%D1%81','%D1%82','%D1%83','%D1%84','%D1%85','%D1%86','%D1%87','%D1%88','%D1%89','%D1%8A','%D1%8B','%D1%8C','%D1%8D','%D1%8E','%D1%8F','%D0%90','%D0%91','%D0%92','%D0%93','%D0%94','%D0%95','%D0%81','%D0%96','%D0%97','%D0%98','%D0%99','%D0%9A','%D0%9B','%D0%9C','%D0%9D','%D0%9E','%D0%9F','%D0%A0','%D0%A1','%D0%A2','%D0%A3','%D0%A4','%D0%A5','%D0%A6','%D0%A7','%D0%A8','%D0%A9','%D0%AA','%D0%AB','%D0%AC','%D0%AD','%D0%AE','%D0%AF');
    return str_replace($from, $to, $a);
}

function GetUploadedFile($file, $file_name, $file_dir = 'files', $resample = 0, $quality = '85') {
	if (!is_writable($file_dir)) die(str('e_not_writable')." (".$file_dir.")");
	$file_dir = preg_replace("~(/$)~", "", $file_dir);
	$file_name = GetPureName($file_name);
	if ($file) {
		if (!$resample || 1) {
			# find first unexisting filename
			$counter = 0;
			$dot = strrpos($file_name, '.');
			$fbase = substr($file_name, 0, $dot);
			$fext = substr($file_name, $dot);

			while (is_file($file_dir.'/'.$file_name)) {
				$counter++;
				$file_name = $fbase."_".$counter.$fext;
			}
		}

		@move_uploaded_file($file, $file_dir.'/'.$file_name) or die(str('e_upload')." (".$file_dir."/".$file_name.")");
		@chmod($file_dir.'/'.$file_name, 0775);

		# resample image
		if ($resample) return ResampleImage($file_dir, $file_name, $resample, $quality);
		else return $file_name;
	}
}

?>