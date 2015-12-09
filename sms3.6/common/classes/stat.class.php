<?php

/**
 * Класс для сбора статистики
 * Приходится использовать функции mysql_... потому что этот класс используется и в движке 2.6
 */

if (defined('STAT_DATABASE') && STAT_DATABASE) {
	$stat_db = '`'.STAT_DATABASE.'`.';
}
else {
	$stat_db = '';
}

if (!defined('STAT_SESSIONS_TABLE')) define('STAT_SESSIONS_TABLE', $stat_db.'stat_sessions');
if (!defined('STAT_LOG_TABLE')) define('STAT_LOG_TABLE', $stat_db.'stat_log');
if (!defined('STAT_PAGES_TABLE')) define('STAT_PAGES_TABLE', $stat_db.'stat_pages');
if (!defined('STAT_AGENTS_TABLE')) define('STAT_AGENTS_TABLE', $stat_db.'stat_agents');
if (!defined('STAT_SETTINGS_TABLE')) define('STAT_SETTINGS_TABLE', $stat_db.'stat_settings');
if (!defined('STAT_TEMP_TABLE')) define('STAT_TEMP_TABLE', $stat_db.'stat_temp_log');
// stat general tables
if (!defined('STAT_IPS_TABLE')) define('STAT_IPS_TABLE', 'stat.stat_ips');
if (!defined('STAT_SEARCHAGENTS_TABLE')) define('STAT_SEARCHAGENTS_TABLE', 'stat.stat_searchagents');
if (!defined('STAT_COUNTRIES_TABLE')) define('STAT_COUNTRIES_TABLE', 'stat.stat_countries');
if (!defined('STAT_CITIES_IP_TABLE')) define('STAT_CITIES_IP_TABLE', 'stat.stat_cities_ru_ip');
if (!defined('STAT_CITIES_TABLE')) define('STAT_CITIES_TABLE', 'stat.stat_cities_ru');
if (!defined('STAT_REGIONS_TABLE')) define('STAT_REGIONS_TABLE', 'stat.stat_regions_ru');

// Длина поля agent в базе данных в таблице stat_agents
define('STAT_AGENT_LENGTH', 80);

class TStatClass {

    /**
     * Возвращает признак - новая сессия или нет
     *
     * @return bool
     */
    function isNewSession() {
        return isset($GLOBALS['statlog_new_session']);
    }

    /**
     * Возвращает номер текущей сессии
     *
     * @return int
     */
    function getSessId() {
        $sess_id =
        isset($GLOBALS['statlog_new_session']) && !empty($GLOBALS['statlog_new_session']) ? $GLOBALS['statlog_new_session'] :
        (isset($GLOBALS['statlog_session']) && !empty($GLOBALS['statlog_session']) ? $GLOBALS['statlog_session'] : 0);
        return $sess_id;
    }

    /**
     * Возвращает последнюю сессию
     *
     * @param int $long_ip
     * @param int $agent_id
     * @param string $host
     * @return array
     */
    function getLastSession($long_ip, $agent_id, $host) {
		$columns = $this->STAT_GetSql('SHOW COLUMNS FROM '.STAT_SESSIONS_TABLE, 'Field');
		if (!isset($columns['host'])) {
		    mysql_query("ALTER TABLE ".STAT_SESSIONS_TABLE." ADD `host` VARCHAR( 255 ) NOT NULL ;");
		    mysql_query("UPDATE ".STAT_SESSIONS_TABLE." SET host=(SELECT p.host FROM ".STAT_PAGES_TABLE." AS p, ".STAT_LOG_TABLE." AS log WHERE log.page_id=p.id AND log.sess_id=sess_id LIMIT 1)");
		}
        $sess_row = $this->STAT_GetSqlRow("SELECT sess_id, time_last FROM ".STAT_SESSIONS_TABLE." WHERE ip='".$long_ip."' AND agent_id='".$agent_id."' AND host='".$host."' ORDER BY time_last DESC LIMIT 1");
		return $sess_row;
    }

    /**
     * Проверка - забанен этот IP или нет
     *
     * @return bool
     */
    function isUserBanned() {
        $ip = $this->STAT_GetIpAddress();
        $long_ip = ip2long($ip);
        $ban = $this->STAT_GetSqlRow("SELECT * FROM stat_banlist WHERE ip='$long_ip' LIMIT 1");
        if ($ban && $ban['id']) return true;
        return false;
    }

    /**
     * Возвращает id агента по HTTP_USER_AGENT
     *
     * @return int
     */
    function getAgentId() {
        return $this->STAT_GetSqlValue("SELECT id FROM ".STAT_AGENTS_TABLE." WHERE agent='".mysql_escape_string(substr($_SERVER['HTTP_USER_AGENT'], 0, STAT_AGENT_LENGTH))."'");
    }

    /**
     * Возвращает uri_id для stat_log
     *
     * @param string $host
     * @param string $uri
     * @param string $ref
     * @return int
     */
    function STAT_GetPageId($host, $uri, $ref='') {
        $page_id = $this->STAT_GetSqlValue("SELECT id FROM ".STAT_PAGES_TABLE." WHERE host='".mysql_escape_string($host)."' AND uri='".mysql_escape_string($uri)."'");
        if (empty($page_id)) {
            $search_ph = $this->STAT_GetSearchPhrase($ref);
            mysql_unbuffered_query("INSERT INTO ".STAT_PAGES_TABLE." SET host='".mysql_escape_string($host)."', uri='".mysql_escape_string($uri)."', search_ph='".mysql_escape_string($search_ph)."'");
            $page_id = mysql_insert_id();
        }
        return $page_id;
    }

    /**
     * Возвращает agent_id для stat_log
     *
     * @return array
     */
    function STAT_GetAgentId($agent = '') {
        if (!$agent) $agent = $_SERVER['HTTP_USER_AGENT'];
        $agent_row = $this->STAT_GetSqlRow("SELECT id, robot FROM ".STAT_AGENTS_TABLE." WHERE agent='".mysql_escape_string(substr($agent,0,STAT_AGENT_LENGTH))."'");
        if ($agent_row) {
            $agent_id = $agent_row['id'];
            $robot = $agent_row['robot'];
        }
        else {
            $info = $this->STAT_GetAgentInfo($agent);
            mysql_unbuffered_query("
            INSERT INTO ".STAT_AGENTS_TABLE." SET
			agent='".mysql_escape_string($info['agent'])."',
			name='".mysql_escape_string($info['br'])."',
			os='".mysql_escape_string($info['os'])."',
			robot='".$info['robot']."'");
            $robot = $info['robot'];
            $agent_id = mysql_insert_id();
        }
        return array($agent_id, $robot);
    }

    /**
     * Возвращает информацию об агенте
     *
     * @param string $agent
     * @return array
     */
    function STAT_GetAgentInfo($agent) {
        $robot = 0;
        $op_systems = array(
        'win95' => 'Windows 95',
        'win98' => 'Windows 98',
        'winnt' => 'Windows NT',
        'win32' => 'Windows',
        'win16' => 'Windows',
        'unix' 	=> 'Linix',
        'hp-ux' => 'Linix',
        'linux' => 'Linux',
        'lynx' 	=> 'Linux',
        'x11'	=> 'Linux',
        'mac_powerpc' 	=> 'Macintosh',
        'macintosh' 	=> 'Macintosh',
        'mac' 			=> 'Macintosh',
        'ppc' 			=> 'Macintosh',
        'freebsd' 	=> 'FreeBSD',
        'openbsd' 	=> 'OpenBSD',
        'netbsd' 	=> 'NetBSD',
        'palmos' 	=> 'PalmOS',
        'sunos' 	=> 'SunOS',
        'symbianos' => 'SymbianOS',
        'os/2' 		=> 'OS/2',
        );
        $op_windows = array(
        'windows nt 5.0' => 'Windows 2000',
        'windows nt 5.1' => 'Windows XP',
        'windows nt 5.2' => 'Windows Longhorn',
        'windows nt 6.0'	=> 'Windows Vista',
        'windows xp' => 'Windows XP',
        'windows nt' => 'Windows NT',
        'windows nt windows ce' => 'Windows NT CE',
        'windows ce' => 'Windows CE',
        'windows nt 4.0' => 'Windows NT',
        'windows me' => 'Windows Me',
        'win 9x 4.90' => 'Windows Me',
        'windows 2000' => 'Windows 2000',
        'windows 95' => 'Windows 95',
        'windows 98' => 'Windows 98'
        );
        // Определение браузера и системы
        if (preg_match('/(Mozilla|Opera)\/(\S*) ([^(]*\s)?\((.+?)\)(\s?(\w*) (\S*).*)?/', $agent, $m1) && strpos($agent, 'http://') === false) {
            $m2 = split(';| |; ', strtolower($m1[4]));
            if ($m1[1]=='Opera') $br = 'Opera '.$m1[2];
            elseif ($m1[6]=='Opera') $br = 'Opera '.$m1[7];
            elseif ($m2[1]=='msie') $br = 'MS Internet Explorer '.$m2[2];
            elseif (substr($m2[1], 0, 9) == 'konqueror') $br = 'Konqueror '.substr($m2[1], 10);
            elseif (substr(end($m2), 0, 3) == 'rv:') $br = 'Mozilla '.substr(end($m2), 3);
            else $br = 'Netscape Navigator '.$m1[2];

            if ($sys = array_intersect(array_keys($op_systems), $m2)) {
                $os = $op_systems[array_shift($sys)];
            }
            if (!$os) {
                $m2 = split('; ?', strtolower($m1[4]));
                if ($sys = array_intersect(array_keys($op_windows), $m2)) {
                    $os = $op_windows[array_shift($sys)];
                }
            }
        }
        // Определение поисковика
        if (empty($br) || empty($os)) {
            $robot = 1;
            $br = preg_replace('/(Mozilla(\S*)|compatible(\S?)|http:\/\/(\S*)|\[\w*\]|)/', '', $agent);
            $br = str_replace(array('(', ')', ';', '+'), '', $br);
            $br = explode(' ', trim($br));
            $br = trim($br[0]);
            if (empty($br)) $br = 'Unknown';
            if (empty($os)) $os = 'Unknown';
        }
        return array(
        'agent' => $agent,
        'br' => $br,
        'os' => $os,
        'robot' => $robot
        );
    }

    /**
     * Возвращает поисковую фразу
     *
     * @param string $ref
     * @return string
     */
    function STAT_GetSearchPhrase($ref) {
        if (empty($ref)) return '';

        $referer = parse_url($ref);
        $search_ph = '';

        if ($referer['query']) {
            // Проверка на поисковый запрос
            $agent = $this->STAT_GetSqlRow("SELECT search_var, method FROM ".STAT_SEARCHAGENTS_TABLE." WHERE '".$referer['host']."' LIKE CONCAT('%',host,'.%')");
            if ($agent) {
                // Определение поискового запроса
                if (preg_match('/\&?('.$agent['search_var'].')=(.*?)(\&|$)/', urldecode($referer['query']), $m))
                $search_ph = urldecode($m[2]);
                if ($agent['method']) {
                    $method = 'STAT_GetPhrase4'.$agent['method'];
                    if (method_exists($this, $method)) {
                        $search_ph = $this->$method($search_ph, $ref);
                    }
                }
            }
        }
        if (!empty($search_ph)) {
            $phrase = iconv("UTF-8", "WINDOWS-1251", $search_ph);
            if (!empty($phrase)) $search_ph = $phrase;
        }
        return $search_ph;
    }

    /**
     * Специально для Yandex - перевод строки из koi8-r в win-1251
     *
     * @param string $phrase
     * @param string $ref
     * @return string
     */
    function STAT_GetPhrase4Yandex($phrase, $ref) {
        if (preg_match('/(\.ru\/yandpage|\.ru\/yandbtm)/',$ref)) {
            return convert_cyr_string($phrase, 'k', 'w');
        }
        return $phrase;
    }

    /**
     * Специально для Google - перевод строки из utf-8 в win-1251
     *
     * @param string $phrase
     * @param string $ref
     * @return string
     */
    function STAT_GetPhrase4Google($phrase, $ref) {
        // видимо, по умолчанию у гугла в urf8,
        // по-этому декодировать надо когда не windows-1251
        if (!preg_match('/ie=windows-1251/i',$ref) || true) {
            if (function_exists('iconv')) {
                return iconv('utf-8', 'windows-1251', $phrase);
            }
        }
        return $phrase;
    }

    /**
     * Специально для MSN - перевод строки из utf-8 в win-1251
     *
     * @param string $phrase
     * @param string $ref
     * @return string
     */
    function STAT_GetPhrase4Msn($phrase, $ref) {
        if (function_exists('iconv')) {
            return iconv('utf-8', 'windows-1251', $phrase);
        }
        return $phrase;
    }

    /**
     * Возвращает страну по ip или WW, если невозможно определить страну
     *
     * @param string $ip
     * @return string
     */
    function STAT_WhoisIP($ip) {
        if (!$ip) return 'WW';

        $country = $this->STAT_GetSqlValue("SELECT country FROM ".STAT_IPS_TABLE." WHERE INET_ATON('".$ip."') BETWEEN ip_from AND ip_to");
        if (!$country) $country = 'WW';

        return $country;
    }

    /**
     * Возвращает город по ip или 0, если невозможно определить город
     *
     * @param string $ip
     * @return string
     */
    function STAT_get_City($ip) {
        if (!$ip) return 0;
        return (int)$this->STAT_GetSqlValue("SELECT city FROM ".STAT_CITIES_IP_TABLE." WHERE INET_ATON('".$ip."') BETWEEN ip_from AND ip_to");
    }

    /**
     * Возвращает Ip адрес
     *
     * @return string
     */
    function STAT_GetIpAddress() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip) {
            return $ip;
        }
        $ip = $_SERVER['REMOTE_HOST'];
        if ($ip) {
            return $ip;
        }
        $z = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = $z[0];
        return $ip;
    }

    /**
     * Сохранение новой сесии
     *
     * @param int $long_ip
     * @param int $agent_id
     * @param int $time
     * @param string $host
     * @param array $sess_row
     * @return int
     */
    function newSession($long_ip, $agent_id, $time, $host, $sess_row = array()) {
        mysql_query("INSERT INTO ".STAT_SESSIONS_TABLE." SET
			ip='".$long_ip."',
			agent_id='".$agent_id."',
			time='".$time."',
			host='".$host."',
			new_visitor='".(!empty($sess_row) ? 0 : 1)."'
		");
        return mysql_insert_id();
    }

    /**
     * Обновление данных в stat_log
     *
     */
    function updateLog($sess_id = 0, $time = 0, $document_status = 0, $host = '', $uri = '', $referer = '', $old = true) {

        $page_id = $this->STAT_GetPageId($host, $uri);

        /***********************************************************/
        // Определение referer адреса
        /***********************************************************/
        if (isset($referer)){
            if (preg_match('|^http://([^/]*)(.*)$|i', $referer, $regs)) {
                $ref_id = $this->STAT_GetPageId($regs[1], $regs[2], $referer);
            }
        }
        // Если referer адреса не нашли и сессия продолжается,
        // то возмем referer адрес как предыдущую страницу из этой сессии со статусом 200
        if (!isset($ref_id) && $old) {
            $ref_id = $this->STAT_GetSqlValue("SELECT page_id FROM ".STAT_LOG_TABLE." WHERE sess_id='".$sess_id."' AND status='200' ORDER BY time DESC LIMIT 1");
        }

        /***********************************************************/
        // Добавляем запись в stat_log
        /***********************************************************/
        $sql = "INSERT INTO ".STAT_LOG_TABLE." (`sess_id`, `time`, `ref_id`, `page_id`, `status`) VALUES ('".$sess_id."', '".$time."', '".$ref_id."', '".$page_id."', '".$document_status."')";
        mysql_unbuffered_query($sql);
        $err = mysql_error();
        if (!empty($err)) {
            if (function_exists('log_error')) log_error("<b>".$sql."</b><br />".$err, __FILE__, __LINE__);
            return $err;
        }

        return array($page_id, $ref_id);
    }

    /**
     * Обновление данных в stat_sessions
     *
     */
    function updateSession($sess_id = 0, $time = 0, $document_status = 0, $client_id = 0, $page_id = 0, $ref_id = 0, $ip, $new = false, $agent = '') {

        $sql = "UPDATE ".STAT_SESSIONS_TABLE." SET ";
        $sql .= "time_last='".$time."', ";
        if ($document_status == 200) {
            $sql .= "loads=loads+1, ";
            $sql .= "last_page='".$page_id."', ";
        }
        if ($client_id) {
            $sql .= "client_id='".$client_id."', ";
        }

        if (!defined('ENGINE_TYPE') || ENGINE_TYPE == 'site') $new = $this->isNewSession();
        if ($new) {

            $country = $this->STAT_WhoisIP(long2ip($ip));
            $city = $this->STAT_get_City(long2ip($ip));
            list($agent_id, $robot) = $this->STAT_GetAgentId($agent);

            $sql .= "ref_id='".$ref_id."', first_page='".$page_id."', agent_id='".$agent_id."', city='".$city."', robot='".$robot."', country='".$country."', ";
        }

        $sql .= "path=IF(path<>'', CONCAT(path, ' ".$page_id."'), '".$page_id."') ";
        $sql .= "WHERE sess_id='".$sess_id."'";

        mysql_unbuffered_query($sql);
        $err = mysql_error();
        if (!empty($err)) {
            if (function_exists('log_error')) log_error("<b>".$sql."</b><br />".$err, __FILE__, __LINE__);
            return $err;
        }
        return true;
    }

    /**
     * Сохранение во временную таблицу
     *
     * @return unknown
     */
    function writeTempData($sess_id = 0, $time = 0, $document_status = 0, $client_id = 0, $host = '', $uri = '', $referer = '', $agent = '', $ip = 0, $is_new_session=1) {


        /*if (!$referer) {
            $referer = $this->STAT_GetSqlValue("SELECT uri FROM ".STAT_TEMP_TABLE." WHERE sess_id='".$sess_id."' ORDER BY time DESC LIMIT 1");
            if ($referer) $referer = 'http://'.$host.$referer;
        }*/

        $data['sess_id'] = $sess_id;
        $data['time'] = $time;
        $data['document_status'] = $document_status;
        $data['client_id'] = $client_id;
        $data['host'] = mysql_real_escape_string($host);
        $data['uri'] = mysql_real_escape_string($uri);
        $data['referer'] = mysql_real_escape_string($referer);
        $data['ip'] = $ip;
        $data['agent'] = mysql_real_escape_string($agent);
        $data['is_new_session'] = $is_new_session;

        $sql = 'INSERT INTO '.STAT_TEMP_TABLE.' (`'.implode('`,`', array_keys($data)).'`) VALUES ("'.implode('","', $data).'")';

        mysql_query($sql);
        $err = mysql_error();
        if($err){
			$columns = $this->STAT_getSQL('SHOW COLUMNS FROM '.STAT_TEMP_TABLE, 'Field');
			if (!isset($columns['is_new_session'])) {
			    mysql_query("ALTER TABLE ".STAT_TEMP_TABLE." ADD `is_new_session` tinyint(1) unsigned NOT NULL default '1' ;");
			}

	        $sql = "
	        CREATE TABLE IF NOT EXISTS ".STAT_TEMP_TABLE." (
	          `id` int(10) unsigned NOT NULL auto_increment,
	          `sess_id` varchar(32) NOT NULL,
	          `time` int(11) NOT NULL,
	          `document_status` int(10) unsigned NOT NULL,
	          `client_id` int(10) unsigned NOT NULL,
	          `host` varchar(255) NOT NULL,
	          `uri` varchar(255) NOT NULL,
	          `referer` varchar(255) NOT NULL,
	          `ip` int(11) NOT NULL,
	          `agent` varchar(255) NOT NULL,
	          `is_new_session` tinyint(1) unsigned NOT NULL default '1',
	          PRIMARY KEY  (`id`)
	        ) ENGINE=MyISAM  DEFAULT CHARSET=cp1251
	        ";
	        mysql_query($sql);

	        /*$err = mysql_error();
	        if (!empty($err)) {
	            return $err;*/
        }

        $id = mysql_insert_id();
        return $id;
    }

################################################################################################################

    // Get SQL Row from result
    function STAT_GetSqlRow($query) {
        return array_shift($this->STAT_GetSql($query, 'number'));
    }

    ### Вытаскивает первое значение из запросов вида (SELECT COUNT(*)... WHERE time>=$from AND time<=$to)
    function STAT_GetSqlValue($query, $from = 0, $to = 0) {
        $ar = $this->STAT_GetSql($query, 'number');
        if (is_array($ar)) $ar = array_shift($ar);
        if (is_array($ar)) $ar = array_shift($ar);
        return $ar;
    }

    // Get mysql Query
    function STAT_GetSql($sql, $key) {
        $res = array();
        if ($sql) $rows = mysql_unbuffered_query($sql);
        if ($rows) {
            if (empty($key)) {
                $field = mysql_fetch_field($rows);
                $key = $field->name;
            }
            while ($row = mysql_fetch_assoc($rows)) {
                if ($key == 'number') $res[] = $row;
                else $res[$row[$key]] = $row;
            }
            mysql_free_result($rows);
        }
        return $res;
    }
}