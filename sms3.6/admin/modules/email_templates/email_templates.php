<?php

class TTemplates extends TTable {

	var $name = 'email_templates';
	var $table = 'email_templates';

	########################

	function TTemplates() {
		global $str, $actions;

		TTable::TTable();	   

		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.mySubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
			),
/*			'versions' => array(
				'Версии текста',
				'Versions',
				'link'	=> 'cnt.frames[0].versions()',
				'img' 	=> 'icon.versions.gif',
				'display'	=> 'none',
			),
*/
			'close' => &$actions['table']['close'],
		);

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

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Шаблоны писем',
				'Email templates',
			),
			'title_one'	=> array(
				'Шаблон письма',
				'Email template',
			),
			'template_id'	=> array(
				'ID темплейта',
				'Template ID',
			),
			'subject'	=> array(
				'Тема',
				'Subject'
			),
			'comment' => array(
				'Комментарий',
				'Comment'
			),
			'updated' => array(
				'Изменен',
				'Modified'
			),
			'private' => array(
				'Личный',
				'Private'
			),
			'type' => array(
				'Тип',
				'Content type',
			),
			'text' =>array(
				'Текст',
				'Text'
			),
			'html' =>array(
				'HTML',
				'HTML'
			),
			'saved' => array(
				'Шаблон письма был сохранен',
				'The email template has been saved'
			),
			'disable_submit' => array(
				'Форма уже отправлена',
				'Form already sent',
			),
		));
	}

	
	########################

	function Show(){
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
					'select'	=> 'id',
					'display'	=> 'template_id',
					'type'		=> 'bold',
				),
				array(
					'select'	=> 'subject',
					'display'	=> 'subject',
				),
				array(
					'select'	=> 'description',
					'display'	=> 'comment',
				),
				array(
					'select'	=> 'IFNULL(user_id,0)',
					'display'	=> 'private',
					'type'		=> 'visible',
					'align'		=> 'center',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(updated)',
					'display'	=> 'updated',
					'type'		=> 'datetime',
					'align'		=> 'right',
				),
			),
			'where'		=> "visible>0 AND (user_id IS NULL OR user_id=".$this->user['id'].")",
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'orderby'	=> 'updated DESC',
			'click'	=> 'ID=cb.value;',
			'dblclick' => 'editItem(id)',

		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');

	}

	########################

	function EditForm(){
		$id = get('id');
		if ($id) {
			$row = $this->GetRow($id);
		}
		else {
			$row['visible'] = 1;
			$row['content_type'] = 'html';
			$row['text'] = '';
			$row['user_id'] = '';
		}
		$row['private_checked'] = $row['user_id'] ? 'checked' : '';
		$row['user_id'] = $GLOBALS['user']['id'];
		$row['options'] = $this->getSetOptions('content_type', $row['content_type']);

		$row['vis_etext'] = $row['content_type']=='text' ? 'show' : 'hide';
		$row['vis_ehtml'] = $row['content_type']=='html' ? 'show' : 'hide';
		###
		include_fckeditor();
		$oFCKeditor = new FCKeditor;
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->Value = $row['text'];
		$row['editor'] = $oFCKeditor->ReturnFCKeditor('editor[html]', '100%', '100%');
		###

		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$editor = get('editor', array(), 'p');
		
		$_POST['fld']['text'] = $editor[$_POST['fld']['content_type']];
		if (!isset($_POST['fld']['user_id'])) $_POST['fld']['user_id'] = 'NULL';
		
		$res = $this->Commit(array('subject'));
		if (is_int($res)) 
			return "<script>alert('".$this->str('saved')."'); window.top.close(); window.top.opener.location.reload();</script>";
		return "<script>alert('".addslashes($res)."'); window.parent.disable_submit=0;</script>";
	}

	########################

	function ShowRecycle() {
		global $limit;

		$limit = -1;
		require_once(core('ajax_table'));

		$this->AddStrings($row);
		$row['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> "description",
					'display'	=> 'comment',
				),
				array(
					'select'	=> "subject",
					'display'	=> 'subject',
				),
			),
			'where'		=> "visible<=0  AND (user_id IS NULL OR user_id=".$GLOBALS['user']['id'].")",
			'orderby'	=> 'updated DESC',
			'params'	=> array('page' => $this->name, 'do' => 'show'),
		), $this);

		return $this->Parse($row, 'recycle.tmpl');
	}

	######################
}

$GLOBALS['email_templates'] =  & Registry::get('TTemplates');
?>