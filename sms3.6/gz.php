<?php
if (!defined('ENGINE_OS')) 		define ('ENGINE_OS'			,(PHP_OS == 'WINNT') ? false : true	);
if (!defined('PATH_INC_DELIM')) define ('PATH_INC_DELIM'		,(ENGINE_OS) ? ':' : ';');

if (is_file('../scripts/gz.php')) {
    $dr = "..";
} else {
    $dr = ".";
}

//----------------------------------------------------------------------------
function getIncludePaths() {
	$inc_path = explode(PATH_INC_DELIM, ini_get('include_path'));
	foreach ($inc_path as $k=>$v){
		$inc_path[$k] .= "/";
	}
	return $inc_path;
}
//----------------------------------------------------------------------------
function find_file($name, $dr = "."){
    // 1. узнаем это новая версия
    // 1.1 если да  - ищем ее в папке уровнем выше чем вызвающий файл; после перенесения в папку scripts
	// 1.2 если нет - ищем ее в папке вызвавшего файла
	// 2 ищем в папках алиасов

    if (is_file($dr.$name)) return $dr.$name;

	$inc = getIncludePaths();
	foreach ($inc as $pref) {
		if ($pref == "./") continue;
		if (is_file($pref . 'sms3.6/project' . $name)) {
			return $pref . 'sms3.6/project' . $name;
		}
	}

	return false;
}

//----------------------------------------------------------------------------
$uri = $_SERVER['REQUEST_URI'];
$uri = explode('?', $uri);
$file = find_file($uri[0], $dr);

if (is_file($file)){
	$update_time = filemtime($file);

	$headers = getallheaders();

	//header для типа файла
	$basename = basename($file);
	$ext = substr($basename, strrpos($basename, ".")+1);
	switch ($ext){
		case 'css':
			header('Content-Type: text/css;');
			break;
		case 'js':
			header('Content-Type: application/x-javascript;');
			break;
		default:
			header('Content-Type: text/html;');
			break;
	}

	if (isset($headers["If-Modified-Since"])) {
		$GMTutime = gmdate('D, d M Y H:i:s', $update_time).' GMT';
		if (strpos($headers["If-Modified-Since"], $GMTutime) !== false) {
			header("HTTP/1.1 304 Not Modified");
			exit;
		}
	}
	header('HTTP/1.0 200 OK');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $update_time).' GMT');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + 24*60*60).' GMT');
	header('Cache-Control: max-age='.(24*60*60));

	$handle = fopen($file, "rb");
	$buffer = '';
	while (!feof($handle)) {
	  $buffer .= fread($handle, 8192);
	}
	fclose($handle);

	// если клиент поддерживает сжатые gz страницы
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
	// Отправляем заголовки gz
		header('Content-Encoding: gzip');
		header('Vary: Accept-Encoding');
		echo gzencode($buffer);
	} else {
		echo $buffer;
	}

} else {
	header('HTTP/1.0 404 Not Found');
}



?>