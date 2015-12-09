<?php
// default objects
if (!defined('DEFAULT_CLASS')) define ('DEFAULT_CLASS', 'TPage');
if (!defined('DEFAULT_OBJ_PAGE')) define ('DEFAULT_OBJ_PAGE', 'home');
if (!defined('DEFAULT_OBJ_HOME')) define ('DEFAULT_OBJ_HOME', 'home');
if (!defined('DEFAULT_OBJ_ERROR')) define ('DEFAULT_OBJ_ERROR', '404');
if (!defined('LANG_SELECT')) define('LANG_SELECT', false);

$commonCfg = & Registry::get('TCommonCfg');

class TCommonCfg
{

    var $domain;
    var $site_domains;
    var $site;
    var $cfg_types;
    var $all_blocks;
    var $all_elements;
    var $mod_names;
    var $ent_blocks;
    var $mod_blocks;
    var $func_modules;
    var $nested_blocks;

    var $cfg;

    //------------------------------------------------------------------------------------------

    function TCommonCfg() {

        include_once(PATH_CONFIG . "common.cfg.php");
        global $domain, $langs;
        if (!defined('LANG_DEFAULT')) define('LANG_DEFAULT', 'ru');
        if (!defined('DISABLE_COOKIE_LANGUAGE')) define('DISABLE_COOKIE_LANGUAGE', true);

        $this->cfg['types'] = array();
        $this->cfg['function_modules'] = array();
        $GLOBALS['site_domains'] = array();
        $lang = '';

        if (!isset($domain)) {
            $domain = $this->getDomainName($_SERVER['HTTP_HOST']);
        }

        if (LANG_SELECT === false) {
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, '?') !== false) {
                $uri = explode('?', $uri);
                $uri = $uri[0];
            }
            $_dirs = explode("/", $uri);
            if (isset($_dirs[1]) && in_array($_dirs[1], $langs)) {
                $lang = (strlen($_dirs[1]) == 2) ? $_dirs[1] : LANG_DEFAULT;
            } else {
                if (!DISABLE_COOKIE_LANGUAGE) {
                    $lang = isset($_COOKIE[LANG_COOKIE_NAME]) && in_array($_COOKIE[LANG_COOKIE_NAME], $langs) ? $_COOKIE[LANG_COOKIE_NAME] : LANG_DEFAULT;
                }
                else {
                    $lang = LANG_DEFAULT;
                }
            }
        } else {
            if (!DISABLE_COOKIE_LANGUAGE) {
                $lang = isset($_COOKIE[LANG_COOKIE_NAME]) && in_array($_COOKIE[LANG_COOKIE_NAME], $langs) ? $_COOKIE[LANG_COOKIE_NAME] : LANG_DEFAULT;
            }
            else {
                $lang = LANG_DEFAULT;
            }
        }

        $table = 'sites';
        $status = sql_getRows('SHOW TABLE STATUS LIKE "' . $table . '"');
        if (empty($status) || (int)sql_getValue('SELECT COUNT(*) FROM sites') < 2) { // Если у нас один домен
            $GLOBALS['site_domains'][$domain] = array(
                'id' => !empty($status) ? sql_getValue('SELECT id FROM sites ORDER BY priority LIMIT 1') : 1,
                'name' => $domain,
                'descr' => '',
                'templates' => '',
                'modules' => '',
                'alias' => '',
                'langs' => array(),
            );
            $i = 1;
            foreach ($GLOBALS['site_languages'] as $k => $v) {
                $root_id = isset($v['root_id']) ? $v['root_id'] : 100;
                $GLOBALS['site_domains'][$domain]['langs'][$k] = $v;
                $GLOBALS['site_domains'][$domain]['langs'][$k]['id'] = $i;
                $GLOBALS['site_domains'][$domain]['langs'][$k]['pid'] = $i;
                $GLOBALS['site_domains'][$domain]['langs'][$k]['root_id'] = $root_id;
                $GLOBALS['site_domains'][$domain]['langs'][$k]['priority'] = $i;
                $i++;
                $this->cfg['types'][$root_id] = $cfg['types'];
                $this->cfg['function_modules'][$root_id] = $cfg['function_modules'];
            }
            $root_id = isset($GLOBALS['site_languages'][$lang]['root_id']) ? $GLOBALS['site_languages'][$lang]['root_id'] : 100;
            define ('ROOT_ID', $root_id);
        }
        else {
            // Несколько сайтов с одинаковой настройкой из configs/common.cfg.php
            $sites = sql_getRows('SELECT * FROM sites ORDER BY priority');
            foreach ($sites as $key => $val) {
                $_domain = $this->getDomainName($val['name']);
                $i = 1;
                $_langs = sql_getRows("SELECT * FROM sites_langs WHERE pid=" . $val['id'] . " ORDER BY priority");
                $GLOBALS['site_domains'][$_domain] = $val;
                $GLOBALS['site_domains'][$_domain]['name'] = $_domain;
                foreach ($_langs as $k => $v) {
                    $GLOBALS['site_domains'][$_domain]['langs'][$v['name']] = $v;
                    $GLOBALS['site_domains'][$_domain]['langs'][$v['name']]['id'] = $i;
                    $GLOBALS['site_domains'][$_domain]['langs'][$v['name']]['pid'] = $i;
                    $GLOBALS['site_domains'][$_domain]['langs'][$v['name']]['priority'] = $i;
                    $GLOBALS['site_domains'][$_domain]['langs'][$v['name']]['root_id'] = $v['root_id'];
                    $i++;
                    $this->cfg['types'][$v['root_id']] = $cfg['types'];
                    $this->cfg['function_modules'][$v['root_id']] = $cfg['function_modules'];
                }
            }
            $site = sql_getRow("SELECT * FROM sites WHERE name='" . $domain . "' OR FIND_IN_SET('" . $domain . "', alias) LIMIT 1");
            if ($site) {
                $domain = $this->getDomainName($site['name']);
                $root_id = sql_getValue("SELECT root_id FROM sites_langs WHERE pid=" . $site['id'] . " AND name='{$lang}'");
                if ($root_id) define('ROOT_ID', $root_id);
            }
        }

        $this->cfg['elements'] = $cfg['elements'];
        $GLOBALS['cfg'] = &$this->cfg;
    }

    function getDomainName($name) {
        $domain = substr($name, 0, 4) == 'www.' ? substr($name, 4) : $name;
        $pos = strpos($domain, ":");
        if ($pos) $domain = substr($domain, 0, $pos);
        return $domain;
    }

    function getSiteByRootID($root_id) {
        global $site_domains;
        foreach ($site_domains as $site => $value) {
            foreach ($value['langs'] as $l) {
                if ($root_id == $l['root_id']) return $site;
            }
        }
        return false;
    }

    function getLangByRootID($root_id) {
        global $site_domains;
        foreach ($site_domains as $site => $value) {
            foreach ($value['langs'] as $l) {
                if ($root_id == $l['root_id']) return $l['name'];
            }
        }
        return false;
    }

    /**
     * Возвращает root_id основного сайта
     * @return int
     */
    function getMainRootID() {
        $status = sql_getRows('SHOW TABLE STATUS LIKE "sites"');
        if (empty($status) || (int)sql_getValue('SELECT COUNT(*) FROM sites') < 2) { // Если у нас один домен
            return sql_getValue("SELECT root_id FROM tree ORDER BY root_id DESC LIMIT 1");
        } else {
            $status = sql_getRows('SHOW TABLE STATUS LIKE "sites_langs"');
            if (empty($status)) {
                return sql_getValue("SELECT root_id FROM sites ORDER BY root_id DESC LIMIT 1");
            } else {
                return sql_getValue("SELECT root_id FROM sites_langs ORDER BY priority, id LIMIT 1");
            }
        }
    }
}