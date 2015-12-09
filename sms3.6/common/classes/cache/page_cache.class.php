<?php
/**
 * Кэширование всей страницы
 */
class TPageCache
{

    /**
     * идентификатор кэша
     *
     * @var string
     */
    var $cache_id;

    /**
     * конструктор
     *
     * @return TPageCache
     */
    function TPageCache() {

    }

    /**
     * выводит контент, если кэш отработал или возвращает false
     *
     * @return bool + выводит контент, если кэш отработает
     */
    function cache() {
        // если нет коннекта к базе - выводим данные из кеша
        if (!sql_getValue('show tables like "tree"')) {
            # интервал отправления писем.. в секундах
            $notify_interval = 60 * 10;
            # имя файла (хранить время последнего отправления)
            $filename = 'database_error_notification';
            # не считаем статистику..
            ignore_user_abort(false);
            # массив адресов куда отправлять письма
            //$admins = explode(' ','oz@rusoft.ru eg@rusoft.ru saga@rusoft.ru andrey@magus.ru');
            $admins = explode(' ', 'eg@rusoft.ru');
            # разделительная полоса

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

            # проверять время последнего отправления письма
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

        // Нужно ли кешировать (настройка config и есть ли данные в GPC)
        if (!$this->IsCacheGPC()) $GLOBALS['no_cache'] = true; // присваиваем, чтобы не сохранять кеш
        if (!CACHE_PAGE || $GLOBALS['no_cache']) return false;


        // запомним время создания touch файла
        $GLOBALS['touch_time'] = 0;
        if (is_file(CACHE_PAGE_TOUCH_FILE)) {
            $GLOBALS['touch_time'] = filemtime(CACHE_PAGE_TOUCH_FILE); // этот параметр нам понадобится в дальнейшем
        }

        // Есть ли файл в кеше для данного url
        if (!is_file(CACHE_PAGES_PATH . $this->get_cache_id() . '.gz')) return false;
        // Проверяет на актуальность этого файла
        if (!$this->IsCacheActual($this->get_cache_id())) {
            // Если файлы кеша старые, их надо удалить
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
        // выдаем заголовки страницы, если страница была ошибочная, то должен быть файл _404,
        // для нормальной страницы файл _200 - по его mtime устанавливаем время обновления
        if (is_file(CACHE_PAGES_PATH . $this->get_cache_id() . '_404'))
            $GLOBALS['document_status'] = 404;
        else
            $GLOBALS['document_status'] = 200;

        $GLOBALS['update_time'] = @filemtime(CACHE_PAGES_PATH . $this->get_cache_id() . '_200');

        headers(); // выдает заголовки страницы.
        return true;
    }

    /**
     * возвращает идентификатор кэша
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
     * Записываем страницу в кеш
     *
     * @param $page string html код страницы
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

    // Проверяет не устарел ли кеш
    /**
     * функция проверяет не устарел ли кэш файл
     *
     * @param string $file название файла
     * @return bool
     */
    function IsCacheActual($file) {
        // cache_time - время создания файла с кешом
        // update_time - время последнего измения сайта (в БД или .cache)

        $update_time = 0;
        // Проверяет дату файла .cache (CACHE_PAGE_TOUCH_FILE)
        if ($GLOBALS['touch_time']) {
            $update_time = $GLOBALS['touch_time'];
            // Если файла нет нужно смотреть на даты изменения таблиц (CACHE_DB)
        } else {
            //просматриваем все файлы в директории кэшированных таблиц и находим дату обновления последнего
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

        // время создания файла с кешом
        $cache_time = @filemtime(CACHE_PAGES_PATH . $file . '.gz');
        if ($cache_time < $update_time) return false;
        else return true;
        // false - генерим страницу
        // true - берем из кеша
    }


    /**
     * Выдавать ли кеш или нет из-за GPC
     */
    function IsCacheGPC() {
        //return true; // включался для тестирования
        // Если _GET или _POST - кеша не будет
        if ($_GET || $_POST) return false;
        // Если сессия - кеша не будет
        if (isset($_COOKIE[session_name()])) {
            if (DEV_MODE) log_notice("cache rejected because of session");
            return false;
        }
        // Если в куках есть нужные переменные - кеша не будет (CACHE_COOKIE)
        if ($_COOKIE && defined('CACHE_COOKIE')) {
            $vars = explode(',', CACHE_COOKIE);
            if (!$vars) foreach ($vars as $var) {
                if (isset($_COOKIE[trim($var)])) return false;
            }
        }
        return true;
        // false - генерим страницу
        // true - берем из кеша
    }

    /**
     * Функция устанавливает заголовок If-Modified-Since в браузере с указанием переданного времени
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
                    log_info("проверить работу скрипта в этом месте");
                    //stat_log();
                    //exit;
                }
            }
        }
    }

}

?>
