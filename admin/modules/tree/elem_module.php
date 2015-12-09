<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TModuleElement extends TElems
{
    ######################
    var $elem_name = "elem_module"; //название elema
    var $elem_table = "elem_module"; //название таблицы elema (DEFAULT $elem_name)
    var $elem_type = "single";
    var $elem_str = array( //строковые константы
        'caption' => array(
            'Выбор модуля',
            'Module choice',
        ),
        '_default_module' => array(
            '--- Выберите модуль ---',
            '--- No module ---',
        ),
        'module' => array(
            'Модуль',
            'Module',
        ),
        'saved' => array(
            'Данные успешно сохранены',
            'Data saved successfully',
        ),
    );

    var $elem_where = '';
    var $elem_req_fields = array();
    var $script;

    ###############################
    var $elem_fields = array(
        'columns' => array(
            'pid' => array(
                'type' => 'hidden',
            ),
            'module' => array(
                'type' => 'select',
                'func' => 'modules_select',
            ),
        ),
        'id_field' => 'pid',
    );

    ######################
    function modules_select() {
        $root_id = 0;
        $id = (int)get('id', 0);
        if ($id) {
            $root_id = sql_getValue("SELECT root_id FROM tree WHERE id=" . $id);
        }
        if (!$root_id) {
            $pid = (int)get('pid', 0);
            if ($pid) {
                $root_id = sql_getValue("SELECT root_id FROM tree WHERE id=" . $pid);
            }
        }
        $function_modules = $root_id ? $GLOBALS['cfg']['function_modules'][$root_id] : array();

        $mods = array('' => $this->str('_default_module'));
        foreach ($function_modules as $k => $v) {
            $mods[$k] = utf($v['name'][int_langId()]);
        }
        return $mods;
    }
    ########################
}

?>