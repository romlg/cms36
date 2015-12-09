<?php

define('SQL_LOG', false);

define ('DEV_MODE', true);
//define ('DEV_MODE', false);

define ('ENABLE_JUMP', true); // ��������� ������� � ������ ��������� ��������, ���� ������������ �������� �� �������� ������
$jump_params = array(
    'types' => array('text'), // � ����� ����� ������� �������� �������
    'elems' => array( // � ���� �������� � ����� ��������� ������� ��������
        'elem_text' => 'text',
    ),
);

define ('CACHE_BLOCKS', false);

# ��������� ������
$langs = array('ru');

$GLOBALS['modules'] = array(
    'site',
    '404',
    'meta',
    'stat',
    'banners',
    'print',
    'map',
    'guestbook',
    'content',
    'forms',
    'publications',
    'registration',
);

//���� ����������� ��������� � �����
$notify_plugins = array(
    'email',
);

$object_urls = array(
    'print' => array('virtual' => true, 'module' => 'print', 'method' => 'show'),
    'p' => array('global' => true),

    'message' => array('global' => true),
);
