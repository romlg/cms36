<?php

/**
 *
 * ������ "������������ �����" (������� ������� �����)
 *
 * @package    admin/modules
 */
class TSite_usersBase extends TTable {

	var $name = 'site_users';
	var $table = 'auth_users';
    var $elements = array('elem_roles');
    var $columns_default = ""; // ���� ��� ����������� � ������������ elem-�, ���� �� ������ ����
    var $selector = false;

	########################

	function TSite_usersBase() {
		global $str, $actions;

		TTable::TTable();

        $actions[$this->name] = array(
            'edit' => &$actions['table']['edit'],
            'create' => &$actions['table']['create'],
            'delete' => &$actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
            'save' => array(
                'title' => array(
                    'ru' => '���������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => '�����',
                    'en' => 'Back',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "����� ������������";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('������������', 'Ste ue',),
            'title_editform' => array("������������: " . $temp, 'Law: ' . $temp,),

			'login'	=> array('�����', 'Login',),
			'name'	=> array('���', 'Name',),
			'email'	=> array('Email', 'Email',),

            'saved' => array(
                '����� ���� ������� ���������',
                'Data has been saved successfully',
            ),
		));

        $tables = sql_getRows("SHOW tables LIKE 'acl_%'");
        if (!$tables) $this->elements = array();
	}

	########################

	function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // ������ �������
        require_once (core('list_table'));
        $data['table'] = list_table(array(
			'columns'	=> array(
                array(
                    'select'	=> 'u.id',
                    'display'	=> 'id',
                    'type'		=> 'checkbox',
                    'width'		=> '1px',
                ),
                array(
                    'select'	=> 'u.login',
                    'display'	=> 'login',
                    'flags'		=> FLAG_SORT | FLAG_SEARCH,
                ),
                array(
                    'select'	=> 'u.name',
                    'display'	=> 'name',
                    'flags'		=> FLAG_SORT | FLAG_SEARCH,
                ),
                array(
                    'select'	=> 'u.email',
                    'display'	=> 'email',
                    'flags'		=> FLAG_SORT | FLAG_SEARCH,
                ),
			),
			'from'		=> $this->table." as u",
            'where' => '',
            'orderby' => 'login',
            // ������ ���������� ���
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
		), $this);

		// ������ � ������ ������� ������� �� ��������
		session_start();
		$_SESSION['client_selection'] = $GLOBALS['where'];
		session_write_close();

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
	}
}

$GLOBALS['site_users'] = & Registry::get('TSite_usersBase');

?>