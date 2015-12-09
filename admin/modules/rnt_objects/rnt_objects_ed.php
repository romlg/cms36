<?php

class TRnt_objectsEd extends BaseEd {

	var $name;
	var $selector = true;
	var $tabs = array();
	var $actions = array();
	var $table = 'rnt_objects';

	################

	function TRnt_objectsEd() {
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

        if (!empty($_GET['id'])) $temp = '��� '.sql_getValue('SELECT lot_id FROM '.$this->table.' WHERE id='.$_GET['id']);
        else $temp = '';

        $str[get_class_name($this)] = array(
			'saved'			=> array('���������� ���� ������� ���������',	'Info has been successfully saved',	),
			'basic_caption'	=> array('������ ������������',					'Realty Object'),
			'basic_tab'		=> array($temp,									'Realty Object'),
			'title'		    => array($temp,									'Realty Object'),
		);
	}

	################

	// ���������� ������ ��������
	function getTabs() {
		$id = get('id', 0, 'gp');

        if (!$id) {
                return $this->tabs;
        }
        $row = $this->GetRow($id);

		$elems = array(
			'elem_image'		=>	array('������� ���� � ����������',		'Image',						'next' => 0),
			'elem_address'		=>	array('�����������������',				'Location',						'next' => 0),
			'elem_text'			=>	array('�����',							'Text',							'next' => 0),
			'elem_gallery'		=>	array('����������',						'Photo',						'next' => 1),
			'elem_flash'		=>	array('����������� ����',				'Flash',						'next' => 1),
			'elem_google'		=>	array('Google Maps',					'Google Maps',					'next' => 0),
		);
		if ($row['obj_type_id'] == 'newbuild') {
			$elems['elem_free'] = array('��������� ��������',	'Free flats',	'next' => 1);
			$elems['elem_plan'] = array('����������',			'Plans',		'next' => 1);
		}

		$i = 0;
        foreach ($elems as $k => $v) {
        	$this->tabs['tab'.$i] = array(
        		'display' => array( // elements conf
        			'ru' => $v[0],
        			'en' => $v[1],
        		),
        		'type' => 'elem',
        		'conf' => array(
        			'elem' => $k,
        			'next' => $v['next'], // elements conf
        			'target' => 'cnt', // cnt | act
        		),
        	);
        	$i++;
        }
        return $this->tabs;
	}
}
Registry::set('object_editor_submodule', Registry::get('TRnt_objectsEd'));

?>