<?php

require_once (module('stat'));

define('STAT_TEMP_TABLE', STAT_DATABASE.$and.'stat_temp_log');

class TProcess extends TStat {

    var $name = 'stat/stat_process';

    ########################

    function TProcess() {
        global $str, $actions;

        TStat::TStat();

        $actions[str_replace('/', '__', $this->name)] = array(
        'process' => array(
        'Обработать',
        'Process',
        'link'	=> 'cnt.MyProcess()',
        'img' 	=> 'icon.save.gif',
        'display'	=> 'block',
        ),
        );

        $str[get_class_name($this)] = $str['tstat'] + array(
        'analyze'     => array('Анализируется', 'It is analyzed'),
        'not_process' => array('Не обработанная', 'Not processed'),
        'total'       => array('Всего', 'Total'),
        'records'     => array('записей', 'records'),
        );
    }
    ######################

    function Show() {

        $row = array();

        $row['analyze']['from'] = sql_getValue("SELECT MIN(time) FROM ".STAT_LOG_TABLE);
        $row['analyze']['to'] = sql_getValue("SELECT MAX(time) FROM ".STAT_LOG_TABLE);

        $row['process']['from'] = sql_getValue("SELECT MIN(time) FROM ".STAT_TEMP_TABLE);
        $row['process']['to'] = sql_getValue("SELECT MAX(time) FROM ".STAT_TEMP_TABLE);

        $row['total'] = sql_getValue("SELECT COUNT(*) FROM ".STAT_TEMP_TABLE);

        $this->AddStrings($row);
        return Parse($row, 'stat/stat_process/stat.process.tmpl');
    }

    ######################

    function Edit() {

        set_time_limit(0);

        /**
	     * Алгоритм обработки:
            - считываем все данные из таблицы stat_temp_log и идем по ним построчно
            - вносим данные в глобальные таблицы (алгоритм, как раньше)
            - удаляем строку
            - в конце удаляем временные таблицы (наверное, которые по дате не совпадают, можно оставить)
	     */

        include_once(common_class('stat'));
        $stat = &Registry::get('TStatClass');

        $sessions = $updates = $times = array();

        $columns = sql_getRows('SHOW COLUMNS FROM '.STAT_SESSIONS_TABLE, true);
        if (!isset($columns['host'])) {
            sql_query("ALTER TABLE ".STAT_SESSIONS_TABLE." ADD `host` VARCHAR( 255 ) NOT NULL ;");
            sql_query("UPDATE ".STAT_SESSIONS_TABLE." SET host=(SELECT p.host FROM ".STAT_PAGES_TABLE." AS p, ".STAT_LOG_TABLE." AS log WHERE log.page_id=p.id AND log.sess_id=sess_id LIMIT 1)");
        }

        $total = sql_getValue("SELECT COUNT(*) FROM ".STAT_TEMP_TABLE);

        $i = 0;

        //ob_end_clean();
        ob_end_clean();

        header('Content-Encoding: text/html');

        while (1) {
            $rows = sql_getRows("SELECT * FROM ".STAT_TEMP_TABLE." ORDER BY time LIMIT 1000");
            if ($i>1 && (!$rows || count($rows) < 1000)) break;

            foreach ($rows as $key=>$row) {

                $_key_ = md5($row['sess_id'].'_'.$row['ip'].'_'.$row['agent'].'_'.$row['host']);
                $new = false;

                if (is_numeric($row['sess_id']) && strlen($row['sess_id']) != 32 && $row['sess_id'] != '0') {
                    // Реальный ID сессии
                    $sess_id = $row['sess_id'];
                }
                else if (isset($sessions[$_key_]) && isset($times[$_key_]) && ($row['time'] - $times[$_key_]) <= STAT_SESS_TIME*60) {
                    // Ранее определили реальный ID и он не устарел еще
                    $sess_id = $sessions[$_key_];
                }
                else {

                    // Во всех остальных случаях пытаемся найти подходящую сессию и если не находим - делаем новую сессию
                    list($agent_id, $robot) = $stat->STAT_GetAgentId($row['agent']);

                    $sess_id = sql_getValue("
            	        SELECT sess_id FROM ".STAT_SESSIONS_TABLE."
            	        WHERE
            	           ip='{$row['ip']}' AND
            	           agent_id='{$agent_id}' AND
            	           host='{$row['host']}' AND
            	           time_last >= ".($row['time'] - STAT_SESS_TIME*60)."
            	        LIMIT 1
            	        ");

                    if (!$sess_id) {
                        $sess_row = sql_getRow("SELECT sess_id, time_last FROM ".STAT_SESSIONS_TABLE." WHERE ip='{$row['ip']}' AND agent_id='{$agent_id}' AND host='{$row['host']}' ORDER BY time_last DESC LIMIT 1");
                        $sess_id = $stat->newSession($row['ip'], $agent_id, $row['time'], $row['host'], $sess_row);
                        if (!$sess_id) {
                            return $this->Error(mysql_escape_string($sess_id));
                        }
                        $new = true;
                    }
                }

                if (!isset($robot) || !$robot) {
                    $sessions[$_key_]   = $sess_id;
                    $times[$_key_]      = $row['time'];
                }

                list($page_id, $ref_id) = $stat->updateLog($sess_id, $row['time'], $row['document_status'], $row['host'], $row['uri'], $row['referer'], !$new);
                if (!$page_id) {
                    return $this->Error('unknown page_id');
                }

                $res = $stat->updateSession($sess_id, $row['time'], $row['document_status'], $row['client_id'], $page_id, $ref_id, $row['ip'], $new, $row['agent']);
                if ($res !== true) return $this->Error(mysql_escape_string($res));

                sql_delete(STAT_TEMP_TABLE, $row['id']);

                if ($key%10 == 0) {
                    echo "<script>parent.process_log2.innerHTML += '.';</script>







                    \r\n";
                    flush();
                }
            }
            $i+=count($rows);

            unset($rows, $row);

            echo "<script>parent.process_log.innerHTML = 'Done ".intval($i/$total*100)."%';</script>







            \r\n";
            echo "<script>parent.process_log2.innerHTML = '';</script>







            \r\n";
            flush();

        }


        $sql = '
	       SELECT
           CONCAT(
                  "DROP TABLE ",
                   GROUP_CONCAT("`'.STAT_TMP_DB.'`.`", TABLE_NAME, "`")
           ) AS stmt
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = "'.STAT_TMP_DB.'" AND TABLE_NAME LIKE "'.MYSQL_DB.'\_%"';
        $query = sql_getValue($sql);
        if ($query) sql_query($query);

        return "<script>window.parent.location.href = 'cnt.php?page=stat/stat_process';</script>";
    }

    ######################
}

$GLOBALS['stat__stat_process'] = & Registry::get('TProcess');

?>