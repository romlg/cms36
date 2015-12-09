<?php

class TInfoblocks_rules extends TTable
{

    ############################################
    var $name = 'infoblocks_rules';
    var $table = 'infoblocks_rules';
    var $selector = false;
    var $elements = array();

    ############################################
    function TInfoblocks_rules() {
        global $actions, $str;
        TTable::TTable();

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT url FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
            $temp = 'Правило для:'.$temp;
        } else {
            $temp = 'Новое правило';
        }
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)],
            array(
                'title' => array('Правила', 'Rules',),
                'title_editform' => array($temp, $temp),
                'url' => array('URL', 'URL Rules'),
                'active' => array('Разрешить показ по этому адресу', 'Active'),
            )
        );

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
    }

}

$GLOBALS['infoblocks_rules'] = &Registry::get('TInfoblocks_rules');