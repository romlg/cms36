<?
/*
����� ������ � ����� ������ MYSQL
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
     ������� ��������� ������ � ���� ������
     ���������:
         $sql 		- sql ������
         $unbuffered - �������������� ����� @default = false #sql mysql_query || mysql_unbuffered_query
         $file 		- ���������� � ����� �� �������� ���� �������
         $line		- ������ ������ �������
     **************************************
     ���������� ����������:
         ENGINE_SQL_LOG  - ��������� ������ � log_change @default = false
         NO_NEED_DB_CONN - �� ����� �� ������������ ���������� � ����� ������ @default = false
     **************************************
     � ���� ������, ������������� ����������:
         $GLOBALS['last_sql'] - ��������� sql ������, ����������� ���� ��������
         ��������� ������ �������� � ��, ���� ��������:
             $GLOBALS['sql_queries'][] = array(
                 'time' => $time,
                 'sql' => $sql,
                 'file' => $file,
                 'line' => $line,
             );
         ���
             $GLOBALS['sql_errors'][] = array(
                 'sql' => $sql,
                 'error' => mysql_error(),
                 'file' => $file,
                 'line' => $line,
             );
     **************************************
     ���.����:
         $file � $line - ���� ����������, ���� ������� ������������� ������������ ��������� �������,
                         ���������� query.
     **************************************
     */
    function query($sql, $file = '', $line = '', $unbuffered = false) {

        //���������, ������������ �� ���� ������
        if (defined('SQL_DB') && SQL_DB === false) {
            return false;
        }
        //���������, ����� �� ���������� � ���
        if (defined('SQL_LOG') && SQL_LOG === true) $log_id = $this->_log($sql);

        //��������� ������
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


        //����������, ������ ���� ������� �������
        if (empty($file) || empty($line)) {
            $bt = debug_backtrace();
            $file = (!isset($bt[1]['file']) ? $bt[2]['file'] : $bt[1]['file']);
            $line = (!isset($bt[1]['line']) ? $bt[2]['line'] : $bt[1]['line']);
        }


        if (!$res || mysql_errno()) {
            //��������� ���� � ������
            $GLOBALS['sql_errors'][] = array(
                'sql' => $sql,
                'error' => mysql_error(),
                'file' => $file,
                'line' => $line,
            );
        } else {
            //��������� ���� � �������
            $GLOBALS['sql_queries'][] = array(
                'time' => $time,
                'sql' => $sql,
                'file' => $file,
                'line' => $line,
            );
        }

        if (defined('SQL_LOG') && SQL_LOG === true && !$res) {
            $this->_log($sql, $log_id);
            // ������ �������, ��� ����, ����� ����� ���������� ���������� ������ ����� ����������� ������� UPDATE
            $res = $unbuffered ? mysql_unbuffered_query($sql) : mysql_query($sql);
        }
        if ($res === false) {
            $res = mysql_error();
            $this->writeError($sql, $res, $file, $line);
        }
        return $res;
    }

    //---------------------------------------------------------------------------------------
    //���������� #sql mysql_insert_id
    function getLastId() {
        return mysql_insert_id();
    }

    //---------------------------------------------------------------------------------------
    //���������� #sql mysql_insert_id
    function getError() {
        return mysql_error();
    }

    //---------------------------------------------------------------------------------------
    //���������� #sql mysql_insert_id
    function getErrNo() {
        return mysql_errno();
    }

    //---------------------------------------------------------------------------------------

    function getValue($sql, $file = '', $line = '') {
        //����������, ������ ���� ������� �������
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
        //����������, ������ ���� ������� �������
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
        //����������, ������ ���� ������� �������
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
                    //� �������� ������ ���������� ������ ���� � ���������� �������
                    $key = (count($row) == 2) ? array_shift($row) : reset($row);
                    $ret[$key] = (count($row) == 1) ? array_shift($row) : (!count($row) ? $key : $row);
                } else if ($use_key !== false) {
                    //� �������� ������ ���������� �������� ���� � ���������� �������
                    $ret[$row[$use_key]] = $row;
                } else {
                    //� �������� ������ ���������� �������� ������� @autoincrement :)
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
     ������� ���������� � log_change ��� ������� � ���� ������
     ���������:
     $sql - sql ������ � ����, #default = false
     $id - ������������� ���������� ������ � ����(������ ����������, ���� ������ �� ��� ��������), #default = false
     return:
     $id - �������������
     */
    function _log($sql = false, $id = false) {

        if (!$id && !empty($sql)) {
            // ����������, ����� � ��� ���� ������, � ����� �� ��� �������� � log_change
            // � ��� ������������ ������ �� �������, ������� ������� � ����������� ���������� $log_change_actions
            static $transaction;
            static $table;
            global $log_change_actions, $log_change_exclude_tables;
            $sql = trim($sql);
            if (!empty($transaction) || preg_match("/^begin/i", $sql)) {
                preg_match("/^\s*(\w*(\s*INTO)?)\s+(.*)/im", $sql, $res); // ����� action - � $res[1]
                // ������ ���� ������� ��� ����� � ret[2]
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
                preg_match("/^\s*(\w*(\s*INTO)?)\s+(.*)/im", $sql, $res); // ����� action
                // res[1] - ��� action (SELECT, UPDATE ....)
                // res[3] - ������� �������
                if (isset($res[1])) {
                    $act = strtoupper($res[1]);
                }
                if (isset($act) && in_array($act, $log_change_actions)) {
                    // ������ ���� ������� ��� ����� � ret[2]
                    if ($act == 'SELECT' || $act == 'DELETE') {
                        preg_match("/FROM\s+(`)?(\w+)(`)?\s+/im", $res[3], $ret);
                    } else {
                        preg_match("/(`)?(\w+)(`)?/im", $res[3], $ret);
                    }
                    if (isset($ret[2])) {
                        $table[0] = $ret[2];
                    }
                    // �������� ������� �� ���������� � ���
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
            //��������� ����� ���������� ������� � ��������
            if (in_array($act, array('DELETE', 'UPDATE', 'REPLACE', 'INSERT'))) {
                foreach ($table as $k => $v) {
                    touch_cache($v);
                }
            }
        } else {
            // ���� ������ ��� �� ��������, �� ������ � ��� ��������
            // �� �������� ��� �� ��������� ����� �� ��������� mysql_insert_id
            // ������� �� ������� ������ ��������� � ���, � ����� ������ ��� ������, � ����� ���������, ��� �� �� ��������
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