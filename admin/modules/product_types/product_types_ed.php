<?php

class TProductTypesEd extends TWorkingCopyBase {
	##############################################
	var $name     = 'product_types';
	var $edname   = 'producttypes';
	var $table    = 'product_types';
	var $tabs     = array();
	var $actions  = array();
	##############################################
	function TProductTypesEd() {
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
			'moveup' => array(
				'title' => array(
					'ru' => '����',
					'en' => 'Up',
				),
				'onclick'    => 'cnt.swapElems(-1)',
				'img'        => 'icon.moveup.gif',
				'display'    => 'none',
				'show_title' => true,
			),
			'movedown' => array(
				'title' => array(
					'ru' => '����',
					'en' => 'Down',
				),
				'onclick'    => 'cnt.swapElems(1)',
				'img'        => 'icon.movedown.gif',
				'display'    => 'none',
				'show_title' => true,
			),
		);

		####### ������ ��������� �������� ##########
		if (!empty($_GET['id'])){
			$temp = sql_getValue('SELECT name FROM product_types WHERE id='.$_GET['id']);
		}
		else { $temp = '';}
		
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
				'saved'			=> array('���������� ���� ������� ���������','Info has been successfully saved',),
				'basic_caption'	=> array('���� ���������','Product types'),
				'basic_tab'		=> array($temp,'Product types'),
	            'title'         => array($temp,'Product types'),
		));
	}
	####### ���������� ������ �������� #########
	function getTabs() {

		$id = get('id', 0, 'gp');
		if (!$id) {
			return $this->tabs;
		}
		$row = $this->GetRow($id);
		$elems = array('elem_params', 'elem_prices');

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
$GLOBALS['object_editor_submodule'] = & Registry::get('TProductTypesEd');
?>