<?php

if (!defined('LANG_SELECT'))
    define('LANG_SELECT', 0);

class TTreeUtils
{

    var $table;
    var $_cached_pids = array();

    function TTreeUtils() {
        $this->table = 'tree';

        $this->elems_pids = array();
        $this->elems_items = array();
        $this->elems_dirs = array();
    }

    function Select($fields, $join = '', $where = '') {
        return select($this->table, $fields, $join, $where);
    }

    function setRootId($root_id) {
        $this->root = $root_id;
    }

    function getRootId() {
        return $this->root;
    }

    // возвращает путь по input
    function getPath($input) {
        if ($input == $this->root) {
            return '';
        }
        $this->_last_pids = $this->getPids($input);
        $res = array();
        foreach ($this->_last_pids as $key => $val) {
            if ($val['id'] == $val['root_id']) {
                continue;
            }
            $res[] = $val['page'];
        }
        return implode('/', $res);
    }

    // возвращает pids по id
    function getPidsById($id) {
        $id = intval($id);
        global $langs;
        if (!isset($this->_cached_pids[$this->getRootId()])) {
            $this->_cached_pids[$this->getRootId()] = array();
        }
        $ids = array();
        do {
            if (empty($this->_cached_pids[$this->getRootId()][$id])) {
                $sql = $this->Select('*', $this->elems_pids, $this->table . '.id=' . $id . ' AND ' . $this->table . '.visible>=0 LIMIT 1');
                $struct = struct(sql_getTableRows($sql, __FILE__, __LINE__), $this->table);
                if (empty($struct)) {
                    break;
                }
                if (defined('LANG_SELECT') && LANG_SELECT) {
                    $struct['name'] = $struct['name_' . lang()] ? $struct['name_' . lang()] : $struct['name_' . LANG_DEFAULT];
                    foreach ($langs as $k => $v) unset($struct['name_' . $v]);
                }
                $this->_cached_pids[$this->getRootId()][$id] = $struct;
            }
            $ids[] = $id;
            $id = $this->_cached_pids[$this->getRootId()][$id]['pid'];
        }
        while (!array_key_exists($id, $this->_cached_pids[$this->getRootId()]) || $id != $this->_cached_pids[$this->getRootId()][$id]['pid']);
        if (empty($ids) && $this->root != $id) {
            $this->getPidsById($this->root);
            $ids[] = $this->root;
        }
        $ids = array_reverse($ids);
        $pids = array();
        $ahref = array();
        $level = 0;
        foreach ($ids as $id) {
            $ahref[] = $this->_cached_pids[$this->getRootId()][$id]['page'];
            $href = implode('/', $ahref);
            $this->_cached_pids[$this->getRootId()][$id]['href'] = $href;
            $this->_cached_pids[$this->getRootId()][$id]['level'] = $level++;
            $this->_cached_pids[$this->getRootId()][$href] = &$this->_cached_pids[$this->getRootId()][$id];
            $pids[] = $this->_cached_pids[$this->getRootId()][$id];
        }
        return $pids;
    }

    // возвращает pids по url
    function getPidsByUrl($url) {
        $url_parts = array_filter(explode('/', $url));
        return $this->getPidsByUrlParts($url_parts);
    }

    // возвращает pids по array
    function getPidsByUrlParts($url_parts) {
        global $langs;
        if (!isset($this->_cached_pids[$this->getRootId()])) {
            $this->_cached_pids[$this->getRootId()] = array();
        }
        $pid = $this->root;
        if (!$pid) return array();
        $cur_url_parts = array();
        $ids = array();

        // кэширует корневой элемент
        $this->getPidsById($this->root);
        $ids[] = $this->root;

        $level = 1;
        foreach ($url_parts as $page) {
            $page = mysql_escape_string($page);
            $cur_url_parts[] = $page;
            $href = implode('/', $cur_url_parts);
            if (empty($this->_cached_pids[$this->getRootId()][$href])) {
                $sql = $this->Select('*', $this->elems_pids, $this->table . '.pid=' . $pid . ' AND ' . $this->table . ".page='" . $page . "' AND " . $this->table . ".visible>=0 LIMIT 1");
                $struct = struct(sql_getTableRows($sql, __FILE__, __LINE__), $this->table);
                if (empty($struct)) {
                    break;
                }
                if (defined('LANG_SELECT') && LANG_SELECT) {
                    $struct['name'] = $struct['name_' . lang()] ? $struct['name_' . lang()] : $struct['name_' . LANG_DEFAULT];
                    foreach ($langs as $k => $v) unset($struct['name_' . $v]);
                }
                $struct['href'] = $href;
                $struct['level'] = $level++;
                $this->_cached_pids[$this->getRootId()][$struct['id']] = $struct;
                $this->_cached_pids[$this->getRootId()][$href] = &$this->_cached_pids[$this->getRootId()][$struct['id']];
            }
            $ids[] = $this->_cached_pids[$this->getRootId()][$href]['id'];
            $pid = $this->_cached_pids[$this->getRootId()][$href]['id'];
        }
        $pids = array();
        foreach ($ids as $id) {
            $pids[] = $this->_cached_pids[$this->getRootId()][$id];
        }
        return $pids;
    }

    function nodeExists($node_id) {
        $pids = $this->getPids($node_id);
        $node_type = $this->getNodeIdType($node_id);
        switch ($node_type) {
            case 1:
                return (count($node_id) == (count($pids) - 1));
            case 2:
                return ($pids[count($pids) - 1]['id'] == $node_id);
            case 3:
                $parts = array_filter(explode('/', $node_id));
                return (count($parts) == (count($pids) - 1));
        }
    }

    //возвращает тип переданного параметра для
    //nodeExists и getPids
    //1 - массив url_parts
    //2 - id
    //3 - url строкой
    function getNodeIdType($node_id) {
        if (is_array($node_id)) {
            return 1;
        }
        if (is_numeric($node_id)) {
            return 2;
        }
        return 3;
    }

    function getPids($input) {
        $node_type = $this->getNodeIdType($input);
        switch ($node_type) {
            case 1:
                return $this->getPidsByUrlParts($input);
            case 2:
                return $this->getPidsById($input);
            case 3:
                return $this->getPidsByUrl($input);
        }
    }

    // НЕ проверена!!!
    // ищет непустой контент
    /*
     function GetNextId($pid) {
         if ($pid) {
             $res = getSQL("SELECT id, nextid, type, IF(LENGTH(text)>0,1,0) AS text FROM ".$this->table." WHERE pid=".$pid." AND visible_".lang()."=1 AND lang='".$this->lang."' ORDER BY type DESC, priority, name");
             if ($res) foreach ($res as $id=>$val) {
                 if ($val['type']) return $pid;
                 if ($val['text']) return $id;
                 if ($val['nextid']) {
                     $next_id = $this->GetNextId($id);
                     if ($next_id) return $next_id;
                 }
             }
         }
         return false;
     }*/

    // берет контент по ID

    function getValuesWithoutLangs(&$array) {
        global $langs;
        foreach ($array as $key => $val) {
            if (is_array($val)) $array[$key] = $this->getValuesWithoutLangs($array[$key]);
            if (in_array(substr($key, -2), $langs) && substr($key, -3, 1) == '_') {
                $row = split("_", $key);
                $array[$row[0]] = $array[$row[0] . '_' . lang()] ? $array[$row[0] . '_' . lang()] : $array[$row[0] . '_' . LANG_DEFAULT];
                foreach ($langs as $k => $v) {
                    unset($array[$row[0] . "_$v"]);
                }
            }
        }
        return $array;
    }

    function getContent($id) {

        $type = sql_getValue('SELECT type FROM ' . $this->table . " WHERE id='" . $id . "' AND visible>=0 LIMIT 1");
        $root_id = sql_getValue('SELECT root_id FROM ' . $this->table . " WHERE id='" . $id . "'");

        $config_obj = & Registry::get('TConfig');

        if ($config_obj->hasType($type, $root_id)) {
            // заполняем $this->elements
            if ($type == 'module') {
                $module_name = sql_getValue("SELECT module FROM elem_module WHERE pid=" . $id);
                if ($module_name) $config = $config_obj->getConfigByModuleName($module_name, $root_id);
                else $config = $config_obj->getConfigByType($type, $root_id);
                if (!in_array('elem_module', $config['elements'])) $config['elements'][] = 'elem_module'; #new
            }
            else
                $config = $config_obj->getConfigByType($type, $root_id);

            $this->elements = $config['elements'];
            $original_elements = array('elem_form', 'elem_gallery', 'elem_meta', 'elem_module', 'elem_text');
            foreach ($this->elements as $k => $v) {
                if (!in_array($v, $original_elements)) {
                    unset($this->elements[$k]);
                }
            }

            // форумируем join для $this->Select
            $join = implode(', ', $this->elements);
            $sql = $this->Select('*', $join, $this->table . '.id=' . $id . ' AND ' . $this->table . '.visible>=0 LIMIT 1');

            $content = struct(sql_getTableRows($sql), $this->table);

            $com_cfg = & Registry::get('TCommonCfg');
            $content['domain'] = $com_cfg->getSiteByRootID($content['root_id']);
            if (defined('LANG_SELECT') && LANG_SELECT) $content = $this->getValuesWithoutLangs($content);
            if (isset($this->_cached_pids[$this->getRootId()][$id]) && is_array($this->_cached_pids[$this->getRootId()][$id])) {
                $content = array_merge($this->_cached_pids[$this->getRootId()][$id], $content);
            }

            return $content;
        }
        return false;
    }

    /* @todo оптимизировать получение href */
    function GetItems($id, $types, $href = '') {
        $items = $this->GetSubItems($id, $types);
        if (empty($href)) {
            $href = $this->content['href'];
        }
        if (substr($href, -1, 1) != '/') {
            $href .= '/';
        }
        foreach ($items as $key => $item) {
            $items[$key]['href'] = $href . $item['page'];
        }
        return $items;
    }

    // Возвращает все элементы каталога
    function GetSubItems($id, $types = 'all', $show_invisible = false) {
        //pr(debug_backtrace());die();
        global $langs;
        if (!is_array($id)) {
            $id = array($id);
        }
        foreach ($id as $k => $v) {
            $id[$k] = (int)$v;
        }

        $config_obj = & Registry::get('TConfig');

        $item_elems = array();
        $types_where = '';

        if (empty($types) || $types == 'all') {
            $item_elems = $config_obj->getAllElems();
        }
        elseif (is_array($types)) {
            $types_where = " AND " . $this->table . ".type IN ('" . implode("', '", $types) . "')";
            $item_elems = $config_obj->getElemsByTypes($types);
        }

        $pid_where_arr = array();
        foreach ($id as $k => $v) {
            $pid_where_arr[] = $this->table . '.pid=' . $v;
        }
        $pid_where = '(' . implode(' AND ', $pid_where_arr) . ')';

        $id_where_arr = array();
        foreach ($id as $k => $v) {
            $id_where_arr[] = $this->table . '.id!=' . $v;
        }
        $id_where = '(' . implode(' AND ', $id_where_arr) . ')';

        $sql = $this->Select('*', $item_elems, $pid_where.' AND '.$id_where.($show_invisible ? ' AND '.$this->table.'.visible>-1' : ' AND '.$this->table.'.visible>0').$types_where.' GROUP BY '.$this->table.'.id ORDER BY '.$this->table.'.pid, '.$this->table.'.priority, '.(defined('LANG_SELECT') && LANG_SELECT ? $this->table.'.name_'.lang() : $this->table.'.name'));

        $rows = sql_getRows($sql, false, __FILE__, __LINE__);

        $items = array();
        foreach ($rows as $key => $value) {
            if (defined('LANG_SELECT') && LANG_SELECT)
                $items[$key] = struct($this->getValuesWithoutLangs($value), $this->table);
            else
                $items[$key] = struct($value, $this->table);
        }

        return $items;
    }

    /*function GetAllItems($id, $item_types, $srch_types, $deep_level = 1, $plain = false, $limit = 0, $href = '') {
         if (empty($href)) $href = $this->content['href'];
         if ($deep_level > 1) $srch_items = $this->GetItems($id, $srch_types, $href);
         $items = $this->GetItems($id, $item_types, $limit, $href);
         $_srch_items = array();
         if ($deep_level > 1 && !empty($srch_items) && is_array($srch_items)) {
             foreach ($srch_items as $srch_key => $srch_item) {
                 $subitems = array();
                 if ($deep_level - 1 > 0 && $srch_item['next']) {
                     $subitems = $this->GetAllItems($srch_item['id'], $item_types, $srch_types, $deep_level - 1, $plain, $limit, $srch_item['href']);
                 }
                 //pr($subitems);
                 if (!empty($subitems) && is_array($subitems)) {
                     if ($plain) {
                         $items = array_merge($items, $subitems);
                     }
                     else {
                         $_srch_items[$srch_key] = $srch_item;
                         $_srch_items[$srch_key]['subitems'] = $subitems;
                     }
                     unset($subitems);
                 }
             }
         }
         if (!$plain && !empty($_srch_items)) {
             $items = array_merge($items, $_srch_items);
         }
         return $items;
     }*/

    // Возвращает все директории каталога
    /*function GetSubDirs($id, $show_invisible = false) {
         $id = (int)$id;
         $types = $GLOBALS['cfg']['modules']['content']['dirs_types'];
         $sql = $this->Select('*', $this->elems_dirs, $this->table.'.pid='.$id.' AND '.$this->table.'.id!='.$id.($show_invisible ? '' : ' AND '.$this->table.'.visible>0').(!empty($types) ? " AND type IN ('".implode("', '", $types)."')" : '').' ORDER BY priority, name');

         $rows = getRows($sql, __FILE__, __LINE__);
         $dirs = array();
         foreach ($rows as $key => $value) {
             $dirs[$key] = struct($value, $this->table);
         }
         return $dirs;
     }*/

}

?>