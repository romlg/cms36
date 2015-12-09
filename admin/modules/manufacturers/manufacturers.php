<?php

/* $Id: manufacturers.php,v 1.1 2009-02-18 13:09:09 konovalova Exp $
 */

class Tmanufacturers extends TTable {

	var $name = 'manufacturers';
	var $table = 'manufacturers';

	########################

	function Tmanufacturers() {
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
				'link'	=> "cnt.deleteItems('".$this->name."',null,0)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);
//		statuslog($actions);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Производители',
				'Manufacturers',
			),
			'title_one'	=> array(
				'Производитель',
				'Manufacturers',
			),
			'name'	=> array(
				'Имя',
				'Name'
			),
			'title_editform' => array(
				'Имя производителя',
				'Title'
			),
			'site' => array(
				'Сайт',
				'Site'
			),
			'text' => array(
				'Описание',
				'text'
			),
			'description' => array(
				'Описание',
				'Description'
			),
			'saved' => array(
				'Производитель сохранен',
				'Manufacturer has been saved'
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
		$row['visible_checked'] = isset($row['visible'])?'checked':(!$id?'checked':'');		
		$trgt = get('trgt');
		$row['target'] = $trgt?$trgt:'tmp'.$this->name;

		include_fckeditor();
		$oFCKeditor = &new FCKeditor();
		$oFCKeditor->ToolbarSet = 'Small';
		$oFCKeditor->CanUpload = false;
		$oFCKeditor->Value = isset($row['description']) ? $row['description'] : '';
		$row['description'] = $oFCKeditor->ReturnFCKeditor('fld[description]', '100%', '275');
		
		
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
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
					'width'		=> '1px',
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
/*				array(
					'select'	=> 'description',
					'display'	=> 'description',
					'width'		=> '50%',
				),*/
				array(
					'select'	=> 'visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'align'		=> 'center',
					'width'		=> '1px',
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

$GLOBALS['manufacturers'] = & Registry::get('Tmanufacturers');

?>