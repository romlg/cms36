<?php

/* $Id: news.php,v 1.1 2009-02-18 13:09:09 konovalova Exp $
 */

class TNews extends TTable {

	// название модуля
	var $name = 'news';
	// название таблицы
	var $table = 'elem_news';
	// отображать ли селектор языка?
	var $selector = true;

	########################

	function TNews() {
		global $actions, $str;

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

		// строковые константы модуля
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Статьи, Новости, Акции',
				'News',
			),
			'name' => array(
				'Заголовок',
				'Title',
			),
			'txt' => array(
				'Акция',
				'Action',
			),
			'publications' => array(
				'Статья',
				'Article',
			),
			'news' => array(
				'Новость',
				'News',
			),
			'date' => array(
				'Дата',
				'Date',
			),
			'type' => array(
				'Тип',
				'Type',
			),			
			'visible' => array(
				'Отображать',
				'Visible',
			),
			'description'=> array(
				'Описание',
				'Description',
			),
			/*'add'	=> array(
				'Добавление новой строки',
				'Add string',
			),
			'edit'	=> array(
				'Редактирование строки',
				'Edit string',
			),*/
			'saved' => array(
				'Даные были успешно сохранены',
				'Data has been saved successfully',
			),
		));
	}

	########################

	function Show() {
		// обязательная фигня
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
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
					'select'	=> 'name',
					'display' => 'name',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'visible',
					'display' => 'visible',
					'type' => 'news_visible',
					'flags' =>  FLAG_SORT,
				),
				array(
					'select'	=> 'type',
					'display' => 'type',
					'type' => 'str',
					'flags' =>  FLAG_SORT,
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(date)',
					'as' => 'date',
					'type' => 'news_date',
					'display' => 'date',
					'flags' => FLAG_SEARCH | FLAG_SORT,
				),
			),
			'from'		=> $this->table,
			'orderby'	=> 'date DESC',
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

	function table_get_str(&$value, &$column, &$row) {
		return $this->str($value);
	}
	
	########################

	function table_get_news_date(&$value, &$column, &$row) {
		return date('d.m.Y', $value);
	}

	########################

	function table_get_news_visible(&$value, &$column, &$row) {
		$check = ($value) ? ' checked="checked"': '';
		$check="<input type='checkbox' onclick=\"changeVisibleN(this, ".$row['id'].")\" ".$check."> ";
		return $check;
	}

	########################
	
	function changeVisible(){
		$checked = get("checked", 0, 'g');
		$id = get("id", 0, 'g');
		if ($id){
			sql_query("UPDATE elem_news SET visible = ".$checked." WHERE id=".$id);
		}
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

		// конвертим дату в нужный формат для отображения
		 if (!empty($row['date'])){
		  $row['date'] =date('d.m.Y',strtotime($row['date']));
		 }
		 else{
			$row['date']=date('d.m.Y');
		 }


		// добавляет в шаблон дефолтные строковые константы
		$this->AddStrings($row);
		$row['types'] = array(
			'article' => $this->str('article'),
			'news' => $this->str('news'),
			'action' => $this->str('action'),
		);

		// подключение FCKeditora
		include_fckeditor();

		$oFCKeditor = & Registry::get('FCKeditor');
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->CanUpload = false;
		$oFCKeditor->Value = isset($row['text']) ? $row['text'] : '';
		$row['text'] = $oFCKeditor->ReturnFCKeditor('fld[text]', '100%', '100%');
		//--
		$row['visible'] = (!empty($row['visible'])) ? ' checked="checked"': '';

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################
	function hsc(&$value){
		$value = str_replace("&", "+++", $value);
		$value = htmlspecialchars($value);
		$value = str_replace("+++", "&", $value);
	}
	########################
	function Edit() {
		$id = get('id', 0, 'p');
		$apply = (int)get('apply', 0, 'p');

		// конвертим дату в нужный формат для сохранения в БД

		 $q = explode('.', $_POST['fld']['date']);
		 $q = array_reverse($q);
		 $_POST['fld']['date'] = implode('-', $q).' '.date('H:i:s');
		 
		$this->hsc($_POST['fld']['name']);
		$this->hsc($_POST['fld']['description']);
		
			
		// пытаемся записать изменение в БД, параметр - массив обязательных полей
		$res = $this->Commit(array('date', 'name','description'));

		// проверяем на apply
		$close = !$apply ? 'window.parent.top.close();' : '';
		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.opener.location.reload();';
//		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.location.reload();';
		$script = (!sql_getError() ? $reload : '').$close;

		// все ок
		if (is_int($res)) {
			return "<script>alert('".$this->str('saved')."'); $script</script>";
		}

		// ошибка
		return $this->Error($res);
	}

	########################
}

$GLOBALS['news'] = & Registry::get('TNews');

?>