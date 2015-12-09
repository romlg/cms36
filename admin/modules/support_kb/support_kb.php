<?php

/* $Id: support_kb.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
 */

class TSupport_KB extends TTable {

	var $name = 'support_kb';
	var $table = 'support_kb';
	var $dialog_table = 'support_kb_dialog';

	########################

	function TSupport_KB() {
		global $str, $actions, $do;

		TTable::TTable();

		$GLOBALS['_elems'] = $do == 'showdetails' ? true : NULL; //# trigger ced left menu

		$actions[$this->name] = array(
			'details' => array(
				'Подробнее',
				'Details',
				'link'	=> 'cnt.showDetails()',
				'img' 	=> 'icon.preview.gif',
				'display'	=> 'none',
			),
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);
		$actions[$this->name.'.showdetails'] = array(
			'edit' => &$actions['table']['edit'],
			'create' => array(
				'Написать сообщение',
				'Add Message',
				'link'	=> 'cnt.editItem(0)',
				'img' 	=> 'icon.create.gif',
				'display'	=> 'block',
			),
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteDetails(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);
		$actions[$this->name.'.showcomponentselector'] = array(
			'null' => array(
				'Обнулить',
				'Set Null',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'block',
			),
			'add' => array(
				'OK',
				'OK',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'База знаний',
				'Knowledge Base',
			),
			'id'	=> array(
				'ID №',
				'ID#',
			),
			'created'	=> array(
				'Создано',
				'Created',
			),
			'updated'	=> array(
				'Обновлено',
				'Updated',
			),
			'views'	=> array(
				'Просмотров',
				'Views',
			),
			'category'	=> array(
				'Категория проблемы',
				'Problem Category',
			),
			'group'	=> array(
				'Типы продуктов',
				'Product Group',
			),
			'part'	=> array(
				'Компонент',
				'Part',
			),
			'description'	=> array(
				'Описание',
				'Description',
			),
			'product'	=> array(
				'Компонент',
				'Part',
			),
			'saved'	=> array(
				'Сохранено успешно',
				'Saved successfully',
			),
			'text'	=> array(
				'Текст',
				'Text',
			),
			'created'	=> array(
				'Создано',
				'Created',
			),
			'updated'	=> array(
				'Обновлено',
				'Updated',
			),
			'record'	=> array(
				'Запись',
				'Record',
			),
			'file'	=> array(
				'Присоединить файл',
				'Attach file',
			),
			'attached'	=> array(
				'Присоединенный файл',
				'Attached file',
			),
			'editform'	=> array(
				'Создание/Редактирование отчета в базу знаний',
				'Create/Edit knowledge base report',
			),
		));
	}
	
	########################

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'k.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'flags'		=> FLAG_FILTER,
					'filter_size'	=> 2,
					'filter_maxlength'	=> 6,
				),
				array(
					'select'	=> 'kd.text',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'k.description',
					'display'	=> 'description',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'cat.name',
					'display'	=> 'category',
					'align'		=> 'center',
				),
				array(
					'select'	=> 'g.name',
					'display'	=> 'group',
				),
				array(
					'select'	=> 'IFNULL(p.name,"")',
					'display'	=> 'product',
					'flags'		=> FLAG_SEARCH,
					'nowrap'	=> 1,
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(k.created)',
					'display'	=> 'created',
					'flags'		=> FLAG_SORT,
					'type'		=> 'date',
					'align'		=> 'center',
					'nowrap'	=> 1,
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(k.updated)',
					'display'	=> 'updated',
					'flags'		=> FLAG_SORT,
					'type'		=> 'date',
					'align'		=> 'center',
					'nowrap'	=> 1,
				),
				array(
					'select'	=> 'k.views',
					'display'	=> 'views',
					'flags'		=> FLAG_SORT,
					'align'		=> 'center',
				),
				array(
					'select'	=> 'k.category_id',
					'flags'		=> FLAG_FILTER,
					'filter_type'	=> 'array',
					'filter_value'	=> array('---все---')+sql_getRows('SELECT id, name FROM support_categories ORDER BY priority, name', true),
					'filter_str'	=> false,
					'filter_display'	=> 'category',
				),
				array(
					'select'	=> 'g.id',
					'flags'		=> FLAG_FILTER,
					'filter_type'	=> 'array',
					'filter_value'	=> array('---все---')+sql_getRows('SELECT id, name FROM product_types ORDER BY priority, name', true),
					'filter_str'	=> false,
					'filter_display'	=> 'group',
				),
			),
			//'from'	=> "$this->table as k LEFT JOIN products as p ON p.id=k.product_id LEFT JOIN support_categories as cat ON cat.id=k.category_id LEFT JOIN kb_groups as g ON g.id=k.group_id",
			'from'	=> "support_kb_dialog as kd
			LEFT JOIN $this->table as k ON k.id=kd.kb_id
			LEFT JOIN products as p ON p.id=k.product_id
			LEFT JOIN support_categories as cat ON cat.id=k.category_id
			LEFT JOIN product_types as g ON g.id=p.product_type_id",
			'groupby'	=> 'kd.kb_id',
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'click'		=> 'ID=cb.value;',
			'dblclick'	=> 'showDetails(id)',
			//'_sql'=>1,
		), $this);
		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$row = $this->GetRow($id);
		} else {
			$support_id = (int)get('support_id');
			if ($support_id) $row = sql_getRow("SELECT product_id, category_id, group_id FROM support WHERE id=$support_id");
			else {
				$row['product_id'] = $row['category_id'] = $row['group_id'] = 0;
			}
		}

		$this->AddStrings($row);
		$row['target'] = get('target', $this->name);
		//if (isset($support_id)) $row['thisname'] = 'supportdetails'; # для отправки формы во фрейм саппорта

		$row['categories'] = $this->GetArrayOptions(sql_getRows("SELECT id, name FROM support_categories ORDER BY priority, name", true), $row['category_id'], true);
		$row['product_types'] = $this->GetArrayOptions(sql_getRows("SELECT id, name FROM product_types ORDER BY priority, name", true), "0", true);   //sql_getValue("SELECT product_type_id FROM products WHERE id=".$row['product_id'])
		$row['parts'] = sql_getRow("SELECT id , name FROM products WHERE id=".$row['product_id'], true);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		global $user;

		$id = (int)get('id', 0, 'p');
		if (!$id) $GLOBALS['_POST']['fld']['created'] = 'NULL';

		$res = $this->Commit(array('description'));

		# автоматическое создание записи в диалоге
		if (!$id) {
			if (is_int($res) && $res) sql_query("INSERT $this->dialog_table (kb_id, manager_id, created, text) VALUES ($res, {$user['id']}, NULL, '')");
		}

		$reload = (!sql_getErrNo()) ? "if (window.top.name != '') window.top.location.reload(); else window.parent.location.reload()" : "";
		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); $reload</script>";
		return $this->Error($res);
	}

	###################### ced: left menu

	function GetBasicElement() {
		return array(
			'basic_caption'	=> $this->str('record'),
			'basic_icon'	=> 'box.support.gif',
			'src'		=> $GLOBALS['_SERVER']['QUERY_STRING'],
			'tree'		=> $this->Summary(),
			'rows'		=> array(
				'value' => "<a HIDEFOCUS href='#' onclick='cnt.editSummary();return false;' title=''><img align=absmiddle src='images/icons/icon.edit.gif' width=16 height=16 border=0 hspace=4><b>".$this->str('edit')."</b></a>",
			),
		);
	}

	######################

	function ShowDetails() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

		$GLOBALS['limit'] = -1;
		$ret['id'] = (int)get('id');

		# счетчик просмотров
		sql_query("UPDATE $this->table SET views=views+1 WHERE id=".$ret['id']);

		$ret['description'] = sql_getValue("SELECT description FROM $this->table WHERE id=".$ret['id']);
		$this->AddStrings($ret);

		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'from'	=> "support_kb_dialog as k LEFT JOIN admins as u ON u.id=k.manager_id",
			'columns'	=> array(
				array(
					'select'	=> 'k.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'k.text',
					'display'	=> 'text',
					'type'		=> 'text',
				),
				array(
					'select'	=> 'IF(LENGTH(u.fullname)>0,u.fullname,u.login)',
					'as'		=> 'manager',
				),
				array(
					'select'	=> 'k.file',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(k.updated)',
					'as'		=> 'updated',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(k.created)',
					'as'		=> 'created',
				),
			),
			'where'		=> 'kb_id='.$ret['id'],
			'orderby'	=> 'k.created',
			'params'	=> array('page' => $this->name, 'do' => 'shiwdetails'),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			'target'	=> 'tmp'.$this->name.'details',
			'roll'		=> 0,
		), $this);
		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.details.tmpl');
	}

	######################

	function table_get_text(&$value, &$column, &$row) {
		$file = $row['file'] ? $file = "<div align=right><i>".$this->str('attached').": <a href='".FILES_URL."/{$row['file']}'><b>{$row['file']}</b></a></div></i>" : "";
		$created = "<div align=right><i>".$this->str('created').": ".date(FORMAT_DATETIME, $row['created'])."</i></div>";
		$updated = $row['created'] != $row['updated'] ? "<i><div align=right>".$this->str('updated').": ".date(FORMAT_DATETIME, $row['updated'])."</div></i>" : "";

		if (!$row['manager']) $row['manager'] = "<font color=red>".strtoupper($this->str('customer'))."</font>";

		return "<div><b>".$row['manager']."</b></div>
		<div style='padding: 8px'>$value</div>
		$created
		$updated
		$file
		";
	}

	######################

	function Summary() {
		$id = (int)get('id');
		$row = sql_getRow("
			SELECT
				k.id,
				UNIX_TIMESTAMP(k.created) as created,
				UNIX_TIMESTAMP(k.updated) as updated,
				g.name as `group`,
				p.name as product,
				cat.name as category
			FROM $this->table as k
				LEFT JOIN products as p ON p.id=k.product_id
				LEFT JOIN support_categories as cat ON cat.id=k.category_id
				LEFT JOIN product_types as g ON g.id=p.product_type_id
			WHERE k.id=$id");

		# обработка дат
		$row['updated'] = date(FORMAT_DATETIME, $row['updated']);
		$row['created'] = date(FORMAT_DATETIME, $row['created']);

		foreach ($row as $key=>$val) {
			$ret[] = array(
				'name'	=> $this->str($key),
				'value'	=> $val ? $val : "<span class=loading>n/a</span>",
			);
		}

		return $this->Parse(array('rows' => $ret), 'support.summary.tmpl');
	}

	########################

	function EditDetailForm() {
		$id = (int)get('id');
		$row['kb_id'] = (int)get('kb_id');
		$this->table = $this->dialog_table;
		if ($id) {
			$row = $this->GetRow($id);
		} else {
		}
		$this->AddStrings($row);

		include_fckeditor();

		$oFCKeditor = new FCKeditor;
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->CanUpload = false;
		$oFCKeditor->Value = $id ? $row['text'] : '';
		//$oFCKeditor->BasePath = BASE.'editor/';
		$row['text'] = $oFCKeditor->ReturnFCKeditor('fld[text]', '100%', '100%');

		$row['thisname'] = $this->name;
		$row['save'] = $this->str('save');
		$row['close'] = $this->str('cancel');
		$GLOBALS['title'] = $this->str('title');
		return $this->Parse($row, $this->name.'.editdetailform.tmpl');
	}

	########################

	function EditDetail() {
		global $user;

		# update parent
		$fld = &$GLOBALS['_POST']['fld'];
		sql_query("UPDATE $this->table SET updated=NULL WHERE id=".$fld['kb_id']);

		# set created
		$id = (int)get('id', 0, 'p');
		if (!$id) $fld['created'] = 'NULL';

		$this->table = $this->dialog_table;
		$GLOBALS['_POST']['fld']['manager_id'] = $user['id'];
		$res = $this->Commit(array('text'));
		$reload = (!sql_getErrNo()) ? "window.parent.location.reload()" : "";
		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); $reload</script>";
		return "<script>alert('".$res."');</script>";
	}

	######################

	function DeleteDetails() {
		$this->table = $this->dialog_table;
		$res = $this->DeleteItems();
		if (empty($res)) return "<script>window.parent.location.reload();</script>";
		return $this->Error($res);
	}

	######################

	function ShowComponentSelector() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$prod_id = get('prod_id', '', 'g');
		$temp=array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'component',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'from'		=> "products",
			'where'     => "product_type_id=".$prod_id,
			'orderby'	=> 'id',
			'params'	=> array('page' => $this->name, 'do' => 'showcomponentselector'),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'addToList2()',
			'target'	=> 'tmp'.$this->name.'select',
//			'_sql'=>true,
		);
		if (empty($prod_id) || $prod_id==0){$temp['where']="";}

		$ret['table'] = ajax_table($temp, $this);
		$this->AddStrings($ret);
		return $this->Parse($ret, 'selectwindow.tmpl');
	}
	########################
	function PostSelect2() {
		$id = get('id', array(), 'p');
		$id = array_shift($id);
		$name = sql_getValue("SELECT name FROM products WHERE id=".$id);
		if (!empty($name)) {
			return "<script>
			window.top.opener.document.forms.editform.elements['fld[product_id]'].value=$id;
			window.top.opener.document.forms.editform.component.value='".addslashes($name)."';
			window.top.close();
			</script>";
		}
	}
	########################

}

$GLOBALS['support_kb'] = & Registry::get('TSupport_KB');

?>