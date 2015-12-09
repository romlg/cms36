<?php
$cfg['types'] = array(
    'home' => array(
        '������� ��������',
        'Home page',
        'icon' => 'icon.domik.gif',
        'elements' => array('elem_meta', 'elem_text'),
        'nested' => array('text', 'module'),
        'template' => 'home',
        'blocks' => array(
            'content' => array(
                'method' => 'show_content',
                'parse' => false,
            ),
            'user' => array(
                'class' => 'TAuth',
                'method' => 'checkUser',
                'parse' => false
            ),
            'loginform' => array(
                'class' => 'TAuth',
                'method' => 'loginform'
            ),

            'forms_handler' => array(
                'class' => 'TForms',
                'method' => 'formsQueryHandler',
                'parse' => false,
            ),
            'guestbook' => array(
                'class' => 'TGuestbook',
                'method' => 'guestbook2',
                'tmpls' => array('guestbook2'),
            ),
            'other' => array( // �� �������, �.�. ���� ������������ �������, � ������� ����� ���������� ���� ����

            ),
            'pids' => array(
                'method' => 'show_pids',
                'parse' => false,
            ),
            'mainmenu' => array(
                'class' => 'TMenu',
                'method' => 'show_menu',
                'params' => array(
                    'start_level' => 1,
                    'levels' => 4,
                    'full' => true,
                ),
                'tmpls' => array('mainmenu'),
                'cache' => true,
                'cache_tables' => array('tree'),
            ),
            'title' => array(
                'method' => 'show_title',
                'parse' => false,
            ),
            'infoblocks_left' => array(
               'class' => 'TPublications',
               'method' => 'showInfoblocks',
               'params' => array(
                   'position' => 'left',
                   'url' => 'p'
               ),
               'tmpls' => array('infoblocks_left'),
           ),
            'infoblocks_right' => array(
                'class' => 'TPublications',
                'method' => 'showInfoblocks',
                'params' => array(
                    'position' => 'right',
                    'url' => 'p'
                ),
                'tmpls' => array('infoblocks_right'),
            ),
            'infoblocks_home' => array(
                'class' => 'TPublications',
                'method' => 'showInfoblocks',
                'params' => array(
                    'position' => 'home',
                    'url' => 'p'
                ),
                'tmpls' => array('infoblocks_home'),
            ),
            'print_url' => array(
                'class' => 'TPrint',
                'method' => 'getPrintUrl',
                'parse' => false,
            ),
            'currenturl' => array(
                'method' => 'show_pids',
                'tmpls' => array('currenturl'),
            ),
            'meta' => array(
                'class' => 'TMeta',
                'method' => 'show_meta',
            ),
        ),
    ),
    'text' => array(
        '��������� ������',
        'Text page',
        'derive_from' => 'home',

        'icon' => 'icon.folder.png',
        'elements' => array('elem_meta', 'elem_text', 'elem_gallery', 'elem_form', 'elem_file'),
        'nested' => array('text', 'module'),
        'template' => 'home',
        'blocks' => array(
            'infoblocks_home' => false,
            'title' => array(
                'method' => 'show_title',
                'parse'=> false,
            ),
            'submenu' => array(
                'class' => 'TMenu',
                'method' => 'show_menu',
                'params' => array(
                    'start_level' => 2,
                    'levels' => 3,
                    'full' => true,
                ),
                'tmpls' => array('submenu'),
                'cache' => true,
                'cache_tables' => array('tree'),
            ),
            'gen_form' => array(
                'class' => 'TForm_generator',
                'method' => 'show',
                'parse' => false,
            ),
            'files' => array(
                'class' => 'TContent',
                'method' => 'getFiles',
            ),
            'gallery' => array(
                'class' => 'TContent',
                'method' => 'showGallery',
            ),
            'publications' => array(
                'class' => 'TPublications',
                'method' => 'showPublications',
                'params' => array(
                    'url' => 'p', // ����� ����
                ),
                'tmpls' => array('publications'),
            ),
        ),
    ),
    'module' => array(
        '�������������� ������',
        'Function module',
        'icon' => 'icon.module.png',
        'elements' => array('elem_meta', 'elem_module', 'elem_text', 'elem_form'),
        'nested' => array('module', 'text'),
        'derive_from' => 'text',
        'blocks' => array(
        ),
    ),
);

//������ ���������. ��������� ������ ���������
//���� ���� - ��������� ������� ��������
//[0] - �������� ��-������
//[1] - �������� ��-���������
$cfg['elements'] = array(
    'elem_site' => array('����', 'Site'),
    'elem_url' => array('������', 'Url'),
    'elem_document' => array('���������', 'Documents'),
    'elem_gallery' => array('�������', 'Gallery'),
    'elem_product' => array('����������', 'Parts'),
    'elem_tab' => array('�������', 'Tabs'),
    'elem_file' => array('�����', 'Files'),
    'elem_features' => array('���������', 'Features'),
    'elem_params' => array('���������', 'Features'),
    'elem_meta' => array('����-����', 'Meta tags'),
    'elem_description' => array('��������', 'Description'),
    'elem_text' => array('�����', 'Text'),
    'elem_image' => array('��������', 'Images'),
    'elem_module' => array('������', 'Module'),
    'elem_variants' => array('�������� �������', 'Variants'),
    'elem_form' => array('��������� ����', 'Form'),
    'elem_rule' => array('������� ������', 'Rules'),
    'elem_usages' => array('������������� �����', 'Form usages'),
);


$cfg['function_modules'] = array(
    '404' => array(
        'name' => array(
            '�������� ������ 404',
            'Error page 404',
        ),
        'elements' => array('elem_meta', 'elem_text', 'elem_module'),
        'derive_from' => 'module',
        'blocks' => array(
            'other' => array(
                'method' => 'show_404',
                'tmpls' => array('404'),
            ),
        ),
    ),
    'sitemap' => array(
        'name' => array(
            '����� �����',
            'Sitemap'
        ),
        'derive_from' => 'module',
        'blocks' => array(
            'other' => array(
                'class' => 'TMenu',
                'method' => 'show_menu',
                'tmpls' => array('map'),
                'params' => array(
                    'start_level' => 1,
                    'levels' => 4,
                    'full' => true,
                ),
                'cache' => true,
                'cache_tables' => array('tree'),
            ),
        ),
    ),
    'search' => array(
        'name' => array(
            '����� �� �����',
            'Search',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_meta', 'elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TSearch',
                'method' => 'search',
                'params' => array( // ��������� ������
                    'tables' => array( // � ����� �������� ������
                        array(
                            'name' => 'tree',
                            'join' => array(
                                array(
                                    'name' => 'elem_text',
                                    'on' => 'elem_text.pid=tree.id'
                                ),
                            ),
                            'search_fields' => array('tree.name', 'elem_text.text'),
                            'select_fields' => array('tree.id', 'tree.pid', 'tree.name', 'elem_text.text'),
                            'where' => 'tree.visible>0',
                            // ����� � �����, ������������ ���� � ������� �� ��� ID
                            'href' => array('class' => 'TTreeUtils', 'method' => 'getPath'),
                        ),
                        array(
                            'name' => 'publications',
                            'search_fields' => array('name', 'text'),
                            'select_fields' => array('id', 'pid', 'name', 'text'),
                            'where' => 'visible>0',
                            // ����� � �����, ������������ ���� � ������� �� ��� ID
                            'href' => array('class' => 'TPublications', 'method' => 'getMainPathToPublication'),
                        ),
                    ),
                ),
                'tmpls' => array('search_result'),
            ),
        ),
    ),
    'print' => array(
        'class' => 'TPrint',
        'name' => array(
            '������ ��� ������',
            'Print',
        ),
        'template' => 'print',
        'tpl_var' => 'print',
        'blocks' => array(
            'other' => array(
                'class' => 'TPrint',
                'method' => 'show',
            ),
        ),
    ),
    'publications' => array(
        'name' => array(
            '���� ����������',
            'One publication',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'publications' => false,
            'other' => array(
                'class' => 'TPublications',
                'method' => 'publication',
                'params' => array(
                    'url' => 'p',
                ),
                'tmpls' => array('publication'),
            ),
        ),
    ),
    'guestbook' => array (
        'name' => array(
            '�������� �����',
            'Guest book',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'publications' => false,
            'other' => array(
                'class' => 'TGuestbook',
                'method' => 'guestbook',
                'tmpls' => array('guestbook'),
            ),
        ),
    ),
    'someModuleWithForm' => array(
        'name' => array(
            '������ ������ � ������',
            'someModuleWithForm',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TContent',
                'method' => 'showSomeForm',
                'tmpls' => array('someModuleWithForm'),
            ),
        ),
    ),
    'registration' => array(
        'name' => array(
            '�����������',
            'Registration',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TRegistration',
                'method' => 'showRegForm',
                'tmpls' => array('registration'),
            ),
        ),
    ),
    'auth' => array(
        'name' => array(
            '�����������',
            'Auth',
        ),
    ),
    'cabinet' => array(
        'name' => array(
            '������ �������',
            'Cabinet',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TCabinet',
                'method' => 'router',
                'tmpls' => array('cabinet'),
            ),
            'cabinet_menu' => array(
                'class' => 'TMenu',
                'method' => 'show_menu',
                'params' => array(
                    'start_uri' => 'cabinet',
                    'levels' => 1,
                    'full' => true,
                    'types' => array('text', 'module'),
                ),
                'tmpls' => array('cabinet_menu'),
                //'cache' => true,
                //'cache_tables' => array('tree'),
            ),
        ),
    ),
    'message' => array(
        'name' => array(
            '���������',
            'Message',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TGuestbook',
                'method' => 'showOne',
            'tmpls' => array('message'),
            ),
        ),
    ),
);

include_once "phpThumb/image.class.php";
require_once PATH_COMMON . "/models/User.php";


// ----------------------------------------------------------------------------------------
//  Hybrid_Auth - ���������� ��� ����������� ����� ���. ����
// ----------------------------------------------------------------------------------------
$GLOBALS['hybridauth_config'] = array(
    "base_url" => "http://" . $_SERVER['HTTP_HOST'] . "/hybridauth/",
    "providers" => array(
        "OpenID" => array(
            "enabled" => true
        ),
        "Google" => array(
            "enabled" => true,
            "keys" => array("id" => "877812920226.apps.googleusercontent.com", "secret" => "4YaW5LYEQKluM_wf3CHFyHOW"),
            "scope" => "https://www.googleapis.com/auth/userinfo.profile " . "https://www.googleapis.com/auth/userinfo.email", // optional
            //"access_type" => "offline", // optional
            "approval_prompt" => "force" // optional
        ),
        "Twitter" => array(
            "enabled" => true,
            "keys" => array("key" => "j7RnOCRjvJw8R2Fqh8PMEA", "secret" => "l9bXw8fMuYE1rlJQN67eHxXn2c7hvRD63CDq4V9Ks")
        ),
        "Vkontakte" => array(
            "enabled" => true,
            //"keys" => array("id" => "3088888", "secret" => "GvEg4myI2OVaVFTHgZB4"),
            "keys" => array("id" => "3088965", "secret" => "daHMBMecwDHs2kRYZgrO"),
        ),
    ),
    // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
    "debug_mode" => true,
    "debug_file" => "./logs/hybridauth.log",
);