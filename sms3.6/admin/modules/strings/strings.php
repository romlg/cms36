<?php

class TStrings extends TTable
{

    var $name = 'strings';
    var $table = 'strings';
    var $selector = true;
    var $enabled_modules = array();

    ########################

    function TStrings() {
        global $actions, $str;

        TTable::TTable();

        if (DEV_MODE) {
            $actions[$this->name] = array(
                'create' => &$actions['table']['create'],
                'edit' => &$actions['table']['edit'],
                'delete' => &$actions['table']['delete'],
            );
        } else {
            $actions[$this->name] = array(
                'edit' => &$actions['table']['edit'],
            );
        }

        $actions[$this->name . '.editform'] = array(
            'apply' => array(
                'title' => array(
                    'ru' => 'Сохранить',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'apply\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'save_close' => array(
                'title' => array(
                    'ru' => 'Сохранить и закрыть',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'save\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => 'Отмена',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

        unset($actions[$this->name . '.editform'][is_devel() ? 'restore' : 'copy']);

        if (!empty($_GET['id'])) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . $_GET['id']);
        } else {
            $temp = "Новая константа";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                'Строковые константы',
                'Strings',
            ),
            'title_editform' => array(
                "Строковая константа : " . $temp,
                'String : ' . $temp,
            ),
            'add' => array(
                'Добавление новой строки',
                'Add string',
            ),
            'edit' => array(
                'Редактирование строки',
                'Edit string',
            ),
            'module' => array(
                'Модуль сайта',
                'Site module',
            ),
            'name' => array(
                'Название',
                'Name',
            ),
            'def' => array(
                'Значение по умолчанию',
                'Default value'
            ),
            'value' => array(
                'Текущее значение',
                'Current value'
            ),
            'description' => array(
                'Описание',
                'Description'
            ),
            'copy_clipboard' => array(
                'Скопировать в буфер',
                'Copy to clipboard'
            ),
            'make_default' => array(
                'Восстановить значение по умолчанию',
                'Restore default value'
            ),
            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
            'all' => array(
                '-- Все --',
                '-- All --'
            ),
            'deleted' => array(
                'Удалено',
                'Deleted',
            ),
            'is_default' => array(
                'Берется по умолчанию',
                'Default',
            ),
        ));
    }

    ########################

    function Show() {

        if (!empty($_POST)) {
            $action = get('actions', '', 'p');

            if ($action) {
                if ($this->Allow($action)) {
                    return $this->$action();
                }
                else {
                    return $this->alert_method_not_allowed();
                }
            }
        }

        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'IFNULL(t2.id,t1.id)',
                    'as' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => "CONCAT(IF(t1.module='site', '', CONCAT(t1.module, '_')), t1.name)",
                    'as' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                ),
                array(
                    'select' => 't1.module',
                    'display' => 'module',
                    'type' => 'module_name',
                    'flags' => FLAG_SEARCH | FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('' => $this->str('all')) + $this->getSiteModules(),
                    'filter_str' => false,
                ),
                array(
                    'select' => 'IFNULL(t2.value,t1.value)',
                    'as' => 'value',
                    'display' => 'value',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'IF(t2.value IS NULL,1,0)',
                    'as' => 'is_default',
                    'display' => 'is_default',
                    'type' => 'visible',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'IFNULL(t2.def,t1.def)',
                    'display' => 'def',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'IFNULL(t2.description,t1.description)',
                    'display' => 'description',
                    'flags' => FLAG_SEARCH,
                ),
            ),
            'from' => $this->table . ' AS t1 LEFT JOIN ' . $this->table . ' AS t2 ON (t1.module=t2.module AND t1.name=t2.name AND t2.root_id=' . domainRootID() . ')',
            'where' => (domainRootId() > 0) ? 't1.root_id=' . getMainRootID() : '',
            'orderby' => 't1.module,t1.name',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);

        return $this->Parse($data, LIST_TEMPLATE);
    }

    function table_get_module_name(&$value, &$column, &$row) {
        global $cfg, $intlang;

        if ($value == 'site') {
            $name = 'Ядро сайта';
        } elseif ($value == 'meta') {
            $name = 'Мета-теги';
        } elseif (isset($cfg['function_modules'][domainRootID()][$value])) {
            $name = $cfg['function_modules'][domainRootID()][$value]['name'][$intlang];
        } else {
            $name = '';
            foreach($cfg['function_modules'] as $modules) {
                if (isset($modules[$value])) {
                    $name = $modules[$value]['name'][$intlang];
                    break;
                }
            }
            if (!$name)
                $name = $value;
        }

        return $name;
    }

    function getSiteModules() {
        global $cfg, $intlang;

        if (isset($cfg['function_modules'][domainRootID()]))
            $function_modules = $cfg['function_modules'][domainRootID()];
        else {
            $function_modules = array();
            foreach($cfg['function_modules'] as $modules) {
                foreach ($modules as $name=>$module) {
                    if (!isset($function_modules[$name]))
                       $function_modules[$name] = $module;
                }
            }
        }

        $filter_modules = array('site' => 'Ядро сайта', 'meta' => 'Мета-теги');
        foreach ($function_modules AS $key => $val) {
            $filter_modules[$key] = $val['name'][$intlang];
        }
        return $filter_modules;
    }

}

$GLOBALS['strings'] = & Registry::get('TStrings');