<?php
/**
* ����� ��������� �������� �������� �� �����
*/
class rusoftInfo{
    //  ����������� IP ������ ��� ��������� � �������
    protected $allowIpList = array('80.243.13.242');
    protected $validVersion = array('2.6','2.7','3.4','3.5','3.6');
    //  ������ ��� ���������� ����������� ����������� ��� ��� ���� ����������
    protected $infoLog = array();
    protected $infoLogBoolean = array();
    protected $rootPath = '';
    protected $resultStatus = true;
    protected $wwwVersion = array();
    //  ������ ��������� �������
    protected $simplePasswords = array(
        '123', '1234', 'abracadabra', 'rusoft123'
    );
    //  ��������� ����� �� �����
    protected $chmod = array(
        '3.4' => array(
            '/backup'                   => '775',
            '/cache'                    => '775',
            '/cache/blocks'             => '775',
            '/cache/files'              => '775',
            '/cache/sessions'           => '775',
            '/cache/sessions_adm'       => '775',
            '/cache/tables'             => '775',
            '/cache/templates'          => '775',
            '/cache/templates_adm_c'    => '775',
            '/cache/templates_c'        => '775',
            '/cache/tmp'                => '775',
            '/cache/tmp_adm'            => '775',
            '/configs'                  => '775',
            '/files'                    => '775'
        ),
        '2.6' => array(
            '/backup'                   => '775',
            '/admin/backup'             => '775',
            '/cache'                    => '775',
            '/admin/sid'                => '775',
            '/sessions'                 => '775',
            '/files'                    => '775',
        )
    );
    //  ������ ��������� ��������

    protected $reportMessage = array(
        'INDEX_NF'              => '������, ���� %s <span style="color:red;">�� ������</span>'
        ,'DIR_NF'               => '%s: <span style="color:red;">�� �������</span>'
        ,'IP_WRONG'             => 'Access denied. IP address not allowed'
        ,'SITE_VERSION'         => '������ �����: %s'
        ,'ADMIN_VERSION'        => '������ �������: %s'
        ,'IS_ROBOTS_TXT'        => '������� ����� robots.txt: %s'
        ,'ROBOTS_TXT_CONTENT'   => '���������� ����� robots.txt: %s'
        ,'LIBS_PATH'            => '������� ���������� ������������ libs ������ (����): %s'
        ,'LIBS_PATH_ADMIN'      => '������� ���������� ������������ libs ������ (�������): %s'
        ,'FCK_ALIAS'            => '������ �� FCK: %s'
        ,'FCK_DIR'              => '������� ���������� FCK � �������: %s'
        ,'CHMOD'                => '����� �� �����: %s'
        ,'CHMOD_FILES'          => '����� �� ����� � ����� ���������� /FILES: %s'
        ,'ROOT_FILES'           => '����� � �����: %s'
        ,'CONF_FILES'           => 'conf �����: %s'
        ,'CACHE_FILES'          => '����� ���������� /CACHE: %s'
        ,'STR_CONST'            => '��������� ���������: %s'
        ,'COPY'                 => '������� ��������� � ������: %s'
        ,'ABOUT_SCREEN'         => '������� �������� /aboutsite: %s'
        ,'ROOT_USER'            => '�������� ������� ������������ ROOT: %s'
        ,'SIMPLE_PASSWORD'      => '�������� ������� ��������� �������: %s'
        ,'ABSOLUTE_URL'         => '����� ���������� ������: %s'
        ,'MAIL_CONFIG'          => '��������� �����: %s'
        ,'CROCUS_ENTRY'         => '����� ��������� ������ crocus.rusoft.ru � php �����: %s'
        ,'STR_RUSOFT1'          => '����� ��������� ������ &laquo;rusoft&raquo; � ��������� ����������: %s'
        ,'STR_RUSOFT2'          => '����� ��������� ������ &laquo;������&raquo; � ��������� ����������: %s'
        ,'META_DATA'            => 'META ������, title=%s, keywords=%s, description=%s'
    );

    protected function getCrocusEntry()
    {
        $site_list = $this->getDirFiles($this->rootPath . '/modules', true);
        $admin_list = $this->getDirFiles($this->rootPath . '/admin', true);
        $summary_list = array_merge($site_list, $admin_list);
        $result = array();
        $bool = true;
        foreach ($summary_list as $k => $v)
        {
            $temp = explode('.', $v);
            if ($temp[sizeof($temp)-1] == 'php')
            {
                if (strstr(file_get_contents($v), 'crocus.rusoft.ru'))
                {
                    $result[] = $v;
                    $bool = false;
                }
            }
        }
        $this->setLog($this->getReportMessage('CROCUS_ENTRY'), $result, $bool);
    } // getCrocusEntry

    protected function getMailConfig()
    {
        $file = $this->rootPath . '/admin/modules/notify/config.ini';
        if (!file_exists($file))
        {
            $result = false;
        } else {
            $result = true;
        }
        $this->setLog($this->getReportMessage('MAIL_CONFIG'), $result);
    } // getMailConfig

    /**
    * ����� ���������� ������
    * SELECT database( )
    */
    protected function getAbsoluteUrl()
    {
        $bool = true;
        $search_term = 'http://';
        require_once $this->rootPath . '/admin/connect.php';
        $q = mysql_query('SHOW TABLES');
        $tables = array();
        while ($r = mysql_fetch_array($q))
        {
            $tables[] = current($r);
        }

        $primary_key = array();
        foreach ($tables as $k => $v)
        {
            $q = mysql_query('SHOW FIELDS FROM `'.$v.'`');
            $tblfields[$v] = array();
            while ($r = mysql_fetch_assoc($q))
            {
                $tblfields[$v][] = $r['Field'];
                if ($r['Extra'] == 'auto_increment') $primary_key[$v] = $r['Field'];
            }
        }

        $result = array();
        foreach ($tblfields as $table => $fields)
        {
            $search = array();
            foreach ($fields as $k => $v)
            {
                $search[] = '`' . $v . '` LIKE "%'.$search_term.'%"';
            }
            $sql = 'SELECT * FROM `' . $table . '` WHERE ' . implode(' OR ', $search);
            $q = mysql_query($sql);
            if (mysql_num_rows($q))
            {
                $bool = false;
                $set = array();
                while ($r = mysql_fetch_assoc($q))
                {
                    $set = array();
                    foreach ($r as $k => $v)
                    {
                       if (strstr($v, $search_term)){

                           $matches = array();
                           $text = htmlspecialchars($v);
                           preg_match_all("/(http:\/\/)([a-zA-Z0-9-.\_]*(rusoft)+)(\.ru)/i", $text, $matches);

                           if ($matches[0]){
                               $text = str_replace($matches[0][0], '<span style="color:red;">'.$matches[0][0].'</span>', $text);
                           }
                           $set[$k] = $text;
                       }
                    }
                    if (isset($primary_key[$table]) && isset($r[$primary_key[$table]])) {
                        $result[$table][$r[$primary_key[$table]]] = $set;
                    }
                    //;
                }
            }
        }
        $this->setLog($this->getReportMessage('ABSOLUTE_URL'), $result, $bool);
    } // getAbsoluteUrl

    /**
    * �������� ������� ��������� �������
    *
    */
    protected function getSimplePasswords()
    {
        require_once $this->rootPath . '/admin/connect.php';
        $bool = true;

        $md5_pass = array();
        $md5_pass_decode = array();
        foreach ($this->simplePasswords as $k => $v)
        {
            $like_md5 = md5($v);
            $md5_pass[] = $like_md5;
            $md5_pass_decode[$like_md5] = $v;
        }

        $result = array();
        $q = mysql_query('SELECT * FROM `admins` WHERE `pwd` IN ("'.implode('","', $md5_pass).'")');
        while ($r = mysql_fetch_assoc($q))
        {
            $bool = false;
            $result[] = array(
                'id' => $r['id'],
                'login' => $r['login'],
                'pwd' => $md5_pass_decode[$r['pwd']]
            );
        }


        $this->setLog($this->getReportMessage('SIMPLE_PASSWORD'), $result, $bool);
    }

    protected function getChmodFiles($dir = '')
    {
        $this->setLog($this->getReportMessage('CHMOD_FILES'), $this->getDirPerms(), $this->resultStatus);
    }

    /**
    * ����������� ���������� ������������� ���� ��� ����� � /FILES
    * @return array ��� ����������
    */
    protected function getDirPerms($dir = '', $recursive = true)
    {
        if (!isset($report)) $report = array();
        if (!$dir) $dir = $this->rootPath . '/files';
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $path = $dir . '/' . $file;
                    $octal_val = substr(sprintf('%o', fileperms($path)), -3);

                    if (is_dir($path)) {
                        if ($octal_val < 775)
                        {
                            $octal_val = '<span style="color:red;">'.$octal_val.'</span>';
                            $this->resultStatus = false;
                        }
                        if ($recursive) $report += $this->getDirPerms($path);
                    } elseif ($octal_val < 664) {
                        $this->resultStatus = false;
                        $octal_val = '<span style="color:red;">'.$octal_val.'</span>';
                    }
                    $report[$path] = $octal_val;
                }
            }
            closedir($handle);
        }
        return $report;
    } // getDirPerms

    /**
    * ����������� ���������� ������������� ���� ��� ��������� �����
    *
    */
    protected function getChmod()
    {
        $message = '';
        $bool = true;
        if (in_array($this->wwwVersion['site'], array('3.4','3.5')))
        {
            $engine = '3.4';
        } else $engine = '2.6';

        foreach ($this->chmod[$engine] as $dir => $code)
        {
            $path = $this->rootPath . $dir;
            if (!is_dir($path))
            {
                $message .= "\n" . sprintf($this->getReportMessage('DIR_NF'), $dir);
                $bool = false;
            } else {
                $octal_val = substr(sprintf('%o', fileperms($path)), -3);
                if ($octal_val < $code)
                {
                    $message .= "\n" . $dir . ': <span style="color:red;">' . $octal_val . '</span>';
                    $bool = false;
                } elseif ($octal_val == $code) {
                    $message .= "\n" . $dir . ': <span style="color:green;">' . $octal_val . '</span>';
                } else {
                    $message .= "\n" . $dir . ': <span style="color:orange;">' . $octal_val . '</span>';
                }
            }
        }

        $this->setLog($this->getReportMessage('CHMOD'), $message, $bool);
    } // getChmod

    /**
    * ����������� ��� ���������� � �����
    * @param string $message ����� ���������
    * @param mixed $result ��������� ��������
    * @param boolean $boolean_result � ������ ���� � ���������� �������� ������������ �� boolean
    */
    protected function setLog($message, $result = null, $boolean_result = null)
    {
        if (isset($result) && is_array($result) && $result)
        {
            $this->infoLog[] = array(
                'message' => sprintf($message, (int)$boolean_result)
                ,'result' => $result
            );
        } elseif (!is_bool($boolean_result)) {
            $this->infoLog[] = is_bool($result) ? sprintf($message, (int)$result) : sprintf($message, $result);
        } else {
            $key = sizeof($this->infoLog);
            $this->infoLog[$key] = array(
                'message' => sprintf($message, (int)$boolean_result)
            );
            if ($result) $this->infoLog[$key]['result'] = $result;
        }

        $key = sizeof($this->infoLogBoolean);
        $this->infoLogBoolean[$key] = array(
            'message' => sprintf($message, '')
            ,'result' => is_bool($boolean_result) ? (int)$boolean_result : $result
        );
        if (is_bool($boolean_result))
        {
            $this->infoLogBoolean[$key]['comment'] = $result;
        }
        $this->resultStatus = true;
    }

    /**
    * ���������� ������ ������ � ����������
    *
    * @param string $dir
    * @return string
    */
    protected function getDirFiles($dir, $recursive = false)
    {
        if (!isset($report)) $report = array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $path = $dir . '/' . $file;
                    if (is_file($path)) {
                        $report[] = $path;
                    } elseif (is_dir($path) && $recursive) {
                        $report = array_merge($report, $this->getDirFiles($path, $recursive));
                    }
                }
            }
            closedir($handle);
        }
        return $report;
    } // getDirPerms

    /**
    * �������� ������� ���������
    *
    */
    protected function getCopyLine()
    {
        $bool = false;
        $list = $this->getDirFiles($this->rootPath . '/templates', true);
        $templates = array();
        $result = array();
        foreach ($list as $k => $v){
            $temp = explode('.', $v);
            if ($temp[sizeof($temp)-1] == 'html')
            {
                $templates[] = $v;
                $content = file_get_contents($v);
                if (strstr($content, 'http://www.rusoft.ru'))
                {
                    $result[] = $v;
                    $bool = true;
                }
            }
        }
        $this->setLog($this->getReportMessage('COPY'), $result, $bool);
    } // getCopyLine

    protected function getRootUser()
    {
        require_once $this->rootPath . '/admin/connect.php';
        $q = mysql_query('SELECT `id` FROM `admins` WHERE `login`="root" LIMIT 0,1');
        $result = mysql_num_rows($q) ? true : false;
        $this->setLog($this->getReportMessage('ROOT_USER'), $result);
    }

    /**
    * ���������� ������� �������� /aboutsite
    *
    */
    protected function getScreen()
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
        $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/aboutsite';
        $bool = (bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
        if ($bool)
        {
            $content = file_get_contents($url);
            $content = htmlspecialchars($content);
        } else $content = '';
        $this->setLog($this->getReportMessage('ABOUT_SCREEN'), $content, $bool);
    }

    /**
    * ��������� ������ ��������� ��������
    * @todo ��������� ROOT_ID
    *
    */
    protected function getStingConstants()
    {
        require_once $this->rootPath . '/admin/connect.php';
        $q = mysql_query('SELECT `id`, `module`, `name`, `value`, `def` FROM `strings` ORDER BY `module`');
        $result = array();
        $bool_rusoft1 = false;
        $bool_rusoft2 = false;
        while ($r = mysql_fetch_assoc($q))
        {
            $result[$r['id']] = array(
                'module' => $r['module'],
                'name' => $r['name'],
                'value' => htmlspecialchars($r['value']),
                'def' => htmlspecialchars($r['def'])
            );

            if ($r['value'] == 'Rusoft'){
                $bool_rusoft1 = true;
            } elseif ($r['value'] == '������'){
                $bool_rusoft2 = true;
            }
        }
        $this->setLog($this->getReportMessage('STR_RUSOFT1'), $result, true);
        $this->setLog($this->getReportMessage('STR_RUSOFT2'), $result, true);

        $this->setLog($this->getReportMessage('STR_CONST'), $result, true);
    } // getStingConstants

    /**
    * ������������ �������� �����
    *
    */
    protected function unusedFiles()
    {
        if (in_array($this->wwwVersion['site'], array('3.4','3.5'))){
            require_once $this->rootPath . '/admin/connect.php';
            $q = mysql_query('SELECT
                    `sites`.`name`,
                    `sites_sites_langs`.`name`  AS lang_name
                FROM `sites`
                LEFT JOIN `sites_sites_langs` ON `sites_sites_langs`.`pid`=`sites`.`id`');
            $defined = array();
            if ($q) while ($r = mysql_fetch_assoc($q))
            {
                $file_name = str_replace('/', '--', $r['name']);
                if ($r['lang_name']) $file_name .= '__' . $r['lang_name'];
                $file_name .= '.conf';
                $defined[] = $file_name;
            }

            $list_configs = $this->getDirFiles($this->rootPath . '/configs');
            foreach ($list_configs as $k => $v)
            {
                $list_configs[$k] = basename($v);
            }

            $report = array();
            foreach ($defined as $k => $v){
                if (!in_array($v, $list_configs))
                {
                    $report[$v]  = false;
                    $this->resultStatus = false;
                } else {
                    $report[$v]  = true;
                }
            }
            $this->setLog($this->getReportMessage('CONF_FILES'), $report, true);

            $cache_files = $this->getDirPerms($this->rootPath . '/cache');
            $this->setLog($this->getReportMessage('CACHE_FILES'), $cache_files, true);
        } else {
            $cache_files1 = $this->getDirPerms($this->rootPath . '/admin/sid');
            $cache_files2 = $this->getDirPerms($this->rootPath . '/sessions');
            $cache_files = array_merge($cache_files1, $cache_files2);
            $this->setLog($this->getReportMessage('CACHE_FILES'), $cache_files, true);
        }

        $list = $this->getDirFiles($this->rootPath);
        $this->setLog($this->getReportMessage('ROOT_FILES'), "\n" . implode("\n", $list), true);
    } // unusedFiles

    protected function getLog()
    {
        echo '<pre>';
        //print_r($this->infoLogBoolean);
        print_r($this->infoLog);
        echo '</pre>';
    } // getLog

    /**
    * ���������� �������� ��������� ��������� �� �� �����
    *
    * @param string $key ����
    * @return string
    */
    private function getReportMessage($key)
    {
        return isset($this->reportMessage[$key]) ? $this->reportMessage[$key] : 'undefined string constant: ' . $key;
    } // getReportMessage

    /**
    * ����������� ������ �����
    * @return string | boolean
    */
    private function getSiteVersion()
    {
        $request_path = '/index.php';
        $file = $this->rootPath . $request_path;
        try {
            if (!file_exists($file))
            {
                throw new Exception(sprintf($this->getReportMessage('INDEX_NF'), $request_path));
            }
            $content = file_get_contents($file);
            $version = $this->get_string_between($content, 'sms', '/');
            if (!$version)
            {
                $version = $this->get_string_between($content, 'ce', '/');
            }
            $this->wwwVersion['site'] = $version;
            $bool = in_array($version, $this->validVersion);
            $this->setLog($this->getReportMessage('SITE_VERSION'), $version, $bool);
        } catch (Exception $e) {
            $this->setLog($e);
        }
    } // getSiteVersion

    /**
    * ����������� ���������� ������������ libs ������
    * @todo �� ��������� ������ �������� �������� � ���������� �������� ��� ������� � �����, ����� � ����� ������� (site^admin) ��������� ������
    *
    */
    private function getLibsPath()
    {
        if (in_array($this->wwwVersion['site'], array('3.4', '3.5')))
        {
            $dir_site = $this->rootPath . '/sms' . $this->wwwVersion['site'];
        } else {
            $dir_site = $this->rootPath . '/ce' . $this->wwwVersion['site'];
        }

        if (is_dir($dir_site))
        {
            $message = $this->getDirFiles($dir_site, true);
            $bool = false;
        } else {
            $message = 'repository';
            $bool =  true;
        }

        $this->setLog($this->getReportMessage('LIBS_PATH'), $message, $bool);

        if (in_array($this->wwwVersion['site'], array('3.4', '3.5')))
        {
            $dir_admin = $this->rootPath . '/admin/sms' . $this->wwwVersion['admin'];
        } else {
            $dir_admin = $this->rootPath . '/admin/adm' . $this->wwwVersion['admin'];
        }

        if (is_dir($dir_admin))
        {
            $message = $this->getDirFiles($dir_admin, true);
            $bool = false;
        } else {
            $message = 'repository';
            $bool =  true;
        }
        $this->setLog($this->getReportMessage('LIBS_PATH_ADMIN'), $message, $bool);
    } // getLibsPath

    /**
    * �������� ������� � ������ ����� robots.txt
    *
    */
    private function getRobotsTxt()
    {
        $request_path = '/robots.txt';
        $file = $this->rootPath . $request_path;
        if (!file_exists($file))
        {
            $this->setLog($this->getReportMessage('IS_ROBOTS_TXT'), false);
        } else {
            $this->setLog($this->getReportMessage('IS_ROBOTS_TXT'), true);
            $content = file_get_contents($file);
            $search_list = array('Disallow:', 'Host:');
            $replace_list = array('<span style="color:red;">Disallow:</span>', '<span style="color:#F00;">Host:</span>');

            $content = str_replace($search_list, $replace_list, $content);
            $this->setLog($this->getReportMessage('ROBOTS_TXT_CONTENT'), $content, true);
        }
    } // getRobotsTxt

    /**
    * ����������� ������ �������
    * @return string | boolean
    */
    private function getAdminVersion()
    {
        $request_path = '/admin/index.php';
        $file = $this->rootPath . $request_path;
        try {
            if (!file_exists($file))
            {
                throw new Exception(sprintf($this->getReportMessage('INDEX_NF'), $request_path));
            }
            $content = file_get_contents($file);
            $version = $this->get_string_between($content, 'sms', '/');
            if (!$version)
            {
                $file = $this->rootPath . '/admin/cfg.php';
                if (file_exists($file))
                {
                    $version = trim($this->get_string_between($content, "'adm", "/'"));
                }
            }
            $this->wwwVersion['admin'] = $version;
            $bool = in_array($version, $this->validVersion);
            $this->setLog($this->getReportMessage('ADMIN_VERSION'), $version, $bool);
        } catch (Exception $e) {
            $this->setLog($e);
        }
    }

    /**
    * ����������� �������� FCK
    *
    */
    private function getFckInfo()
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
        if (in_array($this->wwwVersion['admin'], array('3.4','3.5')))
        {
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/admin/third/editor2/fckconfig.js';
        } else {
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/admin/fck/fckconfig.js';
        }

        $exists = preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
        $this->setLog($this->getReportMessage('FCK_ALIAS'), $exists);
        if (!$exists)
        {
            if (in_array($this->wwwVersion['admin'], array('3.4','3.5')))
            {
                $file = $this->rootPath . '/admin/third/editor2';
            } else {
                $file = $this->rootPath . '/admin/fck';
            }
            $dir_exists = is_dir($file) ? true : false;
            $this->setLog($this->getReportMessage('FCK_DIR'), $dir_exists);
        }
    } // getFckInfo

    function getAlias()
    {
    } // getAlias

    function __construct()
    {
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->allowIpList))
        {
            $this->setLog($this->getReportMessage('IP_WRONG'));
            exit;
        }
        $this->rootPath = getenv('DOCUMENT_ROOT');
        $this->getSiteVersion();
        $this->getAdminVersion();
        $this->getRobotsTxt();
        $this->getLibsPath();
        $this->getFckInfo();
        $this->getAlias();
        $this->getChmod();
        $this->getChmodFiles();
        $this->unusedFiles();
        $this->getStingConstants();
        $this->getCopyLine();
        $this->getScreen();
        $this->getRootUser();
        $this->getSimplePasswords();
        $this->getAbsoluteUrl();
        $this->getCrocusEntry();
        $this->getMeta();
        $this->getMailConfig();
        $this->getLog();
    }

    function getMeta()
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
        $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/';
        $content = file_get_contents($url);
        if ($content)
        {
            $bool = true;
            $description = $this->get_string_between($content, '<meta name="description" content=', '>');
            $title = $this->get_string_between($content, ' <meta name="title" content=', '>');
            $keywords = $this->get_string_between($content, ' <meta name="keywords" content=', '>');
        } else $bool = false;
        $this->setLog(sprintf($this->getReportMessage('META_DATA'), $title, $keywords, $description), $bool);
    }

    /**
    * ��������� ����� ������, ����� ���� ��������
    *
    * @param string $string ������ ��� ���������
    * @param string $start ����� �������
    * @param string $end ������ �������
    * @return string
    */
    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    } // get_string_between

} // rusoftInfo
set_time_limit(120);
$init = new rusoftInfo();
?>