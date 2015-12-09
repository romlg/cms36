<?php

define ('USE_ED_VERSION', '1.0.2');
class TReklama_Obj extends TTable
{

    var $name = 'stat/reklama_obj';
    var $table = 'stat_reklama';

    ########################

    function TReklama_Obj() {
        global $str, $actions;

        TTable::TTable();

        $actions[str_replace('/', '__', $this->name)] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => array(
                'Удалить',
                'Delete',
                'link' => "cnt.deleteItems('" . $this->name . "',null,1)",
                'img' => 'icon.delete.gif',
                'display' => 'none',
            ),
        );

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Рекламные кампании', 'Advertising campaigns'),
            'name' => array('Название', 'Name'),
            'budget' => array('Бюджет', 'Budget'),
            'start_date' => array('Дата начала', 'Start date'),
            'end_date' => array('Дата окончания', 'End date'),
            'root_id' => array('Сайт', 'Site'),
            'saved' => array(
                'Рекламные кампании были сохранены',
                'The advertising campaigns list has been saved'
            ),
        ));
    }

    ############################################

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }
        require_once(core('ajax_table'));
        $ret['thisname'] = $this->name;

        global $site_domains;
        $sites = array();
        foreach ($site_domains as $key => $val) {
            foreach ($val['langs'] as $l) {
                $sites[$l['root_id']] = $val['name'] . ' (' . $l['descr'] . ')';
            }
        }

        $ret['params'] = ajax_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SORT | FLAG_SEARCH,
                ),
                array(
                    'select' => 'budget',
                    'display' => 'budget',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'start_date',
                    'display' => 'start_date',
                    'type' => 'text',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'end_date',
                    'display' => 'end_date',
                    'type' => 'text',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'root_id',
                    'type' => 'text',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_display' => 'root_id',
                    'filter_value' => array('' => '-- все --') + $sites,
                ),
            ),
            'from' => $this->table,
            'where' => '',
            'orderby' => 'id',
            'params' => array('page' => "stat/reklama_obj", 'do' => 'show'),
            'click' => 'ID=cb.value',
            'dblclick' => 'editItem(id)',
//			'_sql' => true,
        ), $this);
        $ret['thisname2'] = str_replace('/', '', $this->name);
        return Parse($ret, $this->name . '/reklama_obj.tmpl');
    }

}

$GLOBALS['stat__reklama_obj'] = & Registry::get('TReklama_Obj');
?>