<?php

require('../configs/common.cfg.php');
require('../configs/admin.cfg.php');
require('../configs/theme.cfg.php');
//require('../configs/customize.cfg.php');
require('../configs/modules.cfg.php');
require('../configs/elements.cfg.php');

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('track_errors', true);
$limit = 10;

$allow_modules = array('ced', 'fm', 'fm2', 'unknown', 'all_modules', 'modules');

#
# массив, содержащий пути к темплейтам; заполняется по мере обращения к модулям
# формат $tmpl_path['name'] = 'path prefix with end slash';
#

$tmpl_pref = array();

# service functions for inclusion

function _name($dir, $name) {
	global $tmpl_pref;
	$inc = explode(INC_PATH_DELIM, ini_get('include_path'));
	$look = array('.php', '.phpe');
	foreach ($look as $ext) {
		foreach ($inc as $pref) {
			if (is_file($pref.'/'.CORE.$dir.$name.$ext)) {
				$tmpl_pref[$name] = $pref.'/'.CORE;
				return $pref.'/'.CORE.$dir.$name.$ext;
			}
		}
	}
	# not found
	return $name;
}
function _tmpl($name) {
	global $tmpl_pref;
	$inc = explode(INC_PATH_DELIM, ini_get('include_path'));
	foreach ($inc as $pref) if (is_file($pref.'/'.CORE.$name)) {
		return $pref.'/'.CORE.$name;
	}
}
function core($name) {
	return _name('lib/', $name);
}
function module($name) {
	global $str;
	return strpos($name, '/') === false ? _name('modules/'.$name.'/', $name) : _name('modules/', $name);
}
?>
