<?php

class TPrint
{

    function show() {
        $page = &Registry::get('TPage');
        $page->template = 'print';
    }

    /**
     * Функция возвращает путь к версии для печати
     * @return string
     */
    function getPrintUrl() {
        $path = $_SERVER['REQUEST_URI'];
        $pids = explode('/', $path);
        foreach ($pids as $k=>$v) {
            if (empty($v)) unset($pids[$k]);
        }
        $pids = array_values($pids);
        $ret = array();
        if (isset($pids[0]) && $pids[0] == lang()) {
            $ret[0] = $pids[0];
            $ret[1] = 'print';
            foreach ($pids as $k=>$v) {
                if ($k == 0) continue;
                $ret[] = $v;
            }
        } else {
            $ret = $pids;
            array_unshift($ret, 'print');
        }
        return '/' . implode('/', $ret);
    }
}