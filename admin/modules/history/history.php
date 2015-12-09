<?php

/* $Id: history.php,v 1.1 2009-02-18 13:09:09 konovalova Exp $
 */

class THistory extends TTable {

	var $name = 'history';

	########################

	function THistory() {
		global $actions, $str;

		TTable::TTable();

		$actions[$this->name] = array(
			'close' => array(
				'Закрыть',
				'Close',
				'link'	=> 'if(opener)opener.focus(); window.close()',
				'img' 	=> 'icon.close.gif',
				'display'	=> 'block',
			),
		);

    	$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'История',
				'History',
			),
			'empty'	=> array(
				'Ничего не найдено',
				'Empty result set',
			),
			'description'	=> array(
				'Описание',
				'Description',
			),
			'date'	=> array(
				'Время выполнения',
				'Realization Date',
			),
			'type' => array(
				'Тип',
				'Type',
			),
			'type_note' => array(
				'Заметка',
				'Note',
			),
			'type_order' => array(
				'Заказ',
				'Order',
			),
			'type_mail' => array(
				'Письмо',
				'E-mail',
			),
			'create_order' => array(
				'Новый заказ #',
				'New order #',
			)
		));

		$this->client_id = get('client_id', 0, 'g');
	}

	########################
	
	function table_get_description(&$value, &$column, &$row) {
		if ($row['type']=='order')
			return $this->str('create_order').' '.$value;
		elseif ($row['text'])
			return '
				<div class="hide">'.$row['text'].'</div>
			';
		else
			return $value;
	}

	function table_get_type(&$value, &$column, &$row) {
		return $this->str('type_'.$value);
	}

	function table_get_edit(&$value, &$column, &$row) {
		$edit = '';
		switch ($row['type']) {
			case 'note':
				$edit = "window.open('ced.php?page=notes&client_id=".$this->client_id."&do=editform&id=".$value."', 'editnote', 'width=600, height=550, resizable=1, status=1').focus()";
			break;
			case 'order':
				$edit = "window.open('ced.php?page=orders&do=editform&id=".$value."', 'editorders', 'width=900, height=600, resizable=1, status=1').focus()";
			break;
			case 'mail':
				$edit = "window.open('dialog.php?page=sent_emails&do=editform&id=".$value."', 'editsent_emails', 'width=600, height=606, resizable=1, status=1').focus()";
			break;
		}
		return '<a href="#" onclick="'.$edit.'; return false;"><img src="images/icons/icon.edit.gif" width=16 height=16 border=0 alt="'.$this->str('edit').'"></a>';
	}

	########################

	function Show() {
		$return = '
		<script langauge = "JavaScript">
		function deleteItems()
		{

		}
		</script>
		<script language = "JavaScript">
var Ahistory0 = new Array(0,0,0,0,0,0,0,0,1);
var Ahistory1 = new Array(0,1,1,1,0,0,0,0,1);
var Ahistory2 = new Array(0,1,0,0,0,0,0,0,1);

window.parent.elemActions(\'history\', 0);
</script>
<script language="JavaScript" src="tooltip/tooltip.js"></script>
';

		require_once(core('ajax_table'));
		// Create templorary table
		sql_query("DROP TABLE tmp_history");
		sql_query("CREATE TABLE tmp_history (
			type VARCHAR(50) NOT NULL,
			id INT UNSIGNED NOT NULL,
			date INT UNSIGNED NOT NULL,
			description VARCHAR(255) NOT NULL,
			text TEXT NOT NULL,
			PRIMARY KEY (type, id)
		)");
		// insert notes
		sql_query("INSERT INTO tmp_history SELECT 'note', id, date, name, text FROM notes WHERE client_id=".$this->client_id);
		// insert notes
		sql_query("INSERT INTO tmp_history SELECT 'order', id, order_date, id, '' FROM orders WHERE client_id=".$this->client_id);
		// insert notes
		sql_query("INSERT INTO tmp_history SELECT 'mail', id, UNIX_TIMESTAMP(em.date), em.subject, em.body FROM email_sent AS em LEFT JOIN email_log AS log ON em.id=log.email_id WHERE log.client_id=".$this->client_id);

		$return .= ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'date',
					'display'	=> 'date',
					'type'		=> 'datetime',
					'nowrap'	=> true,
				),
				array(
					'select'	=> 'type',
					'display'	=> 'type',
					'type'		=> 'type',
				),
				array(
					'select'	=> 'description',
					'display'	=> 'description',
					'type'		=> 'description',
					'width'		=> '50%',
				),
				array(
					'select'	=> 'text',
				),
				array(
					'select'	=> 'id',
					'display'	=> 'edit',
					'type'		=> 'edit',
					'width'		=> '30',
					'align'		=> 'center',
				),
			),
			'from'		=> 'tmp_history',	 
			'orderby'	=> 'date DESC',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'client_id' => $this->client_id),
		), $this);
		return $return;
	}

}

$GLOBALS['history'] = & Registry::get('THistory');

?>