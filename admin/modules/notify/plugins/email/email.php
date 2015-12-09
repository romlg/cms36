<?php
require_once(NOTIFY_DIR.'plugins/sender.php');

class TEmail extends TTable {

	var $name = 'email';
	var $dir = '';
	var $params;
	//-------------------------------------------------------------
	function init(&$params){
		$this->params = $params;
		$this->dir = NOTIFY_DIR.'plugins/'.$this->name;
		$this->LoadStrings();
	}

	//-------------------------------------------------------------

	function LoadStrings(){
		global $str;
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title' => array('Отправка e-mail', 'Send e-mail'),
			'mailfrom' => array('От кого', 'From'),
			'subject' => array('Тема', 'Subject'),
			'attach' => array('Прикрепленный файл', 'Attachment'),
			'content_type' => array('Тип сообщения', 'Type of message'),
			'mailto' => array('Кому', 'Mail to'),
			'client_login' => array('Логин', 'Login'),
			'client_name' => array('Имя', 'Name'),
			'email' => array('e-mail', 'e-mail'),
			'sender' => array('Отправитель', 'Sender'),
			'error' => array('ошибка', 'error'),
			'err_notext' => array('Не указан текст письма', 'The text of the letter is not indicated'),
			'err_noadmemail' => array('Не указан email менеджера', 'Is not indicated email the manager'),
			'err_noemails' => array('Не указаны адреса для отправки', 'Addresses for the sending are not indicated'),
			'mail_good' => array('Успешно', 'Successfully'),
			'mail_bad' => array('Не удачно', 'It is not successful'),
			'sended' => array('Сообщений отправлено', 'Communications it is sent'),
			'template_id'	=> array(
				'ID',
				'ID',
			),
			'description'	=> array(
				'Описание',
				'Description',
			),
		));
	}

	//-------------------------------------------------------------

	function LoadActions(){
		global $actions;
		//всегда указываем 'send.showplugin', менять можно только сами экшены
		$actions['send.showplugin'] = array(
			'add' => array(
				'Добавить',
				'Add to List',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);
	}

	//-------------------------------------------------------------

	function getContent(){
		$data = array();
		$func = get('func','','pg');

		if (!empty($func)){
			$func = get('func','','pg');
			if(!empty($func)){
				$data = $this->$func();
			}
		} else {
			global $user;
			$data['current_user'] = $user['id'];
			$data['managers'] = sql_getRows("SELECT id, CONCAT(login,' (',email,') ') as name FROM admins", true);
			$this->AddStrings($data);
			include_fckeditor();
			$oFCKeditor = new FCKeditor;
			$oFCKeditor->ToolbarSet = 'Common';
			$oFCKeditor->Value = '';
			$data['editor'] = $oFCKeditor->ReturnFCKeditor('fld[html]', '100%', '100%');
			$this->AddStrings($data);
		}

		$tpl = strtolower($this->dir.'/'.$this->name.((!empty($func))?'.'.$func:'').'.tmpl');
		if (is_file($tpl)){
			return Parse($data, $tpl);
		}
		else {
			return $data;
		}
	}

	//-------------------------------------------------------------

	function ShowRecipients() {

		$data = sql_getRows("SELECT id, login, fullname FROM admins");
		require_once(core('ajax_table'));

		$ret['thisname'] = $this->name;
		$ret['thisname2'] = 'send';
		$ret['table'] = ajax_table(array(
			'from'		=> "auth_users",
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'width'		=> '1px',
				),
				array(
					'select'	=> 'login',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'CONCAT(name," ",lname)',
					'display'	=> 'client_name',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'where'		=> 'root_id='.domainRootId(),
			'orderby'	=> 'id',
			'params'	=> array(
				'page' => 'notify/send',
				'do' => 'showplugin',
				'func' => 'showrecipients',
				'plugin' => 'email',
			),
			'click'		=> 'ID=cb.value;',
			'target'	=> 'tmpemailshowrecipients',
			'dblclick'	=> 'PasteRecipients(id)',
		  	//'_sql'=>1,
		), $this);
		return $ret;
	}

	//-------------------------------------------------------------

	function EditPasteRecipients(){
		$id = get('id','','p');
		$id = implode(',', $id);
		$users = sql_getRows("SELECT CONCAT(name,' ',lname) as name, CONCAT('<',login,'>') as email FROM auth_users WHERE id IN(".$id.")");
		$str = '';
		foreach ($users as $k=>$v){
			$str .= ','.$v['name'].$v['email'];
		}
		return "
		<script>
			mailto = top.opener.document.getElementById('mailto');
			if (mailto.value == ''){
				mailto.value = mailto.value+'".substr($str,1)."';
			} else {
				mailto.value = mailto.value+'".$str."';
			}
			top.window.close();
		</script>
		";
	}

	//-------------------------------------------------------------

	function ShowRecipientsAdmins() {

		$data = sql_getRows("SELECT id, login, fullname FROM admins");
		require_once(core('ajax_table'));

		$ret['thisname'] = $this->name;
		$ret['table'] = ajax_table(array(
			'from'		=> "admins",
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
					'width'		=> '1px',
				),
				array(
					'select'	=> 'login',
					'display'	=> 'client_login',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'email',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'where'		=> 'LENGTH(email)>0 ',
			'orderby'	=> 'id',
			'params'	=> array(
				'page' => 'notify/send',
				'do' => 'showplugin',
				'func' => 'ShowRecipientsAdmins',
				'plugin' => 'email',
			),
			'click'		=> 'ID=cb.value;',
			'target'	=> 'tmpemailshowrecipients',
			'dblclick'	=> 'PasteRecipientsAdmins(id)',
		  	//'_sql'=>1,
		), $this);
		//pr($ret);
		return $ret;
	}

	//-------------------------------------------------------------

	function EditPasteRecipientsAdmins(){
		$id = get('id','','p');
		$id = implode(',', $id);
		$users = sql_getRows("SELECT id, fullname, email FROM admins WHERE id IN(".$id.")");
		foreach ($users as $k=>$v){
			$users['id'][] = $v['id'];
			$users['fullname'][] = $v['fullname'];
			$users['email'][] = $v['email'];
		}
		$ids = 'var id=['.implode(',', $users['id']).'];';
		$fullname = 'var fullname=[\''.htmlspecialchars(implode('\',\'', $users['fullname'])).'\'];';
		$email = 'var email=[\''.htmlspecialchars(implode('\',\'', $users['email'])).'\'];';
		return "
		<script>
		".$ids.$fullname.$email."
		for (i in id){
			insertRow(id[i],fullname[i],email[i]);
		}


		function insertRow(id,fullname,email){
			if (!window.top.opener.document.getElementById('email_'+id)){
				var table = window.top.opener.document.getElementById('email');
				tr = table.insertRow();
				td1 = tr.insertCell();
				td2 = tr.insertCell();
				td3 = tr.insertCell();
				tr.className='out';
				tr.id='email_'+id;
				td1.innerHTML=fullname;
				td2.innerHTML=email+'<input type=\"hidden\" name=\"fld[admins][email][]\" value=\"'+id+'\">';
				td3.style.width = '50px';
				td3.style.textAlign = 'center';
				td3.innerHTML='<img src=\"images/icons/icon.close.gif\" width=\"16\" height=\"16\"  border=\"0\" onclick=\"deleteRow(\'email_'+id+'\')\"/>';
				window.top.opener.setEvent('email_'+id);
			}
		}
		top.window.close();
		</script>
		";

	}

	//-------------------------------------------------------------
	function send(){
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$fld = get('fld',array(),'p');


		echo "<script>

			  window.onload = function(){
			  	 load();
			  }

			  function load(){
			  	try {";
		// получаем менеджера
		if (!empty($fld['mailfrom'])){
			$manager = sql_getRow("SELECT CONCAT(login,'<',email,'>') as name,email,fullname FROM admins WHERE email is not null and id=".$fld['mailfrom']);
			if (!$manager['email']){
				echo "parent.inRow('<b>".$this->str('error')."</b>','<font color=\"Red\"><b>".$this->str('err_noadmemail')."</b></font>');";
			}
			$mailfrom = $manager['name'];
		}


		echo "window.top.document.getElementById('name').innerText = '".$mailfrom."';";

		// делаем список получателей уникальным
		$fld['mailto'] = preg_split("~,\s?~", $fld['mailto']);
		$fld['mailto'] = array_keys(array_flip($fld['mailto']));
		if (empty($fld['mailto']) || (empty($fld['mailto']['0']) & !isset($fld['mailto']['1']))){
			echo "parent.inRow('<b>".$this->str('error')."</b>','<font color=\"Red\"><b>".$this->str('err_noemails')."</b></font>');";
			$fld['mailto'] = array();
		}
		//перегруппировываем список для отправки
		foreach ($fld['mailto'] as $k=>$v){
			preg_match("/(.*?)<(.*?)>/im", $v, $s);
			if (isset($s[2]) & isset($s[1])){
				$mailto[$k] = array($s[2] => $s[1]);
			} else {
				$mailto[$k] = array($v => '');
			}
		}

		if (!empty($fld['text']) || !empty($fld['html'])){
			$total = count($fld['mailto']);
			//вырезаем имя для прикрепленных файлов
			if (!empty($fld['attach'])){
				preg_match("/(([а-яa-z0-9_\[\]-]*?)\.(.*){3,4})$/i",$fld['attach'], $name);
				if (!empty($name[1])){$attach = $name[1];}
			}

			# вытаскиваем все картинки из текста
			$images = array();
			$fld[$fld['type']] = str_replace("\\\"", '"', $fld[$fld['type']]);
			$save_text = $fld[$fld['type']];
			preg_match_all("~(src|background)\s*=\s*(\"|')(?!java)(?!mail)(?!ftp)(?!http)([^\"'#]+)(#\w+)?(\"|')~i", $fld[$fld['type']], $m);
			if (!empty($m)) {
				$images = &$m[3];

				# меняем адреса картинок
				foreach ($images as $key=>$val) {
					$names	= explode('/', $val);
					$name	= end($names);
					$fld[$fld['type']] = str_replace($val, 'cid:'.$name, $fld[$fld['type']]);
					$embedded[] = array(
						'name' => $name,
						'path' => '..'.$val,//путь необходим вида ../files/blablabla
						'cid' => $name,
					);
				}
			}

			$sender = new TSender();
			$sender->params = $this->params;

			foreach ($fld['mailto'] as $k=>$v){
				$data = array(
					'from' => (!empty($manager['email'])) ? $manager['email'] : sql_getValue("SELECT value FROM strings WHERE name='site_email'"),
					'fromName' => (!empty($manager['fullname'])) ? $manager['fullname'] : $_SERVER["HTTP_HOST"],
					'subj' => $fld['subject'],
					'to' => $mailto[$k],
					'mess' => array(
						'html' => ($fld['type'] == 'html' & isset($fld['html']) & !empty($fld['html'])) ? "<HTML><HEAD><META content=\"MSHTML 6.00.2800.1106\" name=GENERATOR></HEAD><BODY>".$fld['html']."</BODY></HTML>": '',
						'text' => ($fld['type'] == 'text' &isset($fld['text']) & !empty($fld['text'])) ? $fld['text']: '',
					),
				);

				if (isset($attach)){
					$data['attachment'] = array(
						array(
							'name' => $attach,
							'path' => '..'.$fld['attach'],
							'binary' => '',
						)
					);
				}
				if (isset($embedded)){$data['embedded'] = $embedded;}
				//pr($data);
				if ($sender->emailSend($data)){
					foreach ($mailto[$k] as $email=>$name){
						sql_query("INSERT INTO notify_sent(`plugin`, `whom`,`text`) VALUES('email','".e($name)."<".e($email).">','".e($save_text)."')");
					}
					echo "parent.inRow('<b>".$v."</b>','<font color=\"Green\"><b>".$this->str('mail_good')."</b></font>');";
				} else {
					echo "parent.inRow('<b>".$v."</b>','<font color=\"Red\"><b>".$this->str('mail_bad')."</b></font>');";
				}
				echo "window.top.run('".(($k+1)/$total)."');";
			}

			echo "window.top.document.getElementById('send_footer').innerHTML = '<b>".$this->str('sended')." : ".($k+1)."</b>';";

		} else {
			echo "parent.inRow('<b>".$this->str('error')."</b>','<font color=\"Red\"><b>".$this->str('err_notext')."</b></font>');";
		}

		echo "    }
				  catch(e){}
				  finally{}
			  }
			  </script>";

		return $result;
	}

	//-------------------------------------------------------------
	// функция генерит массив для отправки
	function genParams($event, $user_id, $text, $plugin, $tmpl, $file_dir, $root_id){

	    // определяем получателя
	    if ($event['recipient'] == 'client'){
	        $mail_to = sql_getRow("SELECT login as email,name FROM auth_users WHERE login is not null and id=".$user_id);
	        $to = array(
	        $mail_to['email'] => $mail_to['name'],
	        );

	    } else if ($event['recipient'] == 'admin'){
	        if (is_array($user_id) && !empty($user_id) && isset($user_id[$plugin]) && !empty($user_id[$plugin])) {
	            $to = $user_id[$plugin];
	        }
	        else {
	            $to = sql_getRows("SELECT u.email, u.fullname FROM notify_admins AS na LEFT JOIN admins as u ON na.admin_id=u.id WHERE na.event=".$event['id']." AND na.type='email' ".($root_id ? "AND na.root_id=".ROOT_ID : ""), true);
	        }
	    }

		// проверяем, указан ли email получателя
		if (!empty($to)){

			//вырезаем имя для прикрепленных файлов
			if (!empty($tmpl['attachment'])){
				preg_match("/(([а-яa-z0-9_\[\]-]*?)\.(.*){3,4})$/i",$tmpl['attachment'], $name);
				if (!empty($name[1])){$attach = $name[1];}
			}
			$save_text = $text;
			# вытаскиваем все картинки из текста
			$images = array();
			$embedded = array();
			preg_match_all("~(src|background)\s*=\s*(\"|')(?!java)(?!mail)(?!ftp)(?!http)([^\"'#]+)(#\w+)?(\"|')~i", $text, $m);
			if (!empty($m)) {
				$images = &$m[3];

				# меняем адреса картинок
				foreach ($images as $key=>$val) {
					$names	= explode('/', $val);
					$name	= end($names);
					$text = str_replace($val, 'cid:'.$name, $text);
					//$val - путь до файла в виде /files//dir/name.exp
					//приводим его к необходимому виду
					for ($i=0; $i<strlen($val); $i++){
						if (substr($val, $i, 6) == 'files/'){
							$val = $file_dir.substr($val, $i+7);
							break;
						}
					}
					$embedded[] = array(
						'name' => $name,
						'path' => $val,
						'cid' => $name,
					);
				}
			}

			$params = array(
				'from' => sql_getValue("SELECT value FROM strings WHERE name='site_email'"),
				'fromName' => $_SERVER["HTTP_HOST"],//по умолчанию хост
				'subj' => $tmpl['subject'],
				'to' => $to,
				'mess' => array(
					'html' => "<HTML><HEAD><META content=\"MSHTML 6.00.2800.1106\" name=GENERATOR></HEAD><BODY>".$text."</BODY></HTML>",
				'save_text' => $save_text,
				),
			);

			if (isset($attach)){
				$params['attachment'] = array(
					array(
						'name' => $attach,
						'path' => $file_dir.$tmpl['attachment'],
						'binary' => '',
					)
				);
			}
			if (isset($embedded)){$params['embedded'] = $embedded;}

		}
		return $params;
	}

	//-------------------------------------------------------------

	function SendSubscribe($data){
		set_time_limit(0);
		ignore_user_abort(TRUE);

		// получаем список получателей
		switch ($data['users']){
		    case 'all':
		        $data['users'] = sql_getRows("SELECT login as email, CONCAT(lname,' ',name) FROM auth_users WHERE root_id=".domainRootId(), true);
		        break;
		    case 'subscribe':
		    default:
		        $data['users'] = sql_getRows("SELECT email, '' FROM subscribe_users WHERE root_id=".domainRootId(), true);
		        break;
		}

		//отправляем
		$sender = new TSender();

		// отправляем
		// подгружаем настройки для данного плугина
		$sender->params = $this->params;
		echo "<script>

			  window.onload = function(){
			  	 load();
			  }

			  function load(){
			  	try {";

		echo "window.top.document.getElementById('name').innerText = '".$_SERVER['HTTP_HOST']."';";

		if (!empty($data['text'])){
			$total = count($data['users']);

			# вытаскиваем все картинки из текста
			$images = array();
			$save_text = $data['text'];
			preg_match_all("~(src|background)\s*=\s*(\"|')(?!java)(?!mail)(?!ftp)(?!http)([^\"'#]+)(#\w+)?(\"|')~i", $data['text'], $m);
			if (!empty($m)) {
				$images = &$m[3];
				//убираем дублирующиеся картинки
				foreach ($images as $key=>$val) {
					$img[$val] = 1;
				}
				if (!empty($img)){
					# меняем адреса картинок
					foreach ($img as $key=>$val) {
						$names	= explode('/', $key);
						$name	= end($names);
						$data['text'] = str_replace($key, 'cid:'.$name, $data['text']);
						$embedded[] = array(
							'name' => $name,
							'path' => '..'.$key,//путь необходим вида ../files/blablabla
							'cid' => $name,
						);
					}
				}
			}
			$num = 0;
			foreach ($data['users'] as $k=>$v){
				$send = array(
					'from' => sql_getValue("SELECT value FROM strings WHERE name='email_subscribe'"),
					'fromName' => sql_getValue("SELECT value FROM strings WHERE name='name_subscribe'"),
					'subj' => $data['subj'],
					'to' => array($k=>$v),
					'mess' => array(
						'html' => "<HTML><HEAD><META content=\"MSHTML 6.00.2800.1106\" name=GENERATOR></HEAD><BODY>".$data['text']."</BODY></HTML>",
						'text' => '',
					),
				);
				if (isset($embedded)){$send['embedded'] = $embedded;}
				//pr($data);
				if ($sender->emailSend($send)){
					$num++;
					foreach ($data['users'] as $email=>$name){
						sql_query("INSERT INTO notify_sent(`plugin`, `whom`,`text`) VALUES('email','".e($name)."<".e($email).">','".e($save_text)."')");
					}
					echo "parent.inRow('<b>".$v."</b>','<font color=\"Green\"><b>".$this->str('mail_good')."</b></font>');";
				} else {
					echo "parent.inRow('<b>".$v."</b>','<font color=\"Red\"><b>".$this->str('mail_bad')."</b></font>');";
				}
				echo "window.top.run('".(($num)/$total)."');";
			}

			echo "window.top.document.getElementById('send_footer').innerHTML = '<b>".$this->str('sended')." : ".$num."</b>';";

		} else {
			echo "parent.inRow('<b>".$this->str('error')."</b>','<font color=\"Red\"><b>".$this->str('err_notext')."</b></font>');";
		}

		echo "    }
				  catch(e){}
				  finally{}
			  }
			  </script>";

		return $result;
	}

	//-------------------------------------------------------------

	function AddToSent($data){
		foreach ($data['to'] as $email=>$name){
			sql_query("INSERT INTO notify_sent(`plugin`, `whom`,`text`) VALUES('email','".e($name.'<'.$email.'>')."','".e($data['save_text'])."')");
		}
	}
	//-------------------------------------------------------------

	function ShowTemplates() {
		$data['thisname'] = $this->name;
		$data['close'] = $this->str('close');

		$data['rows'] = sql_getRows("SELECT id, subject, description FROM email_templates WHERE visible>0 AND (user_id IS NULL OR user_id=".$this->user['id'].") ORDER BY updated DESC");

		if (empty($data['rows']))
			$data['empty'] = $this->str('empty');
		else
			$this->AddStrings($data);

		$GLOBALS['title'] = $this->str('templates');
		return $data;
	}

	//-------------------------------------------------------------

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
			parent.sendForm.document.forms.editform.elements["fld[subject]"].value="'.addslashes($tmpl['subject']).'";
			parent.sendForm.document.getElementById("type").value = "'.$tmpl['content_type'].'";
			parent.sendForm.change_editor("'.$tmpl['content_type'].'");
			parent.sendForm.document.forms.editform.elements["fld[text]"].value=document.all.tmpl.innerHTML;
			</script>';
		else
			return '
			<div id=tmpl>'.$tmpl['text'].'</div>
			<script>
			parent.sendForm.document.forms.editform.elements["fld[subject]"].value="'.addslashes($tmpl['subject']).'";
			parent.sendForm.document.getElementById("type").value = "'.$tmpl['content_type'].'";
			parent.sendForm.change_editor("'.$tmpl['content_type'].'");
			parent.sendForm.frames[0].FCK.SetHTML(document.all.tmpl.innerHTML);
			</script>';
	}
}


?>
