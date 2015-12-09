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
					'ru' => 'Добавить',
					'en' => 'Add new',
				),
				'onclick' => 'cnt.editElem(0)',
				'img' => 'icon.create.gif',
				'display' => 'none',
				'show_title' => true,
			),
			'edit' => array(
				'title' => array(
					'ru' => 'Изменить',
					'en' => 'Edit',
				),
				'onclick' => 'cnt.editElem()',
				'img' => 'icon.edit.gif',
				'display' => 'none',
				'show_title' => true,
			),
			'delete' => array(
				'title' => array(
					'ru' => 'Удалить',
					'en' => 'Delete',
				),
				'onclick' => 'cnt.deleteElems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
				'show_title' => true,
			),
		);

        if (!empty($_GET['id'])) $temp = sql_getValue('SELECT name FROM '.$this->table.' WHERE id='.$_GET['id']);
        else $temp = 'Новая секция';

        $str[get_class_name($this)] = array(
			'saved'			=> array('Информация была успешно сохранена',	'Info has been successfully saved',	),
			'basic_caption'	=> array('Планировка',					'Plan'),
			'basic_tab'		=> array($temp,							'Plan'),
			'title'		    => array($temp,							'Plan'),
		);
	}

	################

	// возвращает список закладок
	function getTabs() {
		return $this->tabs;
	}
}
Registry::set('object_editor_submodule', Registry::get('TPlans_Ed'));
?>