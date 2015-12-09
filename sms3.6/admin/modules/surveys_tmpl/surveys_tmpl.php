<?php
class TSurveys_tmpl extends TTable {
    var $name = 'surveys_tmpl';
    var $table = 'surveys_variants_groups';

	function TSurveys_tmpl(){
		global $actions, $str;

		// ����������� ��������
		TTable::TTable();
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'				=> array('������� ��������',				'Question templates',),
			'name'				=> array('������',							'Question',),
			'type'				=> array('��� �������',						'Type',),
			'answers'			=> array('������',							'Answers',),
			'answer'			=> array('�����',							'Answer',),
			'multi'				=> array('� ������������� �������',			'Multi',),
			'single'			=> array('� ������������ �������',			'Single',),
			'quest'				=> array('������',							'Question',),
			'add_answer'		=> array('�������� �����',					'Add answer',),
			'del_answer'		=> array('������� �����',					'Delete answer',),
			'free_form'			=> array('�&nbsp;���������&nbsp;�����',		'Free&nbsp;form',),
			'docopy'			=> array('�����������',						'Copy',),
		));

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'�������',
				'Delete',
				'link' => 'cnt.deleteItems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			),
		);

        $actions[$this->name.'.editform'] = array(
			'save' => array(
				'���������',
				'Save',
				'link' => 'cnt.document.forms.editform.submit()',
				'img' => 'icon.edit.gif',
				'display' => 'none',
			),
		);
	}

	function table_get_docopy(&$value, &$column, &$row) {
		$str = "<a href='#' onclick='window.clipboardData.setData(\"text\",\"groupid=".$value."\")' class='open' title='".$this->str('docopy')."'>".$this->str('docopy')."</a>";
		return $str;
	}

	function Show() {
		// ������������ �����
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

		require_once (core('ajax_table'));
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'id',
					'display'	=> 'docopy',
					'type'		=> 'docopy',
				),
			),
			'from'		=> $this->table,
			'where'		=> '',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			'orderby'	=> 'id',
			//'_sql' => true,
		), $this);

		$this->AddStrings($data);
		$data['thisname'] = $this->name;
		$data['thisname2'] = str_replace('/', '', $this->name);
		return $this->Parse($data, "surveys_tmpl.tmpl");
	}

	function EditForm(){
		$id = get('id', 0,'g');
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		if ($id){
			$data = $this->getRow($id);
			// ������ �������
			$data['answers'] = sql_getRows("SELECT * FROM surveys_variants WHERE id_group=".$id." ORDER BY priority", true);
		} else {
			$data['lang'] = lang();
		}
		$data['types'] = array(
			'multi'		=> $this->str('multi'),
			'single'	=> $this->str('single'),
		);
		$data['max_answer_id'] = sql_getValue("SELECT MAX(id) FROM surveys_variants");

		$this->AddStrings($data);

		return $this->Parse($data, "surveys.editform.tmpl");
	}


	function Edit() {
		$pid = $_POST['id'];
		$fld = $_POST['fld'];
        if (get_magic_quotes_gpc()) {
            $fld['name'] = stripslashes($fld['name']);
        }
        $fld['name'] = e($fld['name']);
        $fld['type'] = e($fld['type']);
        sql_query('BEGIN');
        // ��������� ������
        if ($pid) $query = 'UPDATE surveys_variants_groups SET name="'.$fld['name'].'", type="'.$fld['type'].'" WHERE id='.$pid;
        else $query = 'INSERT INTO surveys_variants_groups (`name`,`lang`,`type`) VALUES ("'.$fld['name'].'","'.lang().'","'.$fld['type'].'")';
		sql_query($query);
		if (!$pid) $pid = sql_getLastId();
		$err = sql_getError();
		if (!empty($err)){
			sql_query('ROLLBACK');
			return '<script>alert("'.$this->str('error').': '.addslashes($err).'");</script>';
		}
		if (!empty($fld['answer'])) {
			// ������� ��� ������
			sql_query("DELETE FROM `surveys_variants` WHERE id_group=".$pid);
			$err = sql_getError();
			if (!empty($err)){
				sql_query('ROLLBACK');
				return '<script>alert("'.$this->str('error').': '.addslashes($err).'");</script>';
			}
			// ��������� ������
			$query = "INSERT INTO `surveys_variants` (`id`, `id_group`, `text`, `free_form`, `priority`) VALUES ";
			$priority = 1;
			foreach ($fld['answer'] as $key=>$val) {
				if (!empty($val)) {
					$query .= "('".$key."', '".$pid."', '".$val."', '".(isset($fld['free_form'][$key]) ? $fld['free_form'][$key] : 0)."', '".$priority."'),";
				}
				$priority++;
			}
			sql_query(substr($query,0,-1));
			$err = sql_getError();
			if (!empty($err)){
				sql_query('ROLLBACK');
				return '<script>alert("'.$this->str('error').': '.addslashes($err).'");</script>';
			}
		} else {
			// ��������� ���� ��������� �����
			$sql = "INSERT INTO `surveys_variants` (`id`, `id_group`, `text`, `free_form`, `priority`) VALUES (NULL, '".$pid."', '����� �1', '0', '1')";
	    	sql_query($sql);
	    	$err = sql_getError();
		    if (!empty($err)){
		    	sql_query('ROLLBACK');
		    	return '<script>alert("'.$this->str('error').': '.addslashes($err).'");</script>';
		    }
		}
		sql_query('COMMIT');
		if ($_POST['id']) return "<script>alert('".$this->str('saved')."');window.parent.top.opener.location.reload(); window.parent.location.reload();</script>";
		else return "<script>alert('".$this->str('saved')."');window.parent.top.opener.location.reload(); window.parent.parent.parent.location='ced.php?page=surveys_tmpl&do=editform&id=".$pid."';</script>";
    }

}
$GLOBALS['surveys_tmpl'] = & Registry::get('TSurveys_tmpl');
?>