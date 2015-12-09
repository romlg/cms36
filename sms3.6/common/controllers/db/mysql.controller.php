<?
/*
Класс работы с базой данных MYSQL
@autor vetal
*/

class DB_Controller_MYSQL
{

    var $table;

    function DB_Controller_MYSQL() {

    }

    //---------------------------------------------------------------------------------------
    /*
     function query($sql, $file = '', $line = '', $unbuffered = false)
     функция выполняет запрос к базе данных
     параметры:
         $sql 		- sql запрос
         $unbuffered - небуферизовать вывод @default = false #sql mysql_query || mysql_unbuffered_query
         $file 		- информация о файле из которого была вызвана
         $line		- строка вызова функции
     **************************************
     Использует переменные:
         ENGINE_SQL_LOG  - отключаем запись в log_change @default = false
         NO_NEED_DB_CONN - не нужно ли использовать соединение с базой данных @default = false
     **************************************
     В ходе работы, устанавливает переменные:
         $GLOBALS['last_sql'] - последний sql запрос, выполненный этой функцией
         дополняет массив запросов к бд, если успешный:
             $GLOBALS['sql_queries'][] = array(
                 'time' => $time,
                 'sql' => $sql,
                 'file' => $file,
                 'line' => $line,
             );
         или
             $GLOBALS['sql_errors'][] = array(
                 'sql' => $sql,
                 'error' => mysql_error(),
                 'file' => $file,
                 'line' => $line,
             );
     **************************************
     Доп.инфо:
         $file и $line - либо передаются, либо берутся автоматически относительно последней функции,
                         вызывающей query.
     **************************************
     */
    function query($sql, $file = '', $line = '', $unbuffered = false) {

        //проверяем, используется ли база данных
        if (defined('SQL_DB') && SQL_DB === false) {
            return false;
        }
        //проверяем, нужно ли записывать в лог
        if (defined('SQL_LOG') && SQL_LOG === true) $log_id = $this->_log($sql);

        //выполняем запрос
        $GLOBALS['last_sql'] = $sql;
        $time = getmicrotime();
        $res = $unbuffered ? mysql_unbuffered_query($sql) : mysql_query($sql);
        if (mysql_errno() === 1016) {
            if (preg_match("/'(\S*)\.MYD'\. \(errno\: 145\)/", mysql_error(), $m)) {
                mysql_unbuffered_query("REPAIR TABLE " . $m[1]);
                $res = $unbuffered ? mysql_unbuffered_query($sql) : mysql_query($sql);
            }
        }
        $time = round((getmicrotime() - $time) * 1000, 2);


        //определяем, откуда была вызвана функция
        if (empty($file) || empty($line)) {
            $bt = debug_backtrace();
            $file = (!isset($bt[1]['file']) ? $bt[2]['file'] : $bt[1]['file']);
            $line = (!isset($bt[1]['line']) ? $bt[2]['line'] : $bt[1]['line']);
        }


        if (!$res || mysql_errno()) {
            //сохраняем инфу о ошибке
            $GLOBALS['sql_errors'][] = array(
                'sql' => $sql,
                'error' => mysql_error(),
                'file' => $file,
                'line' => $line,
            );
        } else {
            //сохраняем инфу о запросе
            $GLOBALS['sql_queries'][] = array(
                'time' => $time,
                'sql' => $sql,
                'file' => $file,
                'line' => $line,
            );
        }

        if (defined('SQL_LOG') && SQL_LOG === true && !$res) {
            $this->_log($sql, $log_id);
            // повтор запроса, для того, чтобы снова установить переменную ошибки после предыдущего запроса UPDATE
            $res = $unbuffered ? mysql_unbuffered_query($sql) : mysql_query($sql);
        }
        if ($res === false) {
            $res = mysql_error();
            $this->writeError($sql, $res, $file, $line);
        }
        return $res;
    }

    //---------------------------------------------------------------------------------------
    //возвращаем #sql mysql_insert_id
    function getLastId() {
        return mysql_insert_id();
    }

    //---------------------------------------------------------------------------------------
    //возвращаем #sql mysql_insert_id
    function getError() {
        return mysql_error();
    }

    //---------------------------------------------------------------------------------------
    //возвращаем #sql mysql_insert_id
    function getErrNo() {
        return mysql_errno();
    }

    //---------------------------------------------------------------------------------------

    function getValue($sql, $file = '', $line = '') {
        //определяем, откуда была вызвана функция
        if (empty($file) || empty($line)) {
            $bt = debug_backtrace();
            $file = isset($bt[1]['file']) ? $bt[1]['file'] : '';
            $line = isset($bt[1]['line']) ? $bt[1]['line'] : '';
        }
        $rows = $this->query($sql, $file, $line);
        if ($rows !== false && is_resource($rows)) {
            $row = mysql_fetch_row($rows);
            mysql_free_result($rows);
            return $row[0];
        }
        return false;
    }

    //---------------------------------------------------------------------------------------

    function getRow($sql, $file = '', $line = '') {
        //определяем, откуда была вызвана функция
        if (empty($file) || empty($line)) {
            $bt = debug_backtrace();
            $file = isset($bt[1]['file']) ? $bt[1]['file'] : '';
            $line = isset($bt[1]['line']) ? $bt[1]['line'] : '';
        }
        if ((int)$sql) {
            $sql = "SELECT * FROM " . $this->table . " WHERE id=" . (int)$sql;
        }
        $rows = $this->query($sql, $file, $line);
        if ($rows !== false && is_resource($rows)) {
            $res = mysql_fetch_assoc($rows);
            mysql_free_result($rows);
            return $res;
        }
        return false;
    }

    //---------------------------------------------------------------------------------------

    function getRows($sql, $use_key = false, $file = '', $line = '', $type = true) {
        //определяем, откуда была вызвана функция
        if (empty($file) || empty($line)) {
            $bt = debug_backtrace();
            $file = isset($bt[1]['file']) ? $bt[1]['file'] : '';
            $line = isset($bt[1]['line']) ? $bt[1]['line'] : '';
        }
        $ret = array();
        $rows = $this->query($sql, $file, $line);
        $type = $type ? $type : MYSQL_ASSOC;
        if ($rows !== false && is_resource($rows)) {
            while ($row = mysql_fetch_array($rows, $type)) { // row & assoc values
                if ($use_key === true) {
                    //в качестве ключей используем первое поле в результате запроса
                    $key = (count($row) == 2) ? array_shift($row) : reset($row);
                    $ret[$key] = (count($row) == 1) ? array_shift($row) : (!count($row) ? $key : $row);
                } else if ($use_key !== false) {
                    //в качестве ключей используем название поля в результате запроса
                    $ret[$row[$use_key]] = $row;
                } else {
                    //в качестве ключей используем числовые индексы @autoincrement :)
                    $ret[] = (count($row) == 1) ? array_shift($row) : $row;
                }

            }
            mysql_free_result($rows);
            return $ret;
        }
        return $ret;
    }

    //---------------------------------------------------------------------------------------
    /*
     Функция сбрасывает в log_change все запросы к базе данных
     параметры:
     $sql - sql запрос к базе, #default = false
     $id - идентификатор изменяемой записи в логе(обычно вызывается, елси запрос не был выполнен), #default = false
     return:
     $id - идентификатор
     */
    function _log($sql = false, $id = false) {

        if (!$id && !empty($sql)) {
            // опледеляем, какой у нас идет запрос, и нужно ли его записать в log_change
            // в лог записываются только те запросы, которые указаны в глобавльной переменной $log_change_actions
            static $transaction;
            static $table;
            global $log_change_actions, $log_change_exclude_tables;
            $sql = trim($sql);
            if (!empty($transaction) || preg_match("/^begin/i", $sql)) {
                preg_match("/^\s*(\w*(\s*INTO)?)\s+(.*)/im", $sql, $res); // берем action - в $res[1]
                // теперь ищем таблицу она будет в ret[2]
                if (isset($res[1])) {
                    $act = strtoupper($res[1]);
                }
                if (isset($act) && in_array($act, $log_change_actions)) {
                    if ($act == 'SELECT' || $act == 'DELETE') {
                        preg_match("/FROM\s+(`)?(\w+)(`)?\s+/im", $res[3], $ret);
                    } else {
                        preg_match("/(`)?(\w+)(`)?/im", $res[3], $ret);
                    }
                    if (!empty($ret[2])) $table[] = $ret[2];
                }
                $transaction .= $sql . "<br>\n ";
                if (preg_match("/^commit/i", $sql)) {

                    mysql_query("INSERT INTO log_change (user, object, action, description) VALUES (
							\"" . (isset($GLOBALS['user']['login']) ? $GLOBALS['user']['login'] : 'site') . "\",
							\"" . implode(',', $table) . "\",
							'TRANSACTION',
							\"" . mysql_escape_string($transaction) . "\" )"
                    );
                    $id = mysql_insert_id();
                    $transaction = '';
                } elseif (preg_match("/^rollback/i", $sql)) {
                    $transaction = '';
                }
            } else {
                preg_match("/^\s*(\w*(\s*INTO)?)\s+(.*)/im", $sql, $res); // берем action
                // res[1] - это action (SELECT, UPDATE ....)
                // res[3] - остаток запроса
                if (isset($res[1])) {
                    $act = strtoupper($res[1]);
                }
                if (isset($act) && in_array($act, $log_change_actions)) {
                    // теперь ищем таблицу она будет в ret[2]
                    if ($act == 'SELECT' || $act == 'DELETE') {
                        preg_match("/FROM\s+(`)?(\w+)(`)?\s+/im", $res[3], $ret);
                    } else {
                        preg_match("/(`)?(\w+)(`)?/im", $res[3], $ret);
                    }
                    if (isset($ret[2])) {
                        $table[0] = $ret[2];
                    }
                    // ненужные таблицы не записываем в лог
                    if (!empty($table[0]) && !in_array($table[0], $log_change_exclude_tables)) {
                        mysql_query("INSERT INTO log_change (user, object, action, description) VALUES (
								\"" . (isset($GLOBALS['user']['login']) ? $GLOBALS['user']['login'] : 'site') . "\",
								\"" . (!empty($table[0]) ? $table[0] : 'UNKNOWN') . "\",
								'" . (isset($act) ? $act : 'UNKNOWN') . "',
								\"" . mysql_escape_string($sql) . "\" )"
                        );
                        $id = mysql_insert_id();
                    }
                }
            }
            //обновляем время последнего доступа к таблицам
            if (in_array($act, array('DELETE', 'UPDATE', 'REPLACE', 'INSERT'))) {
                foreach ($table as $k => $v) {
                    touch_cache($v);
                }
            }
        } else {
            // если запрос был не выполнен, то запись о нем изменяем
            // мы разнесли это от обработки чтобы не испортить mysql_insert_id
            // поэтому мы сначала вносим изменения в лог, а потом делаем сам запрос, а потом проверяем, был ли он успешным
            mysql_unbuffered_query("UPDATE log_change SET action ='ERROR' , description=CONCAT(description,' <br>" . mysql_escape_string(mysql_error()) . "') WHERE id=" . $id);
        }
        return $id;
    }

    function writeError($sql, $error, $file, $line) {
        if ($error == "MySQL server has gone away") return;
        if (ENGINE_TYPE == 'admin') $dir = '../logs';
        else $dir = 'logs';
        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, DIRS_MOD);
        }
        $fp = fopen($dir . "/sql_errors.log", "a");
        fwrite($fp, date('d.m.Y H:i') . "; " . $sql . "; error=" . $error . "; file=" . $file . "; line=" . $line . "; url=" . @$_SERVER['REQUEST_URI'] . ";\r\n");
        fclose($fp);
    }
}

?>