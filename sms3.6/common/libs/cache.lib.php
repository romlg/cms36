<?php

function cache_handler($buffer) {

	$buffer = trim($buffer);
	if (CACHE_PAGE === true){
		$page_cache = Registry::get('TPageCache');
		$file = CACHE_PAGES_PATH.$page_cache->get_cache_id().'.gz';

		if (is_file($file)){
			// Если мы кешируемся то Вывод содержимого кеш-файла (причем уже GZ)
			$buffer = file_get_contents($file);
			log_status("cache has been taken from cache file");
		} else {
			// кодируем $buffer
			$buffer = gzencode($buffer, CACHE_PAGE_COMPRESS_LEVEL);
			// если можно кешировать эту страницу - запишем ее в файл
			$page_cache->put_cache($buffer);
			log_status("cache is saving in cache file");
		}
	}
	else {
		//кодируем наш буфер
		$buffer = gzencode($buffer, CACHE_PAGE_COMPRESS_LEVEL);

	}

	// если клиент поддерживает сжатые gz страницы
	if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false && $GLOBALS['gzip'] == true) {
		// Отправляем заголовки gz
		header('Content-Encoding: gzip');
		header('Vary: Accept-Encoding');
		return $buffer;
	}
	else {
		return gzdecode($buffer);
	}
}

if (!function_exists('gzdecode')) {
function gzdecode($data) {
	$len = strlen($data);
	if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
		// Not GZIP format (See RFC 1952)
		return null;
	}
	$method = ord(substr($data, 2, 1)); // Compression method
	$flags  = ord(substr($data, 3, 1)); // Flags
	if ($flags & 31 != $flags) {
		// Reserved bits are set -- NOT ALLOWED by RFC 1952
		return null;
	}
	// NOTE: $mtime may be negative (PHP integer limitations)
	$mtime = unpack("V", substr($data, 4, 4));
	$mtime = $mtime[1];
	$xfl = substr($data,8,1);
	$os = substr($data,8,1);
	$headerlen = 10;
	$extralen = 0;
	$extra = '';
	if ($flags & 4) {
		// 2-byte length prefixed EXTRA data in header
		if ($len - $headerlen - 2 < 8) {
			// Invalid format
			return false;
		}
		$extralen = unpack('v', substr($data, 8, 2));
		$extralen = $extralen[1];
		if ($len - $headerlen - 2 - $extralen < 8) {
			// Invalid format
			return false;
		}
		$extra = substr($data, 10, $extralen);
		$headerlen += 2 + $extralen;
	}

	$filenamelen = 0;
	$filename = '';
	if ($flags & 8) {
		// C-style string file NAME data in header
		if ($len - $headerlen - 1 < 8) {
			// Invalid format
			return false;
		}
		$filenamelen = strpos(substr($data, 8 + $extralen), chr(0));
		if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
			// Invalid format
			return false;
		}
		$filename = substr($data, $headerlen, $filenamelen);
		$headerlen += $filenamelen + 1;
	}

	$commentlen = 0;
	$comment = '';
	if ($flags & 16) {
		// C-style string COMMENT data in header
		if ($len - $headerlen - 1 < 8) {
			// Invalid format
			return false;
		}
		$commentlen = strpos(substr($data, 8 + $extralen+$filenamelen), chr(0));
		if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
			// Invalid header format
			return false;
		}
		$comment = substr($data, $headerlen, $commentlen);
		$headerlen += $commentlen + 1;
	}

	$headercrc = '';
	if ($flags & 1) {
		// 2-bytes (lowest order) of CRC32 on header present
		if ($len - $headerlen - 2 < 8) {
			// Invalid format
			return false;
		}
		$calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
		$headercrc = unpack('v', substr($data, $headerlen, 2));
		$headercrc = $headercrc[1];
		if ($headercrc != $calccrc) {
			// Bad header CRC
			return false;
		}
		$headerlen += 2;
	}

	// GZIP FOOTER - These be negative due to PHP's limitations
	$datacrc = unpack('V', substr($data, -8, 4));
	$datacrc = $datacrc[1];
	$isize = unpack('V', substr($data, -4));
	$isize = $isize[1];

	// Perform the decompression:
	$bodylen = $len - $headerlen - 8;
	if ($bodylen < 1) {
		// This should never happen - IMPLEMENTATION BUG!
		return null;
	}
	$body = substr($data, $headerlen, $bodylen);
	$data = '';
	if ($bodylen > 0) {
		switch ($method) {
			case 8:
			// Currently the only supported compression method:
			$data = gzinflate($body);
			break;
			default:
			// Unknown compression method
			return false;
		}
	}
	else {
		// I'm not sure if zero-byte body content is allowed.
		// Allow it for now...  Do nothing...
	}

	// Verifiy decompressed size and CRC32:
	// NOTE: This may fail with large data sizes depending on how
	//      PHP's integer limitations affect strlen() since $isize
	//      may be negative for large sizes.
	if ($isize != strlen($data) || crc32($data) != $datacrc) {
		// Bad format!  Length or CRC doesn't match!
		return false;
	}
	return $data;
}
}

#########################################################################################
//--------------------- SMARTY CACHING ---------------------------------------------------
/**
 * насколько понял: функция не используется,так как нет у нас ни одного сайта, у которого было включено SMARTY_CACHING
 * @vetal
 */
// Этот заменяет стандартный сматревский кеш вывода шаблона
// хранит в CACHE_PAGES в gz или так в зав. от SMARTY_CACHE_USE_GZ
// проверяет $GLOBALS['touch_time']
// $GLOBALS['touch_time'] = @filemtime(CACHE_TOUCH_FILE);
function smarty_cache_handler($action, &$smarty_obj, &$cache_content, $tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null){

	$ext = SMARTY_CACHE_USE_GZ === true ? '.html.gz' : '.html';
	$CacheID = CACHE_PAGES.$tpl_file.md5($tpl_file.$cache_id.$compile_id).$ext;

	switch ($action) {
		case 'read':
		// проверяем дату изменения БД
		if($GLOBALS['touch_time']){
			if(is_file($CacheID)) $cache_time = filemtime($CacheID);
			else $cache_time = 0;
			if ($cache_time < $GLOBALS['touch_time']) return;
		}

		if(SMARTY_CACHE_USE_GZ && function_exists("gzuncompress")) {
			if (is_file($CacheID)) $contents = gzuncompress(file_get_contents($CacheID));
		} else {
			if (is_file($CacheID)) $contents = file_get_contents($CacheID);
		}
		// достаем переменные из кеша
		$cache_split = explode('"""|||EOH', $contents, 2);
		$cache_header = $cache_split[0];
		$cache_content = $cache_split[1];
		$smarty_obj->_restore_from_cache = unserialize($cache_header);
		break;
		case 'write':
		//сохраняем переменные
		$content = serialize($smarty_obj->_save_in_cache).'"""|||EOH'.$cache_content;
		$smarty_obj->_save_in_cache = null;

		// save cache to file
		if(SMARTY_CACHE_USE_GZ === true && function_exists('gzcompress')) {
			// compress the contents for storage efficiency
			$content = gzcompress($content);
		}

		if ($fd = fopen($CacheID, 'w')) {
			fwrite($fd, $content);
			fclose($fd);
		}
		else {
			$smarty_obj->trigger_error("problem writing file '$CacheID'");
		}
		break;
		case 'clear':
		// clear cache info
		if(empty($cache_id) && empty($compile_id) && empty($tpl_file)) {
			// clear them all
		}
		else {
			unlink($CacheID);
		}
		break;
		default:
		// error, unknown action
		$smarty_obj->_trigger_error_msg("cache_handler: unknown action \"$action\"");
		break;
	}
}

?>