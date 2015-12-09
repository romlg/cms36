<?php
/**
 * ����������� ���� ��������
 */
class TPageCache
{

    /**
     * ������������� ����
     *
     * @var string
     */
    var $cache_id;

    /**
     * �����������
     *
     * @return TPageCache
     */
    function TPageCache() {

    }

    /**
     * ������� �������, ���� ��� ��������� ��� ���������� false
     *
     * @return bool + ������� �������, ���� ��� ����������
     */
    function cache() {
        // ���� ��� �������� � ���� - ������� ������ �� ����
        if (!sql_getValue('show tables like "tree"')) {
            # �������� ����������� �����.. � ��������
            $notify_interval = 60 * 10;
            # ��� ����� (������� ����� ���������� �����������)
            $filename = 'database_error_notification';
            # �� ������� ����������..
            ignore_user_abort(false);
            # ������ ������� ���� ���������� ������
            //$admins = explode(' ','oz@rusoft.ru eg@rusoft.ru saga@rusoft.ru andrey@magus.ru');
            $admins = explode(' ', 'eg@rusoft.ru');
            # �������������� ������

            $line = "- - - - - - - - - - - - - - - -  - - - - - - - - - - - - - - - - - -\n";
            if (is_file(CACHE_PAGES_PATH . $this->get_cache_id() . '.gz')) {
                $buffer = file_get_contents(CACHE_PAGES_PATH . $this->get_cache_id() . '.gz');
                headers();
                echo $buffer;
                $text = $line . "There was a problem during connection to mysql server.\n" . $line . "Mysql error:\n" . mysql_error() . "\n" . $line . "Page was taken from cache";
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                $text = $line . "There was a problem during connection to mysql server.\n" . $line . "Mysql error:\n" . mysql_error() . "\n" . $line . "Page was not founded in cache";
            }
            $text .= "\n" . "Site: http://" . $_SERVER['HTTP_HOST'];

            # ��������� ����� ���������� ����������� ������
            if (is_file($filename)) {
                if (time() - filemtime($filename) < $notify_interval) {
                    exit();
                }
            }

            $fp = fopen($filename, 'w+');
            fwrite($fp, date() . " no connetcion to DB\n");
            fclose($fp);
            foreach ($admins as $admin_mail) {
                @mail($admin_mail, 'Error', $text, 'From:' . $admin_mail);
            }
            exit();
        }

        // ����� �� ���������� (��������� config � ���� �� ������ � GPC)
        if (!$this->IsCacheGPC()) $GLOBALS['no_cache'] = true; // �����������, ����� �� ��������� ���
        if (!CACHE_PAGE || $GLOBALS['no_cache']) return false;


        // �������� ����� �������� touch �����
        $GLOBALS['touch_time'] = 0;
        if (is_file(CACHE_PAGE_TOUCH_FILE)) {
            $GLOBALS['touch_time'] = filemtime(CACHE_PAGE_TOUCH_FILE); // ���� �������� ��� ����������� � ����������
        }

        // ���� �� ���� � ���� ��� ������� url
        if (!is_file(CACHE_PAGES_PATH . $this->get_cache_id() . '.gz')) return false;
        // ��������� �� ������������ ����� �����
        if (!$this->IsCacheActual($this->get_cache_id())) {
            // ���� ����� ���� ������, �� ���� �������
            if ($handle = opendir(CACHE_PAGES_PATH)) {
                while (false !== ($cfile = readdir($handle))) {
                    if ($cfile != "." && $cfile != "..") {
                        @unlink(CACHE_PAGES_PATH . $file);
                    }
                }
                closedir($handle);
            }
            return false;
        }

        // ------------------------------------------------------------------------------
        // ������ ��������� ��������, ���� �������� ���� ���������, �� ������ ���� ���� _404,
        // ��� ���������� �������� ���� _200 - �� ��� mtime ������������� ����� ����������
        if (is_file(CACHE_PAGES_PATH . $this->get_cache_id() . '_404'))
            $GLOBALS['document_status'] = 404;
        else
            $GLOBALS['document_status'] = 200;

        $GLOBALS['update_time'] = @filemtime(CACHE_PAGES_PATH . $this->get_cache_id() . '_200');

        headers(); // ������ ��������� ��������.
        return true;
    }

    /**
     * ���������� ������������� ����
     *
     * @return string id
     */
    function get_cache_id() {
        if (empty($this->cache_id)) {
            $this->cache_id = md5($_SERVER['REQUEST_URI'] . str_replace('www.', '', $_SERVER['HTTP_HOST']));
        }
        return $this->cache_id;
    }

    /**
     * ���������� �������� � ���
     *
     * @param $page string html ��� ��������
     */
    function put_cache(&$page) {

        if (!CACHE_PAGE || $GLOBALS['no_cache']) return false;
        $name = CACHE_PAGES_PATH . $this->get_cache_id();
        $file = $name . '.gz';

        if ($handle = @fopen($file, 'wb')) {
            fwrite($handle, $page);
            fclose($handle);
            log_status("cache page file has been created");
        } else {
            @mkdir(CACHE_PAGES_PATH);
            fclose('Accees denied in cache page folder (' . CACHE_PAGES_PATH . ')');
        }

        if ($GLOBALS['document_status'] == '404') {
            @touch($name . '_404');
        } else {
            @touch($name . '_200', ((int)$GLOBALS['update_time'] > 0) ? $GLOBALS['update_time'] : time());
        }
    }

    // ��������� �� ������� �� ���
    /**
     * ������� ��������� �� ������� �� ��� ����
     *
     * @param string $file �������� �����
     * @return bool
     */
    function IsCacheActual($file) {
        // cache_time - ����� �������� ����� � �����
        // update_time - ����� ���������� ������� ����� (� �� ��� .cache)

        $update_time = 0;
        // ��������� ���� ����� .cache (CACHE_PAGE_TOUCH_FILE)
        if ($GLOBALS['touch_time']) {
            $update_time = $GLOBALS['touch_time'];
            // ���� ����� ��� ����� �������� �� ���� ��������� ������ (CACHE_DB)
        } else {
            //������������� ��� ����� � ���������� ������������ ������ � ������� ���� ���������� ����������
            if ($handle = opendir(PATH_CACHE_TABLES)) {
                while (false !== ($cfile = readdir($handle))) {
                    if ($cfile != "." && $cfile != "..") {
                        $t = @filemtime(PATH_CACHE_TABLES . $file);
                        if ($t > $update_time) $update_time = $t;
                    }
                }
                closedir($handle);
            }
        }

        if (!$update_time) return false;

        // ����� �������� ����� � �����
        $cache_time = @filemtime(CACHE_PAGES_PATH . $file . '.gz');
        if ($cache_time < $update_time) return false;
        else return true;
        // false - ������� ��������
        // true - ����� �� ����
    }


    /**
     * �������� �� ��� ��� ��� ��-�� GPC
     */
    function IsCacheGPC() {
        //return true; // ��������� ��� ������������
        // ���� _GET ��� _POST - ���� �� �����
        if ($_GET || $_POST) return false;
        // ���� ������ - ���� �� �����
        if (isset($_COOKIE[session_name()])) {
            if (DEV_MODE) log_notice("cache rejected because of session");
            return false;
        }
        // ���� � ����� ���� ������ ���������� - ���� �� ����� (CACHE_COOKIE)
        if ($_COOKIE && defined('CACHE_COOKIE')) {
            $vars = explode(',', CACHE_COOKIE);
            if (!$vars) foreach ($vars as $var) {
                if (isset($_COOKIE[trim($var)])) return false;
            }
        }
        return true;
        // false - ������� ��������
        // true - ����� �� ����
    }

    /**
     * ������� ������������� ��������� If-Modified-Since � �������� � ��������� ����������� �������
     *
     * @param timestamp $utime
     */
    function IfModifiedSince($utime) {

        //debug("IfModifiedSince: no_cache=".$GLOBALS['no_cache']."\n");
        if (!$utime || !CACHE_PAGE || $GLOBALS['no_cache']) return;
        $headers = getallheaders();
        if (isset($headers['If-Modified-Since'])) {
            $GMTutime = gmdate('D, d M Y H:i:s', $utime) . ' GMT';
            if (strpos($headers["If-Modified-Since"], $GMTutime) !== false) {
                if ($GLOBALS['update_time'] > 0) {
                    header("HTTP/1.1 304 Not Modified");
                    //debug("HTTP/1.1 304 Not Modified\n\n");
                    log_info("��������� ������ ������� � ���� �����");
                    //stat_log();
                    //exit;
                }
            }
        }
    }

}

?>
