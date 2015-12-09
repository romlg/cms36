<?php

/* $Id: import_flat.php,v 1.1 2009-04-10 11:44:31 dereshov Exp $*/

class TImport_flat extends TTable {

	// �������� ������
	var $name = 'import_flat';
	// �������� �������
	var $table = 'flat_csv';
	// ���������� �� �������� �����?
	var $selector = true;

	########################

	function TImport_flat() {
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
			'csv' => array(
				'��������� ������� (csv)',
				'Load catalog (csv)',
				'link' => 'cnt.LoadPrice()',
				'img' => 'icon.module.gif',
				'display' => 'none',
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
				'���������&nbsp;���������',
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
				'������ �������� �������',
				'Import flat catalog',
			),
			'street' => array(
				'�����',
				'Street',
			),
			'rooms' => array(
				'���������� ������',
				'Roorms count',
			),			
			'price_rub' => array(
				'���� (���)',
				'Price (rub)',
			),
			'description' => array(
				'��������',
				'Description',
			),			
			'preview' => array (
				'��������',
				'Preview'
			),			
			'action' => array(
				'�����',
				'Action',
			),
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
					'select'	=> 'rooms',
					'display'	=> 'rooms',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'street',
					'display'	=> 'street',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'price_rub',
					'display'	=> 'price_rub',
					'flags'		=> FLAG_SORT,
				),
			),
			'from'		=> $this->table,
			'orderby'	=> 'id DESC',
			// ������ ���������� ���
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'where'		=> '',
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			'_sql' => true,
		), $this);

		$this->AddStrings($data);
		$data['mode'] = DEV_MODE;
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
		$res = $this->Commit(array('image'));

		// ��������� �� apply
		$close = !$apply ? 'window.parent.top.close();' : '';
		$reload = $apply ? 'window.parent.location.reload();' : 'window.parent.top.opener.location.reload();';
		$script = (mysql_affected_rows() ? $reload : '').$close;

		// ��� ��
		if (is_int($res)) {
			return "<script>alert('".$this->str('saved')."'); $script</script>";
		}

		// ������
		return $this->Error($res);
	}

	/**
	 * ����� ��� �������� CSV-�������
	 *
	 * @return string
	 */
	########################	
	function showCSVForm(){
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

		$ret['thisname'] = $this->name;
		$this->AddStrings($ret);
		return $this->Parse($ret, 'loadcsvprice.tmpl');
	}

	/**
	 * �������� ������� � ������� csv:
	 *  C_ID
     *  price
     *  old_price
	 */
	########################	
	function editLoadPrice() {
		$file = $_POST['file'];
		
		if (substr($file, 0, 5) == '@temp') $file = substr($file, 5);
		$type = substr($file, strrpos($file,'.')+1);
		if ($type != 'csv'){
			return "<script>alert('���������� ����� �� ��������������');</script>";
		}
		echo "<script>parent.stopLoad();parent.hideDownloadFrom();</script>";
		flush();
		
		$GLOBALS['gzip'] = false;
		set_time_limit(0);
		ob_end_clean();

    	if(!is_readable($file)) {
			$this->eRror = "�� ���� ������� ���� ��� ������.";
			return "<script>parent.document.getElementById('error').innnerHTML = '".$this->eRror."';</script>";
    	}
		require (elem('csv_tools/Bs_CsvUtil.class.php'));
		$Bs_CsvUtil = new Bs_CsvUtil();

		$data = $Bs_CsvUtil->csvFileToArray($file, ';', 'both', FALSE, FALSE, TRUE);

		if (empty($data)){
			$this->eRror = "���������� ���� ����.";
			return "<script>parent.document.getElementById('error').innnerHTML = '".$this->eRror."';</script>";
		}

		foreach ($data AS $i=>$trow){
			if ($i == 0 && $trow[0] == 'c_id') continue;
			
			$row['c_id'] = $trow[0];
			if (empty($row['c_id'])) continue;
			if (strlen($row['c_id']) < 8) $row['c_id'] = str_pad($row['c_id'], 8-strlen($row['c_id']), "0", STR_PAD_LEFT);  

			$trow[1] = str_replace(array(" ", chr(160)), "", $trow[1]);
			$trow[1] = str_replace(",", ".", $trow[1]);
			$row['price'] = $trow[1];
			
			$trow[2] = str_replace(array(" ", chr(160)), "", $trow[2]);
			$trow[2] = str_replace(",", ".", $trow[2]);
			$row['old_price'] = $trow[2];

    		// ���� � ���� ��� ������ � ������ c_id, �� ������ ������
    		$_id = sql_getValue('SELECT id FROM ' . $this->table . ' WHERE c_id=' . $row['c_id']);
    		if (!$_id) {
    			$this->eRror = '������ � c_id=' . $row['c_id'] . ' �� ���������� � ���� ������!';
    		} else {
    			$sql = 'UPDATE ' . $this->table . ' SET price = "' . $row['price'] . '", old_price = "' . $row['old_price'] . '" WHERE c_id = "' . $row['c_id'] . '"';
        		sql_query($sql);
                $this->eRror = sql_getError();
    		}
    		if ($this->eRror){
    		    $this->errors[$i] = e($this->eRror);
    		}
    		echo '���������� ������ � '.$i."\r\n";
			flush();
		}

		//---------------------
		$str = "<script>";
		$str .= "parent.document.getElementById('error').innerHTML = '�������� ���������.<br>';";
		if ($this->errors) {
			$err_str = '';
			foreach ($this->errors as $k=>$err) {
				$err_str .= '������ '.$k.': '.$err.'<br>';
			}
			$str .= "parent.document.getElementById('error').innerHTML += '".$err_str."';";
		}
		$str .= "</script>";

		return $str;
	}	

}

$GLOBALS['import_flat'] = & Registry::get('TImport_flat');

?>