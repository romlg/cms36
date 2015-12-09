<?php
# путь до папки с файлами движка
if (!defined('PATH')) 					define ('PATH'					,ENGINE_VERSION.'/'.ENGINE_TYPE.'/');
if (!defined('PATH_CORE')) 				define ('PATH_CORE'				,ENGINE_VERSION.'/site/core/');
if (!defined('PATH_CFG')) 				define ('PATH_CFG'		,ENGINE_VERSION.'/cfg/');
if (!defined('PATH_COMMON')) 			define ('PATH_COMMON'			,ENGINE_VERSION.'/common/');
if (!defined('PATH_COMMON_CLASSES')) 	define ('PATH_COMMON_CLASSES'	,PATH_COMMON.'classes/');
if (!defined('PATH_COMMON_LIBS')) 		define ('PATH_COMMON_LIBS'		,PATH_COMMON.'libs/');
if (!defined('PATH_COMMON_CONTROLLER')) define ('PATH_COMMON_CONTROLLER',PATH_COMMON.'controllers/');

if (!defined('PATH_INC_DELIM')) 		define ('PATH_INC_DELIM'		,(ENGINE_OS) ? ':' : ';');
if (!defined('PATH_DELIM')) 			define ('PATH_DELIM'			,(ENGINE_OS) ? '/' : '\\');
if (!defined('BASE')) 					define('BASE', dirname($_SERVER['SCRIPT_NAME']).'/');

if (substr(phpversion(), 0, 1) == 5){
	#подключаем zend framework
	if (!defined('PATH_ZF')) 			define ('PATH_ZF'			, PATH_COMMON.'zf/');
}

# путь до файла коннекта с базой данных
if (file_exists('./scripts/connect.php')) {
    if (!defined('DB_CONNECT')) define ('DB_CONNECT', './scripts/connect.php');
} else {
    if (!defined('DB_CONNECT')) define ('DB_CONNECT', 'connect.php');
}

# базы данных
# default ставим тип бд mysql
if(!defined('SQL_TYPE'))				define('SQL_TYPE'				, 'mysql');
# default отключаем запись в log_change
if(!defined('SQL_LOG'))					define('SQL_LOG'				, true);
$log_change_actions = array('UPDATE','INSERT','REPLACE','DELETE');
if (DEV_MODE) $log_change_actions[] = 'SELECT'; // девелоперам записываем в лог все запросы, включая SELECT
if (!isset($log_change_exclude_tables)) {
	$log_change_exclude_tables = array('params', 'PARAMS', 'log_change');// таблицы вставка в которые не учитывается
}

# нужно ли использовать соединение с базой данных
if(!defined('SQL_DB')) 					define('SQL_DB'					, true);

# грузить коняиг из базы или из файла
if(!defined('CFG_SERIALIZE')) 			define ('CFG_SERIALIZE', false);

// включает глобальный gzip
if (!isset($GLOBALS['gzip'])) $GLOBALS['gzip'] = true;

if (!defined('PATH_CACHE')) 			define ('PATH_CACHE'			, 'cache/');
if (!defined('PATH_CACHE_TABLES')) 		define ('PATH_CACHE_TABLES'			, PATH_CACHE.'tables/');
# cache данных
if (!defined('CACHE_DATA')) 			define ('CACHE_DATA'			, true);
# настройки для кэширования данных
if (!defined('CACHE_DATA_LIFETIME')) 				define ('CACHE_DATA_LIFETIME'			, 60*60*4);		//время жизни кэша, в секундах = 4 часа
if (!defined('CACHE_DATA_DIR')) 					define ('CACHE_DATA_DIR'				, PATH_CACHE.'tmp');	//директория для сохранения файлов кэша
if (!defined('CACHE_DATA_FILE_LOCKING')) 			define ('CACHE_DATA_FILE_LOCKING'		, true);		//включение блокировки файлов при чтении/записи
if (!defined('CACHE_DATA_READ_CONTROL')) 			define ('CACHE_DATA_READ_CONTROL'		, true);		//включить проверку чтения из файлов
if (!defined('CACHE_DATA_READ_CONTROL_TYPE')) 		define ('CACHE_DATA_READ_CONTROL_TYPE'	, 'crc32');		//тип контроля чтения данных
if (!defined('TEMP_FILE_PATH')) 					define ('TEMP_FILE_PATH'				, PATH_CACHE.'files');	//временная директория для хранения закаченных файлов



# cache всей страницы
if (!defined('CACHE_PAGE')) 			define ('CACHE_PAGE'			, false);
//$GLOBALS['no_cache'] аналогично выключает этот кэш
if (!isset($GLOBALS['no_cache'])) $GLOBALS['no_cache'] = !CACHE_PAGE;

# настройки для кэширования всей страницы
if (!defined('CACHE_PAGES_PATH'))				define ('CACHE_PAGES_PATH'		, PATH_CACHE.'pages/');
if (!defined('CACHE_PAGE_COMPRESS_LEVEL')) 		define ('CACHE_PAGE_COMPRESS_LEVEL'	, 9);
if (!defined('CACHE_PAGE_TOUCH_FILE')) 			define ('CACHE_PAGE_TOUCH_FILE'	, '.cache');

//CACHE_COOKIE - переменные в куке, через запятую при которых выключать кэш

# cache блоков
if (!defined('CACHE_BLOCKS')) 			define ('CACHE_BLOCKS', true);
if (!defined('CACHE_BLOCKS_PATH')) 		define ('CACHE_BLOCKS_PATH', './cache/blocks/');

# smarty
if (!defined('SMARTY_CONFIG_PATH')) 				define ('SMARTY_CONFIG_PATH',				'configs/');
if (!defined('SMARTY_TRIM_WHITE_SPACE')) 			define ('SMARTY_TRIM_WHITE_SPACE', 			true);
if (!defined('SMARTY_COMPILE_CHECK')) 				define ('SMARTY_COMPILE_CHECK'   , 			true);
if (!defined('SMARTY_CACHING')) 					define ('SMARTY_CACHING'         , 			false);
if (!defined('SMARTY_CACHE_USE_GZ')) 				define ('SMARTY_CACHE_USE_GZ'    , 			true);
if (!defined('SMARTY_DEBUGGING')) 					define ('SMARTY_DEBUGGING'       , 			false);
if (!defined('SMARTY_CACHE_TEMPLATES')) 			define ('SMARTY_CACHE_TEMPLATES', 			PATH_CACHE.'templates/');
if (!defined('SMARTY_CACHE_COMPILED_TEMPLATES')) 	define ('SMARTY_CACHE_COMPILED_TEMPLATES', 	PATH_CACHE.'templates_c/');
if (!defined('SMARTY_CACHE_PAGES')) 				define ('SMARTY_CACHE_PAGES', 				PATH_CACHE.'pages/');

// Переменные для работы с сессией
if (!defined('SESSION_LIFETIME'))		define ('SESSION_LIFETIME'			, 0); // Сколько времени будет жить сессия
if (!defined('SESSION_USE_TRANS_SID'))	define ('SESSION_USE_TRANS_SID'		, 0); // Использовать ли перенос сессии через url
if (!defined('SESSION_SAVE_PATH'))		define ('SESSION_SAVE_PATH'			, PATH_CACHE.'sessions/'); // директория для записи в нее сессий

# путь к папке с файлами
if (!defined('FILES_DIR'))		define ('FILES_DIR'		, 'files/');
if (!defined('FILES_MOD'))		define ('FILES_MOD'		, 0775);
if (!defined('DIRS_MOD'))		define ('DIRS_MOD'		, 0775);

# Разрешить переход к первой вложенной странице, если родительская страница не содержит текста
if (!defined('ENABLE_JUMP'))	define ('ENABLE_JUMP',	false);
if (empty($jump_params))		$jump_params = array();
/*
$jump_params = array(
	'types'	=> array('text'), 	// с каких типов страниц разрешен переход
	'elems'	=> array( 			// в этих таблицах и полях проверяем наличие контента
		'elem_text' => 'text',
	),
);
*/

# модуль auth_users
if (!defined('AUTH_USER_COOKIE_NAME'))			define ('AUTH_USER_COOKIE_NAME', 'smsu');
if (!defined('AUTH_HASH_COOKIE_NAME'))			define ('AUTH_HASH_COOKIE_NAME', 'smsus');
if (!defined('AUTH_CHPASS_HASH_LIFETIME'))		define ('AUTH_CHPASS_HASH_LIFETIME', 3); // days

# языковые настройки
if (!defined('LANG_DEFAULT'))		define ('LANG_DEFAULT', 'ru'); // Язык по умолчанию
if (!defined('LANG_SELECT'))		define ('LANG_SELECT', false); // Зеркальный многоязычный сайт
if (!defined('LANG_COOKIE_NAME'))	define ('LANG_COOKIE_NAME', 'site_lang');
if (!defined('DISABLE_COOKIE_LANGUAGE'))	define ('DISABLE_COOKIE_LANGUAGE', true);
if (empty($langs))		$langs = array('ru', 'en');

# общие таблицы
if (!defined('TABLE_STAT_IPS'))		define ('TABLE_STAT_IPS'	, 'stat.stat_ips');
if (!defined('TABLE_COUNTRIES'))	define ('TABLE_COUNTRIES'	, 'countries');
if (!defined('TABLE_PARAMS'))		define ('TABLE_PARAMS'		, 'params');
if (!defined('TABLE_MESSAGES'))		define ('TABLE_MESSAGES'	, 'messages');
if (!defined('TABLE_TREE'))			define ('TABLE_TREE'		, 'tree');


# настройки форматов времени
if (!defined('FORMAT_DATE_BASE')) 		define ('FORMAT_DATE_BASE', '%Y%m%d');
if (!defined('FORMAT_DATETIME_BASE'))	define ('FORMAT_DATETIME_BASE', '%Y%m%d%H%i%s');
if (!defined('FORMAT_DATETIME'))		define ('FORMAT_DATETIME',	'H:i d.m.Y');
if (!defined('FORMAT_DATE'))			define ('FORMAT_DATE',		'd.m.Y');
if (!defined('FORMAT_TIME'))			define ('FORMAT_TIME',		'H:i');

# статистика
if (!defined('INCLUDE_STAT'))			define ('INCLUDE_STAT',		true); // включен сбор статистики
if (!defined('NEW_STAT'))			    define ('NEW_STAT',		    false); // Использовать новый алгоритм сбора статистики или нет

if (!defined('DB_SET_NAMES')) define ('DB_SET_NAMES', 'cp1251'); // Кодировка подключения к DB

?>