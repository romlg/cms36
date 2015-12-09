<?php

class THelp extends TTable
{

    var $name = 'help';
    var $table = 'help';
    var $selector = false;

    ########################

    function THelp() {
        global $actions, $str;

        TTable::TTable();

        $this->window_icons['showhelp'] = array(
            'close' => &$GLOBALS['window_icons']['close'],
        );

        $actions[$this->name] = array(
            'edit' => &$actions['table']['edit'],
            'create' => &$actions['table']['create'],
            'delete' => array(
                '�������',
                'Delete',
                'link' => 'cnt.deleteItems(\'' . $this->name . '\')',
                'img' => 'icon.delete.gif',
                'display' => 'none',
            ),
        );

        $actions[$this->name . '.editform'] = array(
            'apply' => array(
                'title' => array(
                    'ru' => '���������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'apply\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'save_close' => array(
                'title' => array(
                    'ru' => '��������� � �������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'save\'; document.forms[\'editform\'].submit(); return false;',
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

        $actions[$this->name . '.showhelp'] = array(
            'close' => &$actions['table']['close'],
            'print' => array(
                '�����������',
                'Print',
                'link' => 'cnt.print()',
                'img' => 'icon.print.gif',
                'display' => 'block',
            ),
        );

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                '������',
                'Help',
            ),
            'add' => array(
                '���������� ������ ������� ������',
                'Add new help page',
            ),
            'edit' => array(
                '�������������� ������� ������',
                'Edit help page',
            ),
            'empty' => array(
                '������ �� �������',
                'Empty result set',
            ),
            'saved' => array(
                '������ ������� ���������',
                'The information has been saved successfully',
            ),
            'name' => array(
                '�������� �������� ������',
                'Help page title',
            ),
            'module' => array(
                '������',
                'Module',
            ),
            'text' => array(
                '����� ������:',
                'Help text:',
            ),
            'nohelp' => array(
                '��� ������ �� ������� ������',
                'There is no help on this module',
            ),
        ));

    }

    ########################

    function Show() {

        if (!empty($_POST)) {
            $action = get('actions', '', 'p');
            if ($action) {
                if ($this->Allow($action)) {
                    return $this->$action();
                }
                else {
                    return $this->alert_method_not_allowed();
                }
            }
        }

        $this->AddStrings($data);

        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'module',
                    'display' => 'module',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SORT,
                ),
            ),
            'orderby' => 'module, name',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID = cb.value',
        ), $this);

        return $this->Parse($data, LIST_TEMPLATE);
    }

    function ShowHelp() {
        $module = get('module', '', 'g');

        $url = "http://help.rusoft.ru/getmanual.php?engine=3.6&module=" . $module . "&site=" . $_SERVER['HTTP_HOST'];

        $html = file_get_contents($url);
        if (!strpos($html, '������������ �� ������ ������� �� �������')) { // �������� ������� ������������ �� �������
            ob_end_clean();
            ob_end_clean();
            Header('Content-Length: 0');
            Header('Location: ' . $url);
        }

        $row = sql_getRow("SELECT * FROM " . $this->table . " WHERE module='" . mysql_real_escape_string($module) . "'");

        if (empty($row)) {
            die('������������ �� ����� ������ �����������');
        }

        $this->AddStrings($row);
        return $this->Parse($row, $this->name . '.showhelp.tmpl');
    }

}

$GLOBALS['help'] = & Registry::get('THelp');