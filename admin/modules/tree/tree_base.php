<?php

class TTreeBase extends BaseEd {
	##############################################
	var $name  = 'tree';
	var $table = 'tree';
	var $tabs	  = array();
	var $actions  = array();
	##############################################
	function TTreeBase() {
		global $str;

		TTable::TTable();
	############### ������ ������� ################
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
			'moveup' => array(
				'title' => array(
					'ru' => '����',
					'en' => 'Up',
				),
				'onclick' => 'cnt.swapElems(-1)',
				'img' => 'icon.moveup.gif',
				'display' => 'none',
				'show_title' => true,
			),
			'movedown' => array(
				'title' => array(
					'ru' => '����',
					'en' => 'Down',
				),
				'onclick' => 'cnt.swapElems(1)',
				'img' => 'icon.movedown.gif',
				'display' => 'none',
				'show_title' => true,
			),
		);
	####### ������ ��������� �������� ##########
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'saved'			   => array('���������� ���� ������� ���������','Info has been successfully saved',	),
			'basic_tab'			=> array('��������','Name',),
			'basic_caption'		=> array('��������','Page',	),
		));

	}
	####### ���������� ������ �������� #########
	### ���� �� ������ � ModuleName_ed
	function getTabs() {

		$id = get('id', 0, 'gp');
		if (!$id) {
			return $this->tabs;
		}
		$row = $this->getRow($id);
		$elems = $GLOBALS['cfg']['types'][$row['type']]['elements'];

		foreach ($elems as $k => $v) {
			$this->tabs['tab'.$k] = array(
				'display' => array( // elements conf
					'ru' => $GLOBALS['cfg']['elements'][$v][0],
					'en' => $GLOBALS['cfg']['elements'][$v][1],
				),
				'type' => 'elem',
				'conf' => array(
					'elem' => $v,
					'next' => $GLOBALS['cfg']['elements'][$v]['next'], // elements conf
					'target' => 'cnt', // cnt | act
				),
			);
		}
		return $this->tabs;
	}

	##############################################

}
?>