<?php

global $settings;

$settings = array(
    'infoblocks_positions' => array(
        'left' => '� ����� �������',
        'right' => '� ������ �������',
        'home' => '� ������ ������� ��������',
        'bottom' => '� ������ (�������� ������������)'
    ),
    'subscribe_categories' => array(
          1 => array(
          	'title' => '�������',
          	'sub'	=> array(
          		array('title' => '�������', 'type' => 'news'),
          	),
          ),
    ),
    'forms_searching_tables' => array(
        'tree' => array(
            'title' => '������ ��������',
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
            'title' => '����������',
            'from' => 'publications',
            'name' => 'name',
            'search_fields' => array('text','notice'),
            'admin_href' => '/admin/editor.php?page=publications&id={$key}',
        ),
        'infoblocks' => array(
            'title' => '���������',
            'from' => 'infoblocks',
            'name' => 'name',
            'search_fields' => array('header_text','footer_text'),
            'admin_href' => '/admin/editor.php?page=infoblocks&id={$key}',
        ),
        'strings' => array(
            'title' => '��������� �����',
            'from' => 'strings',
            'name' => 'name',
            'search_fields' => array('value'),
            'admin_href' => '/admin/editor.php?page=strings&id={$key}',
        ),
    ),
    'changefreq' => array(
        ''          => '-',
        'always'    => '������',
        'hourly'    => '��� � ���',
        'daily'     => '��� � ����',
        'weekly'    => '��� � ������',
        'monthly'   => '��� � �����',
        'yearly'    => '��� � ���',
        'never'     => '�������',
    ),
);