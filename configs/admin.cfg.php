<?php

define ('DEV_MODE', true);
//define ('DEV_MODE',     false);

define('SQL_LOG', false);

$log_change_actions = array('UPDATE', 'INSERT', 'REPLACE', 'DELETE', 'TRANSACTION');

# menu sections
$sections = array(
    'site' => array(
        '������� ����',
        'Site Tasks',
        'cut' => false,
        'modules' => array(
            'tree' => array('������ ��������', 'Site Tree', 'img' => 'icon.tree.gif'),
            'guestbook' => array('������ ���������', 'Guests'),
            'forms' => array('�����', 'Forms'),
            'forms_sent' => array('������ � �����', 'Emails from site'),
            'infoblocks' => array('���������', 'InfoBlocks'),
            'publications' => array('����������', 'Publications'),
            'publications_comments' => array('����������� � �����������', 'Publications comments'),
            'fmr' => array('����� � �����������', 'File Manager', 'img' => 'icon.files.png'),
            'help_generator' => array('������������', 'Help', 'img' => 'icon.gen.png'),
            'news' => array('�������', 'News'),
        ),
    ),
    'admin' => array(
        '�����������������',
        'Administration',
        'cut' => true,
        'modules' => array(
            'site_users' => array('������������ �����', 'Site users'),
            'strings' => array('��������� �����', 'Settings', 'img' => 'icon.conct.png'),
            'mysqldump' => array('��������� ����� �����', 'Dump', 'img' => 'icon.bd_arch.png'),
            'mysqlcon' => array('SQL-�������', 'mySQL console'),
            'admins' => array('��������������', 'Administrators', 'img' => 'icon.users.png'),
            'admin_groups' => array('������ �������������', 'Administrator group', 'img' => 'icon.users_role.png'),
        ),
    ),
);

$hidden_sections = array(
    'hiddens' => array(
        '������ ������',
        'Other modules',
        'cut' => false,
        'modules' => array(
            'gallery' => array('�������', 'Gallery'),
            'file' => array('�����', 'Files'),
        ),
    ),
);

if (DEV_MODE) {
    $sections['admin']['modules']['help'] = array('������', 'Help', 'img' => 'icon.help.png');
}

//Resize
//������: ����-���, array-��������
$resamle_options = array('150*150' => array('150', '150'), '600*400' => array('600', '400'));

// ������ ��� �������� ����� (������� � �������)
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