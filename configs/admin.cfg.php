<?php

define ('DEV_MODE', true);
//define ('DEV_MODE',     false);

define('SQL_LOG', false);

$log_change_actions = array('UPDATE', 'INSERT', 'REPLACE', 'DELETE', 'TRANSACTION');

# menu sections
$sections = array(
    'site' => array(
        'Главное меню',
        'Site Tasks',
        'cut' => false,
        'modules' => array(
            'tree' => array('Дерево разделов', 'Site Tree', 'img' => 'icon.tree.gif'),
            'guestbook' => array('Журнал посещений', 'Guests'),
            'forms' => array('Формы', 'Forms'),
            'forms_sent' => array('Письма с сайта', 'Emails from site'),
            'infoblocks' => array('Инфоблоки', 'InfoBlocks'),
            'publications' => array('Публикации', 'Publications'),
            'publications_comments' => array('Комментарии к публикациям', 'Publications comments'),
            'fmr' => array('Файлы и изображения', 'File Manager', 'img' => 'icon.files.png'),
            'help_generator' => array('Документация', 'Help', 'img' => 'icon.gen.png'),
            'news' => array('Новости', 'News'),
        ),
    ),
    'admin' => array(
        'Администрирование',
        'Administration',
        'cut' => true,
        'modules' => array(
            'site_users' => array('Пользователи сайта', 'Site users'),
            'strings' => array('Настройки сайта', 'Settings', 'img' => 'icon.conct.png'),
            'mysqldump' => array('Резервная копия сайта', 'Dump', 'img' => 'icon.bd_arch.png'),
            'mysqlcon' => array('SQL-консоль', 'mySQL console'),
            'admins' => array('Администраторы', 'Administrators', 'img' => 'icon.users.png'),
            'admin_groups' => array('Группы пользователей', 'Administrator group', 'img' => 'icon.users_role.png'),
        ),
    ),
);

$hidden_sections = array(
    'hiddens' => array(
        'Другие модули',
        'Other modules',
        'cut' => false,
        'modules' => array(
            'gallery' => array('Галерея', 'Gallery'),
            'file' => array('Файлы', 'Files'),
        ),
    ),
);

if (DEV_MODE) {
    $sections['admin']['modules']['help'] = array('Помощь', 'Help', 'img' => 'icon.help.png');
}

//Resize
//формат: ключ-имя, array-значения
$resamle_options = array('150*150' => array('150', '150'), '600*400' => array('600', '400'));

// Конфиг для водяного знака (галереи в админке)
$watermark_img = "../files/watermark/water_transparent_240.png";
$watermark_position = "CM"; // center middle

$GLOBALS['modules'] = array(
    'login',
    'strings',
    'file',
    'ced',
    'help',
    'loading',
    'about_blank',
    'mysqlcon',
    'mysqldump',
    'tree',
    'guestbook',
    'unknown',
    'users',
    'user_groups',
    'versions',
    'references',
    'products',
    'cabinet',
    '404',
    'search',
    'accounts',
    'form',
    'log_access',
    'log_change',
    'gallery',
    'fmr',
    'infoblocks',
    'publications',
    'news',
);