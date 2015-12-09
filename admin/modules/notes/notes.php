<?php

class TNotes extends TTable {

	var $name = 'notes';
	var $table = 'notes';

	function TNotes() {
		global $actions, $str;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => array(
				'Редактировать',
				'Edit',
				'link'	=> 'cnt.editItem()',
				'img' 	=> 'icon.edit.gif',
				'display'	=> 'none',
			),
			'create' => array(
				'Создать',
				'Create',
				'link'	=> 'cnt.editItem(0)',
				'img' 	=> 'icon.create.gif',
				'display'	=> 'block',
			),
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.MySubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
			),
			'close' => &$actions['table']['close'],
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Заметки',
				'Notes',
			),
			'add'	=> array(
				'Добавление новой заметки',
				'Add new note',
			),
			'edit'	=> array(
				'Редактирование заметки',
				'Edit note',
			),
			'empty'	=> array(
				'Ничего не найдено',
				'Empty result set',
			),
			'subject'	=> array(
				'Тема',
				'Subject',
			),
			'date'	=> array(
				'Дата создания',
				'Create Date',
			),
			'notify_date'	=> array(
				'Дата напоминания',
				'Notify Date',
			),
			'text' => array(
				'Подробнее',
				'More info',
			),
			'fulltext' => array(
				'Полный текст',
				'Full text',
			),
			'link' => array(
				'Ссылка',
				'Link',
			),
			'data' => array(
				'Редактирование',
				'Edit note',
			),
			'select_date' => array(
				'Выбрать дату',
				'Select Date',
			),
			'saved' => array(
				'Данные успешно сохранены',
				'The information has been saved successfully',
			),
			'e_no_items' => array(
				'Нет выделенных элементов',
				'No elements selected',
			),
		));
	}

	function table_get_text(&$value, &$column, &$row) {
		return '<div >'.$value.'</div><div class="hide">'.$row['text'].'</div>';
	}

	function table_get_link(&$value, &$column, &$row) {
		if ($value)
			return '<a href="'.$value.'" target="_blank"><img src="images/icons/icon.link.gif" width=11 heidht=11 border=0 alt="'.$this->str('link').'"></a>';
		else
			return '&nbsp;';
	}

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$data['thisname'] = $this->name;
		$this->AddStrings($data);
		$client_id = get('client_id', 0, 'g');
		$data['client_id'] = $client_id;
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'valign'	=> 'top',
				),
				array(
					'select'	=> 'date',
					'display'	=> 'date',
					'type'		=> 'datetime',
					'valign'	=> 'top',
					'nowrap'	=> 1,
				),
				array(
					'select'	=> 'name',
					'display'	=> 'subject',
					'width'		=> '30%',
					'valign'	=> 'top',
				),
				array(
					'select'	=> 'SUBSTRING_INDEX(text, " ", 10)',
					'display'	=> 'text',
					'width'		=> '50%',
					'type'		=> 'text',
				),
				array(
					'select'	=> 'link',
					'display'	=> 'link',
					'width'		=> '20',
					'align'		=> 'center',
					'type'		=> 'link',
				),
				array(
					'select'	=> 'text',
				),
			),
			'where'		=> 'visible="1" and client_id='.$client_id,
			'params'	=> array('page' => $this->name, 'do' => 'show', 'client_id' => $client_id),
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID = cb.value',
		), $this);
		return $this->parse($data, $this->name.'.tmpl');
	}

	########################

	function EditForm() {
		$id = (int)get('id', 0);
		if ($id) {
			$row = $this->GetRow($id);
		}
		else {
			$row['id'] = $id;
			$row['date'] = time();
			$row['notify_date'] = 0;
			$row['text'] = '';
		}
		$GLOBALS['title'] = $this->str($id ? 'edit' : 'add');
		$this->AddStrings($row);
		$client_id = get('client_id', 0, 'g');
		$row['client_id'] = $client_id;
		$row['date'] = date('Y-m-d H:i',$row['date']);
		if ($row['notify_date'])
			$row['notify_date'] = date('Y-m-d H:i',$row['notify_date']);
		else
			$row['notify_date'] = '';
		include_fckeditor();
		$oFCKeditor = &new FCKeditor();
		$oFCKeditor->ToolbarSet = 'Simple';
		$oFCKeditor->CanUpload = false;
		$oFCKeditor->Value = isset($row['text']) ? $row['text'] : '';
		$row['text'] = $oFCKeditor->ReturnFCKeditor('fld[text]', '100%', '100%');
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	######################

	function Edit() {
		$row = get('fld', array(), 'p');
		$id = (int)get('id', 0, 'p');
		$row['date'] = $row['date'] ? strtotime($row['date']) : 0;
		$row['notify_date'] = $row['notify_date'] ? strtotime($row['notify_date']) : 0;
		$row['visible'] = 1;
		if (!$row['date']) $row['date'] = time();
		if ($id) {
			sql_updateId($this->table, $row, $id);
		} else {
			$id = sql_insert($this->table, $row);
		}
		if (!is_int($id)) return $this->error($id);
		return "<script>alert('".$this->str('saved')."'); window.parent.close(); </script>";
	}
}

$GLOBALS['notes'] = & Registry::get('TNotes');

?>