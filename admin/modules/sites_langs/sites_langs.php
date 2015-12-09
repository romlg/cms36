<?php

class TSites_langs extends TTable
{

    var $name = 'sites_langs';
    var $table = 'sites_langs';
    var $columns_default = ""; // ���� ��� ����������� � ������������ elem-�, ���� �� ������ ����

    ########################

    function TSites_langs() {
        global $actions;
        TTable::TTable();

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
                    'ru' => '������',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

        // ����� ����������� ���� �� ��������� ��� ����������� ������
        $this->columns_default = array(
            array(
                'select' => 'id',
                'display' => 'ids',
                'type' => 'checkbox',
                'width' => '1px',
            ),
            array(
                'select' => 'name',
                'display' => 'name',
            ),
            array(
                'select' => 'descr',
                'display' => 'descr',
            ),
            array(
                'select' => 'locale',
                'display' => 'locale',
            ),
            array(
                'select' => 'charset',
                'display' => 'charset',
            ),
            array(
                'select' => 'root_id',
                'display' => 'root_id',
            ),
        );
    }
}

$GLOBALS['sites_langs'] = & Registry::get('TSites_langs');

?>