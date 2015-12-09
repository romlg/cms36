<?php

include_once 'phpmailer/class.phpmailer.php';
include_once 'modules/forms.php';

class TContent {

	function TContent(){
		$print = get('print', 0, 'g');
		if ($print) {
			$page = & Registry::get('TPage');
			$page->template = 'print';
		}
		if (isset($_GET['a']) && $_GET['a'] == 'captcha') return $this->captcha();

		$cache_file = './cache/tables/.cache';
		if (is_file($cache_file)) {
		    $GLOBALS['update_time'] = filemtime($cache_file);
		    $GLOBALS['no_cache'] = false;
		}
	}

	/**
	 * Функция построения постраничной навигации
	 *
	 * @param $count - общее количество элементов
	 * @param $limit - количество элементов на странице
	 * @param $offset - номер текущей страницы
	 * @param $url - ссылка
	 * @param $limits - массив допустимых количеств элементов на странице
	 * @return отпарсенный шаблон
	 */
	function getNavigation($count,$limit,$offset,$href,$limits=array(),$tmpl = 'pages.html'){
		$url = '';
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
			$url = $_SERVER['REDIRECT_QUERY_STRING'];
			$turl = explode('&', $url);
			foreach ($turl as $k=>$v){
				$turl2[$k] = explode('=', $v);
				$turl2[$k][0] = htmlspecialchars(urldecode($turl2[$k][0]));
				if ($turl2[$k][0] == 'offset' || $turl2[$k][0] == 'limit'){
					unset($turl[$k]);
					unset($turl2[$k]);
				}
			}
			$url = implode('&',$turl);
		}

		$pages = pages($count, $limit, $offset, $href."?".$url);
		$pages['limits']['65535'] = 'все';

		$tpl_obj = & Registry::get('TTemplate');
		$tpl_obj->assign(array( 'pages'        => $pages,
		));
		return $tpl_obj->fetch($tmpl);
	}

	/**
	 * Функция возвращает путь к версии для печати
	 *
	 */
	function getPrintUrl(){
		$page = & Registry::get('TPage');
		$path = $page->tpl->_tpl_vars['params']['dirs'];
		$path = add_query_arg($path, 'print', '1');
        return $path;
	}

	/**
	 * Функция построения списка файлов, прикрепленных к странице
	 *
	 */
	function getFiles(){
		$page = & Registry::get('TPage');
		$files = sql_getRows("SELECT id, ".(LANG_SELECT ? "IF (name_".lang()." <> '', name_".lang().", name_".DEFAULT_LANG.") as name" : "name").", fname FROM elem_file WHERE pid=".$page->content['id']." AND visible > 0 ORDER BY priority");
		foreach ($files as $key=>$val){
			$files[$key]['ext'] = $this->getFileExt($val['fname']);
			$files[$key]['size'] = $this->getFileSize(substr($val['fname'], 1));
			$files[$key]['ico'] = $this->getFileIco($val['fname']);
		}
		if (!empty($files)) return array('files_block' => $files);
	}

	/**
	 * Функция вычисляет размер файла
	 *
	 * @param $file - путь к файлу
	 * @return массив с размером и единицей измерения
	 */
	function getFileSize($file) {  // Узнаем размер файла с единицей измерения
		$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
		$size = filesize($file);
		if (!$size) $size = 0;
		else {
			$pass = 0;
			while( $size >= 1024 )
			{
				$size /= 1024;
				$pass++;
			}
			$size = round($size, 2);
			$unit = $units[$pass];
		}
		return array('size'=>$size,'unit'=>$unit);
	}

	/**
	 * Функция определения иконки файла по его расширению
	 *
	 * @param $file - путь к файлу
	 * @return путь к картинке
	 */
	function getFileIco($file) {
		$dot_pos = strrpos($file,".");
		if ($dot_pos === false) return '/images/icons/xxx.gif';
		$ext = substr($file,$dot_pos+1);
		if (is_file('images/icons/'.strtolower($ext).'.gif'))
		return '/images/icons/'.strtolower($ext).'.gif';
		else return '/images/icons/xxx.gif';
	}

	/**
	 * Функция определения расширения файла
	 *
	 */
	function getFileExt($file) {
		$dot_pos = strrpos($file,".");
		if ($dot_pos === false) return "";
		$ext = substr($file,$dot_pos+1);
		return $ext;
	}

	/**
	 * Функция генерирует уникальный ID кеша
	 *
	 */
	function _cache($block) {
		$page_obj = & Registry::get('TPage');

		$cache_id = lang().'_'.$page_obj->content['id'].'_'.$_SERVER['REQUEST_URI'];
		// из всего, что есть в get, формируем кеш
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $cache_id .= '_'.$_SERVER['REDIRECT_QUERY_STRING'];

		return $cache_id;
	}

	/**
	 * Функция закачки файла
	 *
	 */
	function download() {
		// Проверяем: если нет в начале files/ то подставляем
		$filename = get('filename','','pg');
		path($filename);
		$fd = FILES_DIR;
		$rp = realpath($fd);

		if (strpos($filename, '/') == 0) $filename = substr($filename, 1);
		if (strpos($fd, '/') == 0) $fd = substr($fd, 1);
		if (substr($fd, -1) == '/') $fd = substr($fd, 0, -1);

		if (substr($filename, 0, strlen($fd)) != $fd) {
			$filename = $fd.'/'.$filename;
		}

		$vp = dirname(realpath($filename));
		if (substr($vp, 0, strlen($rp)) != $rp) redirect('404');

		ob_clean();
		header("Accept-Ranges: bytes");
		header("Content-Length: ".filesize($filename)."");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".basename($filename)."\"");

		readfile($filename);
		exit();
	}

	/**
	 * Функция построения массива фотографий, прикрепленных к данной странице
	 *
	 */
	function showGallery(&$params){
		$page = & Registry::get('TPage');
		$gallery = sql_getRows("SELECT * FROM elem_gallery WHERE visible > 0 AND pid=".$page->content['id']." ORDER BY priority");
		foreach ($gallery as $key=>$val) {
			$size = getimagesize(substr($val['image_small'], 1));
			$gallery[$key]['width'] = $size[0];
			if (is_file(substr($val['image_small'], 1))) $gallery[$key]['image_small_exist'] = 1;
			if (is_file(substr($val['image_large'], 1))) $gallery[$key]['image_large_exist'] = 1;
		}
		return array('gallery'=>$gallery);
	}








	/**
	 * Формирование подменю текущей страницы
	 *
	 */
	function getContentMenu(&$params) {
		$menu = new TMenu();
		$page = & Registry::get('TPage');
		$list = array();

		$level = $page->content['level'];
		$pid = $page->content['id'];
		while (empty($list) && $pid != ROOT_ID) {
			$list = $menu->menu($pid, $pid, 1, 1, $params['types']);
			$pid = $page->pids[$level]['pid'];
			$level--;
		}

		return array('menu' => $list);
	}

	/**
	 * Форма обратной связи
	 *
	 */
	function showContactForm(&$params) {
		$ret = array();
		$page = & Registry::get('TPage');

		$titles = array('Заявка на покупку', 'Заявка на продажу', 'Заявка на аренду', 'Вопрос', 'Предложение о сотрудничестве', 'Неточность на сайте', 'Жалоба, предложение', 'Благодарность', 'Консультации', 'Другое');

        $form = new TForm(null, $this);
    	$form->form_name='content';
    	$form->elements=array(
    		'title'	=>	array(
    			'name'		=> 'title',
    			'type'		=> 'select',
    			'options'	=> $titles,
    			'text'		=> 'Тема сообщения',
    			'req'		=> 0,
                'atrib'    => 'style="width: 100%"',
                'value'		=> isset($_POST['fld']['title'][0]) ? $_POST['fld']['title'][0] : ($page->content['page'] == 'consulting' ? '8' : '0'),
    		),
    		'name'	=>	array(
    			'name'		=> 'name',
    			'type'		=> 'text',
    			'req'		=> 1,
                'atrib'    => 'style="width: 100%" class="input_text"',
    		),
    		'email'	=>	array(
    			'name'		=> 'email',
    			'type'		=> 'text',
    			'req'		=>	0,
				'check'		=> 'email',
				'onerror'	=> '{#msg_err_invalid_email#}',
                'atrib'    => 'style="width: 100%" class="input_text"',
    		),
    		'phone'	=>	array(
    			'name'		=> 'phone',
    			'type'		=> 'text',
    			'req'		=>	1,
				'check'		=> 'phone',
				'onerror'	=> '{#msg_err_invalid_phone#}',
                'atrib'    => 'style="width: 50%" class="input_text"',
    		),
    		'message'=>	array(
    			'name'		=> 'message',
    			'type'		=> 'textarea',
    			'text'		=> 'Текст сообщения',
    			'req'		=> 1,
                'atrib'    => 'style="width: 100%; height: 120px;" class="input_text"',
    		),
			'captcha' => array(
				'name'		=> 'captcha',
				'type'		=> 'html',
				'req'		=>	1,
	   			'value'		=> '
	   				<table width="100%"><tr>
	   				<td width="40%" align="right">{#content_fld_captcha#}<font color=red>*</font>:</b></td>
	   				<td width="60%"><input type="text" name="fld[captcha]" style="width: 162px; margin-right: 10px"><img src="'.$page->content['href'].'?a=captcha" align="middle" title="Щелкните на картинце, чтобы загрузить другой код" onclick="document.getElementById(\'captcha\').src=\''.$page->content['href'].'?a=captcha&\'+1000*Math.random()" id="captcha"></td>
	   				</tr></table>',
			),
			array(
				'name'	=>	'button1',
				'type'	=>	'submit',
				'value'	=>	$page->tpl->get_config_vars("send"),
				'group'	=>	'system',
				'atrib'	=>	'class="Button"',
			),
			array(
				'name'	=>	'button2',
				'type'	=>	'reset',
				'value'	=>	$page->tpl->get_config_vars("reset"),
				'group'	=>	'system',
				'atrib'	=>	'class="Button"',
			),
		);
		$fdata=$form->generate();

		$fdata['form']['action'] = $page->content['href'];
		$fdata['form']['width'] = '80%';

		if (empty($fdata['form']['errors']) && isset($_POST['fld'])) {

			$keystring = $_SESSION['captcha_keystring'];
			unset($_SESSION['captcha_keystring']);
			if (!empty($_POST['fld']['captcha']) && (empty($keystring) || $_POST['fld']['captcha'] !== $keystring)) {
				$fdata['form']['result'] = 'msg_captcha_error'; // Ошибка при вводе проверочной комбинации
			}
			else {
				$_POST['fld']['title'] = $titles[$_POST['fld']['title'][0]];
				if (sendNotify('CONTACT_FORM', 0, $_POST['fld'])) {
					redirect($page->content['href'].'?msg=msg_send_email');
				}
				redirect($page->content['href'].'?msg=msg_not_send_email');
			}
		}
		$ret['fdata'] = $fdata;
		return $ret;
	}

	// --- Функция проверки формы
	// @params $fld - данные, пришедшие с формы
	// @params $namespace - начало строковой константы
	// @params $required - список обязательных полей
	// @params $check - массив полей, подлежищих спец проверке, в формате:
	// array(
	//		'field_name' => 'func_name',
	// )
	function check_form($fld, $namespace='', $required=array(), $check=array()) {
		$tpl_obj = & Registry::get('TTemplate');
		$empty = '';
		if ($required) {
			foreach ($required as $key=>$value) {
				if ($value && !$fld[$value]) {
					$empty[] = $value;
				}
			}
		}
		if ($empty) {
			$error = $tpl_obj->get_config_vars('msg_err_empty').": ";
			foreach ($empty as $key => $value)
			{
				if ($key) $error .= ', ';
				$error .=  strip_tags($tpl_obj->get_config_vars($namespace.$value));
			}
			$error .= '<br>';
		}
		if ($check) foreach ($fld as $key=>$val){
			if (!empty($val) && in_array($key, array_keys($check))) {
				if (function_exists($check[$key])) {
					$r = call_user_func($check[$key], $val);
					if (!$r)
					$error .= $tpl_obj->get_config_vars($namespace.$key).' '.$tpl_obj->get_config_vars('msg_err_invalid').'<br>';
				}
			}
		}

		$keystring = $_SESSION['captcha_keystring'];
		unset($_SESSION['captcha_keystring']);
		if (!empty($fld['captcha']) && (empty($keystring) || $fld['captcha'] !== $keystring)) {
			$error .= $page->tpl->get_config_vars('msg_captcha_error').'<br>'; // Ошибка при вводе проверочной комбинации
		}

		return $error;
	}

	function showForm(&$params) {
		$page = & Registry::get('TPage');
		$form = sql_getRow('SELECT * FROM elem_form WHERE pid='.$page->content['id'].' AND visible > 0');
		if (!$form) return;

		$rows = sql_getRows('SELECT * FROM elem_form_elems WHERE pid='.$form['form_id']);
		foreach ($rows as $k=>$v){
			if ($v['show']) {
				$_key = !empty($v['db_field']) ? $v['db_field'] : $k;
				$elements[$_key] = array(
					'name' => $_key,
					'type' => $v['type'] == 'input' ? 'text' : $v['type'],
					'text' => $v['text'],
					'key'  => $v['key'],
	                'req'  => $v['req'],
	                'check'=> $v['check'],
	                'db_field'=> $v['db_field'],
	                'atrib'=> ($v['type'] != 'radio' && $v['type'] != 'checkbox' ? 'style="width: 90%;"' : '').($v['type'] == 'textarea' ? ' rows="5"' : '').($v['type'] == 'input' || $v['type'] == 'textarea' || $v['type'] == 'file' ? 'class="input_text"' : ''),
	                'onerror' => !empty($v['check']) ? $page->tpl->get_config_vars('msg_err_invalid_'.$v['check']) : '',
				);
				if ($v['type'] == 'select' || $v['type'] == 'radio' || $v['type'] == 'checkbox'){
					$temp = sql_getRows('SELECT * FROM elem_form_values WHERE pid='.$v['id']);
					foreach ($temp as $key => $value){
						$elements[$k]['options'][$value['value']] = $value['text'];
					}
				}
			}
		}
		$elements['send'] = array(
			'name'	=> 'send',
			'type'	=> 'submit',
			'group'	=> 'system',
			'value'	=> $page->tpl->get_config_vars('send'),
		);

		$form_obj = new TForm(array('elements' => $elements));
        $ret = $form_obj->generate();
        $ret['form']['title'] = $form['name'];
        $ret['form']['width'] = '80%';
        $ret['form']['action'] = $page->content['href'];

        if (!empty($_POST) && empty($ret['form']['errors'])) {
        	$mail = &new PHPMailer();
        	$admin_email = $page->tpl->get_config_vars('admin_email');
        	if (empty($form['email'])) $admin[0] = $admin_email;
        	else $admin = explode(',', str_replace(' ', '', $form['email']));

        	$mail->From = $admin_email;
        	$mail->Sender = $admin_email;
        	$mail->Mailer = 'mail';
        	$mail->Subject = !empty($form['name']) ? $form['name'] : 'Письмо из раздела "'.$page->content['name'].'"';

        	// Аттач файлов
        	$index = array();
        	foreach ($elements as $k=>$v) {
        		if ($v['type'] == 'file') $index[] = $k;
        	}
        	if (isset($_FILES['fld'])) {
        		$from = 'files/';
       			foreach ($index as $ind) {
       				if (!empty($_FILES['fld']['name'][$ind])) {
       					$filename = $from.$_FILES['fld']['name'][$ind];
       					@move_uploaded_file($_FILES['fld']['tmp_name'][$ind], $filename);
       					chmod($filename, 0664);
       					$mail->AddAttachment($filename);
       				}
        		}
        	}

        	$body = '';
        	$text_body = '';
        	foreach ($rows as $key=>$val) {
			if ($val['type'] == 'captcha') continue;
        		$_key = !empty($val['db_field']) ? $val['db_field'] : $key;
        		$value_text = '';
        		if (in_array($elements[$_key]['type'], array('radio', 'select'))) {
        			$value_text = $elements[$_key]['options'][$_POST['fld'][$_key][0]];
        		}
        		elseif ($elements[$_key]['type'] == 'checkbox') {
        			$ar = array();
        			foreach ($_POST['fld'][$_key] as $k=>$v) {
        				$ar[] = $elements[$_key]['options'][$v];
        			}
        			$value_text = implode(', ', $ar);
        		}
        		else {
        			$value_text = $_POST['fld'][$_key];
        		}
        		$body .= $val['text'].': '.$value_text.'<br>';
        		$text_body .= $val['text'].': '.$value_text."\r\n";
        	}
        	$mail->Body = $body;
        	$mail->AltBody = $text_body;
        	foreach ($admin as $k=>$v) $mail->AddAddress($v);

        	$res = $mail->Send();

        	if (isset($_FILES['fld']))
       			foreach ($index as $ind)
       				@unlink($_FILES['fld'][$ind]['name']);

        	if(!$res) {
        		redirect($page->content['href'].'?msg=msg_not_send_email');
        	}

        	if (!empty($form['db_table'])) {
        		// Надо записать в БД
	        	foreach ($_POST['fld'] as $k=>$v) {
	        		if (!empty($elements[$k]['db_field'])) {
	        			$fields[] = $k;
	        			$values[] = h($v);
	        		}
	        	}
	        	if (!empty($fields)) {
	        		$sql = 'INSERT INTO `'.$form['db_table'].'` (`'.implode('`,`', $fields).'`) VALUES ("'.implode('","', $values).'")';
		        	sql_query($sql);
		        	$err = sql_getError();
	        		if (empty($err)) redirect($page->content['href'].'?msg=msg_send_email');
	        		else redirect($page->content['href'].'?msg=msg_fail');
	        	}
        	}

        	redirect($page->content['href'].'?msg=msg_send_email');
        }
		$page->tpl->assign(array('fdata' => $ret,));
        return array('text'	=> $page->tpl->fetch('form.html'));
	}

	/**
	/*	Выборка id подразделов
	/*
	/**/
	function getChilds($id){
		$tree = &Registry::get('TTreeUtils');
		$ids = sql_getColumn('SELECT id FROM tree WHERE pid='.$id.' AND visible > 0');
		foreach ($ids as $key=>$val)
			$ids = array_merge($ids, $this->getChilds($val));
		return $ids;
	}

	/**
	 * Генерация проверочного кода
	 *
	 */
    function captcha(){
		include('modules/kcaptcha/kcaptcha.php');
		$captcha = new KCAPTCHA();
		session_start();
		$_SESSION['captcha_keystring'] = $captcha->getKeyString();
		session_write_close();
		exit();
    }

    /**
     * Проверка свежести курса и возвращение его значения
     *
     * @param array $params
     * @return double
     */
    function getKurs(&$params) {
        $kurs = sql_getValue('SELECT value FROM currencies WHERE name="USD"');
        $cache_name = 'currencies';
        // Проверка - если менялся курс, надо обновить все цены
		if (!cache_table_test($cache_name, array('currencies'), true) && $kurs > 0) {
		    $sql = 'UPDATE objects SET price_dollar = price_rub / '.$kurs.', price_dollar_print = price_rub_print / '.$kurs;
		    sql_query($sql);
		    $sql = 'UPDATE obj_elem_free SET price_metr = price / '.$kurs;
		    sql_query($sql);
			cache_save($cache_name, '', true);
		}
        return $kurs;
    }


    /**
     * Калькулятор примерной стоимости квартиры
     */
    function showCalculator(&$params){
		$house_type = sql_getRows('SELECT id, name FROM obj_housetypes WHERE 1 ORDER BY id, name ASC', true);
		$house_type['0'] = 'не выбрано';
    	$rooms = array(
    		'0' => 'не выбрано',
    		'1' => 1,
    		'2' => 2,
    		'3' => 3,
    		'4' => 4,
    		'5' => '5 и более'
    	);
		$distance = array(
			'0' => 'не выбрано',
			'1' => 'До 5 минут пешком',
			'2' => '5-10 минут пешком',
			'3' => '10-15 минут пешком',
			'4' => 'Более 15 минут пешком',
			'5' => 'До 5 минут транспортом',
			'6' => '5-10 минут транспортом',
			'7' => '10-15 минут транспортом и далее',
		);
		$storeys_number =array(
			'0' => 'не выбрано',
			'1' => 'до 9 этажа',
			'2' => 'от 10 до 22 этажа',
			'3' => 'более 22',
		);

		$ret = array();
		$page = & Registry::get('TPage');

		$metro = sql_getRows('SELECT id, name FROM obj_locat_metrostations WHERE 1 ORDER BY id, name ASC', true);
//		$metro[0] = 'не выбрано';

		//Читаем файл настроек
		chdir ("./configs");
		$filename = "settings.txt";
		$handle = fopen($filename, 'r');
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		chdir ("../");
		$settings = explode("##", $contents);

		//Массив обязательных полей
		$required = unserialize($settings[0]);
		$percent = unserialize($settings[1]);

        $form = new TForm(null, $this);
    	$form->form_name='сalculator';
    	$form->elements=array(
    		'metro_id'	=>	array(
    			'name'		=> 'metro_id',
    			'type'		=> 'select',
    			'options'	=> $metro,
    			'text'		=> 'Метро',
    			'req'		=> (isset($required[1]))?1:0,
                'atrib'    => 'style="width: 100%"',
    		),
    		'house_type'	=>	array(
    			'name'		=> 'house_type',
    			'type'		=> 'select',
    			'options'	=> $house_type,
    			'text'		=> 'Тип дома',
    			'req'		=> (isset($required[2]))?1:0,
                'atrib'    => 'style="width: 100%"',
    		),
    		'rooms'	=>	array(
    			'name'		=> 'rooms',
    			'type'		=> 'select',
    			'options'	=> $rooms,
    			'text'		=> 'Количество комнат',
    			'req'		=> (isset($required[3]))?1:0,
                'atrib'    => 'style="width: 100%"',
    		),
    		'storey'	=>	array(
    			'name'		=> 'storey',
    			'type'		=> 'radio',
    			'text'		=> 'Этаж квартиры',
    			'options'	=> array('0' => 'Любой', '1' => 'Не крайний', '2' => 'Крайний'),
    			'value'		=> 0,
    			'req'		=> (isset($required[4]))?1:0,
    			'onerror'	=> '{#msg_err_invalid_phone#}',
    		),
    		'storeys_number'=>	array(
    			'name'		=> 'storeys_number',
    			'type'		=> 'select',
    			'options'	=> $storeys_number,
    			'text'		=> 'Этажность',
    			'req'		=> (isset($required[5]))?1:0,
                'atrib'    => 'style="width: 100%"',
    		),
    		'distance'	=>	array(
    			'name'		=> 'distance',
    			'type'		=> 'select',
    			'options'	=> $distance,
    			'text'		=> 'Расстояние от метро',
    			'req'		=> (isset($required[6]))?1:0,
                'atrib'    => 'style="width: 100%"',
    		),
    		'total_area'=>	array(
    			'name'		=> 'total_area',
    			'type'		=> 'text',
    			'text'		=> 'Общая площадь кв.м.',
    			'req'		=> (isset($required[7]))?1:0,
    			'onerror'	=> '{#msg_err_invalid_phone#}',
                'atrib'    => 'style="width: 100%" class="input_text"',
    		),
			array(
				'name'	=>	'button1',
				'type'	=>	'submit',
				'value'	=>	$page->tpl->get_config_vars("calculate"),
				'group'	=>	'system',
				'atrib'	=>	'class="Button"',
			),
			array(
				'name'	=>	'button2',
				'type'	=>	'reset',
				'value'	=>	$page->tpl->get_config_vars("reset"),
				'group'	=>	'system',
				'atrib'	=>	'class="Button"',
			),
		);
		$fdata=$form->generate();

		$fdata['form']['action'] = $page->content['href']."#report";
		$fdata['form']['width'] = '80%';

		if (empty($fdata['form']['errors']) && isset($_POST['fld'])) {
			$fdata['form']['report'] = $this->calculate ($percent);
		}

		$ret['fdata'] = $fdata;
		return $ret;
    }

    /**
     * Вычисление данных
     */
    function calculate ($percent){
    	$page = & Registry::get('TPage');

		if ($_POST['fld']['rooms'][0]) {
			$rooms = ($_POST['fld']['rooms'][0]=='5')?" AND room>'".$_POST['fld']['rooms'][0]."'":" AND room='".$_POST['fld']['rooms'][0]."'";
		}
		else {
			$rooms = "";
		}

		if ($_POST['fld']['storey'][0]) {
			// не крайний
			if ($_POST['fld']['storey'][0]=='1'){
				$storey = " AND storey>1 AND storey<storeys_number ";
			}
			// крайний
			if ($_POST['fld']['storey'][0]=='2'){
				$storey = " AND (storey=1 OR storey=storeys_number) ";
			}
		}
		else {
			$storey = "";
		}

		if ($_POST['fld']['storeys_number'][0]) {

			// до 9 этажа
			if ($_POST['fld']['storeys_number'][0]=='1'){
				$storeys_number = " AND storeys_number<10 ";
			}
			// от 10 до 22 этажа
			if ($_POST['fld']['storeys_number'][0]=='2'){
				$storeys_number = " AND storeys_number>=10 AND storeys_number<23 ";
			}
			// более 22
			if ($_POST['fld']['storeys_number'][0]=='3'){
				$storeys_number = " AND storeys_number>=23 ";
			}
		}
		else {
			$storeys_number = "";
		}

		if ($_POST['fld']['distance'][0]) {
			switch ($_POST['fld']['distance'][0]) {
			    case 1:
		        	$distance = " AND metro_dest_value<=5 AND metro_dest_text='0'";
       				break;
			    case 2:
		        	$distance = " AND metro_dest_value>5 AND metro_dest_value<=10 AND metro_dest_text='0'";
       				break;
			    case 3:
		        	$distance = " AND metro_dest_value>10 AND metro_dest_value<=15 AND metro_dest_text='0'";
       				break;
			    case 4:
		        	$distance = " AND metro_dest_value>15 AND metro_dest_text='0'";
       				break;
			    case 5:
		        	$distance = " AND metro_dest_value<=5 AND metro_dest_text='1'";
       				break;
			    case 6:
		        	$distance = " AND metro_dest_value>5 AND metro_dest_value<=10 AND metro_dest_text='1'";
       				break;
			    case 7:
		        	$distance = " AND metro_dest_value>10 AND metro_dest_text='1'";
       				break;
			}
		}
		else {
			$distance = "";
		}

		//Составляем sql запрос для поиска похожих квартир
		$sql = "SELECT AVG(price_rub/total_area) AS summa FROM `objects` WHERE
		metro_id='".$_POST['fld']['metro_id'][0]."'
		".(($_POST['fld']['house_type'][0])?" AND house_type='".$_POST['fld']['house_type'][0]."'":"").
		$rooms.
		$storey.
		$storeys_number.
		$distance;
		$summa = sql_getValue($sql);
		$summa = (int)$_POST['fld']['total_area']*$summa;

		if ($summa) {
			switch ($_POST['fld']['rooms'][0]) {
			    case 1:
		        	$down = $summa - ($percent[10]*$summa/100);
		        	$up = $summa + ($percent[11]*$summa/100);
       				break;
			    case 2:
		        	$down = $summa - ($percent[20]*$summa/100);
		        	$up = $summa + ($percent[21]*$summa/100);
       				break;
			    case 3:
		        	$down = $summa - ($percent[30]*$summa/100);
		        	$up = $summa + ($percent[31]*$summa/100);
       				break;
			    case 4:
		        	$down = $summa - ($percent[40]*$summa/100);
		        	$up = $summa + ($percent[41]*$summa/100);
       				break;
			    case 5:
		        	$down = $summa - ($percent[50]*$summa/100);
		        	$up = $summa + ($percent[51]*$summa/100);
       				break;
			    default:
		        	$down = $summa - ($percent[10]*$summa/100);
		        	$up = $summa + ($percent[11]*$summa/100);
		        	break;
			}
			$down = number_format ($down, 0, ',', ' ');
			$up = number_format ($up, 0, ',', ' ');

			$search = array("/{down}/", "/{up}/");
			$replace = array($down, $up);
			$report['yes'] = preg_replace($search, $replace, $page->tpl->get_config_vars("calculate_ok"));
		}
		else {
			$report['no'] = $page->tpl->get_config_vars("calculate_fail");//"По заданным условиям ничего не найденно, попробуйте изменить условия.";
		}

		return $report;
	}

	function getTotalObjects(){
        return sql_getValue('SELECT COUNT(*) FROM `objects` WHERE visible>0');
	}

	function getTotalRntObjects(){
        return sql_getValue('SELECT COUNT(*) FROM `rnt_objects` WHERE visible>0');
	}
}

?>