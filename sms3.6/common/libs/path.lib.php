<?php

//----------------------------------------------------------------------------
/*
	Функция возвращает массив из include_path
*/
function getIncludePaths() {
	$inc_path = explode(PATH_INC_DELIM, ini_get('include_path'));
	foreach ($inc_path as $k=>$v){
		path($inc_path[$k]);
		$inc_path[$k] .= "/";
	}
	return $inc_path;
}

//----------------------------------------------------------------------------
/*
	Возвращает путь до папки с общими файлами
*/
function common($name) {
	return _name(PATH_COMMON, $name);
}

//----------------------------------------------------------------------------
function cfg($name) {
	return _name(PATH_CFG, $name.".cfg");
}

//----------------------------------------------------------------------------
function common_class($name) {
	return _name(PATH_COMMON_CLASSES, $name.".class");
}

//----------------------------------------------------------------------------
function common_lib($name) {
	return _name(PATH_COMMON_LIBS, $name.".lib");
}
//----------------------------------------------------------------------------
function common_controller($name) {
	return _name(PATH_COMMON_CONTROLLER, $name.".controller");
}
//----------------------------------------------------------------------------
/*
	Возвращает путь до папки с ядром
*/
function core($name, $error = true) {
	return _name(PATH_CORE, $name,"", $error);
}
//----------------------------------------------------------------------------
function module($name, $error = true) {
	$ret = _name("modules/".$name."/", $name, PATH, $error);
	return $ret;
}
//----------------------------------------------------------------------------
function base($name, $error = true) {
	return _name("", $name, PATH, $error);
}
//----------------------------------------------------------------------------
function elem($name, $error = true) {
     return _name('modules/', $name, PATH, $error);
}
//----------------------------------------------------------------------------
function elem_inc($name, $error = true) {
	 $t_pos = strrpos($name, '/');
	 $name = $name."/".substr($name, ($t_pos ? $t_pos + 1 : 0 ));
     return _name('modules/', $name, PATH, $error);
}
//----------------------------------------------------------------------------
function inc($name) {
	 $ret = module($name, false);
	 if (!$ret) $ret = elem($name, false);
	 if (!$ret) $ret = elem_inc($name, false);
	 if (!$ret) $ret = core($name, false);
	 if (!$ret) $ret = base($name);
	 return $ret;
}
//----------------------------------------------------------------------------
/*
	Функция ищет файл в переданном пути и в Include_path
*/
function _name($dir, $name, $other = "", $error = true) {

	$inc = getIncludePaths();
	$look = array('.php', '.phpe', '');

	if (!empty($other)) {
		$dirs = array($dir, $other.$dir);
	} else {
		$dirs = array($dir);
	}

	foreach ($dirs as $k=>$dir){
		foreach ($inc as $pref) {
			foreach ($look as $ext) {
				$put = $pref.$dir;
				$pathname = $put.$name.$ext;
				if (!is_file($pathname)) continue;
				return $pathname;
			}
		}
	}
	return false;
	# not found
	if ($error){
		log_error('Failed to find file "'.path($dir).$name.'" in paths: '.implode(PATH_INC_DELIM, $inc));
	}
}

//----------------------------------------------------------------------------
/*
	Функция номарлизует внешний вид пути
	к виду "bla/bla/bla"
*/
function path(&$path) {
	$path = str_replace('\\', '/', $path);
	$elems = explode("/", $path);
	foreach ($elems as $k=>$v){
		if (empty($v) && !is_numeric($v)) unset($elems[$k]);
	}
	$path = (strpos($path, '/') === 0 ? "/" : "");
	$path .= implode("/", $elems);
	return $path;

}
//----------------------------------------------------------------------------

function find_dir($name){
	//ищем директорию, везде где возможно:)
	//приоритет:

	// 1. ищем ее в папке вызвавшего файла
	// 2. ищем в корне сайта (1 и 2 пункт меняются в зависимости от режима работы)
	// 3. ищем в директории admin
	// 4. ищем по Include_path
	// 5. ищем на уровень выше, от вызвавшего файла:)

	// 1.
	$bt = backtrace(1, 'file');
	$path = explode("/", path($bt));
	array_pop($path);
	$dir = implode("/", $path)."/".$name;

	$rp = realpath($dir);
	if (is_dir($dir)) return path($rp)."/";

	// 2.
	$rp = realpath($name);
	if (is_dir($name)) return path($rp)."/";

	// 3.
	if (is_dir('admin')){
		//мы на сайте
		$rp = realpath("admin/".$name);
		if (is_dir("admin/".$name)) return path($rp)."/";
	} else {
		//мы в админке
		$rp = realpath("../".$name);
		if (is_dir("../".$name)) return path($rp)."/";
	}

	// 4.
	$inc = getIncludePaths();
	foreach ($inc as $pref) {
		if ($pref == "./") continue;
		if (is_dir($pref . $name)) {
			$rp = realpath($pref . $name);
			return path($rp)."/";
		}
	}

	// 5.
	array_pop($path);
	$dir = implode("/", $path)."/".$name;
	if (is_dir($dir)) {
		$rp = realpath($dir);
		return path($rp)."/";
	}

	log_notice('Директория "'.$name.'" не найдена', backtrace(1, 'file'), backtrace(1, 'line'));
	return false;

}
//-----------------------------------------------------------------------------------------------------------
function _tmpl($name) {
	$inc = getIncludePaths();
	$theme_dir = 'modules/';

	foreach ($inc as $pref) {
		if (substr($pref, -1) != '/') {
			$pref .= '/';
		}
		if (substr($pref, 0, 1) != '.') {
			$pref .= PATH;
		}

		$pathname = $pref.$theme_dir.$name;
		if (!is_file($pathname)) {
			continue;
		}

		return $pathname;
	}

	# not found
	return $name;
}

//-----------------------------------------------------------------------------------------------------------
function getProjectDir($core = false) {
	$core = ($core === false) ? constant('PATH').'../' : $core;
	$inc = getIncludePaths();
	foreach ($inc as $pref) {

		if (substr($pref, -1) != '/') {
			$pref .= '/';
		}
		if (substr($pref, 0, 1) != '.') {
			$pref .= $core;
		}

		$pathname = $pref.'project/admin/';
		if (!is_dir($pathname)) {
			continue;
		}
		return $pathname;
	}
	return './';
}

//-----------------------------------------------------------------------------------------------------------

?>