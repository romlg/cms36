<?php


class TCurrencies extends TTable {

	var $name = 'currencies';
	var $table = 'currencies';

	########################

	function TCurrencies() {
		global $str, $actions;

		// обязательно вызывать
		TTable::TTable();

		// экшены для метода Show (когда отображаем табличку)
		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		// экшены для формы редактирования
		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.SaveSubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'apply' => array(
				'Применить',
				'Apply',
				'link'	=> 'cnt.ApplySubmit()',
				'img' 	=> 'icon.kb.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'close' => &$actions['table']['close'],
		);

		
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Валюты',
				'Сurrencies',
			),
			'saved' => array(
				'Даные были успешно сохранены',
				'Data has been saved successfully',
			),
			'iso'	=> array(
				'iso',
				'iso',
			),
			'name'	=> array(
				'Название',
				'Name',
			),
			'value'	=> array(
				'Значение',
				'Value',
			),
			'display'	=> array(
				'На сайте',
				'Display',
			),
			'description'	=> array(
				'Описание',
				'Description',
			),
		));

	}

	########################

	function Show() {
		global $cfg;
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

		// подключение библиотеки для построения таблиц
		require_once (core('ajax_table'));
		// строим таблицу
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'iso',
					'display' => 'iso',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'name',
					'display' => 'name',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'display',
					'display' => 'display',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'value',
					'display' => 'value',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'description',
					'display' => 'description',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
			),
			'from'		=> $this->table,
			'orderby'	=> '',
			// всегда передается это
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'where'		=> '',
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => true,
		), $this);

		$this->AddStrings($data);
		return $this->Parse($data, $this->name.'.tmpl');
	}

	########################

	function EditForm() {
		// получает значение из GET, POST, COOKIE, SESSION
		// 1 параметр - название переменной, 2 параметр - дефолтное значение
		$id = (int)get('id', 0);
		if ($id) {
			// выбирает строку из $this->table по id
			$row = $this->GetRow($id);
		}
		else {
			$row['id'] = $id;
		}

		// добавляет в шаблон дефолтные строковые константы
		$this->AddStrings($row);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = get('id', 0, 'p');
		$apply = (int)get('apply', 0, 'p');

		// пытаемся записать изменение в БД, параметр - массив обязательных полей
		$res = $this->Commit(array('display', 'name','value'));

		// проверяем на apply
		$close = !$apply ? 'window.parent.top.close();' : '';
		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.opener.location.reload();';
//		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.location.reload();';
		$script = (mysql_affected_rows() ? $reload : '').$close;

		// все ок
		if (is_int($res)) {
			return "<script>alert('".$this->str('saved')."'); $script</script>";
		}

		// ошибка
		return $this->Error($res);
	}

	########################
}

$GLOBALS['currencies'] =  & Registry::get('TCurrencies');

?>