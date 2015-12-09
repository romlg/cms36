<?php

/* $Id: product_type_params.php,v 1.1 2009-02-18 13:09:11 konovalova Exp $
 */

class TProduct_type_params extends TTable {

	var $name = 'product_type_params';
	var $table = 'product_type_params';

	########################

	function Tproduct_type_params() {
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
				'link'	=> "cnt.deleteItems('".$this->name."',null,1)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Типы продуктов',
				'Product types',
			),
			'title_one'	=> array(
				'Тип продукта',
				'Product type',
			),
			'name'	=> array(
				'Название типа',
				'Type name'
			),
			'url'	=> array(
				'URL',
				'URL'
			),
			'title_editform' => array(
				'Название типа',
				'Type name'
			),
			'description' => array(
				'Описание',
				'Description'
			),
			'saved' => array(
				'Тип продукта был сохранен',
				'The product groupp has been saved'
			),
			'param_saved' => array(
				'Название параметра было сохранено',
				'The parma name has been saved'
			),
			'param_names'	=> array(
				'Список параметров для типа',
				'Paramы list 4 type'
			),
			'param_name'	=> array(
				'Название параметра',
				'Param name'
			),
			
			'visible'	=> array(
				'Показывать',
				'Visible',
			),
			'add'	=> array(
				'Добавить параметр',
				'Add param',
			),
			'title_edit_param'	=> array(
				'Редактировать параметр',
				'Edit param',
			),
		));
	}
	
	########################

	function table_get_edit(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		return "<input onkeypress='modified(1)' onpaste='modified(1)' type=text name='row[{$row['id']}][{$column['display']}]' value='$value' size='$size' maxlength='$maxlength' style='text-align: $text_align'><input type=image src='images/s.gif' width=1 height=1>";
	}
	########################

	function Show($product_type_id) {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		if(!$product_type_id) return;

		require_once(core('ajax_table'));
		$ret['thisname'] = $this->name;
		
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'param_names',
				),
				array(
					'select'	=> 'visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'align'		=> 'center',
					'flags'		=> FLAG_SORT,
				),

			),
			'from'	=> 'product_type_params',
			'orderby'	=> 'priority, name',
			'where' => 'product_type_id='.$product_type_id,
			'params'	=> array('page' => 'product_types', 'do' => 'editform', 'id' => $product_type_id),
			'click'	=> 'ID=cb.value',
			'dblclick' => 'editItem(id)',
//			'_sql' => true,
		), $this);

		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################

	function EditForm(){
		$id = (int)get('id','');

		$product_type_id = (int)get('product_type_id','');
		if(!$product_type_id && !$id) return;
		
		if ($id) 
			$row = sql_getRow("SELECT * FROM product_type_params WHERE id=$id");
		else 
			$row['product_type_id'] = (int)get('product_type_id');

		$row['visible_checked'] = isset($row['visible'])?'checked':(!$id?'checked':'');
		$this->AddStrings($row);
		$GLOBALS['title'] = $this->str('title_edit_param');
		$row['id'] = $id;

		$row = $this->Parse($row, $this->name.'.editform.tmpl');
		return $row;
	}

	########################

	function Edit() {
		$res = $this->Commit(array('name', 'product_type_id'), true);
		if (is_int($res)) return "<script>alert('".$this->str('param_saved')."'); top.window.opener.location.reload(); top.window.close()</script>";
		return $this->Error($res);
	}


}

$GLOBALS['product_type_params'] =  & Registry::get('TProduct_type_params');

?>