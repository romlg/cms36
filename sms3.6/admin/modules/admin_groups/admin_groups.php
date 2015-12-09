<?php

class TAdmin_groups extends TTable {

	var $name = 'admin_groups';
	var $table = 'admin_groups';
	var $domain_selector = false;
    var $selector = false;

	########################

	function TAdmin_groups() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'create' => &$actions['table']['create'],
			'edit' => &$actions['table']['edit'],
			'delete' => &$actions['table']['delete'],
		);

		$actions[$this->name.'.editform'] = array(
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
				'onclick' => 'window.location=\'/admin/?page='.$this->name.'\'',
				'img' => 'icon.close.gif',
				'display' => 'block',
				'show_title' => true,
			),
		);

        if (!empty($_GET['id'])){
            $temp = sql_getValue("SELECT name FROM ".$this->table." WHERE id=".$_GET['id']);
        } else {
            $temp = "����� ������";
        }

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'     => array('������ �������������', 'User Groups',),
			'title_editform'=> array("������ : ".$temp,  'Group : '.$temp,),
			'editform'  => array('������ �������������', 'User Groups',),
			'name'      => array('��������', 'Name',),
			'rights'    => array('����� �������', 'Access Rights',),
			'saved'     => array('������ ���� ������� ���������', 'The group has been saved successfully',),
			'none'      => array('���', 'No',),
			'read'      => array('��', 'Rd',),
			'edit'      => array('���', 'Ed',),
			'delete'    => array('��', 'Del',),
			'norights'  => array('��������� �������', 'The limited sections',),
			'module_title' => array('�������� ������', 'Module Title',),
		));
	}

	########################

	function Show() {
		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				if($this->Allow($action)) {
					return $this->$action();
				}
				else {
					return $this->alert_method_not_allowed();
				}
			}
		}

        require_once (core('list_table'));
		$ret['thisname'] = $this->name;
		$ret['table'] = list_table(array(
			'columns' => array(
				array(
					'select' => 'id',
					'display' => 'id',
					'type' => 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display' => 'name',
				),
				array(
					'select' => 'priority',
					'display' => 'priority',
					'type' => 'priority',
					'width' => '1',
				),
			),
			'orderby' => 'priority, name',
			'params' => array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click' => 'ID=cb.value',
			'dblclick' => 'editItem(id)',
		), $this);
		return $this->Parse($ret, LIST_TEMPLATE);
	}

	########################

	function Info() {
		return array(
			'version' => get_revision('$Revision: 1.1 $'),
			'checked' => 0,
			'disabled' => 0,
			'type' => 'checkbox',
		);
	}

	######################
}

$GLOBALS['admin_groups'] = & Registry::get('TAdmin_groups');

?>