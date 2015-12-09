<?php

class TObjHousetypesEd extends BaseEd {
	##############################################
	var $name     = 'objhousetypes';
	var $table    = 'obj_housetypes';
	var $tabs     = array();
	var $actions  = array();
	##############################################
	function TObjHousetypesEd() {
		global $str;

		TTable::TTable();
	############### ������ ������� ################
		$this->actions = array(
			'add' => array(
				'title' => array(
					'ru' => '��������',
					'en' => 'Add new',
				),
				'onclick'    => 'cnt.editElem(0)',
				'img'        => 'icon.create.gif',
				'display'    => 'none',
				'show_title' => true,
			),
			'edit' => array(
				'title' => array(
					'ru' => '��������',
					'en' => 'Edit',
				),
				'onclick'    => 'cnt.editElem()',
				'img'        => 'icon.edit.gif',
				'display'    => 'none',
				'show_title' => true,
			),
			'delete' => array(
				'title' => array(
					'ru' => '�������',
					'en' => 'Delete',
				),
				'onclick'    => 'cnt.deleteElems(\''.$this->name.'\')',
				'img'        => 'icon.delete.gif',
				'display'    => 'none',
				'show_title' => true,
			),

		);
	####### ������ ��������� �������� ##########
        if (!empty($_GET['id'])){
            $temp = sql_getValue("SELECT name FROM ".$this->table." WHERE id=".$_GET['id']);
        }
        else { $temp = '����� ���';}
		$str[get_class($this)] = array(
			'saved'			=> array('���������� ���� ������� ���������','Info has been successfully saved',),
			'basic_caption'	=> array('��� ������','Housetypes'),
			'basic_tab'		=> array($temp,'Housetypes'),
            'title'         => array($temp,'Housetypes'),
		);
	}
	####### ���������� ������ �������� #########
	function getTabs() {
		return $this->tabs;
	}

	##############################################
}
Registry::set('object_editor_submodule', Registry::get('TObjHousetypesEd'));


?>