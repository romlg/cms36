<?php

/* $Id: product_types.php,v 1.1 2009-02-18 13:09:11 konovalova Exp $
 */

class TProductTypes extends TTable {

	var $name = 'product_types';
	var $edname = 'product_types';
	var $table = 'product_types';

	########################

	function TProductTypes() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'moveup' => &$actions['table']['moveup'],
			'movedown' => &$actions['table']['movedown'],
			'delete' => array(
				'�������',
				'Delete',
				'link'	=> "cnt.deleteItems('".$this->name."',null,1)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'���� ���������',
				'Product types',
			),
			'title_one'	=> array(
				'��� ��������',
				'Product type',
			),
			'name'	=> array(
				'�������� ����',
				'Type name'
			),
			'title_editform' => array(
				'�������� ����',
				'Type name'
			),
			'description' => array(
				'��������',
				'Description'
			),
			'saved' => array(
				'��� �������� ��� ��������',
				'The product groupp has been saved'
			),
			'param_saved' => array(
				'�������� ��������� ���� ���������',
				'The parma name has been saved'
			),
			'param_names'	=> array(
				'������ ���������� ��� ����',
				'Param� list 4 type'
			),
			'param_name'	=> array(
				'�������� ���������',
				'Param name'
			),
			'visible'	=> array(
				'����������',
				'Visible',
			),
			'generator'	=> array(
				'���������� � ������������',
				'Visible',
			),
			'add_parameter'		=> array('�������� ��������',				'Add param',),
			'add_range'			=> array('�������� ��������',				'Add range',),
		));
		
	}
	
	########################

	function EditForm(){
		$id = (int)get('id');
		if ($id) $row = $this->GetRow($id);
		$this->AddStrings($row);

		# load class
		include_once (module('product_type_params'));
		$row['params'] = $GLOBALS['product_type_params']->Show($id);

		# load class
		include_once (module('product_type_prices'));
		$row['prices'] = $GLOBALS['product_type_prices']->Show($id);

		$row['thisname'] = $this->name;		
		$GLOBALS['title'] = $this->str('title_one');
		$row['id'] = $id;
		$row['visible_checked'] = $row['visible']?'checked':(!$id?'checked':'');
		$trgt = get('trgt');
		$row['target'] = $trgt?$trgt:'tmp'.$this->name;
		
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function EditParamForm(){
		$id = (int)get('id','');
		
		if ($id) $row = sql_getRow("SELECT * FROM product_type_params WHERE id=$id");
		else 
			$row['product_type_id'] = (int)get('product_type_id');
//		pr($row['table']);

		$this->AddStrings($row);
//		$row['thisname'] = 'product_types_param';

		$GLOBALS['title'] = $this->str('title_edit_param');
		$row['id'] = $id;
		$row = $this->Parse($row, 'product_type_param.editform.tmpl');
		return $row;
	}

	########################

	function EditParam() {
		$actions = get('actions', '', 'p');
		if ($actions) return $this->$actions();
		
		$this->table = 'product_type_params';
		$res = $this->Commit(array('name'), true);
//		if (is_int($res)) return "<script>alert('".$this->str('saved')."');</script>";
		if (is_int($res)) return "<script>alert('".$this->str('param_saved')."'); top.window.opener.location.reload(); top.window.close()</script>";
		return $this->Error($res);
	}

	########################

	function Edit() {
		$fld = $_POST['fld'];
		if (empty($fld['priority'])){
			$pr = sql_getRows('SELECT priority FROM product_types ORDER BY priority DESC');
			$fld['priority'] = current($pr)+1;
		}
		$_POST['fld'] = $fld;

		$res = $this->Commit(array('name'), true);
//		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); top.window.opener.location.reload(); top.window.close();</script>";
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
		
		$ret['params'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'priority',
					'type'		=> 'priority',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'description',
					'display'	=> 'description',
					'width'		=> '50%',
				),
				/*array(
					'select'	=> 'priority',
					'display'	=> 'priority',
					'align'		=> 'center',
					'flags'		=> FLAG_SORT,
				),*/
				array(
					'select'	=> 'visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'align'		=> 'center',
					//'flags'		=> FLAG_SORT,
				),
				/*array(
					'select'	=> 'generator',
					'display'	=> 'generator',
					'type'		=> 'visible',
					'align'		=> 'center',
					//'flags'		=> FLAG_SORT,
				),*/
			),
//			'where' => 'visible>0',
			'orderby'	=> 'priority',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'	=> 'ID=cb.value',
			'dblclick' => 'editItem(id)',
		   	//'_sql' => true,
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');
	}
	
	######################
	function product_type_params(){
		$actions = get('actions', '', 'p');
		if ($actions=='Delete')
			$this->DeleteItems(1,'product_type_params');
		return '<script>window.parent.location.reload()</script>';
	}

}

$GLOBALS['product_types'] = & Registry::get('TProductTypes');
?>