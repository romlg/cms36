<?php

global $settings;

$settings = array(
    'infoblocks_positions' => array(
        'left' => 'В левой колонке',
        'right' => 'В правой колонке',
        'home' => 'В центре главной страницы',
        'bottom' => 'В футере (счетчики посещаемости)'
    ),
    'subscribe_categories' => array(
          1 => array(
          	'title' => 'Новости',
          	'sub'	=> array(
          		array('title' => 'Новости', 'type' => 'news'),
          	),
          ),
    ),
    'forms_searching_tables' => array(
        'tree' => array(
            'title' => 'Тексты разделов',
            'from' => 'elem_text AS et JOIN tree AS t ON et.pid=t.id',
            'where' => '1',
            'key' => 'et.pid',
            'dir' => 't.dir',
            'name' => 't.name',
            'search_fields' => array('et.text'),
            'admin_href' => '/admin/editor.php?page=tree&id={$key}#tab2',
            'site_href' => '{$dir}',
        ),
        'publications' => array(
            'title' => 'Публикации',
            'from' => 'publications',
            'name' => 'name',
            'search_fields' => array('text','notice'),
            'admin_href' => '/admin/editor.php?page=publications&id={$key}',
        ),
        'infoblocks' => array(
            'title' => 'Инфоблоки',
            'from' => 'infoblocks',
            'name' => 'name',
            'search_fields' => array('header_text','footer_text'),
            'admin_href' => '/admin/editor.php?page=infoblocks&id={$key}',
        ),
        'strings' => array(
            'title' => 'Параметры сайта',
            'from' => 'strings',
            'name' => 'name',
            'search_fields' => array('value'),
            'admin_href' => '/admin/editor.php?page=strings&id={$key}',
        ),
    ),
    'changefreq' => array(
        ''          => '-',
        'always'    => 'всегда',
        'hourly'    => 'раз в час',
        'daily'     => 'раз в день',
        'weekly'    => 'раз в неделю',
        'monthly'   => 'раз в месяц',
        'yearly'    => 'раз в год',
        'never'     => 'никогда',
    ),
);