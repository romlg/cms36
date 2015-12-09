<?php

class TGuestbook extends TTable {

	var $name = 'guestbook';
	var $table = 'guestbook';
   // var $selector = true;
    var $columns_default = "";
	########################

	function TGuestbook() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
            'create' => &$actions['table']['create'],
			'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],

		);
        $actions[$this->name . '.editform'] = array(
            'save' => array(
                'title' => array(
                    'ru' => '���������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].submit(); return false;',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => '������',
                    'en' => 'Cancel',
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
            $temp = "��������";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'name' => array('��������', 'User',),
            'date' => array('����', 'Date',),
            'message' => array('�����', 'Message',),
            'visible' => array('����������', 'Visible',),
            'visiblemain' => array('���������� �� �������', 'Visible main',),
            'saved' => array(
                '����� ���� ������� ���������',
                'Data has been saved successfully',
            ),
        ));}
	########################    

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // ������ �������
        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'm.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'm.name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                    'type' => 'text',
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(m.date)',
                    'display' => 'date',
                    'flags' => FLAG_FILTER | FLAG_SORT,
                    'type' => 'date',
                    'filter_type' => 'date',
                    'filter_display' => '����������� �� ����'
                ),
                array(
                    'select' => 'm.message',
                    'display' => 'message',
                    'size' => '50',
                ),

                array(
                    'select' => 'm.visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags'=> FLAG_SORT,
                ),
                array(
                    'select' => 'm.visiblemain',
                    'display' => 'visiblemain',
                    'type' => 'visible',
                    'flags'=> FLAG_SORT,
                ),
            ),
            'from' => $this->table . " AS m",
            'orderby' => 'm.date DESC',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);
        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

}


$GLOBALS['guestbook'] = & Registry::get('TGuestbook');

?>