<?php

// @todo рефакторить меню, полностью
class TMenu
{

    var $_active_ids = array();

    function Tmenu() {
    }

    function ext_show_menu($params) {
        // show_level
        if (!isset($params['show_level'])) {
            $params['show_level'] = 0;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $page_obj = & Registry::get('TPage');
        // root_id для меню
        if (isset($params['start_uri'])) {
            $root = $params['start_uri'];
        }
        elseif (isset($params['start_level']) && $params['start_level'] > 1) {
            $root = 0;
            if (isset($page_obj->pids[$params['start_level'] - 1]['id'])) {
                $root = $page_obj->pids[$params['start_level'] - 1]['id'];
            }
        }
        elseif (isset($params['start_level']) && $params['start_level'] < 0) {
            $end = end($page_obj->pids);

            $root = 0;
            /**/
            if ($params['show_level'] && ($params['start_level'] + $end['level']) < $params['show_level']) {
                $root = $end['id'];
            }
            /**/
            elseif (isset($page_obj->pids[$end['level'] + $params['start_level']]['id'])) {
                $root = $page_obj->pids[$end['level'] + $params['start_level']]['id'];
            }
        }
        else {
            $root = $tree_obj->root;
        }

        // levels
        if (!isset($params['levels'])) {
            $params['levels'] = 1;
        }
        // full
        if (!isset($params['full'])) {
            $params['full'] = true;
        }
        // types
        if (!isset($params['types'])) {
            //$params['types'] = $this->config['nested'];
            $params['types'] = 'all';
        }
        $ret = array();

        if ($root) {
            if ($page_obj->content['level'] >= $params['show_level']) {
                // массив с меню
                $menu = $this->ext_menu($root, $page_obj->content['id'], $params['levels'], $params['full'], $params['types']);
            }
            else {
                $menu = array();
            }
            $ret['menu'] = $menu;
            $ret['current']['id'] = $page_obj->content['id'];
        }
        return $ret;
    }

    function ext_menu($from, $current = 0, $level = 1, $full = 0, $types = 'all') {
        // $from			id элемента дерева от которого строить меню
        // $current		id текущего элемента, который нунжо выделять в меню
        // $level 		кол-во уровней вложенности
        // $full			0: (по умолчанию) раскрыты только элементы на пути к текущему
        // 				1: раскрыты все элементы меню
        //-------------------------------------------------------
        if (empty($from)) {
            return;
        }
        if (empty($current)) {
            $current = $from;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $page_obj = & Registry::get('TPage');
        $pids = $tree_obj->getPids($current);
        foreach ($pids as $n => $val) {
            $this->_active_ids[$n] = $val['id'];
        }

        $path = $tree_obj->getPath($from);

        $end = '';
        if (!($path == '/' || $path == '')) {
            $end = '/';
        }

        $dir_sep = '';
        if (!empty($this->dirs_lang) && substr($path, 0, 1) != '/') {
            $dir_sep = '/';
        }
        $start_url = $page_obj->dirs_lang . $path . $end;

        $pids = $tree_obj->getPids($from);
        if (isset($pids) && is_array($pids)) {
            $last = end($pids);
            $from = $last['id'];
        }
        return $this->ext_getmenu($from, $current, $level, $start_url, $full, $types);
    }

    function ext_getmenu($id, $cur_id, $level, $start_url = '', $full = 0, $types = 'all') {
        if ($level <= 0) {
            return;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $menu = $tree_obj->getSubItems($id, $types);

        foreach ($menu as $key => $val) {

            $menu[$key]['href'] = $start_url . $val['page'];
            if (!empty($start_url) && substr($start_url, -1, 1) != '/') {
                $menu[$key]['href'] = $start_url . '/' . $val['page'];
            }

            $menu[$key]['active'] = '';
            if (in_array($val['id'], $this->_active_ids)) {
                $menu[$key]['active'] = 'active';
            }

            if (($full || (isset($menu[$key]['active']) && $menu[$key]['active'])) && $val['next'] && $level - 1 > 0) {
                $menu[$key]['menu'] = $this->ext_getmenu($val['id'], $cur_id, $level - 1, $menu[$key]['href'], $full, $types);
            }
            else {
                $menu[$key]['menu'] = null;
            }
        }
        return $menu;
    }

    ####################################################################

    function menu($from, $current = 0, $level = 1, $full = 0, $types = 'all') {
        // $from			id элемента дерева от которого строить меню
        // $current		id текущего элемента, который нунжо выделять в меню
        // $level 		кол-во уровней вложенности
        // $full			0: (по умолчанию) раскрыты только элементы на пути к текущему
        // 				1: раскрыты все элементы меню
        //-------------------------------------------------------
        if (empty($from)) {
            return;
        }
        if (empty($current)) {
            $current = $from;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $page_obj = & Registry::get('TPage');
        $pids = $tree_obj->getPids($current);
        foreach ($pids as $n => $val) {
            $this->_active_ids[$n] = $val['id'];
        }

        $path = $tree_obj->getPath($from);

        $end = '';
        if (!($path == '/' || $path == '')) {
            $end = '/';
        }

        $dir_sep = '';
        if (!empty($this->dirs_lang) && substr($path, 0, 1) != '/') {
            $dir_sep = '/';
        }
        $start_url = $page_obj->dirs_lang . (isset($GLOBALS['virtual_urls']) ? implode('/', $GLOBALS['virtual_urls']) . '/' : '') . $path . $end;

        $pids = $tree_obj->getPids($from);
        if (isset($pids) && is_array($pids)) {
            $last = end($pids);
            $from = $last['id'];
        }
        return $this->getmenu($from, $current, $level, $start_url, $full, $types);
    }

    // ----------------------------------------------------------
    function getmenu($id, $cur_id, $level, $start_url = '', $full = 0, $types = 'all') {
        if ($level <= 0) {
            return;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $menu = $tree_obj->getSubItems($id, $types);

        foreach ($menu as $key => $val) {

            $menu[$key]['href'] = $start_url . $val['page'];
            if (!empty($start_url) && substr($start_url, -1, 1) != '/') {
                $menu[$key]['href'] = $start_url . '/' . $val['page'];
            }

            $menu[$key]['active'] = '';
            if (in_array($val['id'], $this->_active_ids)) {
                $menu[$key]['active'] = 'active';
            }

            if (($full || (isset($menu[$key]['active']) && $menu[$key]['active'])) && $val['next'] && $level - 1 > 0) {
                $menu[$key]['menu'] = $this->getmenu($val['id'], $cur_id, $level - 1, $menu[$key]['href'], $full, $types);
            }
            else {
                $menu[$key]['menu'] = null;
            }
        }
        return $menu;
    }

    function show_menu($params) {
        // show_level
        if (!isset($params['show_level'])) {
            $params['show_level'] = 0;
        }

        $tree_obj = & Registry::get('TTreeUtils');
        $page_obj = & Registry::get('TPage');
        // root_id для меню
        if (isset($params['start_uri'])) {
            $root = $params['start_uri'];
        }
        elseif (isset($params['start_level']) && $params['start_level'] > 1) {
            $root = 0;
            if (isset($page_obj->pids[$params['start_level'] - 1]['id'])) {
                $root = $page_obj->pids[$params['start_level'] - 1]['id'];
            }
        }
        elseif (isset($params['start_level']) && $params['start_level'] <= 0) {
            $end = end($page_obj->pids);

            $root = 0;
            /**/
            if (!isset($end['id'])) {
                $end = $page_obj->pids[count($page_obj->pids)-2];
            }
            if ($params['show_level'] && ($params['start_level'] + $end['level']) < $params['show_level']) {
                $root = $end['id'];
            }
            /**/
            elseif (isset($page_obj->pids[$end['level'] + $params['start_level']]['id'])) {
                $root = $page_obj->pids[$end['level'] + $params['start_level']]['id'];
            }
        }
        else {
            $root = $tree_obj->root;
        }

        // return_parent
        if (!isset($params['return_parent'])) {
            $params['return_parent'] = false;
        }
        // levels
        if (!isset($params['levels'])) {
            $params['levels'] = 1;
        }
        // full
        if (!isset($params['full'])) {
            $params['full'] = true;
        }
        // types
        if (!isset($params['types'])) {
            //$params['types'] = $this->config['nested'];
            $params['types'] = 'all';
        }
        $ret = array();

        if ($root) {
            if ($page_obj->content['level'] >= $params['show_level']) {
                // массив с меню
                $menu = $this->menu($root, $page_obj->content['id'], $params['levels'], $params['full'], $params['types']);
            }
            else {
                $menu = array();
            }
            if ($params['return_parent']) {
                $root_item = array();
                $pids = $tree_obj->getPids($root);
                if (isset($pids) && is_array($pids)) {
                    $last = end($pids);
                    $from = $last['id'];
                    $root_item = $tree_obj->getContent($from);
                }
                $root_item['menu'] = $menu;
                $ret['item'] = $root_item;
                $ret['menu'] = &$root_item['menu'];
                return $ret;
            }
            $ret['menu'] = $menu;
            $ret['current']['id'] = $page_obj->content['id'];
        }
        return $ret;
    }

    function _cache($block) {
        global $domain;
        $page_obj = & Registry::get('TPage');
        $tree_obj = & Registry::get('TTreeUtils');
        // root_id для меню
        if (isset($block['params']['start_uri'])) {
            $root = $block['params']['start_uri'];
        }
        elseif (isset($block['params']['start_level']) && $block['params']['start_level'] > 1) {
            $root = 0;
            if (isset($page_obj->pids[$block['params']['start_level'] - 1]['id'])) {
                $root = $page_obj->pids[$block['params']['start_level'] - 1]['id'];
            }
        }
        elseif (isset($block['params']['start_level']) && $block['params']['start_level'] < 0) {
            $end = end($page_obj->pids);
            $root = 0;
            if ($block['params']['show_level'] && ($block['params']['start_level'] + $end['level']) < $block['params']['show_level']) {
                $root = $end['id'];
            }
            elseif (isset($page_obj->pids[$end['level'] + $block['params']['start_level']]['id'])) {
                $root = $page_obj->pids[$end['level'] + $block['params']['start_level']]['id'];
            }
        }
        else {
            $root = $tree_obj->root;
        }
        $cache = $domain . '_' . lang() . '_' . $root . '_' . $page_obj->content['id'];
        if (isset($GLOBALS['virtual_urls']) && is_array($GLOBALS['virtual_urls']) && !empty($GLOBALS['virtual_urls'])) {
            $cache .= '_' . implode('_', $GLOBALS['virtual_urls']);
        }
        return $cache;
    }

}

//end of class TMenu

?>