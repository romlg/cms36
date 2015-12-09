<?php

# ���� �� ����� � ������� ������
if (!defined('PATH')) 					define ('PATH'				    ,ENGINE_VERSION.'/'.ENGINE_TYPE.'/');
if (!defined('PATH_CORE')) 				define ('PATH_CORE'				,ENGINE_VERSION.'/admin/lib/');
if (!defined('PATH_MODULES')) 			define ('PATH_MODULES'			,ENGINE_VERSION.'/admin/modules/');
if (!defined('PATH_CFG')) 				define ('PATH_CFG'				,ENGINE_VERSION.'/cfg/');
if (!defined('PATH_COMMON')) 			define ('PATH_COMMON'			,ENGINE_VERSION.'/common/');
if (!defined('PATH_COMMON_CLASSES')) 	define ('PATH_COMMON_CLASSES'	,PATH_COMMON.'classes/');
if (!defined('PATH_COMMON_LIBS')) 		define ('PATH_COMMON_LIBS'		,PATH_COMMON.'libs/');
if (!defined('PATH_COMMON_CONTROLLER')) define ('PATH_COMMON_CONTROLLER',PATH_COMMON.'controllers/');

if (!defined('PATH_INC_DELIM')) 		define ('PATH_INC_DELIM'		,(ENGINE_OS) ? ':' : ';');
if (!defined('PATH_DELIM')) 			define ('PATH_DELIM'			,(ENGINE_OS) ? '/' : '\\');

if (!defined('BASE')) 					define('BASE', dirname($_SERVER['SCRIPT_NAME']).'/');

if (substr(phpversion(), 0, 1) == 5){
	#���������� zend framework
	if (!defined('PATH_ZF')) 			define ('PATH_ZF'			, PATH_COMMON.'zf/');
}

# ���� �� ����� �������� � ����� ������
if (!defined('DB_CONNECT')) 			define ('DB_CONNECT'			,'connect.php');

# ���� ������
# default ������ ��� �� mysql
if(!defined('SQL_TYPE'))				define('SQL_TYPE'				, 'mysql');
# default ��������� ������ � log_change
if(!defined('SQL_LOG'))					define('SQL_LOG'				, true);
$log_change_actions = array('UPDATE','INSERT','REPLACE','DELETE');
if (DEV_MODE) $log_change_actions[] = 'SELECT'; // ����������� ���������� � ��� ��� �������, ������� SELECT
if (!isset($log_change_exclude_tables)) {
	$log_change_exclude_tables = array('params', 'PARAMS', 'log_change');// ������� ������� � ������� �� �����������
}

# ����� �� ������������ ���������� � ����� ������
if(!defined('SQL_DB')) 					define('SQL_DB'					, true);

# ������ ������ ��������
if(!defined('TREE_VERSION'))			define('TREE_VERSION'			, 1);

# ������� ������ �� ���� ��� �� �����
if(!defined('CFG_SERIALIZE')) 			define ('CFG_SERIALIZE', false);

if(!defined('REPLACE_HTTP'))			define ('REPLACE_HTTP', false); // �������� ������ ������ �� ������ ����� �� ������ ShowHTTP()

// �������� ���������� gzip
if (!isset($GLOBALS['gzip'])) $GLOBALS['gzip'] = true;

if (!defined('PATH_CACHE')) 			define ('PATH_CACHE'			, '../cache/');
if (!defined('PATH_CACHE_TABLES')) 		define ('PATH_CACHE_TABLES'			, PATH_CACHE.'tables/');
# cache ������
if (!defined('CACHE_DATA')) 			define ('CACHE_DATA'			, true);
# ��������� ��� ����������� ������
if (!defined('CACHE_DATA_LIFETIME')) 				define ('CACHE_DATA_LIFETIME'			, 60*60*4);		//����� ����� ����, � �������� = 4 ����
if (!defined('CACHE_DATA_DIR')) 					define ('CACHE_DATA_DIR'				, PATH_CACHE.'tmp_adm');	//���������� ��� ���������� ������ ����
if (!defined('CACHE_DATA_FILE_LOCKING')) 			define ('CACHE_DATA_FILE_LOCKING'		, true);		//��������� ���������� ������ ��� ������/������
if (!defined('CACHE_DATA_READ_CONTROL')) 			define ('CACHE_DATA_READ_CONTROL'		, true);		//�������� �������� ������ �� ������
if (!defined('CACHE_DATA_READ_CONTROL_TYPE')) 		define ('CACHE_DATA_READ_CONTROL_TYPE'	, 'crc32');		//��� �������� ������ ������
if (!defined('TEMP_FILE_PATH')) 					define ('TEMP_FILE_PATH'				, PATH_CACHE.'files');	//��������� ���������� ��� �������� ���������� ������


# cache ���� ��������
if (!defined('CACHE_PAGE')) 			define ('CACHE_PAGE'			, false);
//$GLOBALS['no_cache'] ���������� ��������� ���� ���
if (!isset($GLOBALS['no_cache'])) $GLOBALS['no_cache'] = !CACHE_PAGE;
# ��������� ��� ����������� ���� ��������
if (!defined('CACHE_PAGES_PATH'))				define ('CACHE_PAGES_PATH'		, PATH_CACHE.'pages/');
if (!defined('CACHE_PAGE_COMPRESS_LEVEL')) 		define ('CACHE_PAGE_COMPRESS_LEVEL'	, 9);
if (!defined('CACHE_PAGE_TOUCH_FILE')) 			define ('CACHE_PAGE_TOUCH_FILE'	, '.cache');

//CACHE_COOKIE - ���������� � ����, ����� ������� ��� ������� ��������� ���

# cache ������
if (!defined('CACHE_BLOCKS')) 			define ('CACHE_BLOCKS', false);

// ���������� ��� ������ � �������
if (!defined('SESSION_LIFETIME'))		define ('SESSION_LIFETIME'			, 60*60*4); // ������� ������� ����� ���� ������
if (!defined('SESSION_USE_TRANS_SID'))	define ('SESSION_USE_TRANS_SID'		, 0); // ������������ �� ������� ������ ����� url
if (!defined('SESSION_SAVE_PATH'))		define ('SESSION_SAVE_PATH'			, PATH_CACHE.'sessions_adm/'); // ���������� ��� ������ � ��� ������

# ���� � ����� � �������
if (!defined('FILES_DIR'))		define ('FILES_DIR'		, '../files/');
if (!defined('FILES_URL'))		define ('FILES_URL'		, '/files/');
if (!defined('FILES_MOD'))		define ('FILES_MOD'		, 0664);
if (!defined('DIRS_MOD'))		define ('DIRS_MOD'		, 0775);
if (!defined('FILE_NO_SELECT_STR'))		define ('FILE_NO_SELECT_STR', '���� �� ������');

# ������ auth_users
if (!defined('AUTH_USER_COOKIE_NAME'))			define ('AUTH_USER_COOKIE_NAME', 'smsu');
if (!defined('AUTH_HASH_COOKIE_NAME'))			define ('AUTH_HASH_COOKIE_NAME', 'smsus');
if (!defined('AUTH_CHPASS_HASH_LIFETIME'))		define ('AUTH_CHPASS_HASH_LIFETIME', 3); // days

# �������� ���������
if (!defined('LANG_DEFAULT'))		define ('LANG_DEFAULT', 'ru'); // ���� �� ���������
if (!defined('LANG_DEFAULT_ID'))	define ('LANG_DEFAULT_ID', 0); // ���� �� ���������
if (!defined('LANG_SELECT'))		define ('LANG_SELECT', false); // ���������� ������������ ����
if (!defined('LANG_COOKIE_NAME'))	define ('LANG_COOKIE_NAME', 'site_lang');
if (empty($langs))		$langs = array('ru', 'en');
if (!isset($intlangs) || empty($intlangs)) $intlangs = array('ru'); // ����� ����������

# ����� �������
if (!defined('TABLE_STAT_IPS'))		define ('TABLE_STAT_IPS'	, 'stat.stat_ips');
if (!defined('TABLE_COUNTRIES'))	define ('TABLE_COUNTRIES'	, 'countries');
if (!defined('TABLE_PARAMS'))		define ('TABLE_PARAMS'		, 'params');
if (!defined('TABLE_MESSAGES'))		define ('TABLE_MESSAGES'	, 'messages');
if (!defined('TABLE_TREE'))			define ('TABLE_TREE'		, 'tree');


# ��������� �������� �������
if (!defined('FORMAT_DATE_BASE')) 		define ('FORMAT_DATE_BASE', '%Y%m%d');
if (!defined('FORMAT_DATETIME_BASE'))	define ('FORMAT_DATETIME_BASE', '%Y%m%d%H%i%s');
if (!defined('FORMAT_DATETIME'))		define ('FORMAT_DATETIME',	'H:i d.m.Y');
if (!defined('FORMAT_DATE'))			define ('FORMAT_DATE',		'd.m.Y');
if (!defined('FORMAT_TIME'))			define ('FORMAT_TIME',		'H:i');


#�������� ��������
// ���������� ��������� �� ��������� ��� ObjectEditor'a
if (!defined('OBJECT_EDITOR_SCRIPT')) 	define ('OBJECT_EDITOR_SCRIPT', 'editor.php');
if (!defined('OBJECT_EDITOR_MODULE')) 	define ('OBJECT_EDITOR_MODULE', 'editor');
if (!defined('OBJECT_EDITOR_CLASS')) 	define ('OBJECT_EDITOR_CLASS', 'TObjectEditor');
if (!defined('SESSION_OBJPREFIX'))  	define ('SESSION_OBJPREFIX', 'oed_wc');

#������������� � ����� ������
if (!defined('PHP'))					define ('PHP'			,'.php');
# ���� � mysql & mysql_dump
if (!defined('MYSQL_BIN'))				define ('MYSQL_BIN'		,'/usr/local/lib/php/');
# �������� ��� ��� ������������� �������� � utf-8
if (!defined('UTF8'))					define ('UTF8',		 		false);
# ������� �������� ip ������ ����� (0 - �� ���������, 4 - ��������� ��� 4 ����� ������)
if (!defined('IP_CHECK_LEVEL'))			define ('IP_CHECK_LEVEL', 	0);
# for stat
if (!defined('STAT_CLIENT_REPORT'))		define('STAT_CLIENT_REPORT', true);
if (!defined('INCLUDE_STAT'))			define('INCLUDE_STAT',		 true); // ���������� ������ "���������� � �������"
if (!defined('NEW_STAT'))			    define('NEW_STAT',		     true); // ������������ ����� �������� ����� ���������� ��� ���
# �������� �� ���������
if (!defined('DEF_PAGE'))				define ('DEF_PAGE',	  		'tree');
# �������� �� ���������
if (!defined('DEF_DO'))					define ('DEF_DO',	  		'show');

# ������ �� ��������� ��� ���������� ������
if (!defined('LIST_TEMPLATE'))          define ('LIST_TEMPLATE',    'table_list.tmpl');

# ��������� ����������� � DB
if (!defined('DB_SET_NAMES')) define ('DB_SET_NAMES', 'cp1251');

if (!defined('ADMIN_LOGO')) define('ADMIN_LOGO', 'images_custom/logo.png');
if (!defined('ADMIN_LOGO_LINK')) define('ADMIN_LOGO_LINK', 'http://www.rusoft.ru');
if (!defined('ADMIN_LOGO_ALT')) define('ADMIN_LOGO_ALT', 'www.rusoft.ru');

if (!defined('CKEDITOR_VERSION')) define('CKEDITOR_VERSION', '3');

# ����� �� ���������
if (!defined('DEF_DOMAIN')){
    if (substr($_SERVER['SERVER_NAME'], 0, 4) == "www."){
        define ('DEF_DOMAIN',   substr($_SERVER['SERVER_NAME'], 4));
    } else {
        define ('DEF_DOMAIN',   $_SERVER['SERVER_NAME']);
    }
}

if (isset($_GET['page'])){
	$page = strtolower($_GET['page']);
} else if (isset($_POST['page'])){
	$page = strtolower($_POST['page']);
} else {
	$page = false;
}
if (isset($_GET['do'])){
	$do = strtolower($_GET['do']);
} else if (isset($_POST['do'])){
	$do = strtolower($_POST['do']);
} else {
	$do = DEF_DO;
}

$src = $GLOBALS['_SERVER']['QUERY_STRING'];
$src64 = base64_encode($GLOBALS['_SERVER']['QUERY_STRING']);

$_default_stat = array(
	'stat/stat_process'		=> array('�������������� ����������',   'The raw statistics',	'img' => 'square.gif',),
	'stat/stat_summary'		=> array('������� ����������',			'Summary',				'img' => 'square.gif',),
	'stat/stat_attendance'	=> array('������������ �����',			'Site attendance',		'img' => 'square.gif',),
	'stat/stat_clients' 	=> array('������������������ �������',	'Register users',		'img' => 'square.gif',),
	'stat/stat_ref_server'	=> array('����������� �������',			'Referring servers',	'img' => 'square.gif',),
	'stat/stat_ref_pages'	=> array('����������� ��������',		'Referring pages',		'img' => 'square.gif',),
	'stat/stat_search_ph'	=> array('��������� �����',				'Search phrases',		'img' => 'square.gif',),
	'stat/stat_popular' 	=> array('���������� ��������',			'Popular pages',		'img' => 'square.gif',),
	'stat/stat_pathes' 		=> array('���� �� �����',				'Site pathes',			'img' => 'square.gif',),
	'stat/stat_points' 		=> array('����� �����',					'Enter points',			'img' => 'square.gif',),
	'stat/stat_outs' 		=> array('����� ������',				'Exit points',			'img' => 'square.gif',),
	'stat/stat_ip' 			=> array('IP-������',					'IP-addresses',			'img' => 'square.gif',),
	'stat/stat_geography' 	=> array('���������',					'Geography',			'img' => 'square.gif',),
	'stat/stat_regions' 	=> array('�������',						'Regions',				'img' => 'square.gif',),
	'stat/stat_cities' 		=> array('������',						'Cities',				'img' => 'square.gif',),
	'stat/stat_browsers' 	=> array('��������',					'Browsers',				'img' => 'square.gif',),
	'stat/stat_os' 			=> array('������������ �������',		'Operating systems',	'img' => 'square.gif',),
	'stat/stat_errors' 		=> array('��������� ��������',			'Error pages',			'img' => 'square.gif',),
	'stat/stat_robots' 		=> array('��������������',				'Search Robots',		'img' => 'square.gif',),
	'stat/stat_now' 		=> array('������ �� �����',				'Visitors now',			'img' => 'square.gif',),
	'stat/stat_banlist' 	=> array('������ ������',				'Ban list',				'img' => 'square.gif',),
	'stat/stat_search' 		=> array('����� �� �����',				'Site search',			'img' => 'square.gif',),
	'stat/stat_reklama' 	=> array('��������� ��������',			'Reklama',				'img' => 'square.gif',),
	//'stat/stat_export' 		=> array('������� ����������',			'Export',				'img' => 'square.gif',),
	'stat/stat_clear' 		=> array('������� ����������',			'Clear statistics',		'img' => 'square.gif',),
	'stat/stat_settings' 	=> array('��������� ����������',		'Statistics Settings',	'img' => 'controlpanel.png',),
);
if(!defined('STAT_CLIENT_REPORT') || STAT_CLIENT_REPORT==false) {
	unset($_default_stat['stat/stat_clients']);
}
if(!defined('NEW_STAT') || NEW_STAT==false) {
	unset($_default_stat['stat/stat_process']);
}

$_default_sites = array(
	'sites/sites'			=> array('�����',						'Sites',				'img' => 'icon.countries.gif',),
	'sites/node_types'		=> array('���� ����� ������',			'Tree nodes types',		'img' => 'folder.gif',),
	'sites/elements'		=> array('�������� �������',			'Page elements',		'img' => 'mainmenu.png',),
	'sites/blocks'			=> array('�����',						'Blocks',				'img' => 'content.png',),
	'sites/modules'			=> array('������',						'Modules',				'img' => 'controlpanel.png',),
);
$_default_notify = array(
	'notify/subscribed' => array(
		'����������',
		'Subscribers',
		'img' => 'users.png',
		'do' => 'show',
	),
	'notify/send' => array(
		'���������',
		'Send',
		'img' => 'nomail.png',
		'do' => 'show',
	),
	'notify/sent' => array(
		'������������',
		'Sent',
		'img' => 'mail.png',
		'do' => 'show',
	),
	'notify/distribution' => array(
		'��������',
		'Distribution',
		'img' => 'mass_email.png',
		'do' => 'show',
	),
	'notify/events' => array(
		'�������',
		'Events',
		'img' => 'messaging_inbox.png',
		'do' => 'show',
	),
	'notify/templates' => array(
		'������� �������',
		'Templates',
		'img' => 'template.png',
		'do' => 'show',
	),
	'email_templates' => array(
		'������� �����',
		'Templates',
		'img' => 'template.png',
		'do' => 'show',
	),
	'notify/properties' => array(
		'���������',
		'Properties',
		'img' => 'controlpanel.png',
		'do' => 'show',
	),
);

foreach ($_default_notify as $name=>$values){
	if (!isset($_notify[$name])) $_notify[$name] = $values;
	if ($_notify[$name] === false) unset($_notify[$name]);
}

foreach ($_default_sites as $name=>$values){
	if (!isset($_sites[$name])) $_sites[$name] = $values;
	if ($_sites[$name] === false) unset($_sites[$name]);
}
foreach ($_default_stat as $name=>$values){
	if (!isset($_stat[$name])) $_stat[$name] = $values;
	if ($_stat[$name] === false) unset($_stat[$name]);
}
if (!isset($notify_plugins)){
	$notify_plugins = array(
		'email',
	);
}

# �������� ��� ������� �� ���������
$_default_images = array(
	'tree' 					=> 'frontpage.gif',
	'strings' 				=> 'header_icon.png',
	'fm2' 					=> 'preview_f2.png',
	'banners' 				=> 'mediamanager.png',
	'news' 					=> 'coffee.jpg',
	'clients'	 			=> 'users.png',
	'site_groups'			=> 'groups_f2.png',
	'support'				=> 'help_f2.png',
	'support_categories' 	=> 'note_f2.png',
	'support_kb'			=> 'dbrestore.png',
	'discounts'				=> 'credits.png',
	'discount_types'		=> 'bookmarks_f2.png',
	'discount_groups' 		=> 'bookmark_f2.png',
	'history'	 			=> 'history_f2.png',
	'cart'		 			=> 'square.gif',
	'bills'		 			=> 'contacts_f2.png',
	'orders'	 			=> 'downloads_f2.png',
	'currencies'			=> 'icon.vat.gif',
	'manufacturers'			=> 'cpanel.png',
	'products'				=> 'square.gif',
	'product_types'			=> 'tool_f2.png',
	'solutions_types'		=> 'mark_f2.png',
	'properties' 			=> 'controlpanel.png',
	'admins'  				=> 'users.png',
	'admin_groups'  		=> 'groups_f2.png',
	'help'  				=> 'help.gif',
	'mysqldump'  			=> 'backup.png',
	'mysqlcon'  			=> 'mainmenu.png',
	'log_access' 			=> 'db.png',
	'log_change' 			=> 'edit_f2.png',
);

if (INCLUDE_STAT === false) unset($sections['stat']);

foreach ($sections as $name=>$items){
	$t_modules = array();
	foreach ($items['modules'] as $module_name=>$descr){
		if (is_array($descr)){
			$t_modules[$module_name] = $descr;
			if (!in_array($module_name, $GLOBALS['modules'])){
				$GLOBALS['modules'][] = $module_name;
			}
		} else {
			if (isset($_default_images[$descr])){
				$t_modules[$descr] = array('img' => $_default_images[$descr]);
			} else {
				$t_modules[] = $descr;
			}
			if (!in_array($descr, $GLOBALS['modules'])){
				$GLOBALS['modules'][] = $descr;
			}
		}
	}
	$GLOBALS['modules'][$name] = $t_modules;
}


// �������� �������, � ������� ����� ������ ��� ������������
if (!isset($allow_modules)){
	$allow_modules = array(
		'ced',
		'fm',
		'fmr',
		'fm2',
		'unknown',
		'modules',
		'about_blank',
		'help',
		'progress',
		'login',
	);
}

if (!isset($limit)) $limit = 10;

if (!defined('ALLOW_SELECT'))	define('ALLOW_SELECT', 1);
if (!defined('ALLOW_INSERT'))	define('ALLOW_INSERT', 2);
if (!defined('ALLOW_UPDATE'))	define('ALLOW_UPDATE', 4);
if (!defined('ALLOW_DELETE'))	define('ALLOW_DELETE', 8);

if (!defined('RIGHT_FULL'))	 	define('RIGHT_FULL', ALLOW_SELECT | ALLOW_INSERT | ALLOW_UPDATE | ALLOW_DELETE);

######################
#
# Common Window Icons
if (!isset($window_icons)){
	$window_icons = array(
		'help' => array(
			'display' => array(
				'������',
				'Help',
			),
			'icon' => 'images/icons/icon.help.gif',
			'link' => '#',
			'target' => '',
			'onclick' => 'return showHelp(\''.($page ? $page : DEF_PAGE).'.'.$do.'\');',
		),
		/*'source' => array(
			'display' => array(
				'�������� ���',
				'Source',
			),
			'icon' => 'images/icons/icon.php.gif',
			'link' => 'act.php?page='.$page.'&do=showsource',
			'target' => '',
			'onclick' => '',
		),*/
		'maximize' => array(
			'display' => array(
				'���������� �� ���� �����',
				'Maximize window',
			),
			'icon' => 'images/icons/icon.maximize.gif',
			'link' => 'act.php?page=about_blank&src='.$src64,
			'target' => '',
			'onclick' => 'return maximizeCnt(\\\'ced.php?'.$src.'&fs=1\\\')', // ��������� ������ js, ������� ������� ������
		),
		'restore' => array(
			'display' => array(
				'������������',
				'Restore window',
			),
			'icon' => 'images/icons/icon.cross.gif',
			'link' => '#',
			'target' => '',
			'onclick' => 'if(window.top.opener){window.top.opener.focus();if(window.top.opener.frames.cnt && window.top.opener.frames.cnt.document.forms.restoreform)window.top.opener.frames.cnt.document.forms.restoreform.subm.click();}window.top.close();return false;',
		),
		'close' => array(
			'display' => array(
				'�������',
				'Close',
			),
			'icon' => 'images/icons/icon.cross.gif',
			'link' => '#',
			'target' => '',
			'onclick' => 'if(window.top.opener)window.top.opener.focus();window.top.close();return false;',
		),
	);
}
######################
#
# Common Actions

if (!isset($actions['table'])){
	$actions['table'] = array(
        'create' => array(
            '�������',
            'Create',
            'link'      => 'createItem',
            'img'       => 'icon.create.gif',
            'display'   => 'block',
        ),
        'edit' => array(
            '�������������',
            'Edit',
            'link'      => 'editItem',
            'img'       => 'icon.edit.gif',
            'display'   => 'block',
            'multiaction'=> 'false',
//            'funct_arg' => array(2,3),
        ),
        'delete' => array(
            '�������',
            'Delete',
            'link'      => 'deleteItems',
            'img'       => 'icon.delete.gif',
            'display'   => 'block',
        ),
        'copy' => array(
            '����������',
            'Copy',
            'link'      => 'copyItems',
            'img'       => 'icon.copy.gif',
            'display'   => 'block',
        ),
/*
		'close' => array(
			'�������',
			'Close',
			'link' => 'if(window.top.opener)window.top.opener.focus();window.top.close()',
			'img' => 'icon.close.gif',
			'display' => 'block',
			'hint' => array(
				'������� ����',
				'Close window',
			),
			'show_title' => true,
		),
		'moveup' => array(
			'����',
			'Move up',
			'link'	=> 'cnt.ChangePriority(-1)',
			'img' 	=> 'icon.moveup.gif',
			'display'	=> 'none',
			'show_title' => true,
		),
		'movedown' => array(
			'����',
			'Move down',
			'link'	=> 'cnt.ChangePriority(1)',
			'img' 	=> 'icon.movedown.gif',
			'display'	=> 'none',
			'show_title' => true,
		),
*/
	);
}

$multielemactions = array(
    'create' => array(
        '�������',
        'create',
        'onclick' => 'showSelectDiv',
        'div_id' => '',
        'visible' => true,
    ),
    'delete' => array(
        '�������',
        'delete',
        'onclick' => 'deleteListsElem',
        'div_id' => '',
        'visible' => true,
    ),
);

//�������� �������, ��� ������� � ��������� ������ �������� ����� "���"
if (!isset($modules_selectors)){
    $modules_selectors = array(
        'publications',
        'infoblocks',
        'strings',
        'dynamic_img',
    );
}
?>