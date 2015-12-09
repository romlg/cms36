<?php

/* $Id: create_email.php,v 1.2 2009-08-07 12:45:01 konovalova Exp $
 */

class TCreateEmail extends TTable {

	var $name = 'create_email';
	var $table;

	// ���������� ������ ��� �������� ��� �������� �����
	var $emailing_sleep = 5;

	########################

	function TCreateEmail() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'send' => array(
				'���������',
				'Send',
				'link'	=> 'cnt.mySubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
			),
			'templates' => array(
				'�������',
				'Templates',
				'link'	=> 'cnt.showTemplates()',
				'img' 	=> 'icon.versions.gif',
				'display'	=> 'block',
			),
		);

		$actions[$this->name.'.showrecipients'] = array(
			'add' => array(
				'��������',
				'Add to List',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);

		$str[get_class($this)] = array(
			'title'	=> array(
				'�������� ������',
				'Send E-mail',
			),
			'template_id'	=> array(
				'ID',
				'ID',
			),
			'mailto'	=> array(
				'����',
				'To',
			),
			'client_selection' => array(
				'������� �� ������� ������������� (%d �����������)',
				'Selection from client\'s table (%d recipients)',
			),
			'mailfrom'	=> array(
				'�� ����',
				'From',
			),
			'subject'	=> array(
				'����',
				'Subject',
			),
			'attach'	=> array(
				'������������� ����',
				'Atached File',
			),
			'lang'	=> array(
				'����',
				'Language',
			),
			'description'	=> array(
				'��������',
				'Description',
			),
			'templates'	=> array(
				'������� �����',
				'Templates',
			),
			'recipients'	=> array(
				'����� �����������',
				'Select Recipients',
			),
			'client'	=> array(
				'������',
				'Client',
			),
			'email'	=> array(
				'E-mail',
				'E-mail',
			),
			'registered'	=> array(
				'���������������',
				'Registered',
			),
			'send'	=> array(
				'��������',
				'Mail to',
			),
			'content_type'	=> array(
				'��� ���������',
				'Content type',
			),
			'charset'	=> array(
				'���������',
				'Character Set',
			),
			'disable_submit' => array(
				'����� ��� ����������',
				'Form already sent',
			),
			'e_template' => array(
				'�� ������� ��������� ������',
				'Wrong load template',
			),
			'message_from' => array(
				'������ ��',
				'Message From',
			),
			'sending_finished' => array(
				'�������� ����� ���������. ���������� �����: %d',
				'Sending emails finished: %d message(s) sent',
			),
			'close_window' => array(
				'������� ����',
				'Close window',
			),
			'email_to' => array(
				'����',
				'Email to',
			),
			'result' => array(
				'���������',
				'Result',
			),
			####### ������ #######
			'success'	=> array(
				'�������',
				'Successful',
			),
			'err_mailfrom'	=> array(
				'������ ���� "�� ����"',
				'Empty field "From"',
			),
			'err_text'	=> array(
				'������ ���� "�����"',
				'Empty field "Text"',
			),
			'err_to'	=> array(
				'������ ���� "����"',
				'Empty field "To"',
			),
			'err_iconv'	=> array(
				'��������� ���� �������� ����������� ������� ��� ���� ���������',
				'Text fields contains prohibited characters for this encoding',
			),
			'err_log'	=> array(
				'������ ������ ���� � ��',
				'Database insert error',
			),
			'err_send'	=> array(
				'������ �������� ������',
				'Send E-mail from server error',
			),

		);

	}

	########################

	function Show() {
		$mailto = '';
		$id		= get('id', array(), 'g');
		$use	= get('use', '', 'g');
		if ($id) {
			$emails = $this->GetRows("SELECT id, CONCAT(cont_name, ' ', cont_lname, ' <', email, '>') FROM clients WHERE id IN (".join(',', $id).")", true);
			if (!empty($emails)) $mailto = join(', ', $emails);
		}
		elseif ($use!="blank") {
			$mailto = 'use_client_selection';
			$_SESSION['use_client_selection'] = $_SESSION['client_selection'];
		}

		return $this->CreateEmail($mailto);
	}

	######################

	function CreateEmail($mailto='', $subject='', $text='') {
		$this->AddStrings($row);
		if ($mailto=='use_client_selection' && $_SESSION['use_client_selection']) {
			$client_selection = join(' AND ', $_SESSION['use_client_selection']);
			$row['use_client_selection'] = '1';
			$emails = $this->GetValue("SELECT COUNT(*) FROM clients AS c WHERE subscribe=1 AND ".$client_selection);
            $row['mailto'] = sprintf($this->str('client_selection'), $emails);
			$row['mailto_readonly'] = 'READONLY';
		}
		else {
			$row['mailto'] = htmlspecialchars($mailto);
			$row['use_client_selection'] = '0';
			$row['client_selector']['value'] = ' ';
		}
		$row['subject'] = htmlspecialchars($subject);

		$managers = sql_getRows("SELECT id, CONCAT(fullname,' &lt;',email,'&gt;') FROM admins WHERE email<>'' ".(is_root() ? '' : "AND id IN (".join(',',$this->user['subst']).") AND fullname!=''")." ORDER BY fullname", true);
		$row['managers'] = $this->GetArrayOptions($managers, $this->user['id'], true);

		###
		include("editor/fckeditor.php") ;
		$oFCKeditor = new FCKeditor;
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->Value = $text;
		$row['editor'] = $oFCKeditor->ReturnFCKeditor('editor[html]', '100%', '100%');
		###

		return Parse($row, $this->name.'.tmpl');
	}

	########################

	function ShowTemplates() {
		$data['thisname'] = $this->name;
		$data['close'] = $this->str('close');

		$data['rows'] = $this->GetRows("SELECT id, subject, description FROM email_templates WHERE visible>0 AND (user_id IS NULL OR user_id=".$this->user['id'].") ORDER BY updated DESC");

		if (empty($data['rows']))
			$data['empty'] = $this->str('empty');
		else
			$this->AddStrings($data);

		$GLOBALS['title'] = $this->str('templates');

		return Parse($data, $this->name.'.versions.tmpl');
	}

	######################

	function EditPasteTemplate() {
		# ������ �������, ������� ������ ����� � ���������

		$id = (int)get('id', 0, 'p');

		$tmpl = $this->GetRow("SELECT content_type, text FROM email_templates WHERE id=".$id);
		if (!$tmpl) 
			return "<script>alert('".$this->str('error').": ".$this->str('e_template')."');</script>";

		if ($tmpl['content_type']=='text')
			return '
			<textarea id=tmpl>'.$tmpl['text'].'</textarea>
			<script>
				window.top.frames.cnt.change_editor("text");
				window.top.frames.cnt.document.forms.editform.elements["content_type"].value="text"; 
				window.top.frames.cnt.document.forms.editform.elements["editor[text]"].value=document.all.tmpl.innerHTML;
			</script>';
		else
			return '
			<div id=tmpl>'.$tmpl['text'].'</div>
			<script>
				window.top.frames.cnt.change_editor("html");
				window.top.frames.cnt.document.forms.editform.elements["content_type"].value="html";
				window.top.frames.cnt.frames[0].FCK.SetHTML(document.all.tmpl.innerHTML);
			</script>';
	}

	######################

	function table_get_registered(&$value, &$column, &$row) {
		return ($value ? date(FORMAT_DATE, $value) : $this->str('&minus;'));
	}

	function ShowRecipients() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core_file('table.lib'));
		$ret['thisname'] = $this->name;
		
		$ret['table'] = table(array(
			'from'		=> "clients",
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'CONCAT(cont_name,CHAR(32),cont_lname)',
					'display'	=> 'client',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'email',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'reg_date',
					'display'	=> 'registered',
					'align'		=> 'right',
					'type'		=> 'registered',
				),
			),
			'where'		=> 'visible=1 AND LENGTH(email)>0 '.(is_root() ? '' : 'AND (admin_id IS NULL OR admin_id IN ('.join(',',$this->user['subst']).'))'),
			'orderby'	=> 'id',
			'params'	=> array('page' => $this->name, 'do' => 'showrecipients'),
			'click'		=> 'ID=cb.value;',
			'dblclick'	=> 'PasteRecipients(id)',
		), $this);
		return Parse($ret, $this->name.'.recipients.tmpl');
	}

	########################

	function EditPasteRecipients() {
		$id = get('id', array(), 'p');
		$emails = $this->GetRows("SELECT id, CONCAT(cont_name, ' ', cont_lname, ' <', email, '>') FROM clients WHERE id IN (".join(',', $id).")", true);
		if (!empty($emails)) {
			return "<script>
			if (window.parent.document.forms.editform.elements['fld[mailto]'].value.length > 0)
				window.parent.document.forms.editform.elements['fld[mailto]'].value+=', ".join(', ', $emails)."'
			else
				window.parent.document.forms.editform.elements['fld[mailto]'].value='".join(', ', $emails)."'
			</script>";
		} else return "<script>alert('Error')</script>";
	}

	########################

	function EditSend() {
		@ob_end_clean();
		set_time_limit(0);
		ignore_user_abort(TRUE);
		ob_implicit_flush(1);

		$fld = get('fld', array(), 'p');

		$content_type	= get('content_type', 'html', 'p');
		$use_client_selection = (int)get('use_client_selection', 0, 'p');
		$subject		= '=?utf-8?B?'.base64_encode($fld['subject']).'?=';

		if ($fld['mailfrom']==1)
			$mailfrom = $this->GetMessage('mail_sales');
		else
			$mailfrom = $this->GetValue("SELECT CONCAT(fullname, ' <', email, '>') FROM admins WHERE id=".$fld['mailfrom']);

		echo "
<link rel='stylesheet' type='text/css' href='main.css'>
<table cellpadding=0 cellspacing=2 bgcolor=white width=100% style='border: 1px solid #1C5180; color: white' background='images/xpbox/blue_bg.gif'><tr>
	<td bgcolor=#0F89DA nowrap><img align=absmiddle src='images/xpbox/blue_bg.gif' width=4 height=23 border=0><b>".$this->str('message_from').": ".htmlspecialchars($mailfrom)."</b></td>
	<td bgcolor=#0F89DA align=right><a href='#' onclick='if(opener) opener.focus(); window.close();' HIDEFOCUS><img align=absmiddle src='images/icons/icon.cross.gif' width=16 height=16 hspace=4 border=0 alt='Close window'></a></td>
</tr></table>
		";

		if (empty($mailfrom))
			return $this->SendEmailError('err_mailfrom');

		$text = $_POST['editor'][$content_type];

		if (empty($text))  
			return $this->SendEmailError('err_text');

		# �������� �����
		if (get_magic_quotes_gpc()) {
			$text = stripslashes($text);
		}

		$mailto = array();
		# ���� ��� ������� �� �������
		if ($use_client_selection) {
			if ($_SESSION['use_client_selection']) {
				$clients = $this->GetRows("
					SELECT 
						c.id, c.cont_name, c.cont_lname, c.email, c.lang
					FROM clients AS c
						LEFT JOIN discount_groups AS dgr ON c.discount_group=dgr.id
						LEFT JOIN client_groups AS cgr ON c.client_group=cgr.id
						LEFT JOIN countries AS cntr ON c.country_id=cntr.id
						LEFT JOIN admins AS u ON c.admin_id=u.id
					WHERE 
						c.subscribe=1 AND ".join(' AND ', $_SESSION['use_client_selection']));
				if ($clients) foreach ($clients as $client) {
					$mailto[] = $client['cont_name'].' '.$client['cont_lname'].' <'.$client['email'].'>';
					$lang[] = $client['lang'];
				}
			}
		}
		# ��������� ������ � ������ ������ ����������
		else {
	        $mailto = preg_split("~,\s?~", $fld['mailto']);
			$mailto = array_keys(array_flip($mailto));
		}
		if (!$mailto || empty($mailto[0])) 
			return $this->SendEmailError('err_to');

		// ���� ���� �������, �� ����� ��������� �� �� ��� �������
		if ($use_client_selection) {
			$unsubscribe = $this->GetRow("SELECT text_".join(', text_', array_values($lang)).", text_en FROM messages WHERE grp_name='admin' AND name='mail_unsubscribe_".$content_type."'");
		}


		# ����������� ��� �������� �� ������
		$images = array();
		preg_match_all("~(src|background)\s*=\s*(\"|')(?!java)(?!mail)(?!ftp)(?!http)([^\"'#]+)(#\w+)?(\"|')~i", $text, $m);
		if (!empty($m)) {
			$images = &$m[3];

			# ������ ������ ��������
			foreach ($images as $key=>$val) {
				$names	= explode('/', $val);
				$name	= end($names);
				$text	= str_replace($val, 'cid:'.$name, $text);
				$images[$key] = substr($val, strlen(FILES_URL));
			}
		}

		# ���������� ������ � ������������
		$sent_email = array(
			'user_id'		=> $fld['mailfrom'],
			'subject'		=> $fld['subject'],
			'body'			=> $text,
			'content_type'	=> $content_type,
		);
		$this->table = 'email_sent';
		$email_id = $this->Insert($sent_email);
		if (mysql_error()) 
			return $this->SendEmailError('err_log');

		# ����� ����������� ��������
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

        	# ���������� ��� ��������
			$client_id = $this->GetValue("SELECT id FROM clients WHERE email='".$email_to."'");
			if ($client_id) {
				$this->table = 'email_log';
				$email_log = array(
					'email_id'	=> $email_id,
					'client_id'	=> $client_id,
				);
				$this->Insert($email_log);
				if (mysql_error())
					$this->SendEmailResults($val, 'err_log');
			}
			else 
		    	$this->SendEmailResults($val, 'err_log');

			// ������ ��� �������� ������
			$data = array(
				'{name}'	=> trim($email_name), # ��������� ��� �������
				'{email}'	=> $email_to,
				'{md5_mail}' => md5('unsubscribe'.$email_to),
			);
			// ��������� ������� ��� �������
			if ($use_client_selection) {
				$unsubscribe_text = empty($unsubscribe['text_'.$lang[$key]]) ? 
					$unsubscribe['text_en'] : 
					$unsubscribe['text_'.$lang[$key]];
				$unsubscribe_text =
					($content_type=='html' ? "\n\n<br><br>" : "\n\n")
					.$unsubscribe_text;
			}
			else $unsubscribe_text = '';
			// ������� ������
			$body = str_replace(
				array_keys($data),
				array_values($data),
				$text.$unsubscribe_text
			);

			// ��������
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
		// �������� use_client_selection ����� �� ��������� ��� ����
		$_SESSION['use_client_selection'] = '';
		echo "
<table cellpadding=0 cellspacing=2 bgcolor=white width=100% style='border: 1px solid #1C5180; color: white' background='images/xpbox/blue_bg.gif'><tr>
	<td bgcolor=#0F89DA nowrap><img align=absmiddle src='images/xpbox/blue_bg.gif' width=4 height=23 border=0><b>".sprintf($this->str('sending_finished'), $count)."</b></td>
	<td bgcolor=#0F89DA align=right><a href='#' onclick='if(opener) opener.focus(); window.close();' HIDEFOCUS><img align=absmiddle src='images/icons/icon.cross.gif' width=16 height=16 hspace=4 border=0 alt='".$this->str('close_window')."'></a></td>
</tr></table>
		";
		return "<script>window.opener.disable_submit = 0;</script>";
	}

	########################

	// ����� ������ 
	function SendEmailError($errtext) {
		return '
			<div style="color:#cc0000;"><b>'.$this->str('error').':</b> '.$this->str($errtext).'!</div>
			<script>window.opener.disable_submit = 0;</script>';
	}

	########################

	// ����� �����������
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

$GLOBALS['create_email'] = &new TCreateEmail;

?>