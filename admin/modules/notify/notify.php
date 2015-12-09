<?php

class TNotify extends TTable
{

    var $name = 'notify';
    var $table = 'notify_events';
    var $selector = false;

    function TNotify() {
        global $actions, $str;
        TTable::TTable();
        $actions[$this->name] = array();
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

        if (!empty($_GET['id'])) $temp = sql_getValue("SELECT `description` FROM `" . $this->table . "` WHERE `id`=" . (int)$_GET['id']);
        else $temp = 'новое уведомление';

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
                'title' => array('Уведомления', 'Notifications'),
                'title_editform' => array('Уведомление: ' . $temp, 'Notification: ' . $temp),
                'event' => array('Событие', 'Event'),
                'description' => array('Описание', 'Description'),
                'template' => array('Шаблон', 'Template'),
                'mails' => array('Адреса для отправки', 'Emails'),
                'saved' => array('Даные были успешно сохранены', 'Data has been saved successfully'),
            )
        );
    }

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }
        require_once(core('list_table'));
        $ret['thisname'] = $this->name . '.editform';

        $ret['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                ),
                array(
                    'select' => 'event',
                    'display' => 'event',
                    'flags' => FLAG_SORT | FLAG_SEARCH,
                ),
                array(
                    'select' => 'description',
                    'display' => 'description',
                    'flags' => FLAG_SEARCH,
                ),
            ),
            'from' => $this->table,
            'orderby' => '`event` ASC',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'click' => 'ID=cb.value',
            'dblclick' => 'editItem(id)',
        ), $this);
        $this->AddStrings($ret);
        return $this->Parse($ret, LIST_TEMPLATE);
    }
}

$GLOBALS['notify'] =& Registry::get('TNotify');