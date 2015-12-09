<?php

class TObjects_stars extends TTable {

	var $name = 'objects_stars';
	var $table = 'obj_stars';

	########################

	function TObjects_stars() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Звездность объектов',		'Comfort of objects',),
			'stars'			=> array('Количество звезд',		'Count of stars',),
			'storey'		=> array('Этажность',				'Storeys',),
			'material'		=> array('Материал стен',			'Material of wall',),
			'area'			=> array('Общая площадь',			'Total area',),
		));

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
					'select'	=> 'stars',
					'display'	=> 'stars',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '1px',
				),
			),
			'from'		=> $this->table,
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'orderby'	=> 'stars',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
		  	//'_sql'=>true,
		), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$row = $this->GetRow($id);
		}
		else {
			$row = array();
		}
		$this->SetDefaultValues($row);
		$this->AddStrings($row);

		if (!empty($row['stars'])) $row['selected'.$row['stars']] = 'selected';
		$storey = unserialize($row['storey']);
		if (!empty($storey)) {
			$row['storey_from'] = $storey['storey_from'];
			$row['storey_to'] = $storey['storey_to'];
		} else {
			$row['storey_from'] = "";
			$row['storey_to'] = "";
		}

		$material = unserialize($row['material']);
		$materials = sql_getRows('SELECT id, name FROM obj_housetypes', true);
		$row['material'] = '';
		foreach ($materials AS $key=>$val) {
			$row['material'] .= "<input type='checkbox' name='fld[material][]' value='".$key."' ".((array_search ($key, $material)===false)?'':'checked')." />".$val."<br />";
		}

		$area = unserialize($row['area']);
		$counter = 0;
		if (is_array($area)) foreach ($area as $key=>$val) if (is_array($val)) {
			$row['areas'] .= "<tr>".
			"<td valign='bottom'>
				<select name=fld[area][$key][flat]><option value='0'>--- Не выбрано ---</option><option value='1' ".(($val['flat']==1)?'selected':'').">1-ком.кв.</option><option value='2' ".(($val['flat']==2)?'selected':'').">2-ком.кв.</option><option value='3' ".(($val['flat']==3)?'selected':'').">3-ком.кв.</option><option value='4' ".(($val['flat']==4)?'selected':'').">4-ком.кв.</option><option value='5' ".(($val['flat']==5)?'selected':'').">5-ком.кв.</option><option value='6' ".(($val['flat']==6)?'selected':'').">более 5-комнат</option></select>
			</td>".
			"<td valign='bottom'>
				от [>] <input name=fld[area][$key][from] type='text' size='5' value='".$val['from']."'> м<sup>2</sup>
			</td>".
			"<td valign='bottom'>
				до [<=] <input name=fld[area][$key][to] type='text' size='5' value='".$val['to']."'> м<sup>2</sup>
			</td>".			
			"<td valign='bottom'>
				<a href='#' onclick='deleteAreaRow(this)'>Удалить</a>
			</td></tr>";
			$counter++;
		}		
		$row['counter'] = $counter;
		
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

    ########################

	function Edit() {
		$id = (int)get('id', 0, 'p');
		$metro_subst = get('metro_subst', array(), 'p');
		$fld = &$GLOBALS['_POST']['fld'];

		$fld['storey'] = serialize(array('storey_from'=>$fld['storey_from'],'storey_to'=>$fld['storey_to']));
		unset($fld['storey_from']);
		unset($fld['storey_to']);
		
		$fld['material'] = serialize($fld['material']);
		$fld['area'] = serialize($fld['area']);
		
		$id = $this->Commit(array('stars'));
		
		$reload = mysql_affected_rows() ? 'window.parent.location.reload()' : '';
		if (!is_int($id)) {
			return $this->Error($id);
		}
		return '<script>alert(\''.$this->str('saved').'\'); '.$reload.'</script>';
	}


}


$GLOBALS['objects_stars'] = &Registry::get('TObjects_stars');
?>