<?php

class TFile extends TTable
{

    var $name = 'file';
    var $table = 'elem_file';
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои

    ########################

    function TFile() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
            'save' => array(
                'title' => array(
                    'ru' => 'Сохранить',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].submit(); return false;',
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

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новый файл";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Файлы', 'File',),
            'title_editform' => array("Файл: " . $temp, 'File: ' . $temp,),

            'name' => array('Заголовок', 'Title',),
            'fname' => array('Имя файла', 'Filename',),
            'visible' => array('Показывать', 'Visible',),

            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
        ));

        // Здесь описываются поля по умолчанию для отображения списка
        $this->columns_default = array(
            array(
                'select' => 'id',
                'display' => 'ids',
                'type' => 'checkbox',
                'width' => '1px',
            ),
            array(
                'select' => 'name',
                'display' => 'name',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'fname',
                'display' => 'fname',
            ),
            array(
                'select' => 'priority',
                'display' => 'priority',
                'type' => 'edit_priority'
            ),
        );

    }

    ########################

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // строим таблицу
        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'fname',
                    'display' => 'fname',
                ),
                array(
                    'select' => 'visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT,
                ),
            ),
            'from' => $this->table,
            'orderby' => 'priority ASC',
            // всегда передается это
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

}

$GLOBALS['file'] = & Registry::get('TFile');

?>