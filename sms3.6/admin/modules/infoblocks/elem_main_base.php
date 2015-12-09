<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "infoblocks";
    var $elem_type = "single";

    var $elem_str = array(
        'position' => array('Место', 'Position'),
        'visible' => array('Показывать', 'Visible'),
        'name' => array('Название внутреннее (на сайте не показывается)', 'Name'),
        'priority' => array('Порядок отображения', 'Priority'),
        'root_id' => array('На каком сайте показывать', 'Show at sites'),
    );

    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
                'size' => 255,
            ),
            'position' => array(
                'type' => 'select',
                'func' => 'infoblocks_positions'
            ),
            'priority' => array(
                'type' => 'text',
                'size' => 4,
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
        ),
        'id_field' => 'id',
    );

    var $elem_req_fields = array('name');

    function ElemInit() {
        // проверим сколько сайтов, если несколько то выводим колонку
        global $site_domains;
        $current = current($site_domains);
        if (count($site_domains) > 1 || count($current['langs']) > 1) {
            $columns = array_slice($this->elem_fields['columns'], 0, 2);
            $columns += array('root_id' => array(
                'type' => 'select',
                'func' => 'getRoots',
            ));
            $columns += array_slice($this->elem_fields['columns'], 2);
            $this->elem_fields['columns'] = $columns;
        } else {
            $this->elem_fields['columns']['root_id'] = array(
                'type' => 'hidden',
                'display' => array(
                    'func' => 'getRootId'
                ),
            );
        }
        return parent::ElemInit();
    }

    function getRoots() {
        global $site_domains;
        $ret = array();
        foreach ($site_domains as $site) {
            foreach ($site['langs'] as $lang) {
                if (!allowDomainForUser($lang['root_id'])) continue;
                $ret[$lang['root_id']] = $site['name'] . ' (' . $lang['descr'] . ')';
            }
        }
        return $ret;
    }

    function getRootId() {
        return domainRootID();
    }

    function infoblocks_positions() {
        global $settings;
        return $settings['infoblocks_positions'];
    }
}