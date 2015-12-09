<?php

/* $Id: support_categories.php,v 1.1 2009-02-18 13:09:12 konovalova Exp $
 */

class TSupportCategories extends TTable {

	var $name = 'support_categories';
	var $table = 'support_categories';

	########################

	function TSupportCategories() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'moveup' => &$actions['table']['moveup'],
			'movedown' => &$actions['table']['movedown'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Категории проблем',
				'Problem Categories',
			),
			'title_one'	=> array(
				'Категория проблемы',
				'Problem Category',
			),
			'name'	=> array(
				'Имя',
				'Name'
			),
			'title_editform' => array(
				'Заголовок',
				'Title'
			),
			'saved' => array(
				'Категория была сохранена',
				'The category has been saved'
			),
		));
	}
	
	########################

	function EditForm(){
		$id = (int)get('id');
		if ($id) $row = $this->GetRow($id);
		$GLOBALS['title'] = $this->str('title_one');
		$row['id'] = $id;

		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = (int)get('id', 0, 'p');
		if (!$id) $GLOBALS['_POST']['fld']['priority'] = 1 + (int)$this->GetValue("SELECT COUNT(*) FROM $this->table");

		$res = $this->Commit(array('name'));
		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
		return $this->Error($res);
	}

	########################

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$ret['thisname'] = $this->name;
		
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'width'		=> '1px',
				),
				/*array(
					'select'	=> 'priority',
					'display'	=> 'priority',
					'type'		=> 'priority',
					'align'		=> 'right',
				),*/
				array(
					'select'	=> 'name',
					'display'	=> 'name',
				),
			),
			'orderby'	=> 'priority, name',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'	=> 'ID=cb.value',
			'dblclick' => 'editItem(id)',
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');

	}

	######################
}

$GLOBALS['support_categories'] = & Registry::get('TSupportCategories');

?>