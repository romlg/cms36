Настройка модуля в common.cfg.php:

    'search' => array(
        'name' => array(
            'Поиск по сайту',
            'Search',
        ),
        'derive_from' => 'module',
        'elements' => array('elem_meta', 'elem_module'),
        'blocks' => array(
            'other' => array(
                'class' => 'TSearch',
                'method' => 'search',
                'params' => array( // параметры поиска
                    'tables' => array( // в каких таблицах искать
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
                            // класс и метод, возвращающие путь к объекту по его ID
                            'href' => array('class' => 'TTreeUtils', 'method' => 'getPath'),
                        ),
                        array(
                            'name' => 'publications',
                            'search_fields' => array('name', 'text'),
                            'select_fields' => array('id', 'pid', 'name', 'text'),
                            'where' => 'visible>0',
                            // класс и метод, возвращающие путь к объекту по его ID
                            'href' => array('class' => 'TPublications', 'method' => 'getPathToPublication'),
                        ),
                        array(
                            'name' => 'persons',
                            // _ в конце означает, что для разных языковых версий свое поле
                            'search_fields' => array('name_', 'text_'),
                            'select_fields' => array('id', 'null', 'name_', 'text_'),
                            'where' => '1',
                            // класс и метод, возвращающие путь к объекту по его ID
                            'href' => array('class' => 'TPersons', 'method' => 'getPathToPerson'),
                        ),
                        array(
                            'name' => 'projects',
                            // _ в конце означает, что для разных языковых версий свое поле
                            'search_fields' => array('name_', 'text_'),
                            'select_fields' => array('id', 'null', 'name_', 'text_'),
                            'where' => '1',
                            // класс и метод, возвращающие путь к объекту по его ID
                            'href' => array('class' => 'TProjects', 'method' => 'getPathToProject'),
                        ),
                    ),
                ),
                'tmpls' => array('search_result'),
            ),
        ),
    ),
