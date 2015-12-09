<?php

define('SQL_LOG', false);

define ('DEV_MODE', true);
//define ('DEV_MODE', false);

define ('ENABLE_JUMP', true); // Разрешить переход к первой вложенной странице, если родительская страница не содержит текста
$jump_params = array(
    'types' => array('text'), // с каких типов страниц разрешен переход
    'elems' => array( // в этих таблицах и полях проверяем наличие контента
        'elem_text' => 'text',
    ),
);

define ('CACHE_BLOCKS', false);

# Настройка языков
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

//типы нотификации доступные с сайта
$notify_plugins = array(
    'email',
);

$object_urls = array(
    'print' => array('virtual' => true, 'module' => 'print', 'method' => 'show'),
    'p' => array('global' => true),

    'message' => array('global' => true),
);
