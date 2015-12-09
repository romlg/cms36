<?php

class TSend_Email extends TTable {

	var $name = 'send_email';
	var $table;

	// Количество секунд для перерыва при рассылке писем
	var $emailing_sleep = 5;

	########################

	function TSend_Email() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'send' => array(
				'Отправить',
				'Send',
				'link'	=> 'cnt.mySubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
				'show_title'	=> true,
			),
			'templates' => array(
				'Шаблоны',
				'Templates',
				'link'	=> 'cnt.showTemplates()',
				'img' 	=> 'icon.versions.gif',
				'display'	=> 'block',
				'show_title'	=> true,
			),
		);

		$actions[$this->name.'.showrecipients'] = array(
			'add' => array(
				'Добавить',
				'Add to List',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		); 

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Отправка письма',
				'Send E-mail',
			),
			'template_id'	=> array(
				'ID',
				'ID',
			),
			'mailto'	=> array(
				'Кому',
				'To',
			),
			'client_selection' => array(
				'Выборка из таблицы пользователей (%d получателей)',
				'Selection from client\'s table (%d recipients)',
			),
			'mailfrom'	=> array(
				'От кого',
				'From',
			),
			'subject'	=> array(
				'Тема',
				'Subject',
			),
			'attach'	=> array(
				'Прикрепленный файл',
				'Atached File',
			),
			'lang'	=> array(
				'Язык',
				'Language',
			),
			'description'	=> array(
				'Описание',
				'Description',
			),
			'templates'	=> array(
				'Шаблоны писем',
				'Templates',
			),
			'recipients'	=> array(
				'Выбор получателей',
				'Select Recipients',
			),
			'client'	=> array(
				'Клиент',
				'Client',
			),
			'client_login'	=> array(
				'Логин',
				'Login',
			),
			'client_name'	=> array(
				'Имя',
				'Name',
			),
			'email'	=> array(
				'E-mail',
				'E-mail',
			),
			'registered'	=> array(
				'Зарегистрирован',
				'Registered',
			),
			'send'	=> array(
				'Отправка',
				'Mail to',
			),
			'content_type'	=> array(
				'Тип сообщения',
				'Content type',
			),
			'charset'	=> array(
				'Кодировка',
				'Character Set',
			),
			'disable_submit' => array(
				'Форма уже отправлена',
				'Form already sent',
			),
			'e_template' => array(
				'Не удалось загрузить шаблон',
				'Wrong load template',
			),
			'message_from' => array(
				'Письмо От',
				'Message From',
			),
			'sending_finished' => array(
				'Отправка писем завершена. Отправлено писем: %d',
				'Sending emails finished: %d message(s) sent',
			),
			'close_window' => array(
				'Закрыть Окно',
				'Close window',
			),
			'email_to' => array(
				'Кому',
				'Email to',
			),
			'result' => array(
				'Результат',
				'Result',
			),
			####### Ошибки #######
			'success'	=> array(
				'Успешно',
				'Successful',
			),
			'err_mailfrom'	=> array(
				'Пустое поле "От кого"',
				'Empty field "From"',
			),
			'err_text'	=> array(
				'Пустое поле "Текст"',
				'Empty field "Text"',
			),
			'err_to'	=> array(
				'Пустое поле "Кому"',
				'Empty field "To"',
			),
			'err_iconv'	=> array(
				'Текстовые поля содержат запрещенные символы для этой кодировки',
				'Text fields contains prohibited characters for this encoding',
			),
			'err_log'	=> array(
				'Ошибка записи лога в БД',
				'Database insert error',
			),
			'err_send'	=> array(
				'Ошибка отправки письма',
				'Send E-mail from server error',
			),

		));

	}

	########################

	function Show() {
	    $mailto = '';
	    $id = get('id', array(), 'g');
		if ($id) {
			$emails = $this->GetRows("SELECT id, CONCAT(name, ' ', lname, ' <', email, '>') FROM auth_users WHERE id IN (".join(',', $id).")", true);
			if (!empty($emails)) $mailto = join(', ', $emails);
		}
		return $this->Send_Email($mailto);
	}

	######################

	function Show_Selection() {
		$mailto = '';
		$id		= get('id', array(), 'g');
		// Берем из сессии
		$client_selection = get('client_selection', array(), 's');
		if ($id) {
			$emails = sql_getRows("SELECT id, CONCAT(name, ' ', lname, ' <', login, '>') FROM auth_users WHERE id IN (".join(',', $id).")", true);
			if (!empty($emails)) $mailto = join(', ', $emails);
		}
		elseif (!empty($client_selection)) {
			$mailto = 'use_client_selection';
			session_start();
			$_SESSION['use_client_selection'] = $client_selection;
			session_write_close();
		}
		return $this->Send_Email($mailto);
	}

	######################

	function Send_Email($mailto='', $subject='', $text='') {
		$this->AddStrings($row);
		// Берем из сессии
		$use_client_selection = get('use_client_selection', array(), 's');
		
		if ($mailto=='use_client_selection' && $use_client_selection) {
			$client_selection = join(' AND ', $use_client_selection);
			$row['use_client_selection'] = '1';
			$emails = $this->GetValue("SELECT COUNT(*) FROM auth_users AS c WHERE subscribe=1 AND ".$client_selection);
				$row['mailto'] = sprintf($this->str('client_selection'), $emails);
			$row['mailto_readonly'] = 'READONLY';
		}
		else {
			$row['mailto'] = h($mailto);
			$row['use_client_selection'] = '0';
			$row['client_selector']['value'] = ' ';
		}
		$row['subject'] = h($subject);

		$managers = sql_getRows("SELECT id, CONCAT(fullname,' &lt;',email,'&gt;') FROM admins WHERE email<>'' "." AND fullname!='' ORDER BY fullname", true);
		$row['managers'] = $this->GetArrayOptions($managers, $this->user['id'], true);
		###
		include_fckeditor();
		$oFCKeditor = new FCKeditor;
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->Value = $text;
		$row['editor'] = $oFCKeditor->ReturnFCKeditor('editor[html]', '100%', '100%');
		###

		return $this->Parse($row, $this->name.'.tmpl');
	}

	########################

	function ShowTemplates() {
		$data['thisname'] = $this->name;
		$data['close'] = $this->str('close');

		$data['rows'] = sql_getRows("SELECT id, subject, description FROM email_templates WHERE visible>0 AND (user_id IS NULL OR user_id=".$this->user['id'].") ORDER BY updated DESC");

		if (empty($data['rows']))
			$data['empty'] = $this->str('empty');
		else
			$this->AddStrings($data);

		$GLOBALS['title'] = $this->str('templates');
			 // pr($data);
		return $this->Parse($data, $this->name.'.versions.tmpl');
	}

	######################

	function EditPasteTemplate() {
		# выдача скрипта, который меняет текст в редакторе

		$id = (int)get('id', 0, 'p');

		$tmpl = sql_getRow("SELECT subject, content_type, text FROM email_templates WHERE id=".$id);

		if (!$tmpl)
			return "<script>alert('".$this->str('error').": ".$this->str('e_template')."');</script>";


		if ($tmpl['content_type']=='text')
			return '
			<textarea id=tmpl>'.$tmpl['text'].'</textarea>
			<script>
			window.parent.document.forms.editform.elements["fld[subject]"].value="'.addslashes($tmpl['subject']).'";
			window.parent.document.forms.editform.elements["editor[text]"].value=document.all.tmpl.innerHTML;
			window.parent.change_editor("text");
			window.parent.document.forms.editform.elements["content_type"].value="text";
			</script>';
		else
			return '
			<div id=tmpl>'.$tmpl['text'].'</div>
			<script>
			window.parent.change_editor("html");
			window.parent.document.forms.editform.elements["content_type"].value="html";
			window.parent.document.forms.editform.elements["fld[subject]"].value="'.addslashes($tmpl['subject']).'";
			window.parent.frames[0].FCK.SetHTML(document.all.tmpl.innerHTML);
			</script>';
	}

	######################

	function table_get_registered(&$value, &$column, &$row) {
		return ($value ? date(FORMAT_DATE, $value) : $this->str('&minus;'));
	}

	function ShowRecipients() {
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
			'from'		=> "auth_users",
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'login',
					'display'	=> 'client_login',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'name',
					'display'	=> 'client_name',
					'flags'		=> FLAG_SEARCH,
				),
				/*array(
					'select'	=> 'email',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),*/
				array(
					'select'	=> 'subscribe',
					'display'	=> 'subscribe',
					'flags'		=> FLAG_SORT,
					'align'		=> 'center',
					'type'		=> 'visible',
				),
			),
			/*'where'		=> 'visible=1 AND LENGTH(email)>0 '.(is_root() ? '' : 'AND (admin_id IS NULL OR admin_id IN ('.join(',',$this->user['subst']).'))'), */
			//'where'		=> 'LENGTH(email)>0 ',
			'where'		=> 'LENGTH(login)>0 ',
			'orderby'	=> 'id',
			'params'	=> array('page' => $this->name, 'do' => 'showrecipients'),
			'click'		=> 'ID=cb.value;',
			'dblclick'	=> 'PasteRecipients(id)',
//		  	'_sql'=>1,
		), $this);

		return $this->Parse($ret, $this->name.'.recipients.tmpl');
	}

	########################

	function EditPasteRecipients() {
		$id = get('id', array(), 'p');
		$emails = sql_getRows("SELECT id, CONCAT(name, ' ', lname, ' <', login, '>') FROM auth_users WHERE id IN (".join(',', $id).")", true);

		if (!empty($emails)) {
			return "<script>
			if (window.parent.document.forms.editform.elements['fld[mailto]'].value.length > 0)
				window.parent.document.forms.editform.elements['fld[mailto]'].value+=', ".join(', ', $emails)."'
			else
				window.parent.document.forms.editform.elements['fld[mailto]'].value='".join(', ', $emails)."'
			</script>";
		} else return "<script>alert('Error:".mysql_error()."')</script>";
	}

	########################

	function EditSend() {
		session_start();
		@ob_end_clean(); @ob_end_clean();
		set_time_limit(0);
		ignore_user_abort(TRUE);
		ob_implicit_flush(1);

		$fld = get('fld', array(), 'p');

		$content_type	= get('content_type', 'html', 'p');
		$use_client_selection = (int)get('use_client_selection', 0, 'p');
		$subject		= '=?utf-8?B?'.base64_encode($fld['subject']).'?=';

		if ($fld['mailfrom']==1)
			$mailfrom = sql_getValue("SELECT value FROM strings WHERE pid=1 AND name='robot_email'");
		else
			$mailfrom = sql_getValue("SELECT CONCAT(fullname, ' <', email, '>') FROM admins WHERE id=".$fld['mailfrom']);


		echo "
<link rel='stylesheet' type='text/css' href='main.css'>
<table cellpadding=0 cellspacing=2 bgcolor=white width=100% style='border: 1px solid #1C5180; color: white' background='images/xpbox/blue_bg.gif'><tr>
	<td bgcolor=#0F89DA nowrap><img align=absmiddle src='images/xpbox/blue_bg.gif' width=4 height=23 border=0><b>".$this->str('message_from').": ".h($mailfrom)."</b></td>
	<td bgcolor=#0F89DA align=right><a href='#' onclick='if(opener) opener.focus(); window.close();' HIDEFOCUS><img align=absmiddle src='images/icons/icon.cross.gif' width=16 height=16 hspace=4 border=0 alt='Close window'></a></td>
</tr></table>
		";

		if (empty($mailfrom))
			return $this->SendEmailError('err_mailfrom');

		$text = $_POST['editor'][$content_type];

		if (empty($text))
			return $this->SendEmailError('err_text');

		# дурацкие слеши
		if (get_magic_quotes_gpc()) {
			$text = stripslashes($text);
		}

		$mailto = array();
		
		# если это выборка из таблицы
		// Берем из сессии
		$client_selection = get('use_client_selection', array(), 's');

		if ($use_client_selection) {
			if ($client_selection) {
				$clients = sql_getRows("
					SELECT
						c.id, c.name, c.lname, c.login
					FROM auth_users AS c

					WHERE
						c.subscribe=1 AND ".join(' AND ', $client_selection));
				if ($clients) foreach ($clients as $client) {
					$mailto[] = $client['name'].' '.$client['lname'].' <'.$client['login'].'>';
				}
			}
		}
		# разбиваем адреса и делаем список уникальным
		else {
			  $mailto = preg_split("~,\s?~", $fld['mailto']);
			$mailto = array_keys(array_flip($mailto));
		}
		if (!$mailto || empty($mailto[0]))
			return $this->SendEmailError('err_to');

		// Если есть выборка, то берем сообщение из БД для отписки
		if ($use_client_selection) {
			//nsubscribe = $this->GetRow("SELECT value FROM strings WHERE  AND name='mail_unsubscribe'");
//			$unsubscribe = "To unsubscribe goto URL: http://".$_SERVER['HTTP_HOST']."/client/unsubscribe/{md5_mail}";
		}


		# вытаскиваем все картинки из текста
		$images = array();
		preg_match_all("~(src|background)\s*=\s*(\"|')(?!java)(?!mail)(?!ftp)(?!http)([^\"'#]+)(#\w+)?(\"|')~i", $text, $m);
		if (!empty($m)) {
			$images = &$m[3];

			# меняем адреса картинок
			foreach ($images as $key=>$val) {
				$names	= explode('/', $val);
				$name	= end($names);
				$text	= str_replace($val, 'cid:'.$name, $text);
				$images[$key] = substr($val, strlen(FILES_URL));
			}
		}

		# записываем письмо в отправленные
		$sent_email = array(
			'user_id'		=> $fld['mailfrom'],
			'subject'		=> $fld['subject'],
			'body'			=> $text,
			'content_type'	=> $content_type,
		);
		$this->table = 'email_sent';
		$email_id = sql_insert($this->table, $sent_email);
		if (sql_getError())
			return $this->SendEmailError('err_log');

		# вывод результатов отправки
		echo "
<table cellpadding=2 cellspacing=2 bgcolor=white width=100%><tr><td bgcolor=#E5E5E5 style='border: 1px solid #C7C7C7' class=mbox>
	<table cellpadding=0 cellspacing=0 border=0><tr>
		<td><b>0%</b></td>
		<td width=100%><img id=graf src='images/stat/graf.gif' width=0 height=20 hspace=10></td>
		<td align=right><b>100%</b></td>
	</tr></table>
</td></tr></table>
		";
		echo "
<table cellpadding=2 cellspacing=2 bgcolor=white width=100%><tr>
	<td bgcolor=#E5E5E5 style='border: 1px solid #C7C7C7' class=mbox width=50%><b>".$this->str('email_to')."</b></td>
	<td bgcolor=#E5E5E5 style='border: 1px solid #C7C7C7' class=mbox width=50% align=right><b>".$this->str('result')."</b></td></td>
</tr></table>
		";
		flush();
		
		$count = 0;
		$mailto_count = count($mailto);
		foreach ($mailto as $key=>$val) {
			$graph = round(($key+1)/$mailto_count*100);

			preg_match("~([\w\-.]+@[\w\-.]+)?([^<]*)<?([\w\-.]+@[\w\-.]+)?>?~", $val, $m);
			if (isset($m[3]) && $m[3]) {
				$email_to	= $m[3];
				$email_name	= $m[2];
				$to = '"=?utf-8?B?'.base64_encode($email_name).'?=" <'.$email_to.'>';
			} elseif (isset($m[1]) && $m[1]) {
				$email_to	= $m[1];
				$email_name	= substr($m[1], 0, strpos($m[1], '@'));
				$to	= $email_to;
			} else {
				$this->SendEmailResults($val, 'err_to', $graph);
				continue;
			}

			# записываем лог отправки
			$client_id = sql_getValue("SELECT id FROM auth_users WHERE login='".$email_to."'");
			if ($client_id) {
				$this->table = 'email_log';
				$email_log = array(
					'email_id'	=> $email_id,
					'client_id'	=> $client_id,
				);
				sql_insert($this->table, $email_log);
				if (sql_getError())
					$this->SendEmailResults($val, 'err_log');
			}
			else
				$this->SendEmailResults($val, 'err_log');

			// данные для парсинга письма
			$data = array(
				'{name}'	=> trim($email_name), # параметры для шаблона
				'{email}'	=> $email_to,
				'{md5_mail}' => md5('unsubscribe'.$email_to),
			);
			// Добавляем строчку для отписки
/*			if ($use_client_selection) {
				$unsubscribe_text = empty($unsubscribe['text_'.$lang[$key]]) ?
					$unsubscribe['text_en'] :
					$unsubscribe['text_'.$lang[$key]];
				$unsubscribe_text =
					($content_type=='html' ? "\n\n<br><br>" : "\n\n")
					.$unsubscribe_text;
			}
			else 
*/
			$unsubscribe_text = '';
			// парсинг письма
			$body = str_replace(
				array_keys($data),
				array_values($data),
				$text.$unsubscribe_text
			);

			// отправка
			$res = SendMail(
				$mailfrom,
				$to,
				$subject,
				strip_tags($body),
				$content_type=='html' ? $body : '',
				'utf-8',
				$fld['attach'],
				$images
			);

			if ($res) {
				$count++;
				$this->SendEmailResults($val, '', $graph);
			 }
			else {
				$this->SendEmailResults($val, 'err_send', $graph);
			}

			if ($mailto_count - $key > 1)
				sleep($this->emailing_sleep);
		}
		// Обнуляем use_client_selection чтобы не отправили два раза
		$_SESSION['use_client_selection'] = '';
		session_write_close();

		echo "
<table cellpadding=0 cellspacing=2 bgcolor=white width=100% style='border: 1px solid #1C5180; color: white' background='images/xpbox/blue_bg.gif'><tr>
	<td bgcolor=#0F89DA nowrap><img align=absmiddle src='images/xpbox/blue_bg.gif' width=4 height=23 border=0><b>".sprintf($this->str('sending_finished'), $count)."</b></td>
	<td bgcolor=#0F89DA align=right><a href='#' onclick='if(opener) opener.focus(); window.close();' HIDEFOCUS><img align=absmiddle src='images/icons/icon.cross.gif' width=16 height=16 hspace=4 border=0 alt='".$this->str('close_window')."'></a></td>
</tr></table>
		";
		return "<script>window.opener.disable_submit = 0;</script>";
	}

	########################

	// Вывод ошибки
	function SendEmailError($errtext) {
		return '
			<div style="color:#cc0000;"><b>'.$this->str('error').':</b> '.$this->str($errtext).'!</div>
			<script>window.opener.disable_submit = 0;</script>';
	}

	########################

	// Вывод результатов
	function SendEmailResults($email, $error='', $graph=0) {
		echo "
<table cellpadding=2 cellspacing=2 bgcolor=white width=100%><tr>
	<td bgcolor=#E5E5E5 style='border: 1px solid #C7C7C7' class=mbox width=50%>".htmlspecialchars($email)."</td>
	<td bgcolor=#E5E5E5 style='border: 1px solid #C7C7C7' class=mbox  width=50% align=right>".($error?
		'<div style="color:#cc0000;">'.$this->str($error).'!</div>' :
		'<div style="color:#009900;">'.$this->str('success').'</div>' )."
	</td></td>
</tr></table>
		";
		if ($graph)
			 echo "<script>document.all.graf.style.width='".$graph."%'</script>";
		flush();
	}

	########################
}

$GLOBALS['send_email'] = &Registry::get('TSend_Email');

?>