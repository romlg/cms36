<?
require_once("phpmailer/class.phpmailer.php");
class TSender {

	//---------------------------------------------------------------------------------------

	var $params = array();
	var $sms_handle; // ресурс соединения с базой

	//---------------------------------------------------------------------------------------

	function TSender(){


	}
	//---------------------------------------------------------------------------------------	
	//                              SMS
	//---------------------------------------------------------------------------------------
	/*
	$data = array(
		'type'       => '', //send, receive, balanc, history, groups
		'message'    => '', //само сообщение
		'translit'   => '',
		'speed'      => '',
		'http_id'	 => '', //только для receive
		'month'		 => '', //только для history
		'year'		 => '', //только для history
		'phone_list' => array('','',), // список телефонов
		'send_on'    => '', // дата отправки   25.10.2002 12:35:00
	);
	*/
	//---------------------------------------------------------------------------------------
	// Функция отправки смс
	function SmsSend($data){
		$sms = &$this->params;
		$sms['files'] = array(
			'send'    => 'http_in3.asp',       // Отправка сообщений.
			'receive' => 'http_out3.asp',      // Получение результатов.
			'balanc'  => 'http_credit.asp',    // Получение остатка средств.
			'history' => 'History_email.asp',  // Получение отчета за месяц
			'groups'  => 'http_Group.asp', // Получение списка групп и количества записей в них.
		);
		$sms['errors'] = array(
			'1'  => 'в запросе не указан телефон',
			'2'  => 'в запросе не указано сообщение для отправки',
			'3'  => 'в конфигурации не указан логин для отправки sms',
			'4'  => 'в запросе не указано идентификатор сообщения',
			'5'  => 'в запросе не указано год отчета',
			'6'  => 'в запросе не указано месяц отчета',
			'7'  => 'в конфигурации не указаны пароль для отправки sms',
			'8'  => 'ошибка при открытии сокета',
			'9'  => 'не указан тип сообщения или неудачный post',
			'10' => 'ошибка закрытия сокета',
		);
		$files = &$sms['files'];

		// устанавливаем соединение с сервером
		if (!$this->SmsOpenConnect()){ return $this->SmsError(8);}

		$query = $this->SmsGetQuery($data);
		if (isset($query['error'])) {return $query; }

		$post = '';
		$and = '';
		foreach ($query as $k=>$v){
			$post .= $and.$k.'='.$v;
			$and = '&';
		}

		// постим заголовок
		if (!isset($files[$data['type']]) || !$this->SmsPost($post, $files[$data['type']])){ return $this->SmsError(9);}

		//получаем ответ
		$answer = $this->SmsGetAnswer($data);

		//закрываем соединение
	   	if (!$this->SmsCloseConnect()){ return $this->SmsError(10);}

		return $answer;
	}

	//---------------------------------------------------------------------------------------

	function SmsCheckParams( &$query, $sms, $type){

		 switch($type){
			case 0:
				if (!isset($sms['http_username'])){
					return $this->SmsError(3);
				}
				$query['http_username'] = $sms['http_username'];

				if (!isset($sms['http_password'])){
					return $this->SmsError(7);
				}
				$query['http_password'] = $sms['http_password'];
				break;
			case 1:
				if (!isset($sms['http_username'])){
					return $this->SmsError(3);
				}
				$query['Login'] = $sms['http_username'];

				if (!isset($sms['http_password'])){
					return $this->SmsError(7);
				}
				$query['Password'] = $sms['http_password'];
				break;
		 }
	}

	//---------------------------------------------------------------------------------------

	function SmsGetQuery($data){

		$sms = &$this->params;
		$query = array();

		switch($data['type']){
			case 'send':

				$this->SmsCheckParams( $query, $sms, 0);
				if (isset($query['error'])){ return $query; }

				if (!isset($data['phone_list'])){ return $this->SmsError(1); }
				$query['phone_list'] = implode(',', $data['phone_list']);
				if (!isset($data['message'])){ return $this->SmsError(2); }
				$query['message'] = $this->SmsGetMessage($data['message']);
				$query['Nosplit'] = $sms['Nosplit'];
				$query['translit']  = isset($data['translit']) ? $data['translit'] : $sms['translit'];
				$query['speed']     = isset($data['speed']) ? $data['speed'] : $sms['speed'];
				if (isset($data['send_on'])){ $query['send_on']    = $data['send_on'];}
				break;
			case 'receive':

				$this->SmsCheckParams( $query, $sms, 0);
				if (isset($query['error'])){ return $query; }

				if (!isset($data['http_id'])){
					return $this->SmsError(4);
				}
				$query['http_id'] = $data['http_id'];
				break;

			case 'balanc':
				$this->SmsCheckParams( $query, $sms, 1);
				if (isset($query['error'])){ return $query; }
				break;

			case 'history':
				$this->SmsCheckParams( $query, $sms, 1);
				if (isset($query['error'])){ return $query; }

				if (!isset($data['year'])){
					return $this->SmsError(5);
				}
				$query['Year'] = $data['year'];

				if (!isset($data['month'])){
					return $this->SmsError(6);
				}
				$query['Month'] = $data['month'];
				break;
			case 'groups':
				$this->SmsCheckParams( $query, $sms, 0);
				if (isset($query['error'])){ return $query; }
				break;
		}

		return $query;
	}

	//---------------------------------------------------------------------------------------

	function SmsError($num){

		$errors = $this->params['errors'];
		return array(
			'error' => array(
				'code' => $num,
				'message' => $errors[$num],
			),
		);
	}

	//---------------------------------------------------------------------------------------

	function SmsGetMessage( $message){
		$sms = &$this->params;
		if (strlen($message)>1440){	$message = substr($message, 0, 1440); }
		$message = rawurlencode($message); //необходимо заменить все спец символы, 16-ным представлением
		return $message;
	}

	//---------------------------------------------------------------------------------------

	function SmsOpenConnect(){

		$sms = &$this->params;
		//$this->sms_handle = fsockopen($sms['host'], 80, $errno, $errstr, 1);
		$this->sms_handle = @fsockopen($sms['host'], 80, $errno, $errstr, 3);
		if (!$this->sms_handle) return false;
		return true;

	}
	//---------------------------------------------------------------------------------------

	function SmsCloseConnect(){

		return @fclose($this->sms_handle);

	}

	//---------------------------------------------------------------------------------------

	function SmsPost($post, $file){

		$sms = &$this->params;
		$header = "POST http://".$sms['host']."/".$file." HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Host: ".$sms['host']."\r\n";
		$header .= "Content-Length: ".strlen($post)."\r\n";
		$header .= $post."\r\n\r\n";
		//pr($header);
		$ret = fwrite( $this->sms_handle, $header);
		if (!$ret) return false;
		return true;

	}

	//---------------------------------------------------------------------------------------

	function SmsGetAnswer($data) {
		// получаем ответ сервера, обрезая headers
		$ret = '';
		$flag = 0;
		while (!feof($this->sms_handle)) {
			$str = @fgets($this->sms_handle, 128);
			if ($flag == 1){ $ret .= $str; }
			if ($str == rawurldecode('%0D%0A')){ $flag = 1;}
		}


		switch($data['type']){
			case 'send':
				$lines = explode("\r\n",$ret);

				$last_array = '';
				foreach ($lines as $k=>$v){
					preg_match("/\[(.*?)\]/im", $v, $str);
					if (!empty($str) & isset($str['1'])){
						$last_array = $str['1'];
					} elseif(!empty($v) & !empty($last_array)) {
						$str = explode('=', $v);
						$return[$last_array][$str['0']] = $str['1'];
					}
				}
				break;
			case 'receive':
				$lines = explode("\r\n",$ret);

				$last_array = '';
				foreach ($lines as $k=>$v){
					preg_match("/\[(.*?)\]/im", $v, $str);
					if (!empty($str) & isset($str['1'])){
						$last_array = $str['1'];
					} elseif(!empty($v) & !empty($last_array)) {
						$str = explode('=', $v);
						$return[$last_array][$str['0']] = $str['1'];
					}
				}
				break;
			case 'balanc':
				return $ret;
				break;
			case 'history':
				return $ret;
				break;
			case 'groups':
				$lines = explode(";",$ret);
				foreach ($lines as $k=>$v){
					list($key, $value) = explode('=', $v);
					$return[$key] = $value;
				}
				break;
		}
		return $return;

	}

	//---------------------------------------------------------------------------------------	
	//                              SMTP
	//---------------------------------------------------------------------------------------		
	
	
	
	/* массив для передачи данных
	$data = array(
		'from' => '',
		'fromName' => '',
		'to' => array(
			'email' => 'name',
		),
		'subj' => '',
		'mess' => array(
			'html' => '',
			'text' => '',
		),
		'attachment' => array(
			1 => array(
				'name' => '',
				'path' => '',
				'binary' => '',
			)
		),
		'embedded' => array(
			1 => array(
				'name' => '',
				'path' => '',
				'cid' => '',
			)
		),
	);*/
	// функция отправки письма через сокет
	function emailSend($data){
		$stime = array_sum(explode(' ', microtime())); 
		require_once("getmxrr.php");
		
		$smtp = &$this->params;
		$mail = new phpmailer();
		$mail->Mailer    = "smtp";
		$mail->From      = (isset($data['from']) & !empty($data['from']))?$data['from'] : 'info@rusoft.ru';
		$mail->FromName  = (isset($data['fromName']) & !empty($data['fromName']))?$data['fromName'] : 'RuSoft';
		$mail->Sender    = (isset($data['from']) & !empty($data['from']))?$data['from'] : 'info@rusoft.ru';
		
		$mail->Host      = $smtp['host'];
		
		$mail->CharSet   = $smtp['charset'];
		$mail->Encoding  = $smtp['encoding'];
		$mail->Port      = $smtp['port'];
		$mail->SMTPAuth  = $smtp['auth'];
		$mail->Subject   = (isset($data['subj']) & !empty($data['subj']))?$data['subj'] : '';

		if ($smtp['auth']){
		   $mail->Username  = $smtp['user'];
		   $mail->Password  = $smtp['pass'];
		}

		// HTML body
		if (isset($data['mess']['html']) & !empty($data['mess']['html'])){
			$body  = $data['mess']['html'];
			$mail->isHTML(true);
		}

		
		// Plain text body (for mail clients that cannot read HTML)
		if (isset($data['mess']['text']) & !empty($data['mess']['text'])){
			$text_body  = $data['mess']['text'];
			$mail->isHTML(false);
		}
		$mail->AltBody = (isset($text_body)) ? $text_body : '';
		$mail->Body    = (isset($body)) ? $body : ((isset($text_body)) ? $text_body : '');

		$i = 1; // порядковый номер файла
		//добавляем файлы прикрепленные файлы
		if (isset($data['attachment']) & !empty($data['attachment'])){
			foreach ($data['attachment'] as $k => $item){
				if (isset($item['binary']) & !empty($item['binary'])){
					$mail->AddStringAttachment(
						$item["binary"],
						(isset($item["name"]) & !empty($item["name"])) ? $item["name"] : 'file'.$i,
						$smtp['encoding']
					);
					$i++;
				}
				elseif(isset($item['path']) & !empty($item['path'])){
					$mail->AddAttachment(
						$item["path"],
						(isset($item["name"]) & !empty($item["name"])) ? $item["name"] : 'file'.$i,
						$smtp['encoding']
					);
					$i++;
				}
			}
		}
		// добавляем файлы, отображаемые на странице
		if (isset($data['embedded']) & !empty($data['embedded'])){
			foreach ($data['embedded'] as $k => $item){
				if (isset($item['path']) & !empty($item['path'])){
					$mail->AddEmbeddedImage(
						$item["path"],
						(isset($item["cid"]) & !empty($item["cid"])) ? $item["cid"] : $i,
						(isset($item["name"]) & !empty($item["name"])) ? $item["name"] : 'file'.$i,
						$smtp['encoding']
					);
					$i++;
				}
			}
		}
		//pr($mail);
		//на данном этапе имеется уже собранное письмо и нам необходимо определить mx серверы для отправки...для каждого письма.
		//чтобы повторно не искать серверы в момент отправки для каждого...
		//сохраняем для каждого домена один и тот же сервер
		$mxsrvs = array();
		$mxemails = array();

		$debug['ctime'] = round((array_sum(explode(' ', microtime())) - $stime)*1000, 2)." ms"; 
		foreach ($data['to'] as $email => $name){
			//берем чисто host
			if (!$this->_is_valid_email($email)){
				$debug['emails'][$email]['error'] = "неправильно указан email адрес.";
				continue;
			}
			$host = substr($email,strpos($email, "@")+1);
			$domains = explode(".", $host);
			foreach ($domains as $level => $domain){
				$address = implode(".", $domains);
				if (!key_exists($address,$mxsrvs)){
					$time = array_sum(explode(' ', microtime())); 
					if (getmxrr_portable($address, $mxhosts,  $preference) == true) {
						array_multisort($preference, $mxhosts);
					}
					$debug['emails'][$email]['mxtime'] = round((array_sum(explode(' ', microtime())) - $time)*1000, 2)." ms";
					if (!empty($mxhosts)){
						$mxhosts[] = $smtp['host'];
						//потому что shadow тормознутый сервак
						if (in_array('shadow.rusoft.ru', $mxhosts)){
							unset($mxhosts[0]);
						}
						//чтобы включить рассылку на smtp серверы получателей, необходимо закоментировать следующую строчку
						$mxhosts = array_reverse($mxhosts);				
						
						$mxsrvs[$address] = $mxhosts;
						$mxemails[$email] = &$mxsrvs[$address];
						$debug['emails'][$email]['mxsrvs'] = &$mxsrvs[$address];
						break;
					} else {
						unset($domains[$level]);
					}
				} else {
					$debug['emails'][$email]['mxtime'] = 'cache(0 ms)';
					$mxemails[$email] = &$mxsrvs[$address];
					$debug['emails'][$email]['mxsrvs'] = &$mxsrvs[$address];
				}
			}
		}	
		
		//получены все mx северы и теперь начинаем отправку по списку
		foreach ($mxemails as $email => $mxs){
			//проверяем email адрес на существование и работу mx сервера
			//можно включить проверку, но это 1) замедляет, 2) вероятность очень низкая
			//$this->checkEmail($email, $mxs, $debug); 
			$mail->AddAddress($email, $name);
			foreach ($mxs as $k=>$host){
				$mail->Host = $host;
				$time = array_sum(explode(' ', microtime())); 
				$status = $mail->Send();
				$debug['emails'][$email]['sendtime'] = round((array_sum(explode(' ', microtime())) - $time)*1000, 2)." ms";
				$debug['emails'][$email]['status'] = $status;
				
				if ($status)  {
					$debug['emails'][$email]['host'] = $host;
					break; 
				}
			}
			$mail->ClearAddresses();
		}
		$debug['time'] = round((array_sum(explode(' ', microtime())) - $stime)*1000, 2)." ms"; 
		if (function_exists('log_notice')){
			//скидываем в лог информацию о отправленных сообщениях
			$str = "<b>Были отправлены следующие сообщения:</b><br>Время генерации шалона для отправки:&nbsp".$debug['ctime']."<br>Общее время:&nbsp".$debug['time']."<br><b>Адреса:</b><br>";
			foreach ($debug['emails'] as $k=>$v){
				$str .= "<br>&nbsp;&nbsp;&nbsp;<b><font color='blue'>".$k."</font></b>";
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Определение smtp серверов:&nbsp".$v['mxtime'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Отправлено через: ".$v['host'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Время отправления: ".$v['sendtime'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Статус: ".($v['status']?'<font color="green">успешно</font>':'<font color="red">неудачно</font>');
			}
			log_notice('email', false, $str);
		}
		//$status = true;
		// Clear attachments for next loop
		$mail->ClearAttachments();
		if ($status) return true;

		return false;
	}
	
	function _is_valid_email($email = "") {
		return preg_match('/^[.\w-]+@([\w-]+\.)+[a-zA-Z]{2,6}$/', $email);
	}
	
	function checkEmail($email = "", &$mxsrvs, &$debug) {
		$smtp = &$this->params;
		$timeout = 5;
		$rw_timeout = 1;
		$localhost = $_SERVER['HTTP_HOST'];
		$sender = 'info@'.$localhost;
		
		$result = false;
		$id = 0;
		while (!$result && $id < count ($mxsrvs)) {
			if (function_exists("fsockopen")) {
				if ($connection = fsockopen($mxsrvs[$id], $smtp['port'], $errno, $error, $timeout)) {
					stream_set_timeout($connection, $rw_timeout);

					$data = fgets($connection,1024);
					$response = substr($data,0,1);
					$debug['emails'][$email]['fsockopen'][] = '<b>RESPONSE:</b>'.htmlspecialchars($data);

					$request = "HELO $localhost\r\n";
					fputs($connection,$request); // 250
					$data = fgets($connection,1024);
					$response = substr($data,0,1);
					$debug['emails'][$email]['fsockopen'][] = '<b>REQUEST:</b>'.htmlspecialchars($request);
					$debug['emails'][$email]['fsockopen'][] = '<b>RESPONSE:</b>'.htmlspecialchars($data);

					if ($response == '2') {// 200, 250 etc.
						$request = "MAIL FROM:<$sender>\r\n";
						fputs($connection,$request);
						$data = fgets($connection,1024);
						$response = substr($data,0,1);
						$debug['emails'][$email]['fsockopen'][] = '<b>REQUEST:</b>'.htmlspecialchars($request);
						$debug['emails'][$email]['fsockopen'][] = '<b>RESPONSE:</b>'.htmlspecialchars($data);

						// VRFY
						if ($response == '2') {// 200, 250 etc.
							$name = explode('@', $email);
							$request = "VRFY: $name[0]\r\n";
							fputs($connection,$request);
							$data = fgets($connection,1024);
							$response = substr($data,0,1);
							
							$debug['emails'][$email]['fsockopen'][] = '<b>REQUEST:</b>'.htmlspecialchars($request);
							$debug['emails'][$email]['fsockopen'][] = '<b>RESPONSE:</b>'.htmlspecialchars($data);

							if ($response == '2') $result = true;// 200, 250 etc.
							/*if ($response == '2') {// 200, 250 etc.
								fputs($connection,"data\r\n");
								$data = fgets($connection,1024);
								$response = substr($data,0,1);
								if (DEBUG_OK) pr($data);

							}*/
							// RCPT TO
							else {
								$request = "RCPT TO:<$email>\r\n";
								fputs($connection,$request);
								$data = fgets($connection,1024);
								$response = substr($data,0,1);
								$debug['emails'][$email]['fsockopen'][] = '<b>REQUEST:</b>'.htmlspecialchars($request);
								$debug['emails'][$email]['fsockopen'][] = '<b>RESPONSE:</b>'.htmlspecialchars($data);

								if ($response == '2') $result = true;
								/*{// 200, 250 etc.
									$request = "data\r\n";
									fputs($connection,$request);
									$data = fgets($connection,1024);
									$response = substr($data,0,1);
									if (DEBUG_OK) {
										pr($request, 'REQUEST');
										pr($data, 'RESPONSE');
									}

									if ($response == '2')// 200, 250 etc.
								}*/
							}
						}
						/*if ($response == '2') {// 200, 250 etc.
							fputs($connection,"RCPT TO:<$email>\r\n");
							$data = fgets($connection,1024);
							$response = substr($data,0,1);
							if (DEBUG_OK) pr($data);

							if ($response == '2') {// 200, 250 etc.
								fputs($connection,"data\r\n");
								$data = fgets($connection,1024);
								$response = substr($data,0,1);
								if (DEBUG_OK) pr($data);

								if ($response == '2') $result = true;// 200, 250 etc.
							}
						}*/
					}

					fputs($connection,"QUIT\r\n");
					fclose($connection);
					if ($result) return true;
				}
			}
			else break;
			$id++;
		}
		return false;
	}
}
?>