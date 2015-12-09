<?php

/* $Id: news.php,v 1.1 2009-02-18 13:09:09 konovalova Exp $
 */

class TNews extends TTable {

	// �������� ������
	var $name = 'news';
	// �������� �������
	var $table = 'elem_news';
	// ���������� �� �������� �����?
	var $selector = true;

	########################

	function TNews() {
		global $actions, $str;

		// ����������� ��������
		TTable::TTable();

		// ������ ��� ������ Show (����� ���������� ��������)
		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'�������',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		// ������ ��� ����� ��������������
		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'���������',
				'Save',
				'link'	=> 'cnt.SaveSubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'apply' => array(
				'���������',
				'Apply',
				'link'	=> 'cnt.ApplySubmit()',
				'img' 	=> 'icon.kb.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'close' => &$actions['table']['close'],
		);

		// ��������� ��������� ������
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'������, �������, �����',
				'News',
			),
			'name' => array(
				'���������',
				'Title',
			),
			'txt' => array(
				'�����',
				'Action',
			),
			'publications' => array(
				'������',
				'Article',
			),
			'news' => array(
				'�������',
				'News',
			),
			'date' => array(
				'����',
				'Date',
			),
			'type' => array(
				'���',
				'Type',
			),			
			'visible' => array(
				'����������',
				'Visible',
			),
			'description'=> array(
				'��������',
				'Description',
			),
			/*'add'	=> array(
				'���������� ����� ������',
				'Add string',
			),
			'edit'	=> array(
				'�������������� ������',
				'Edit string',
			),*/
			'saved' => array(
				'����� ���� ������� ���������',
				'Data has been saved successfully',
			),
		));
	}

	########################

	function Show() {
		// ������������ �����
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

		// ����������� ���������� ��� ���������� ������
		require_once (core('ajax_table'));

		// ������ �������
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
			// ������ ���������� ���
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
		// �������� �������� �� GET, POST, COOKIE, SESSION
		// 1 �������� - �������� ����������, 2 �������� - ��������� ��������
		$id = (int)get('id', 0);
		if ($id) {
			// �������� ������ �� $this->table �� id
			$row = $this->GetRow($id);
		}
		else {
			$row['id'] = $id;
		}

		// ��������� ���� � ������ ������ ��� �����������
		 if (!empty($row['date'])){
		  $row['date'] =date('d.m.Y',strtotime($row['date']));
		 }
		 else{
			$row['date']=date('d.m.Y');
		 }


		// ��������� � ������ ��������� ��������� ���������
		$this->AddStrings($row);
		$row['types'] = array(
			'article' => $this->str('article'),
			'news' => $this->str('news'),
			'action' => $this->str('action'),
		);

		// ����������� FCKeditora
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

		// ��������� ���� � ������ ������ ��� ���������� � ��

		 $q = explode('.', $_POST['fld']['date']);
		 $q = array_reverse($q);
		 $_POST['fld']['date'] = implode('-', $q).' '.date('H:i:s');
		 
		$this->hsc($_POST['fld']['name']);
		$this->hsc($_POST['fld']['description']);
		
			
		// �������� �������� ��������� � ��, �������� - ������ ������������ �����
		$res = $this->Commit(array('date', 'name','description'));

		// ��������� �� apply
		$close = !$apply ? 'window.parent.top.close();' : '';
		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.opener.location.reload();';
//		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.location.reload();';
		$script = (!sql_getError() ? $reload : '').$close;

		// ��� ��
		if (is_int($res)) {
			return "<script>alert('".$this->str('saved')."'); $script</script>";
		}

		// ������
		return $this->Error($res);
	}

	########################
}

$GLOBALS['news'] = & Registry::get('TNews');

?>