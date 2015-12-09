<?php

class TObjMetroStations extends TTable {

	var $name = 'metrostations';
	var $table = 'obj_locat_metrostations';

	########################

	function TObjMetroStations() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('������� �����',				'Metro stations',),
			'name'			=> array('�������',						'Stations',),
			'okrug'			=> array('�����(�)',					'Region(s)'),
			'okrug1'		=> array('����',					'SZAO'),
			'okrug2'		=> array('���',						'SAO'),
			'okrug3'		=> array('����',					'SVAO'),
			'okrug4'		=> array('���',						'ZAO'),
			'okrug5'		=> array('���',						'CAO'),
			'okrug6'		=> array('���',						'VAO'),
			'okrug7'		=> array('����',					'UZAO'),
			'okrug8'		=> array('���',						'UAO'),
			'okrug9'		=> array('����',					'UVAO'),

		));

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
	}

	########################

	function Show() {
		if (!empty($_POST)) {
			$actions = get('actions', '', 'p');
			if ($actions) {
				return $this->$actions();
			}
		}
		
		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'as'        => 'id',
					'width'     => '1%',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '1px',
				),
				array(
					'select'	=> 'okrug',
					'display'	=> 'okrug',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '1px',
					'type'		=> 'okrug',
				),
			),
			'from'		=> $this->table,
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'orderby'	=> 'name',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
		  	//'_sql'=>true,
		), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	function table_get_okrug(&$value, &$column, &$row) {
		$rt="";
		$okruga=explode(',',$value);
		foreach($okruga as $okrug) {
			if(!intval($okrug)) continue;
			switch(intval($okrug)) {
				case 1 : $rt.="����"; break;
				case 2 : $rt.="���";  break;
				case 3 : $rt.="����"; break;
				case 4 : $rt.="���";  break;
				case 5 : $rt.="���";  break;
				case 6 : $rt.="���";  break;
				case 7 : $rt.="����"; break;
				case 8 : $rt.="���";  break;
				case 9 : $rt.="����"; break;
			}
			$rt.=", ";
		}
		if(strlen($rt)>=2) $rt=substr($rt,0,-2);
		return $rt;
	}

	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$row = $this->GetRow($id);
			$okruga=explode(',',$row['okrug']);
			foreach($okruga as $okrug) $row['osel'][intval($okrug)]=" selected";
		}
		else {
			$row = array();
		}
		$this->SetDefaultValues($row);
		$this->AddStrings($row);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

    ########################

	function Edit() {
		$id = (int)get('id', 0, 'p');
		$metro_subst = get('metro_subst', array(), 'p');
		$fld = &$GLOBALS['_POST']['fld'];
		$fld['okrug']=implode(",",$_POST['fld']['okrug']);

		$id = $this->Commit(array('name'));

		$reload = mysql_affected_rows() ? 'window.parent.location.reload()' : '';
		if (!is_int($id)) {
			return $this->Error($id);
		}
		return '<script>alert(\''.$this->str('saved').'\'); '.$reload.'</script>';
	}


}


$GLOBALS['metrostations'] = &Registry::get('TObjMetroStations');
?>