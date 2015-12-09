<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $elem_name = "elem_main";
    var $elem_table = "sites_langs";
    var $elem_type = "single";
    var $elem_str = array(
        'name' => array('Название', 'Title',),
        'descr' => array('Описание', 'Description',),
        'locale' => array('Локаль', 'Locale',),
        'charset' => array('Кодировка', 'Charset',),
        'root_id' => array('ID', 'ID',),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'name' => array(
                'type' => 'text',
            ),
            'root_id' => array(
                'type' => 'text',
                'display' => array(
                    'func' => 'showRootID',
                ),
            ),
            'descr' => array(
                'type' => 'text',
            ),
            'locale' => array(
                'type' => 'select',
                'func' => 'showLocals',
            ),
            'charset' => array(
                'type' => 'select',
                'func' => 'showCharset',
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'files'
    );
    var $elem_where = "";
    var $script = "";
    var $elem_req_fields = array('name', 'root_id',);

    function showLocals() {
        global $settings;
        return $settings['locale'];
    }

    function showCharset() {
        global $settings;
        return $settings['charset'];
    }

    function showRootID() {
        $id = (int)get('id', '');
        if (!empty($id)) return (int)sql_getValue("SELECT root_id FROM " . $this->elem_table . " WHERE id=" . $id);

        $root_id = (int)sql_getValue("SELECT MIN(root_id) FROM " . $this->elem_table);
        if (empty($root_id)) return 100;
        else {
            if ($root_id <= 1) {
                $root_id = (int)sql_getValue("SELECT MAX(root_id) FROM " . $this->elem_table) + 1;
            }
            else {
                return $root_id - 1;
            }
        }
    }

    /**
     * Вызывается после сохранения в БД
     * @param array() $fld
     * @param integer $id
     * @return array()
     */
    function ElemRedactAfter($fld, $id) {
        $tree = sql_getValue("SELECT * FROM tree WHERE root_id='" . $fld['root_id'] . "' AND id=pid LIMIT 1");
        if (!$tree) {
            // сделать в дереве раздел (только один раздел с type=home)
            $tree_row = sql_getRow("SELECT * FROM tree WHERE id=pid LIMIT 1");
            if ($tree_row) {
                $tree_row['id'] = $tree_row['pid'] = $tree_row['root_id'] = $fld['root_id'];
                $tree_row['pids'] = '/' . $fld['root_id'] . '/';
                $tree_row['next'] = 0;
                $tree_row['priority'] = (int)sql_getValue("SELECT MAX(priority) FROM tree WHERE id=pid") + 1;
                sql_insert('tree', $tree_row);
            }
        }
        return $fld;
    }
}