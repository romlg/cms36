<?php


class TCurrencies extends TTable {

	var $name = 'currencies';
	var $table = 'currencies';

	########################

	function TCurrencies() {
		global $str, $actions;

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

		
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'������',
				'�urrencies',
			),
			'saved' => array(
				'����� ���� ������� ���������',
				'Data has been saved successfully',
			),
			'iso'	=> array(
				'iso',
				'iso',
			),
			'name'	=> array(
				'��������',
				'Name',
			),
			'value'	=> array(
				'��������',
				'Value',
			),
			'display'	=> array(
				'�� �����',
				'Display',
			),
			'description'	=> array(
				'��������',
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

		// ��������� � ������ ��������� ��������� ���������
		$this->AddStrings($row);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = get('id', 0, 'p');
		$apply = (int)get('apply', 0, 'p');

		// �������� �������� ��������� � ��, �������� - ������ ������������ �����
		$res = $this->Commit(array('display', 'name','value'));

		// ��������� �� apply
		$close = !$apply ? 'window.parent.top.close();' : '';
		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.opener.location.reload();';
//		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.location.reload();';
		$script = (mysql_affected_rows() ? $reload : '').$close;

		// ��� ��
		if (is_int($res)) {
			return "<script>alert('".$this->str('saved')."'); $script</script>";
		}

		// ������
		return $this->Error($res);
	}

	########################
}

$GLOBALS['currencies'] =  & Registry::get('TCurrencies');

?>