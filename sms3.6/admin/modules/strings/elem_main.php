<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    ######################
    var $elem_name = "elem_main"; //название elema
    var $elem_table = "strings"; //название таблицы elema (DEFAULT $elem_name)
    var $elem_type = "single";
    var $elem_str = array( //строковые константы
        'name' => array('Название', 'Name',),
        'def' => array('Значение по умолчанию', 'Default value'),
        'value' => array('Текущее значение', 'Current value'),
        'description' => array('Описание', 'Description'),
        'copy_clipboard' => array('Скопировать в буфер', 'Copy to clipboard'),
        'make_default' => array('Восстановить значение по умолчанию', 'Restore default value'),
        'module' => array('Модуль сайта', 'Site module',),
        'root_id' => array('На каком сайте показывать', 'Show at sites'),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'module' => array(
                'type' => 'select',
                'func' => 'get_module_list'
            ),
            'name' => array(
                'type' => 'text',
                'size' => 60,
                'maxlength' => 255,
            ),
            'value' => array(
                'lang_select' => LANG_SELECT,
                'type' => 'textarea',
                'cols' => 50,
                'rows' => 5,
            ),
            'description' => array(
                'lang_select' => LANG_SELECT,
                'type' => 'textarea',
                'cols' => 50,
                'rows' => 5,
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script = "";

    //var $sql = true;

    #####################

    function ElemInit() {
        $id = (int)get('id', 0);
        $root_id = domainRootID();
        if ($id) {
            // если нет своего value, то вывести value из языка по умолчанию
            # получаем данные по id
            $row = sql_getRow('SELECT id,module,name,def,value FROM strings WHERE id=' . $id);
            if (!$row['value'] || !$row['def']) {
                # получаем данные для такого module, name
                $temp_row = sql_getRow('SELECT * FROM strings WHERE module="' . $row['module'] . '" AND name="' . $row['name'] . '" AND lang="' . LANG_DEFAULT . '" AND root_id=' . getMainRootID());
                if ($temp_row) {
                    if ($temp_row['value']) $row['value'] = $temp_row['value'];
                    if ($temp_row['def']) $row['def'] = $temp_row['def'];
                }
            }
            $this->elem_fields['columns']['value']['value'] = h($row['value']);
            $this->elem_fields['columns']['def'] = array(
                'type' => 'hidden',
                'value' => h($row['def']),
            );
        }
        $this->elem_fields['columns']['root_id'] = array(
            'type' => 'select',
            'func' => 'getRoots',
        );
        if (!$id && $root_id) {
            $this->elem_fields['columns']['root_id']['value'] = $root_id;
        }
        $this->elem_fields['columns']['lang'] = array(
            'type' => 'hidden',
        );
        if (!$id) {
            $this->elem_fields['columns']['lang']['value'] = '';
        }

        return parent::ElemInit();
    }

    #####################

    function get_module_list() {
        global $cfg, $intlang;

        $function_modules = $cfg['function_modules'][domainRootID()];

        $filter_modules = array('site' => 'Ядро сайта', 'meta' => 'Мета-теги');
        foreach ($function_modules AS $key => $val) {
            $filter_modules[$key] = $val['name'][$intlang];
        }
        return $filter_modules;
    }

    function ElemEdit($id, $row) {
        $id = get('id', 0, 'p');

        $this->table = $this->elem_table;

        # получаем значение для записи с индексом  id
        $exist_row = sql_getRow('SELECT * FROM ' . $this->elem_table . ' WHERE id=' . $id);

        if (!$row['lang'] && $row['root_id']) {
            // заполнение поля lang
            $row['lang'] = $this->getLang($row['root_id']);
            if (!$row['lang']) $row['lang'] = LANG_DEFAULT;
        }

        $req_fields = array();
        if (defined('LANG_SELECT') && LANG_SELECT) {
            foreach ($this->elem_req_fields as $key => $val) {
                // Проверяем, это многоязыковое поле или нет
                if (isset($this->elem_fields['columns'][$val]['lang_select']) && $this->elem_fields['columns'][$val]['lang_select']) {
                    $req_fields[] = $val . "_" . $row['lang'];
                } else $req_fields[] = $val;
            }
        } else $req_fields = $this->elem_req_fields;

        # запись такая есть?
        if (!isset($exist_row['id'])) {
            // если создается новая запись на не основном языке, нужно еще создать такую же на основном (если не создана ранее)
            $main_root_id = getMainRootID();
            if ($row['root_id'] != $main_root_id || $row['lang'] != LANG_DEFAULT) {
                $exist_row_default_lang = sql_getRow('SELECT * FROM ' . $this->elem_table . ' WHERE name="' . $row['name'] . '" AND module="' . $row['module'] . '" AND lang="' . LANG_DEFAULT . '" AND root_id="' . $main_root_id . '"');
                if (!$exist_row_default_lang) {
                    $def_row = array(
                        'module' => $row['module'],
                        'name' => $row['name'],
                        'value' => $row['value'],
                        'description' => $row['description'],
                        'def' => $row['value'],
                        'root_id' => $main_root_id,
                        'lang' => LANG_DEFAULT,
                    );
                    $this->EditorCommit($req_fields, true, $def_row, $this->elem_fields['id_field'], 0);
                }
            }
        }
        else {
            # запись для текущего языка или для текущего домена ?
            if ($exist_row['lang'] != $row['lang'] || $exist_row['root_id'] != $row['root_id']) {
                # значит запись для нового языка
                $row['id'] = '';
                # для новой записи значение устанавливается по умолчанию
                $row['def'] = $row['value'];
                $row['lang'] = $this->getLang($row['root_id']);
            }
        }

        if (!is_devel()) {
            # поля были disabled... следовательно их нет в $_POST
            $row['module'] = $exist_row['module'];
            $row['name'] = $exist_row['name'];
        }
        else {
            # разработчик изменяет также и значение по умолчанию
            $row['def'] = $row['value'];
        }

        $ret = $this->EditorCommit($req_fields, true, $row, $this->elem_fields['id_field'], $row['id']);
        return $ret;
    }

    function getLang($root_id) {
        global $site_domains;
        foreach ($site_domains as $d) {
            foreach ($d['langs'] as $k => $v) {
                if ($v['root_id'] == $root_id) {
                    return $k;
                }
            }
        }
        return false;
    }

    function getRoots() {
        global $site_domains;
        $ret = array();
        foreach ($site_domains as $site) {
            foreach ($site['langs'] as $lang) {
                $ret[$lang['root_id']] = $site['name'] . ' (' . $lang['descr'] . ')';
            }
        }
        return $ret;
    }

}

?>