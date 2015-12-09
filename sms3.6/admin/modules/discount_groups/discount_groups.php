<?php

/* $Id: discount_groups.php,v 1.1 2009-02-18 13:09:08 konovalova Exp $
 */

class TDiscountGroups extends TTable {

	var $name = 'discount_groups';
	var $table = 'discount_groups';

	########################

	function TDiscountGroups() {
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
				'link'	=> "cnt.deleteItems('".$this->name."',null,null)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);
//		statuslog($actions);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Группы скидок',
				'Discount groups',
			),
			'title_one'	=> array(
				'Группа скидок',
				'Discount Group',
			),
			'name'	=> array(
				'Имя',
				'Name'
			),
			'title_editform' => array(
				'Заголовок',
				'Title'
			),
			'description' => array(
				'Описание',
				'Description'
			),
			'saved' => array(
				'Группа скидок была сохранена',
				'The discount group has been saved'
			),
			'visible'	=> array(
				'Показывать',
				'Visible',
			),
		));
	}
	
	########################

	function EditForm(){
		$id = (int)get('id');
		if ($id) $row = $this->GetRow($id);
		$GLOBALS['title'] = $this->str('title_one');
		$row['id'] = $id;
		$row['visible_checked'] = $row['visible']?'checked':(!$id?'checked':'');
		$trgt = get('trgt');
		$row['target'] = $trgt?$trgt:'tmp'.$this->name;
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$fld = $_POST['fld'];
		if (empty($fld['priority'])){
			$pr = sql_getRows('SELECT priority FROM discount_groups ORDER BY priority DESC');
			$fld['priority'] = current($pr)+1;
		}
		$_POST['fld'] = $fld;

		$res = $this->Commit(array('name'), true);
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
				),
/*				array(
					'select'	=> 'priority',
					'display'	=> 'priority',
					'type'		=> 'priority',
					'align'		=> 'right',
				),*/
				array(
					'select'	=> 'name',
					'display'	=> 'name',
				),
				array(
					'select'	=> 'description',
					'display'	=> 'description',
					'width'		=> '50%',
				),
				array(
					'select'	=> 'visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'align'		=> 'center',
					'flags'		=> FLAG_SORT,
				),
				
			),
			'orderby'	=> 'priority, name',
//			'where' => 'visible>0',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'	=> 'ID=cb.value',
			'dblclick' => 'editItem(id)',
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');

	}

	######################
}

$GLOBALS['discount_groups'] =  & Registry::get('TDiscountGroups');
?>