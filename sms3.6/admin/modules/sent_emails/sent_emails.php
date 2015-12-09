<?php

class TSentEmails extends TTable {

	var $name = 'sent_emails';
	var $table = 'email_sent';

	########################

	function TSentEmails() {
		global $str, $actions;


		// обязательно вызывать
		TTable::TTable();

		$actions[$this->name] = array(
			'editform' => array(
				'Просмотр',
				'Preview',
				'link'	=> 'cnt.editItem()',
				'img' 	=> 'icon.preview.gif',
				'display'	=> 'none',
			),
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
				'Отправленные письма',
				'Sent Emails'
			),
			'close' => array(
				'Закрыть',
				'Close'
			),
			'preview' => array(
				'Просмотр',
				'Preview'
			),
			'subject'	=> array(
				'Тема',
				'Subject'
			),
			'date'	=> array(
				'Дата',
				'Date'
			),
			'to'	=> array(
				'Кому',
				'To'
			),
			'from'	=> array(
				'От кого',
				'From'
			),
			'text'	=> array(
				'Текст',
				'Text'
			),
			'recipients' => array(
				'получателей',
				'recipients'
			),
			'not_defined' => array(
				'не определен',
				'not defined',
			),
		));
	}
	
	########################

	function table_get_to(&$value, &$column, &$row) {
		if ($value == NULL)
			return $this->str('not_defined');
		elseif ($row['em_count']>1)
			return $row['em_count'].' '.$this->str('recipients');
		else
			return $value;
	}

	function table_get_from(&$value, &$column, &$row) {
		return $value ==1 ? 
			h(sql_getValue("SELECT value FROM strings WHERE name='from_email'")) : 
			$row['u_fullname'];
	}

	########################

	function Show() {
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

		$client_id = get('client_id', 0, 'g');
		$ret['thisname'] = $this->name;

		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'em.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'COUNT(*)',
					'as'		=> 'em_count',
				),
				array(
					'select'	=> 'CONCAT(cl.name,CHAR(32),cl.lname,CHAR(32),"&lt;",cl.login,"&gt;")',
					'as'		=> 'to_field',
					'display'	=> $client_id ? NULL : 'to',
					'type'		=> 'to',
				),
				array(
					'select'	=> 'u.id',
					'display'	=> $client_id || is_root() ? 'from' : NULL,
					'type'		=> 'from',
				),
				array(
					'select'	=> 'em.subject',
					'display'	=> 'subject',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(em.date)',
					'as'		=> 'from_date',
					'display'	=> 'date',
					'type'		=> 'datetime',
				),
				array(
					'select'	=> 'CONCAT(u.fullname,CHAR(32),"&lt;",u.login,"&gt;")',
					'as'		=> 'u_fullname',
				),
			),
			'from'		=>	$this->table.' AS em
							LEFT JOIN email_log AS log ON em.id=log.email_id
							LEFT JOIN auth_users AS cl ON log.client_id=cl.id
							LEFT JOIN users AS u ON em.user_id=u.id',
			'where'		=>	$client_id ?
							'log.client_id='.$client_id : '',
							//(is_root() ? '' : 'em.user_id IN ('.join(',',$this->user['subst']).')'),
			'orderby'	=> 'em.date DESC',
			'groupby'	=> 'em.id',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'client_id' => $client_id),
			'click'	=> 'ID=cb.value;',
			'dblclick' => 'editItem(id)',
//			'_sql' => true,			
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################
	
	function EditForm(){
		$id = (int)get('id');
		if ($id)
			$row = $this->GetRow($id);

		$row['date'] = sql_getValue("SELECT UNIX_TIMESTAMP(date) FROM ".$this->table." WHERE id=".$row['id']);
		$row['date'] = date(FORMAT_DATETIME, $row['date']);
		if ($row['content_type']=='text') $row['body'] = nl2br($row['body']);
		$row['text_tag'] = $row['content_type']=='html' ? 'div' : 'code';

		$row['from'] = $row['user_id'] ==1 ?
			h(sql_getValue("SELECT value FROM strings WHERE pid=1 AND name='from_email'")) : 
			sql_getValue("SELECT CONCAT(fullname,CHAR(32),'&lt;',login,'&gt;') FROM users WHERE id=".$row['user_id']);

		$to = sql_getRows("SELECT cl.id, CONCAT('<nobr>',cl.name,CHAR(32),cl.lname,CHAR(32),'&lt;',cl.login,'&gt;</nobr>') FROM email_log AS e, auth_users AS cl WHERE e.client_id=cl.id AND e.email_id=".$row['id'], true);
		$row['to'] = join('; ', $to);

		$this->AddStrings($row);
		$row['close'] = $this->str('close');
		return $this->Parse($row, $this->name.'.editform.tmpl');

	}

	######################

}

$GLOBALS['sent_emails'] = &Registry::get('TSentEmails');

?>