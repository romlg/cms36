<?php

class TSearch
{

    var $search;
    var $params;

    function search(&$params) {
        if (!$params['tables']) die("Не заданы таблицы для поиска");

        foreach ($params['tables'] as $k=>$table) {
            $params['tables'][$k]['type'] = isset($table['as']) && $table['as'] ? $table['as'] : $table['name'];
        }
        $this->params = $params;

        $this->find();

        /**
         * @var TContent $content
         */
        $content = & Registry::get('TContent');
        $page = & Registry::get('TPage');
        $ret['pages'] = $content->getNavigation($this->search['total'], $this->search['limit'], $this->search['offset'], (($page->lang != LANG_DEFAULT) ? $page->lang . "/search" : "search"));

        $ret['search'] = $this->search;

        return $ret;
    }

    function find() {
        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        $query = h(get('search', '', 'g'));
        if (!$query) return;

        $limit = (int)get('limit', $view->get_config_vars("search_limit"), 'g');
        if (!$limit) $limit = 10;
        $offset = (int)get('offset', 0, 'g');
        $type = get('type', '', 'g'); // Параметр, определяющий, в каком разделе искать
        $types = $type ? explode(",", $type) : array();

        // Временные таблицы для результатов поиска
        if ($type && count($types) == 1) {
            // Нужна только одна таблица
            $sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_" . $type . "
	            (`id` INT(11), `pid` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `text` TEXT)";
        } else {
            // Временные таблицы для результатов поиска
            foreach ($this->params['tables'] as $table) {
                $sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_{$table['type']}
                    (`id` INT(11), `pid` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `text` TEXT)";
            }
        }
        foreach ($sql as $_sql) sql_query($_sql);

        foreach ($this->params['tables'] as $table) {
            if ($types && !in_array($table['type'], $types)) continue;
            $this->searchInTables(
                $table['type'],
                $query,
                $table['name'],
                $table['select_fields'],
                $table['search_fields'],
                isset($table['where']) ? $table['where'] : '1',
                isset($table['join']) ? $table['join'] : array()
            );
            if ($type && count($types) == 1) break;
        }

        $limit_str = "";
        if ($limit != -1)
            $limit_str = " LIMIT " . $offset . ", " . $limit;

        if ($type && count($types) == 1) {
            $res = $this->getTypeResults($query, $type, $limit_str, $table);
        } elseif (!$type && !$types && !$this->params['group_results']) {
            $res = $this->getAllResults($limit_str, $this->params['tables']);
        } elseif ($this->params['group_results']) {
            $res = $this->getAllUngroupedResults($query, $limit_str, $this->params['tables']);
        }

        $this->search = array(
            'type' => ($type && count($types) == 1) ? $type : $types,
            'html' => isset($res['html']) ? $res['html'] : null,
            'query' => $query,
            'total' => $res['total'],
            'limit' => $limit,
            'offset' => $offset,
            'selected' => isset($res['html']) ? $res['selected'] : count($res['list']),
            'results' => $res['list'],
        );
    }

    /**
     * Вывод результатов поиска одного вида
     * @param $query
     * @param $type
     * @param $limit_str
     * @return array
     */
    function getTypeResults($query, $type, $limit_str, $table) {
        $res = sql_getRows("SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM tmp_" . $type . " " . $limit_str, true);
        $total = sql_getValue("SELECT FOUND_ROWS()");

        foreach ($res as $i => $val) {
            $href = $this->getHref($val['id'], $table);
            if (!$href) continue;

            $res[$i]['href'] = $href;
            $res[$i]['path'] = $this->getPath($val['pid'], $table, $val['id'], $href);

            $res[$i]['name'] = $this->convertText($res[$i]['name'], array($query), 0);
            $res[$i]['text'] = $this->convertText($res[$i]['text'], array($query));
        }
        return array('list' => $res, 'total' => $total);
    }

    /**
     * Показ всех результатов поиска
     * @param $limit_str
     * @param $tables
     * @return array
     */
    function getAllResults($limit_str, $tables) {

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');

        $res = array();
        $i = 0;
        foreach ($tables as $table) {
            $title = $view->get_config_vars("search_title_" . $table['name']);
            if (!$title) $title = $table['name'];
            $res[$i]['title'] = $title;
            $res[$i]['name'] = $table['name'];
            $res[$i]['list'] = sql_getRows("SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM tmp_" . $table['type'] . $limit_str, true);
            $res[$i]['total'] = (int)sql_getValue("SELECT FOUND_ROWS()");
            $i++;
        }

        $_total = 0;
        foreach ($res as $v) $_total += $v['total'];

        return array('list' => $res, 'total' => $_total);
    }

    /**
     * Показ всех результатов поиска
     * @param $limit_str
     * @param $tables
     * @return array
     */
    function getAllUngroupedResults($query, $limit_str, $tables) {
        $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM (";
        $end = end($tables);
        foreach ($tables as $table) {
            $sql .= "SELECT * FROM tmp_" . $table['type'];
            if ($table != $end) $sql .= "
            UNION ";
        }
        $sql .= ") AS result " . $limit_str;
        $res = sql_getRows($sql);

        $_total = (int)sql_getValue("SELECT FOUND_ROWS()");

        foreach ($res as $i => $val) {
            foreach ($tables as $table) {
                if ($table['type'] == $val['type']) break;
            }
            $res[$i]['href'] = $this->getHref($val['id'], $table);
            $res[$i]['path'] = $this->getPath($val['pid'], $table, $val['id'], $res[$i]['href']);

            $res[$i]['name'] = $this->convertText($res[$i]['name'], array($query), 0);
            $res[$i]['text'] = $this->convertText($res[$i]['text'], array($query));
        }

        return array('list' => $res, 'total' => $_total);
    }

    /**
     * Возвращает хлебные крошки до найденной страницы
     * @param $pid
     * @return string
     */
    function getPath($pid, $table = array(), $id = 0, $href = null) {
        if (!$pid) return;

        /**
         * @var TTreeUtils $tree_obj
         */
        $tree_obj = & Registry::get('TTreeUtils');
        $pids = $tree_obj->getPids($pid);

        $path = "";
        if ($href && strpos($href,'http://')!==false) {
            $urlinfo = parse_url($href);
            $domain = $urlinfo['host'];
            $path .= "<a href='http://" . $domain . "'>" . $domain . "</a>" . (!empty($pids) ? "&gt;&nbsp;" : '');
            $domain = "http://" . $domain . '/';
        }

        foreach ($pids as $key => $val) {
            if ($val['id'] == ROOT_ID) continue;
            $path .= "<a href='" . $domain . $val['href'] . "'>" . $val['name'] . "</a>" . ($key < count($pids) - 1 ? "&gt;&nbsp;" : "");
        }
        return $path;
    }

    /**
     * Возвращает ссылку до найденной страницы
     * @param $id
     * @param $table
     * @return string
     */
    function getHref($id, $table) {

        $href = "";
        // В настройках поиска для каждой таблицы задается класс и метод, возвращающие путь до найденного объекта по его ID
        if (isset($table['href'])) {
            if ($table['href']['class'] && $table['href']['method']) {
                $obj = & Registry::get($table['href']['class']);
                if ($obj) {
                    $href = $obj->$table['href']['method']($id);
                }
            }
        }

        if ($href) {
            /**
             * @var TPage $page_obj
             */
            $page_obj = & Registry::get('TPage');
            $href = $page_obj->dirs_lang . $href;
        }

        if (strpos($href, "http://") !== false) {
            $href = "http://" . str_replace('//', '/', substr($href, strlen("http://")));
        } else {
            if (substr($href, 0, 1) != '/') $href = '/' . $href;
            $href = str_replace('//', '/', $href);
        }

        return $href;
    }

    /**
     * Форматирование текста перед выводом: подсветка найденных слов
     * @param $text
     * @param $query
     * @param int $cut - обрезать текст
     * @return mixed|string
     */
    function convertText($text, $query, $cut = 1) {
        $text = strip_tags($text);
        $pos = array();
        foreach ($query as $v) {
            if (!empty($v)) {
                $offset = 0;
                $i = 0;
                do {
                    $pos[$i] = strpos(strtolower($text), strtolower($v), $offset);
                    if ($pos[$i]) $offset = $pos[$i] + 1;
                    $i++;
                } while ($pos[$i - 1] !== false && $i < 5);
                if ($pos[$i - 1] === false) unset($pos[$i - 1]);
            }
        }
        if (count($pos) > 0) {
            if ($cut) {
                $temp = '';
                foreach ($pos as $val) {
                    $minus = $val - 100;
                    if ($minus < 0) $minus = 0;
                    $temp_text = substr($text, $minus, 200);
                    if ($temp != '...' . $temp_text . '...<br>') $temp .= '...' . $temp_text . '...<br>';
                }
                $text = $temp;
            }
            foreach ($query as $v) {
                if (empty($v)) continue;
                $text = preg_replace("`(" . preg_quote($v) . ")`i", "<span style='color: red;'>\\1</span>", $text);
            }
        } elseif ($cut) $text = "";
        return $text;
    }

    /**
     * Поиск ключевого слова в произвольной таблице
     * @param $type - тип искомых данных
     * @param $query - искомая фраза
     * @param $table - название таблицы, в которой искать
     * @param $fields_select
     * @param $fields_search - поля, в которых искать
     * @param string $where - дополнительное условие выборки     *
     */
    function searchInTables($type, $query, $table, $fields_select, $fields_search, $where = '', $join = array()) {

        $select_fields = array();
        $search_fields = array();
        $joins = array();

        foreach ($fields_select as $val) {
            if (substr($val, -1) == '_') {
                if (lang() == LANG_DEFAULT) $val = substr($val, 0, strlen($val) - 1);
                else $val .= lang();
            }
            $sel_f = strpos($val, '.') === false ? $table . "." . $val : $val;
            $as_f = strpos($val, '.') === false ? $val : substr($val, strpos($val, ".") + 1);
            if ($val != 'null') $select_fields[] = "IFNULL(" . $sel_f . ", '') AS " . $as_f;
            else $select_fields[] = 'NULL';
        }
        foreach ($fields_search as $val) {
            if (substr($val, -1) == '_') {
                if (lang() == 'ru') $val = substr($val, 0, strlen($val) - 1);
                else $val .= lang();
            }
            $sel_f = strpos($val, '.') === false ? $table . "." . $val : $val;
            $search_fields[] = "IFNULL(" . $sel_f . ", '')";
        }

        if ($join) {
            foreach ($join as $k => $join_table) {
                $joins[] = " LEFT JOIN " . $join_table['name'] . " AS " . $join_table['name'] . " ON " . $join_table['on'] . PHP_EOL;
            }
        }

        // where
        $ss = array();
        $ss[] = "LCASE(CONCAT(" . implode(', ', $search_fields) . ") LIKE '%" . e(strtolower($query)) . "%')";

        $search_where = array();
        $search_where[] = $where;
        $search_where[] = '(' . implode(' OR ', $ss) . ')';

        $columns = sql_getRows("SHOW columns FROM " . $table);
        foreach ($columns as $val) {
            if ($val['Field'] == 'root_id') {
                $search_where[] = $table . '.root_id=' . ROOT_ID;
                break;
            }
        }

        $sql = "INSERT INTO tmp_" . $type . " (id, pid, name, text)
        SELECT " . implode(', ', $select_fields) . "
        FROM " . $table .
                implode(' ', $joins) .
                " WHERE " . implode(' AND ', $search_where);
        sql_query($sql);

        $sql = "UPDATE tmp_" . $type . " SET type='" . $type . "' WHERE type IS NULL";
        sql_query($sql);
    }

}