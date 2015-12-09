<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');
define ('USE_ED_VERSION', '1.0.2');

class TGeoMainElement extends TElems
{

    var $elem_name = "elem_main";
    var $elem_table = "geo";
    var $elem_type = "single";
    var $elem_str = array( //строковые константы
        'name' => array('Название (ru)', 'Name (ru)'),
        'name_en' => array('Название (en)', 'Name (en)'),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'pid' => array(
                'type' => 'hidden',
                'display' => array(
                    'func' => 'get_pid',
                ),
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
                'size' => 40,
            ),
            'name_en' => array(
                'type' => 'text',
                'size' => 40,
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script;

    ########################
    function get_pid() {
        $pid = (int)get('pid');
        return $pid ? $pid : sql_getValue("SELECT pid FROM {$this->elem_table} WHERE id='" . (int)get('id') . "'");
    }

    /**
     * Вызывается перед сохранением в базу
     *
     * @param array $fld
     * @return array
     */
    function ElemRedactBefore($fld) {
        $fld = parent::ElemRedactBefore($fld);

        $error = "";

        $page_id = get('id', 0, 'gp');
        $page_pid = get('pid', 0, 'gp');

        if ($page_pid) {
            $parent = sql_getRow("SELECT dir, pids, level FROM " . $this->elem_table . " WHERE id=" . $page_pid);
        } else {
            $page_pid = sql_getValue("SELECT pid FROM `" . $this->elem_table . "` WHERE id=" . $page_id);
            $parent = sql_getRow("SELECT dir, pids, level FROM " . $this->elem_table . " WHERE id=" . $page_pid);
        }

        if (!$page_id) { // создание нового раздела
            $auto = sql_getRow("SHOW TABLE STATUS LIKE '" . $this->elem_table . "'");
            if ($auto['Auto_increment']) $new_id = $auto['Auto_increment'];

            if ($page_pid == $page_id) {
                $fld['level'] = 1;
            } else {
                $fld['level'] = $parent['level'] + 1;
            }

            $fld['priority'] = sql_getValue("SELECT MAX(priority) FROM `" . $this->elem_table . "` WHERE pid=" . $page_pid) + 1;
        }

        $page = $page_id ? $page_id : $new_id;

        if ($page_pid != $page_id) {
            # pids
            $pids = explode('/', $parent['pids']);
            if (!$pids[0]) array_shift($pids);
            if (!$pids[count($pids) - 1]) array_pop($pids);
            $pids[] = $page_pid;
            $fld['pids'] = '/' . join('/', $pids) . '/';

            # dir
            $fld['dir'] = $parent['dir'] . $page . '/';
        } else {
            # pids
            $fld['pids'] = '/' . $page_pid . '/';
            $fld['dir'] = '/' . $page . '/';
        }

        # изменим next у родителя
        sql_query("UPDATE tree SET next='1' WHERE id='" . (isset($parent['id']) ? $parent['id'] : $page_pid) . "'");

        # Проверка на существование dir
        $check = sql_getValue("SELECT id FROM " . $this->elem_table . " WHERE dir='" . $fld['dir'] . "'");
        if ($check && $check != $page_id) {
            $error_tab = $k;
            $error = "Раздел с таким URL уже существует";
        }
        return array('fld' => $fld, '_error_text' => $error);
    }

}