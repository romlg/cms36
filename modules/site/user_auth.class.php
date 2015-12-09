<?php

class TUserAuth {

	var $users_in_groups = array();
	var $users_rights = array();
	var $userdata = array();
	var $privs = array();

	var $cached_user = array();

	//constructor
	// когда создаем в первый раз за сессию, заодно и обновим данные из кук
	function TUserAuth() {
		//$this->updatePrivs();
	}

	function registerPriv($priv_code) {
		if (in_array($priv_code, $this->privs)) {
			return;
		}
		$this->privs[] = $priv_code;
	}

	function getPrivs() {
		return $this->privs;
	}

	//функция проверяет, авторизован ли пользователь или нет
	//возвращает либо профиль, либо false
    function checkLogin($params = array()){
		$login    = get('login','','pgc');
		$password = get('password','','pgc');
		$params['dirs'] = get('dirs', (isset($params['dirs']) ? $params['dirs']: ''),'pg');
		if (empty($params['dirs'])){ $params['dirs'] = $_SERVER['REQUEST_URI'];}
		$uid = (int)get(AUTH_USER_COOKIE_NAME, 0, 'c');
		if (!$uid && !empty($login) && !empty($password)){
			$uid = $this->checkUser($login, $password);
			if ($uid && $this->getAuth($login)){
			    $this->login($uid);
			} else if ($uid) {
				// Не стоит признак auth
				$error = 'registration_user_not_allow';
				return array($ret, 'params'=> $params, 'error' => $error);
			} else if (!$uid) {
				// Неверный логин/пароль
				$error = 'registration_error_user_not_exist';
				return array($ret, 'params'=> $params, 'error' => $error);
			}
		}
		if ($uid){
			$GLOBALS['client_id'] = $uid;
			//обновляем время последнего доступа на сайт
			$sql = "UPDATE auth_users SET last_visited = UNIX_TIMESTAMP() WHERE id=".$uid;
			mysql_query($sql);
		    $this->setUserData($uid);
			//получаем профиль
		    $ret = $this->userdata;
		} else {
			$ret = false;
		}
		if (isset($params['var'])){
			return array($params['var']=>$ret, 'params'=> $params);
		} else {
			return array($ret, 'params'=> $params);
		}
    }

    //функция возвращает авторизован ли пользователь или нет, либо id, либо NULL
    function getAuth($login = ''){
    	if (empty($login)) {$login = get('login','','pgc');}
        $sql = "SELECT id FROM auth_users WHERE login='".e($login)."' AND auth = 1";
        $id = sql_getValue($sql, __FILE__, __LINE__);
        if (sql_getErrNo()){
        	 $sql = "SELECT id FROM auth_users WHERE login='".e($login)."'";
       		 $id = sql_getValue($sql, __FILE__, __LINE__);
        }
        return $id;
    }

    //функция проверяет наличие пользователя по его логину возвращает id
    function getId($login = ''){
    	if (empty($login)) {$login = get('login','','pgc');}
        $sql = "SELECT id FROM auth_users WHERE login='".e($login)."'";
        $id = sql_getValue($sql, __FILE__, __LINE__);
        return $id;
    }
    function getLogin($id = ''){
    	if (empty($id)) {$id = get('id','','pgc');}
        $sql = "SELECT login FROM auth_users WHERE id='".e($id)."'";
        $login = sql_getValue($sql, __FILE__, __LINE__);
        return $login;
    }

    //отправка запроса на смену пароля
    function sendHash($tpl = 'SEND_HASH', $login){
    	if (empty($login)) { $login = mysql_real_escape_string(get('login','','pgc'));}
    	if ($this->fp_userExists($login)){
    		$user_id = $this->cached_user['id'];
    		$data['site_name'] = $_SERVER["HTTP_HOST"];
    		$hash = $this->fp_createChPassHash($login);
    		if (!$hash){ return false;}
    		$data['hash'] = $hash;
    		$data['user'] = $this->cached_user;
    		return SendNotify($tpl, $user_id, $data);
    	}
    	return false;
    }

    //проверяет полученный hash, возвращает true или false
    function checkHash($params = array()){
    	$id = mysql_real_escape_string(get('id', 0,'pg'));
    	$hash = get('hash', 0,'pg');
    	$sql = "SELECT login FROM auth_users WHERE id=".$id;
    	$login = sql_getValue($sql, __FILE__, __LINE__);
    	$ret = $this->fp_checkChPassHash($login, $hash);
    	if (isset($params['var'])){
        	return array($params['var']=>$ret);
        } else {
        	return $ret;
        }
    }

    //функция изменения пароля
    function changePassword($params = array()){
    	$login = get('login', '','pg');
    	$password1 = get('password1', '','pg');
    	$password2 = get('password2', '','pg');
    	$hash = get('hash', 0,'pg');

    	if ($password1 !== $password2){
    		$ret = false;
    	} else {
    		$ret = $this->fp_changePassword($login, $password1, $hash);
    	}
    	if (isset($params['var'])){
        	return array($params['var']=>$ret);
        } else {
        	return $ret;
        }
    }

    function unloginUser($params){
    	$this->unlogin();
    	if (isset($params['redirect'])){
    		redirect($params['redirect']);
    	} else {
    		redirect($_SERVER['HTTP_REFERER']);

    	}
    }



	// вернет профиль юзера - без пароля, естессно
	function getUserProfile($user_id) {
		$sql = "SELECT * FROM `auth_users` WHERE id = '".mysql_real_escape_string($user_id)."'";
		$res = sql_getRow($sql, __FILE__, __LINE__);
		if (empty($res)) {
			return false;
		}
		//unset($res['password']);
		return $res;
	}

	// вернет id текущего юзера
	function getCurrentUserId() {
		if (!$this->userdata) {
			return 0;
		}
		return $this->userdata['id'];
	}

	// возвращает профиль текущего пользователя
	function getCurrentUserData() {
		if (!$this->userdata) {
			return array();
		}
		return $this->userdata;
	}

	// проверяет, существует ли пользователь с таким именем/паролем; если существует - вернет его id
	function checkUser($login, $pass) {
		$login = mysql_real_escape_string($login);
		$pass = mysql_real_escape_string($pass);
		$sql = "select id from auth_users where login='".$login."' and password=md5('".$pass."')";
		$res = sql_getValue($sql, __FILE__, __LINE__);
		return $res;
	}

	// возвращает, принадлежит ли пользователь к группе (результаты кэшируется)
	function isUserInGroup($user_id, $group_id) {
		$user_id = mysql_real_escape_string($user_id);
		$group_id = mysql_real_escape_string($group_id);
		if (!$user_id || !$group_id) {
			return false;
		}

		if (isset($this->users_in_groups[$group_id][$user_id])) {
			return $this->users_in_groups[$group_id][$user_id];
		}
		$sql = "select
						count(*) as cvo
					from
						auth_users_groups
					where
						user_id = '".$user_id."' and
						group_id = '".$group_id."'";
		$res = sql_getValue($sql, __FILE__, __LINE__);
		$res = $res ? true : false;
		$this->users_in_groups[$group_id][$user_id] = $res;
		return $res;
	}

	// возвращает, есть ли у пользователя право на действие
	function hasRight($user_id, $priv_code) {
		$user_id = mysql_real_escape_string($user_id);
		$priv_code = mysql_real_escape_string($priv_code);
		if (!$user_id || !$priv_code) {
			return false;
		}

		if (!in_array($priv_code, $this->privs)) {
			return false;
		}

		if (isset($this->users_rights[$user_id][$priv_code])) {
			return $this->users_rights[$user_id][$priv_code];
		}

		$sql = "select
						count(*) as cvo
					from
						auth_users_privs
					where
						user_id = '".$user_id."' and
						priv_code = '".$priv_code."'";
		$res = sql_getValue($sql, __FILE__, __LINE__);
		if ($res > 0) {
			$this->users_rights[$user_id][$priv_code] = 1;
			return 1;
		}

		$sql = "select
						count(users.id)
					from
						auth_users right join auth_users_groups on
							(auth_users.id = auth_users_groups.user_id)
						right join auth_groups on
							(auth_users_groups.group_id = auth_groups.id)
						right join auth_groups_privs on
							(auth_groups.id = auth_groups_privs.group_id)
					where
						auth_groups_privs.priv_code = '".$priv_code."' and
						auth_users.id = '".$user_id."'";
		$res = sql_getValue($sql, __FILE__, __LINE__);
		if ($res > 0) {
			$this->users_rights[$user_id][$priv_code] = 1;
			return 1;
		}

		$this->users_rights[$user_id][$priv_code] = 0;
		return 0;
	}

	// производит восстановление прав, если юзер залогинен
	function updatePrivs() {
		$uid = isset($_COOKIE[AUTH_USER_COOKIE_NAME]) ? $_COOKIE[AUTH_USER_COOKIE_NAME] : null;
		if (!isset($uid)) return;
		if ($this->checkCookie($uid)) {
			$this->login($uid);
		}

		if (empty($this->userdata['id'])) {
			return;
		}

		$this->getUserPrivs($this->userdata['id']);
	}

	// возвращает все привилегии пользователя
	function getUserPrivs($id) {
		$id = mysql_real_escape_string($id);
		$sql = "select
						auth_users_privs.priv_code
					from
						auth_users_privs
					where
						auth_users_privs.user_id = ".$id;
		$r = sql_getRows($sql, __FILE__, __LINE__);
		foreach ($r as $k => $t) {
			$this->users_rights[$id][$t["priv_code"]] = 1;
		}

		$sql = "select
					auth_groups_privs.priv_code
					from
						auth_users right join auth_users_groups on
							(auth_users.id = auth_users_groups.user_id)
						right join auth_groups on
							(auth_users_groups.group_id = auth_groups.id)
						right join auth_groups_privs on
							(auth_groups.id = auth_groups_privs.group_id)
					where
						auth_users.id = '".$id."'";
		$r = sql_getRows($sql, __FILE__, __LINE__);
		foreach ($r as $k => $t) {
			$this->users_rights[$id][$t['auth_group_privs']['priv_code']] = 1;
		}
	}

	// проверяет правильность пришедшей из браузера хэш-куки
	function checkCookie($uid) {
		$right_str = $this->constructCookieStr($uid);
		return (isset($_COOKIE[AUTH_HASH_COOKIE_NAME]) && $_COOKIE[AUTH_HASH_COOKIE_NAME] == $right_str);
	}

	// создает хэш-куки по id пользователя
	function constructCookieStr($uid) {
		$uid = mysql_real_escape_string($uid);
		$t_str = $_SERVER['REMOTE_ADDR'].'.'.$uid.'.';
		$sql = "select password from auth_users where id = '".$uid."'";
		$pass = sql_getValue($sql);
		$t_str = $t_str.$pass;
		$t_str = md5($t_str);
		return $t_str;
	}

	// устанавливает куки идентефицирующие пользователя
	function setLoginCookie() {
		$t_str = $this->constructCookieStr($this->userdata['id']);
		setcookie(AUTH_HASH_COOKIE_NAME, $t_str, time() + 24 * 3600 * 30, '/');
		setcookie(AUTH_USER_COOKIE_NAME, $this->userdata['id'], time() + 24 * 3600 * 30, '/');
	}

	// снимает те же самые куки
	function unsetLoginCookie() {
		setcookie(AUTH_HASH_COOKIE_NAME, '', 0, '/');
		setcookie(AUTH_USER_COOKIE_NAME, '', 0, '/');
	}

	//записывает данные пользователя в переменную класса "данные текущего пользователя"
	function setUserData($uid) {
		$this->userdata = $this->getUserProfile($uid);
		if (!empty($this->userdata)) {
			$GLOBALS['client_id'] = $uid;
		}
		$this->userdata['password'] = '';
	}

	// логинит пользователя
	function login($uid) {
		if (!$uid) {
			return;
		}
		$this->setUserData($uid);
		$this->setLoginCookie();
	}

	// отлогинивает пользователя
	function unlogin() {
		$this->unsetLoginCookie();
		unset($this->userdata);
	}

	/* забыли пароль */

	function fp_userExists($login) {
		if (!empty($this->cached_user)) {
			return true;
		}
		$login = mysql_real_escape_string($login);
		$sql = "select * from auth_users where login='".$login."'";
		$row = sql_getRow($sql, __FILE__, __LINE__);
		if ($row) {
			$row['password'] = '';
			$this->cached_user = $row;
			return true;
		}
		return false;
	}

	function fp_getUserEmail() {
		//проверяет, есть ли поле email, если его нет, то значит в качестве email используется логин
		if(isset($this->cached_user['login'])) {
			return $this->cached_user['login'];
		}
		return $this->cached_user['login'];
	}

	function fp_changePassword($login, $new_pass, $hash) {
		$login = mysql_real_escape_string($login);
		$new_pass = mysql_real_escape_string($new_pass);
		$hash = mysql_real_escape_string($hash);

		$sql = "UPDATE auth_users SET chpass_hash='', chpass_hash_date=0, password=md5('".$new_pass."') WHERE login='".$login."' AND chpass_hash='".$hash."' AND chpass_hash<>'' AND chpass_hash_date>=".(time() - AUTH_CHPASS_HASH_LIFETIME * 86400);
		$res = sql_query($sql);
		if (!$res) {
			return false;
		}
		return true;
	}

	function fp_createChPassHash($login) {
		$hash = getUniqueId();
		$sql = "UPDATE auth_users SET chpass_hash='".$hash."', chpass_hash_date='".time()."' WHERE login='".$login."'";
		$res = sql_query($sql);
		if ($res && $hash) {
			return $hash;
		}
		return false;
	}

	function fp_checkChPassHash($login, $hash) {
		$login = mysql_real_escape_string($login);
		$hash = mysql_real_escape_string($hash);
		$sql = "SELECT id FROM auth_users WHERE login='".$login."' AND chpass_hash='".$hash."' AND chpass_hash<>'' AND chpass_hash_date>=".(time() - AUTH_CHPASS_HASH_LIFETIME * 86400);
		$res = sql_getValue($sql, __FILE__, __LINE__);
		return $res ? $res : false;
	}

}

?>