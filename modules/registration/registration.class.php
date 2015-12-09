<?php
define('SHOW_LEVELS', 3);
define('DISTRICT', 195);

class TRegistration {
	var $name = 'registration';

	function TRegistration() {
	}

	function show_auth(&$params) {
		$auth_obj = &Registry::get('TUserAuth');
		$id = $auth_obj->checkHash();
		if ($id){
			$sql = "UPDATE auth_users SET auth=1 WHERE id=".$id;
			sql_query($sql);
			$auth_obj->login($id);
			$r = get('redirect', '', 's');
			$profile = $auth_obj->getUserProfile($id);

			$profile['type_id'] = sql_getValue("SELECT name FROM auth_groups WHERE id=(SELECT group_id FROM auth_users_groups WHERE user_id=".$profile['id'].")");
			$profile['district_id'] = sql_getValue("SELECT name FROM obj_locat_districts WHERE id=".$profile['district_id']." AND visible>0");
			SendNotify('CLIENT_AUTH_TO_ADMIN', $id, $profile);

			if ($r){
				redirect($r);
			} else {
				redirect('/cabinet/success_auth');
			}
		} else {
			redirect('/nko/registration');
		}
	}

	function show_forget_password(){
		$auth_obj = &Registry::get('TUserAuth');
		$id = $auth_obj->checkHash();
		if (!$id){
			$auth_obj->sendHash('SEND_HASH', mysql_escape_string(get('login', '', 'pg')));
		} else {
			$auth_obj->login($id);
			redirect('/cabinet/cart');
		}
	}

	function show_menu(){
		$auth_obj = & Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		if ($profile){
			$menu_obj = new TMenu;

			$params['_block']['params'] = array(
				'return_parent' => true,
				'levels' => 2,
				'full' => true,
				'start_uri' => '/cabinet/',
				'types' => array('text', 'module'),
			);
			$ret['authmenu'] = $menu_obj->show_menu($params['_block']['params']);
		} else { $ret = true;}
		return $ret;
	}

	function show_form_user(&$params) {
		$page = &Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'] . "__" . lang().'.conf');
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		if (!$profile){
			$fld = (isset($_POST) && isset($_POST['fld'])) ? $_POST['fld'] : array();
			if (!empty($fld)){
				//получаем все строковые константы
				$sql = "SELECT name, strings.* FROM strings WHERE module='".$this->name."'";
				$str = sql_getRows($sql, true);

				//проверка полей и сохранение
				if (empty($fld['login'])){
					$error['login'] = $str['error_login_empty']['value'];
				} else {
					$prf = $auth_obj->getUserProfile($auth_obj->getId($fld['login']));
					if (isset($prf['auth']) && $prf['auth'] == 1) $error['login'] = $str['error_login']['value']; //пользователь существует
					if (!CheckMailAddress($fld['login'])) $error['login'] = $str['error_login_incorrect']['value']; // Некорректный email
				}

				$req_fields = array('fio', 'password1', 'password2');
				foreach($req_fields as $key=>$val){
					if (empty($fld[$val]))
						$error[$val] = $str['error_'.$val]['value'];
				}

				$keystring = $_SESSION['captcha_keystring'];
    	    	unset($_SESSION['captcha_keystring']);
        		if (empty($keystring) || $fld['captcha'] !== $keystring) {
        			$error['captcha'] = $str['error_captcha']['value']; // Ошибка при вводе проверочной комбинации
    	    	}

				if ($fld['password1']!=$fld['password2']){$error['global'][] = $str['error_passwords']['value'];}
				//if ($fld['login']!=$fld['login2']){$error['global'][] = $str['error_emails']['value'];}

				if (empty($error)){
					if (isset($prf['auth']) && $prf['auth'] == 0){
						$sql = "DELETE FROM auth_users WHERE id = ".$prf['id'];
						sql_query($sql);
					}
					//сохраняем пользователя
					$fld['auth'] = 0;
					$fld['visible'] = 0;
					$fld['password'] = $fld['password1'];
					unset($fld['password1']);
					unset($fld['password2']);
					unset($fld['captcha']);

					$fld['reg_date'] = date('Y-m-d H:i:s');
					$fields = "`".implode( "`,`", array_keys($fld))."`";
					$values = "";
					foreach ($fld as $k=>$v){
						if ($k == 'password'){$v = md5($v);}
						$values .= ",'".mysql_escape_string($v)."'";
					}

					$sql= "INSERT INTO auth_users (".$fields.") VALUES (".substr($values,1).")";
					sql_query($sql);
					$id = sql_getLastId();

					//получаем идентификатор группы пользователя(группа с наивысшим приоритетом = 1)
					$sql= "SELECT id FROM `auth_groups` ORDER BY priority ASC LIMIT 1";
					$group_id = sql_getValue($sql);

					if ($id) {
						$sql = "UPDATE auth_users SET auth=1 WHERE id=".$id;
						sql_query($sql);

						//прикрепляем пользователя к группе
						$sql = "REPLACE INTO auth_users_groups (user_id, group_id) VALUES('".$id."','".$group_id."')";
						sql_query($sql);

						$auth_obj = &Registry::get('TUserAuth');

						$fld['user_id'] = $id;
						$fld['site_name'] = $page->tpl->get_config_vars('title');
						$fld['site_url'] = $_SERVER['HTTP_HOST'];
						$fld['hash'] = $auth_obj->fp_createChPassHash($fld['login']);
						$fld['user'] = true;

						SendNotify('USER_REGISTRATION_TO_ADMIN', $id, $fld);
//						SendNotify('CLIENT_REGISTRATION', $id, $fld);
/*
						$redirect = '/cabinet/cart';
						session_start();
						unset($_SESSION['smsm']['login_registration_redirect']);
						session_write_close();
						redirect($redirect);
*/
						$ret['form'] = false;
						$ret['error']['global'] = 'Учетная запись была создана.';
						$page = &Registry::get('TPage');
						unset($page->tpl->_tpl_vars['text']);
						return $ret;
					} else {
						$error['global'] = 'Ошибка создания учетной записи! Свяжитесь с администратором сайта.';
					}
				}
			}
		}
		$ret['form'] = true;
		$ret['fld'] = isset($fld) ? $fld : array();
		$ret['error'] = isset($error) ? $error : array();
		$ret['dirs'] = get('dirs', $_SERVER['REDIRECT_URL'],'pg');
		return $ret;
	}


	function verifyPhone($phone){
		/**
		 * Делаем формат телефона по-мягче
		//формат ввода: (111)111-11-11
		//пишем регулярное выражение
		if (preg_match("/\([0-9]+?\)[0-9]{3}-[0-9]{2}-([0-9]+)$/", $phone)){
			return true;
		}
		*/
		if (preg_match('/[^0-9\s\(\)\-]/',$phone)) {
			return false;
		}
		if (strlen(preg_replace('/[^0-9]/','',$phone)) < 7)
		{
			return false;
		}
		return true;
	}

	function CheckNumber($value,$len){
		if (!is_numeric($value)) return false;
		if (strlen($value) != $len) return false;
		return true;
	}

	function show_login_form(&$params){
		$ret = array();
		$ret['form'] = true;
		$auth_obj = &Registry::get('TUserAuth');
		$user_id = $auth_obj->getCurrentUserId();
		if ($user_id) {
			$ret['form'] = false;
			redirect('/cabinet/cart/');
			$ret['dirs'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
		} else {
			$ret['dirs'] = '/cabinet/login';
		}
		return $ret;
	}

	function show_login_registration_form(&$params) {
		$what_order = $_POST['what_order'];
		$auth_obj = &Registry::get('TUserAuth');
		$user_id = $auth_obj->getCurrentUserId();

		if ($what_order != "") {
			$redirect = "/cabinet/basket/step1?filter";
		}
		else {
			$redirect = isset($_SESSION['smsm']['login_registration_redirect']) ? $_SESSION['smsm']['login_registration_redirect'] : '/cabinet';
		}
		if ($user_id) {
			session_start();
			$_SESSION['what_order'] = $what_order;
			unset($_SESSION['smsm']['login_registration_redirect']);
			session_write_close();
			redirect($redirect);
		}
		// Показываем форму экспресс-регистрации
		$_GET['express'] = 1;

		if (basename($_SERVER['HTTP_REFERER']) == 'basket') {
			$cart_obj = &Registry::get('TCart');
			$cart = $cart_obj->show_basket();

			$basket_buttons = ($cart['products'])?1:0;
		}

		$ret = $this->show_form();
		$ret['basket_buttons'] = $basket_buttons;
		return $ret;
	}

	function show_cart(){
		$auth_obj = &Registry::get('TUserAuth');
		session_start();
		$post = isset($_POST['fld']) ? $_POST['fld'] : array();
		session_write_close();

		$page = &Registry::get('TPage');

		$user_id = $auth_obj->getCurrentUserId();

		if (!$user_id){
			session_start();
			$_SESSION['smsm']['login_registration_redirect'] = $page->content['href'];
			session_write_close();
			redirect("/registruser/");
		}

		if ($user_id && !empty($post)){
			//получаем все строковые константы
			$sql = "SELECT name, strings.* FROM strings WHERE module='".$this->name."'";
			$str = sql_getRows($sql, true);
			//обновляем карточку данного пользователя

			if (empty($post['login'])){
				$error['email'] = $str['error_login_empty']['value'];
			} else {
				$prf = $auth_obj->getUserProfile($auth_obj->getId($post['login']));
				if (isset($prf['auth']) && $prf['auth'] == 1 && $prf['id']!=$user_id) $error['login'] = $str['error_login']['value']; //пользователь существует
				if (!CheckMailAddress($post['login'])) $error['login'] = $str['error_login_incorrect']['value']; // Некорректный email
			}

			$req_fields = array('fio', 'login');
			foreach($req_fields as $key=>$val){
				if (empty($post[$val]))
					$error[$val] = $str['error_'.$val]['value'];
			}
			if ($post['password1']!=$post['password2']){$error['global'][] = $str['error_passwords']['value'];}
			if ($post['login']!=$post['login2']){$error['global'][] = $str['error_emails']['value'];}

			if (empty($error)){
				if ($post['password1']) $post['password'] = md5($post['password1']);
				unset($post['login2']);
				unset($post['password1']);
				unset($post['password2']);
				unset($post['password0']);
				$str = "";
				foreach ($post as $k=>$v){
					$str .= ", `".$k."`='".e($v)."' ";
				}

				$sql = "UPDATE auth_users SET ".substr($str,1)." WHERE id=".$user_id;

				sql_query($sql);
				$auth_obj->setLoginCookie();
				$auth_obj->setUserData($user_id);
			} else {
				$profile = $post;
			}
		}
		if (!isset($profile)){
			$profile = $auth_obj->getCurrentUserData();
		}

		$ret['form'] = true;
		$ret['fld'] = isset($profile) ? $profile : array();
		$ret['error'] = isset($error) ? $error : array();
		$ret['dirs'] = get('dirs', $_SERVER['REDIRECT_URL'],'pg');
		return $ret;
	}

	########################
}

?>