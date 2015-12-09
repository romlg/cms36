<?php
define ('PATH_CONFIG',	'../configs/');
//подгружаем admin.cfg
require_once(PATH_CONFIG.'admin.cfg.php');

/*
Определяем параметры, для конкретной версии движка
*/
if (!defined('ENGINE_VERSION')) define ('ENGINE_VERSION'	,'sms3.6');
if (!defined('ENGINE_TYPE')) 	define ('ENGINE_TYPE'		,'admin');
if (!defined('ENGINE_OS')) 		define ('ENGINE_OS'			,(PHP_OS == 'WINNT') ? false : true	);

// Подключение Zend
$zend_dir = array(dirname(__FILE__), 'common', 'classes');
set_include_path(get_include_path() . PATH_SEPARATOR . implode(DIRECTORY_SEPARATOR, $zend_dir));

//подгружаем дополнительный массив настроек
require_once(ENGINE_VERSION.'/cfg/admin.cfg.php');

/* устанавливается document_status */
$GLOBALS['document_status'] = 200;

require_once(PATH_COMMON_LIBS.'error.lib.php');
require_once(PATH_COMMON_LIBS.'path.lib.php');

//подключаем класс реестра объектов
include_once(common_class('registry.php5'));

/* устанавливает соединение с базой данных */
// @todo нормальный обработчик ошибок при коннекте к БД
(require_once (DB_CONNECT)) or log_error('Cannot find file "'.DB_CONNECT.'"to connect to DB');
@mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PASSWORD) or die(mysql_error());
@mysql_select_db(MYSQL_DB) or die(mysql_error());
@mysql_query("SET NAMES \"".DB_SET_NAMES."\"");

/* подключаем встроенный дебаггер */
require_once(common_lib('debug'));
timer_start('all');

//подключаем вывод страницы в gz
require_once(common_lib('cache'));
ob_start('cache_handler');

/* подключается библиотека функций */
require_once (common_lib('utils'));

/* подгружаем контроллер, инициализирующий общие классы, для сайта и админки */

require_once (common('main.init'));
$main = & Registry::get('TMainInit');

require_once (PATH_CONFIG.'settings.cfg.php');
require_once (PATH_CONFIG.'lang.cfg.php');

/* Определение глобальных переменных */
require_once (cfg('common'));
require_once (base('core.dispatcher'));

timer_end('all');
echo showDebugInfo();
ob_end_flush();

?>