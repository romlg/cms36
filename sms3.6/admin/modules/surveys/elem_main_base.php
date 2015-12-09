<?php

/**
 *
 * Модуль опросов (главный элемент формы)
 *
 * @package    admin/modules
 */

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems {

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "surveys";
    var $elem_type = "single";
    var $elem_str = array(
        'cnt' => array('Кол-во проголосовавших', 'Votes',),
        'name' => array('Название', 'Name',),
        'author' => array('Кто проводит опрос', 'Author',),
        'export' => array('Экспорт', 'Export',),
        'closed' => array('Закрыт', 'Closed',),
        'root_id' => array('Отображать на сайте', 'Site',),
        'results' => array('Результаты', 'Results',),
        'comments' => array('Комментарии', 'Comments',),
        'date_from' => array('Начало опроса', 'Date from',),
        'date_till' => array('Окончание опроса', 'Date till',),
        'show_popup' => array('Всплывающее окно на главной', 'Show popup',),
        'description' => array('Описание', 'Description',),
        'show_results' => array('Показывать результаты на сайте', 'Show results',),
        'show_comments' => array('Показывать коментарии', 'Show comments',),
        'realtime_results' => array('Результаты в реальном времени', 'Realtime results',),
        'results_after_answer' => array('После ответа', 'After answer',),
        'results_after_close' => array('После закрытия опроса', 'After close',),
        'results_always' => array('Всегда', 'Always',),
        'results_never' => array('Никогда', 'Never',),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
            ),
            'author' => array(
                'type' => 'text',
            ),
            'date_from' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'getCurrentDate',
                ),
            ),
            'date_till' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'getCurrentDate',
                ),
            ),
            'root_id' => array(
                'type' => 'input_treeid',
            ),
            'show_results' => array(
                'type' => 'select',
                'func' => 'get_show_results',
            ),
            'description' => array(
                'type' => 'fck',
            ),
            'comments' => array(
                'type' => 'textarea',
            ),
            'closed' => array(
                'type' => 'checkbox',
            ),
            'show_comments' => array(
                'type' => 'checkbox',
            ),
            'show_popup' => array(
                'type' => 'checkbox',
            ),
            'realtime_results' => array(
                'type' => 'checkbox',
            ),
        ),
        'id_field' => 'id',
        'folder' => ''
    );
    var $elem_req_fields = array('name');
    var $script = '';

    ########################

    function ElemInit() {
        // есть ли привязку к сайту
        $is_root_id = sql_getValue("SHOW COLUMNS FROM " . $this->elem_table . " LIKE 'root_id'");
        if (!$is_root_id) unset($this->elem_fields['columns']['root_id']);
        else {
            global $site_domains;
            $count = 0;
            $root_id = 0;
            foreach ($site_domains as $site) {
                foreach ($site['langs'] as $lang) {
                    if (!allowDomainForUser($lang['root_id'])) continue;
                    $root_id = $lang['root_id'];
                    $count++;
                }
            }
            if ($count == 1) {
                $this->elem_fields['columns']['root_id']['value'] = $root_id;
            }
        }
        TElems::ElemInit();
    }

    function ElemRedactBefore($fld) {
        if (isset($fld['root_id'])) {
            $root_id = $fld['root_id'];
            while ($pid = sql_getValue("SELECT pid FROM tree WHERE id = {$root_id}")) {
                if ($pid == $root_id) break;
                else $root_id = $pid;
            }
            $fld['root_id'] = $root_id ? $root_id : 'NULL';
        }
        return parent::ElemRedactBefore($fld);
    }

    ########################

    function getCurrentDate($v) {
        return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }

    function get_show_results() {
        return array(
            'always' => $this->str('results_always'),
            'after_answer' => $this->str('results_after_answer'),
            'after_close' => $this->str('results_after_close'),
            'never' => $this->str('results_never'),
        );
    }
}

?>