<?php

class TNotifyLog extends TTable
{

    var $name = 'notifylog';
    var $table = 'notify_log';
    var $selector = false;

    function TNotifyLog() {
        global $actions, $str;
        TTable::TTable();
        $actions[$this->name] = array();
        $actions[$this->name . '.editform'] = array(
            'cancel' => array(
                'title' => array(
                    'ru' => 'Закрыть',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );
        if (!empty($_GET['id'])) $temp = sql_getValue("SELECT `event` FROM `" . $this->table . "` WHERE `id`=" . $_GET['id']);
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
                'title' => array('Логи уведомлений', 'Notifications log'),
                'event' => array('Событие', 'Event'),
                'date' => array('Дата', 'Date'),
                'email' => array('Адрес', 'Email'),
                'all_events' => array('Все события', 'All events'),
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
                    'select' => 'date',
                    'display' => 'date',
                ),
                array(
                    'select' => 'event',
                    'display' => 'event',
                    'flags' => FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array(0 => $str[get_class_name($this)]['all_events']) + sql_getRows("SELECT DISTINCT `event`,`event` FROM `notify_events` ORDER BY `event`", true),
                ),
                array(
                    'select' => 'email',
                    'display' => 'email',
                ),
            ),
            'from' => $this->table,
            'orderby' => '`date` DESC',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'click' => 'ID=cb.value',
            'dblclick' => 'editItem(id)',
        ), $this);
        $this->AddStrings($ret);
        return $this->Parse($ret, LIST_TEMPLATE);
    }
}

$GLOBALS['notifylog'] =& Registry::get('TNotifyLog');

?>