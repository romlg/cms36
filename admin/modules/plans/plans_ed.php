<?php

class TPlans_Ed extends BaseEd {

	var $name;
	var $selector = true;
	var $tabs = array();
	var $actions = array();
	var $table = 'obj_elem_plans_items';

	################

	function TPlans_Ed() {
		global $str;

		TTable::TTable();

		$this->actions = array(
			'add' => array(
				'title' => array(
					'ru' => '��������',
					'en' => 'Add new',
				),
				'onclick' => 'cnt.editElem(0)',
				'img' => 'icon.create.gif',
				'display' => 'none',
				'show_title' => true,
			),
			'edit' => array(
				'title' => array(
					'ru' => '��������',
					'en' => 'Edit',
				),
				'onclick' => 'cnt.editElem()',
				'img' => 'icon.edit.gif',
				'display' => 'none',
				'show_title' => true,
			),
			'delete' => array(
				'title' => array(
					'ru' => '�������',
					'en' => 'Delete',
				),
				'onclick' => 'cnt.deleteElems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
				'show_title' => true,
			),
		);

        if (!empty($_GET['id'])) $temp = sql_getValue('SELECT name FROM '.$this->table.' WHERE id='.$_GET['id']);
        else $temp = '����� ������';

        $str[get_class_name($this)] = array(
			'saved'			=> array('���������� ���� ������� ���������',	'Info has been successfully saved',	),
			'basic_caption'	=> array('����������',					'Plan'),
			'basic_tab'		=> array($temp,							'Plan'),
			'title'		    => array($temp,							'Plan'),
		);
	}

	################

	// ���������� ������ ��������
	function getTabs() {
		return $this->tabs;
	}
}
Registry::set('object_editor_submodule', Registry::get('TPlans_Ed'));
?>