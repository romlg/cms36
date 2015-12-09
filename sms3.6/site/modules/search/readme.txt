��������� ������ � common.cfg.php:

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
                            'href' => array('class' => 'TPublications', 'method' => 'getPathToPublication'),
                        ),
                        array(
                            'name' => 'persons',
                            // _ � ����� ��������, ��� ��� ������ �������� ������ ���� ����
                            'search_fields' => array('name_', 'text_'),
                            'select_fields' => array('id', 'null', 'name_', 'text_'),
                            'where' => '1',
                            // ����� � �����, ������������ ���� � ������� �� ��� ID
                            'href' => array('class' => 'TPersons', 'method' => 'getPathToPerson'),
                        ),
                        array(
                            'name' => 'projects',
                            // _ � ����� ��������, ��� ��� ������ �������� ������ ���� ����
                            'search_fields' => array('name_', 'text_'),
                            'select_fields' => array('id', 'null', 'name_', 'text_'),
                            'where' => '1',
                            // ����� � �����, ������������ ���� � ������� �� ��� ID
                            'href' => array('class' => 'TProjects', 'method' => 'getPathToProject'),
                        ),
                    ),
                ),
                'tmpls' => array('search_result'),
            ),
        ),
    ),
