<?
require_once("phpmailer/class.phpmailer.php");
class TSender {

	//---------------------------------------------------------------------------------------

	var $params = array();
	var $sms_handle; // ������ ���������� � �����

	//---------------------------------------------------------------------------------------

	function TSender(){


	}
	//---------------------------------------------------------------------------------------	
	//                              SMS
	//---------------------------------------------------------------------------------------
	/*
	$data = array(
		'type'       => '', //send, receive, balanc, history, groups
		'message'    => '', //���� ���������
		'translit'   => '',
		'speed'      => '',
		'http_id'	 => '', //������ ��� receive
		'month'		 => '', //������ ��� history
		'year'		 => '', //������ ��� history
		'phone_list' => array('','',), // ������ ���������
		'send_on'    => '', // ���� ��������   25.10.2002 12:35:00
	);
	*/
	//---------------------------------------------------------------------------------------
	// ������� �������� ���
	function SmsSend($data){
		$sms = &$this->params;
		$sms['files'] = array(
			'send'    => 'http_in3.asp',       // �������� ���������.
			'receive' => 'http_out3.asp',      // ��������� �����������.
			'balanc'  => 'http_credit.asp',    // ��������� ������� �������.
			'history' => 'History_email.asp',  // ��������� ������ �� �����
			'groups'  => 'http_Group.asp', // ��������� ������ ����� � ���������� ������� � ���.
		);
		$sms['errors'] = array(
			'1'  => '� ������� �� ������ �������',
			'2'  => '� ������� �� ������� ��������� ��� ��������',
			'3'  => '� ������������ �� ������ ����� ��� �������� sms',
			'4'  => '� ������� �� ������� ������������� ���������',
			'5'  => '� ������� �� ������� ��� ������',
			'6'  => '� ������� �� ������� ����� ������',
			'7'  => '� ������������ �� ������� ������ ��� �������� sms',
			'8'  => '������ ��� �������� ������',
			'9'  => '�� ������ ��� ��������� ��� ��������� post',
			'10' => '������ �������� ������',
		);
		$files = &$sms['files'];

		// ������������� ���������� � ��������
		if (!$this->SmsOpenConnect()){ return $this->SmsError(8);}

		$query = $this->SmsGetQuery($data);
		if (isset($query['error'])) {return $query; }

		$post = '';
		$and = '';
		foreach ($query as $k=>$v){
			$post .= $and.$k.'='.$v;
			$and = '&';
		}

		// ������ ���������
		if (!isset($files[$data['type']]) || !$this->SmsPost($post, $files[$data['type']])){ return $this->SmsError(9);}

		//�������� �����
		$answer = $this->SmsGetAnswer($data);

		//��������� ����������
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
		$message = rawurlencode($message); //���������� �������� ��� ���� �������, 16-��� ��������������
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
		// �������� ����� �������, ������� headers
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
	
	
	
	/* ������ ��� �������� ������
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
	// ������� �������� ������ ����� �����
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

		$i = 1; // ���������� ����� �����
		//��������� ����� ������������� �����
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
		// ��������� �����, ������������ �� ��������
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
		//�� ������ ����� ������� ��� ��������� ������ � ��� ���������� ���������� mx ������� ��� ��������...��� ������� ������.
		//����� �������� �� ������ ������� � ������ �������� ��� �������...
		//��������� ��� ������� ������ ���� � ��� �� ������
		$mxsrvs = array();
		$mxemails = array();

		$debug['ctime'] = round((array_sum(explode(' ', microtime())) - $stime)*1000, 2)." ms"; 
		foreach ($data['to'] as $email => $name){
			//����� ����� host
			if (!$this->_is_valid_email($email)){
				$debug['emails'][$email]['error'] = "����������� ������ email �����.";
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
						//������ ��� shadow ����������� ������
						if (in_array('shadow.rusoft.ru', $mxhosts)){
							unset($mxhosts[0]);
						}
						//����� �������� �������� �� smtp ������� �����������, ���������� ��������������� ��������� �������
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
		
		//�������� ��� mx ������ � ������ �������� �������� �� ������
		foreach ($mxemails as $email => $mxs){
			//��������� email ����� �� ������������� � ������ mx �������
			//����� �������� ��������, �� ��� 1) ���������, 2) ����������� ����� ������
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
			//��������� � ��� ���������� � ������������ ����������
			$str = "<b>���� ���������� ��������� ���������:</b><br>����� ��������� ������ ��� ��������:&nbsp".$debug['ctime']."<br>����� �����:&nbsp".$debug['time']."<br><b>������:</b><br>";
			foreach ($debug['emails'] as $k=>$v){
				$str .= "<br>&nbsp;&nbsp;&nbsp;<b><font color='blue'>".$k."</font></b>";
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;����������� smtp ��������:&nbsp".$v['mxtime'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;���������� �����: ".$v['host'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;����� �����������: ".$v['sendtime'];
				$str .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;������: ".($v['status']?'<font color="green">�������</font>':'<font color="red">��������</font>');
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