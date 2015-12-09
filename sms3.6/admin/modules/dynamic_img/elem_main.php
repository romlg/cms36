<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $elem_name = "elem_main";
    var $elem_table = "dynamic_img";
    var $elem_type = "single";
    var $elem_str = array(
        'name' => array('Название', 'Title',),
        'priority' => array('Порядок', 'priority',),
        'image' => array('Изображение', 'image',),
        'alt' => array('Альтернативный текст', 'alt',),
        'link' => array('Название кнопки', 'link',),
        'link_url' => array('Ссылка кнопки', 'link',),
        'visible' => array('Отображать', 'visible',),
        'description' => array('Описание', 'description',),
        'root_ids' => array('На каких сайтах показывать', 'root ids',),
    );

    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
            ),
            'image' => array(
                'type' => 'input_image',
            ),
            'link' => array(
                'type' => 'text',
            ),
            'link_url' => array(
                'type' => 'text',
            ),
            'alt' => array(
                'type' => 'text',
            ),
            'root_ids' => array(
                'type' => 'multi_select',
                'func' => 'getRoots',
            ),
            'priority' => array(
                'type' => 'text',
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
            'description' => array(
                'type' => 'fck',
            ),
        ),
        'folder' => 'dynamic_img',
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script = "";

//	var $sql = true;

    function ElemInit() {
        global $site_domains;
        $count = 0;
        foreach ($site_domains as $site) {
            foreach ($site['langs'] as $lang) {
                $count++;
            }
        }
        if ($count < 2) {
            $this->elem_fields['columns']['root_ids'] = array(
                'type' => 'hidden',
                'value' => domainRootId()
            );
        }
        return parent::ElemInit();
    }

    function getRoots() {
        $ret = array();
        if ($this->elem_fields['columns']['root_ids'] != 'hidden') {
            global $site_domains;
            $ret = array();
            foreach ($site_domains as $site) {
                foreach ($site['langs'] as $lang) {
                    $ret[$lang['root_id']] = $site['name'] . ' (' . $lang['descr'] . ')';
                }
            }
        }
        return $ret;
    }
}