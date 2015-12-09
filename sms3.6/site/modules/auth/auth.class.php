<?php

/**
 * Класс для регистрации/авторизации на сайте
 */

class TAuth
{

    /**
     * @var string название модели для пользователя
     */
    protected $_model = 'User';
    /**
     * @var Object объект пользователя
     */
    protected $_user = null;
    /**
     * @var string таблица для хранения пользователей
     */
    protected $_table = 'auth_users';
    /**
     * @var string имя колонки с логином
     */
    protected $_identityField = 'login';
    /**
     * @var string имя колонки с паролем
     */
    protected $_credentialField = 'password';
    /**
     * @var string имя колонки с e-mail
     */
    protected $_emailField = 'email';
    /**
     * @var string имя колонки с флагом - разрешен или нет доступ на сайт
     */
    protected $_authField = 'auth';
    /**
     * @var string таблица для хранения ссылок на соц. сети
     */
    protected $_table_socials = 'auth_users_socials';
    /**
     * @var строковые константы
     */
    protected $_messages = array();

    protected $_errors = array();

    public function TAuth() {
        $this->_messages = sql_getRows("
            SELECT
                IF(t2.value IS NULL, t1.name, t2.name) AS name,
                IFNULL(t2.value,t1.value) AS value
            FROM strings AS t1
            LEFT JOIN strings AS t2 ON (t1.module=t2.module AND t1.name=t2.name AND t2.lang='" . lang() . "' AND t2.root_id='" . ROOT_ID . "')
            WHERE t1.lang='" . LANG_DEFAULT . "' AND t1.root_id='" . getMainRootID() . "' AND t1.module='auth'
        ", true);

        // Подключение библиотеки HybridAuth для авторизации через соц. сети
        $this->init_hybridauth();
    }

    /**
     * Отображение формы авторизации
     * @return array
     */
    public function loginForm($params) {
        $ret = $this->loginAction($params);
        if (is_array($ret)) return $ret;
        else {
            if (isset($params['frame']) && $params['frame'] == true) {
                echo '<script type="text/javascript">
                    document.domain = "' . (defined('MAIN_AUTH_DOMAIN') ? str_replace('http://', '', MAIN_AUTH_DOMAIN) : $_SERVER['HTTP_HOST']) . '";
                    top.location.href = top.location.href;
                </script>';
                die();
            } else {
                redirect($ret);
            }
        }
    }

    /**
     * @return array|string
     */
    public function loginAction($params) {

        $login = get($this->_identityField, '', 'p');
        $password = get($this->_credentialField, '', 'p');

        $ret = array($this->_identityField . '_error' => array());

        if ($login && $password) {
            if (($error = $this->trylogin($login, $password)) !== true) {
                $ret[$this->_identityField . '_error'][] = $error;
            }
            else {
                return $_SERVER['REQUEST_URI'];
            }
        }
        else {
            $redirect = false;
            if ($this->hybridauth() === true) {
                // успешно авторизовались через соц. сети
                $redirect = true;
            }
            elseif ($errors = $this->getErrors()) {
                // ошибка
                $domains = $this->getDomainForCookie();
                foreach ($domains as $domain) {
                    setcookie('msg', $errors, time() + 24 * 3600 * 30, '/', $domain);
                }
                $redirect = true;
            }
            elseif (isset($_GET['provider_auth']) && $_GET['provider_auth'] == 1) {
                // какая-то ошибка
                $domains = $this->getDomainForCookie();
                foreach ($domains as $domain) {
                    setcookie('message', $this->_user ? 'auth_link_provider_error' : 'auth_provider_error', time() + 24 * 3600 * 30, '/', $domain);
                }
                $redirect = true;
            }
            if ($redirect) {
                if (isset($params['socials_popup']) && $params['socials_popup'] == true) {
                    echo '<script type="text/javascript">
                        document.domain = "' . (defined('MAIN_AUTH_DOMAIN') ? str_replace('http://', '', MAIN_AUTH_DOMAIN) : $_SERVER['HTTP_HOST']) . '";
                        if (window.opener){
                            window.opener.top.location.reload();
                        }
                        window.self.close();
                    </script>';
                    die();
                }
                else {
                    if (isset($_GET['return_to'])) redirect($_GET['return_to']);
                    else redirect('/');
                }
            }
        }
        return $ret;
    }

    /**
     * Авторизует пользователя по логину и паролю
     * @param $login
     * @param $password
     * @return true|string
     */
    public function trylogin($login, $password) {

        $login = mysql_real_escape_string($login);
        $pass = mysql_real_escape_string($pass);

        $sql = "SELECT * FROM {$this->_table} WHERE {$this->_identityField}='{$login}' AND {$this->_credentialField}=md5('{$password}')";
        $row = sql_getRow($sql);
        if (!$row) return $this->getMessage('wrong_login_pwd');

        if (!$row[$this->_authField]) return $this->getMessage('user_not_allow');

        $this->login($row['id']);

        return true;
    }

    /**
     * Авторизует пользователя по ID
     * @param $id
     * @return true|string
     */
    public function login($id) {
        $this->setUser($id);
        $this->setLoginCookie($id);
    }

    /**
     * отлогинивает пользователя
     */
    public function unlogin() {
        global $hybridauth;
        if (is_object($hybridauth) && isset($hybridauth)) {
            $hybridauth->logoutAllProviders();
        }
        $this->unsetLoginCookie();
        $this->_user = null;
    }

    /**
     * функция проверяет, авторизован ли пользователь или нет
     * возвращает либо объект пользователя, либо false
     * @return object|false
     */
    public function checkUser($params = array()) {
        if (isset($params['model'])) $this->_model = $params['model'];
        $ret = false;
        $user_id = (int)get(AUTH_USER_COOKIE_NAME, 0, 'c');
        if ($user_id > 0 && $this->checkCookie($user_id)) {
            $this->_user = new $this->_model($user_id);
            //обновляем время последнего доступа на сайт
            $this->_user->setData(array('last_visited' => time()));
            $this->_user->update();
            // продлеваем куки
            $this->setLoginCookie($user_id);
            // установка данных пользователя
            $this->setUser($user_id);
            // возвращаем объект пользователя
            $ret = $this->_user;
        }
        return $ret;
    }

    /**
     * вернет модель текущего залогиненного пользователя
     * @return int
     */
    public function getCurrentUser() {
        return $this->_user;
    }

    /**
     * вернет id текущего залогиненного пользователя
     * @return int
     */
    public function getCurrentUserId() {
        if (!$this->_user) {
            return 0;
        }
        return $this->_user->getId();
    }

    /**
     * возвращает профиль текущего пользователя
     * @return array
     */
    public function getCurrentUserData() {
        if (!$this->_user) {
            return array();
        }
        return $this->_user->getData();
    }

    // проверяет правильность пришедшей из браузера хэш-куки
    protected function checkCookie($uid) {
        $right_str = $this->constructCookieStr($uid);
        return (isset($_COOKIE[AUTH_HASH_COOKIE_NAME]) && $_COOKIE[AUTH_HASH_COOKIE_NAME] == $right_str);
    }

    /**
     * создает хэш-куки по id пользователя
     * @param int $uid
     * @return string
     */
    protected function constructCookieStr($uid) {
        $uid = (int)$uid;
        $t_str = $_SERVER['REMOTE_ADDR'] . '.' . $uid . '.';
        $sql = "SELECT {$this->_credentialField} FROM {$this->_table} WHERE id = {$uid}";
        $pass = sql_getValue($sql);
        $t_str = $t_str . $pass;
        $t_str = md5($t_str);
        return $t_str;
    }

    /**
     * устанавливает куки, идентифицирующие пользователя
     * @param int $uid
     */
    protected function setLoginCookie($uid) {
        $t_str = $this->constructCookieStr($uid);
        $domains = $this->getDomainForCookie();
        foreach ($domains as $domain) {
            setcookie(AUTH_HASH_COOKIE_NAME, $t_str, time() + 24 * 3600 * 30, '/', $domain);
            setcookie(AUTH_USER_COOKIE_NAME, $uid, time() + 24 * 3600 * 30, '/', $domain);
        }
    }

    /**
     * Удаляет авторизационные куки
     */
    protected function unsetLoginCookie() {
        $domains = $this->getDomainForCookie();
        foreach ($domains as $domain) {
            setcookie(AUTH_HASH_COOKIE_NAME, '', 0, '/', $domain);
            setcookie(AUTH_USER_COOKIE_NAME, '', 0, '/', $domain);
        }
    }

    /**
     * Возвращает список доменов, где ставить куку
     * @return array
     */
    protected function getDomainForCookie() {
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') return array('');
        $ret = array();
        global $site_domains;
        foreach ($site_domains as $sitename => $info) {
            $ret[] = $sitename;
            if ($info['alias']) {
                $alias = explode(',', $info['alias']);
                foreach ($alias as $a) {
                    $a = trim($a);
                    $ret[] = $a;
                }
            }
        }
        $ret = array_unique($ret);
        $ret = $this->excludeDomains($ret);
        foreach ($ret as $k => $v) {
            if (!$v) {unset($ret[$k]); continue;}
            if (strtolower(substr($v, 0, 4)) == 'www.') $v = substr($v, 4);
            if (substr($v, 0, 1) != '.' && strpos($v, '.') !== false) $v = '.' . $v;
            $ret[$k] = $v;
        }
        return $ret;
    }

    /**
     * Исключаем повторяющиеся поддомены (если в списке есть домен более верхнего уровня)
     * @param $ret
     * @return mixed
     */
    protected function excludeDomains($ret) {
        $return = array();
        foreach ($ret as $k => $v) {
            $find = false;
            foreach ($ret as $k2 => $v2) {
                if ($v == $v2) continue;
                if (substr($v, -strlen($v2)) == $v2) {
                    $find = true;
                    break;
                }
            }
            if (!$find) $return[] = $v;
        }
        return $return;
    }

    /**
     * записывает данные пользователя в переменную класса "данные текущего пользователя"
     * @param $uid
     */
    protected function setUser($uid) {
        $this->_user = new $this->_model($uid);
    }

    /**
     * Проверяет существование пользователя по логину
     * @param $login
     * @return int|false
     */
    public function isUserExists($login) {
        $login = mysql_real_escape_string($login);
        $sql = "SELECT * FROM {$this->_table} WHERE login='" . $login . "'";
        $row = sql_getRow($sql);
        if ($row) {
            return $row['id'];
        }
        return false;
    }

    // --------------------------------------------------------------
    // Изменение пароля, если пользователь забыл пароль
    // --------------------------------------------------------------

    /**
     * Создание хеша и сохранение его в БД
     * @param $login
     * @return bool|string
     */
    public function createChPassHash($login) {
        $hash = getUniqueId();
        $sql = "UPDATE {$this->_table} SET chpass_hash='{$hash}', chpass_hash_date='" . time() . "' WHERE login='{$login}'";
        $res = sql_query($sql);
        if ($res && $hash) {
            return $hash;
        }
        return false;
    }

    /**
     * Проверка хеша
     * @param $id
     * @param $hash
     * @return bool
     */
    public function checkChPassHash($id, $hash) {
        $id = mysql_real_escape_string($id);
        $hash = mysql_real_escape_string($hash);
        $sql = "SELECT id FROM {$this->_table} WHERE id='{$id}' AND chpass_hash='{$hash}' AND chpass_hash<>'' AND chpass_hash_date>=" . (time() - AUTH_CHPASS_HASH_LIFETIME * 86400);
        $res = sql_getValue($sql);
        return $res ? $res : false;
    }

    /**
     * Отправка уведомления со ссылкой для смены пароля
     * @param $login
     * @param string $tpl
     * @return true|string
     */
    public function sendChangePassNotify($login, $tpl = 'SEND_HASH') {
        if ($user_id = $this->isUserExists($login)) {
            $hash = $this->createChPassHash($login);
            if (!$hash) {
                $this->getMessage('error_create_hash');
            }
            $data = array(
                'user_id' => $user_id,
                'hash' => $hash,
                'site_name' => $_SERVER["HTTP_HOST"]
            );
            $email = sql_getValue("SELECT {$this->_emailField} FROM {$this->_table} WHERE id={$user_id}");
            $sent = Notify($tpl, $email, $data);
            if ($sent === true) return true;
            else return implode("<br>", $sent);
        }
        return $this->getMessage('no_user_with_login');
    }

    /**
     * функция изменения пароля
     * @param int $id
     * @param string $password
     * @param string $hash
     * @return array|bool
     */
    public function changePassword($id, $password, $hash) {

        $id = mysql_real_escape_string($id);
        $password = mysql_real_escape_string($password);
        $hash = mysql_real_escape_string($hash);

        $sql = "UPDATE {$this->_table} SET
                chpass_hash='',
                chpass_hash_date=0,
                {$this->_credentialField}=md5('{$password}')
            WHERE
                id='{$id}'
                AND chpass_hash='{$hash}'
                AND chpass_hash<>''
                AND chpass_hash_date>=" . (time() - AUTH_CHPASS_HASH_LIFETIME * 86400);
        $ret = sql_query($sql);
        return $ret;
    }

    // --------------------------------------------------------------

    /**
     * Инициализация библиотеки HybridAuth
     */
    function init_hybridauth() {
        global $hybridauth_config, $hybridauth;
        if (is_array($hybridauth_config)) {
            if ($hybridauth_config['debug_file']) {
                if (!is_file($hybridauth_config['debug_file'])) {
                    if (!is_dir(dirname($hybridauth_config['debug_file']))) {
                        mkdir(dirname($hybridauth_config['debug_file']));
                        chmod(dirname($hybridauth_config['debug_file']), DIRS_MOD);
                    }
                    $fp = fopen($hybridauth_config['debug_file'], 'w');
                    fclose($fp);
                }
            }
            include_once "hybridauth/Hybrid/Auth.php";
            try {
                $hybridauth = new Hybrid_Auth($hybridauth_config);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Авторизация по протоколам OpenID и OAuth с использование библиотеки HybridAuth
     * @return bool
     */
    function hybridauth() {
        /**
         * @var Hybrid_Auth $hybridauth
         */
        global $hybridauth;
        if (!is_object($hybridauth) || !isset($hybridauth)) {
            $this->init_hybridauth();
            if (!is_object($hybridauth) || !isset($hybridauth)) {
                return false;
            }
        }

        // Аутентификация
        $provider = get('provider', '', 'g');
        if ($provider) {
            $providers = $hybridauth->getProviders();
            // проверяем, доступен ли данный провайдер
            $allow = array_keys($providers);
            foreach ($allow as $k => $v) $allow[$k] = strtolower($v);
            if ($provider && in_array(strtolower($provider), $allow)) {
                return $this->hybridauth_authenticate($provider);
            }
            else {
                Hybrid_Error::setError("Unknown provider " . $provider . ". Line=" . __LINE__);
                $this->setError("unknown_provider");
                return false;
            }
        }

        return false;
    }

    /**
     * Аутентификация HybridAuth
     * @param string $provider
     * @return bool
     */
    protected function hybridauth_authenticate($provider) {
        global $hybridauth;
        try {
            $params = array();
            if ($provider == 'OpenID') {
                $openid_identifier = get('openid_identifier', '', 'g');
                if ($openid_identifier) $params['openid_identifier'] = $openid_identifier;
            }
            $adapter = $hybridauth->authenticate($provider, $params);
            $user_profile = $adapter->getUserProfile();
            if (!$user_profile) {
                Hybrid_Error::setError("Error getUserProfile, provider=" . $provider . ". Line=" . __LINE__);
                $this->setError("error_get_profile");
                return false;
            }

            if ($this->_user) {
                // Есть текущий залогиненный пользователь, надо привязать к нему
                // Если не привязано уже к другому пользователю
                $link_user_id = (int)sql_getValue("SELECT user_id FROM {$this->_table_socials} WHERE provider='{$provider}' AND identifier='{$user_profile->identifier}'");
                if ($link_user_id && $link_user_id != $this->_user->getId()) {
                    $adapter->logout();
                    Hybrid_Error::setError("Provider={$provider}, identifier={$user_profile->identifier}: already linked to another user={$link_user_id}" . ". Line=" . __LINE__);
                    $this->setError("link_provider_error");
                    return false;
                }
                $name = '';
                if ($user_profile->firstName) {
                    $name = $user_profile->firstName;
                    if ($user_profile->lastName) $name .= ' ' . $user_profile->lastName;
                }
                else {
                    if ($user_profile->displayName) $name = $user_profile->displayName;
                }
                if (!$name) $name = $user_profile->identifier;
                if ($name) {
                    $win1251 = iconv('utf-8', 'windows-1251', $name);
                    if ($win1251) $name = $win1251;
                }
                $this->_user->createSocialLink(array(
                    'provider' => $provider,
                    'identifier' => $user_profile->identifier,
                    'profileURL' => $user_profile->profileURL,
                    'photoURL' => $user_profile->photoURL,
                    'name' => $name
                ));
            }
            else {
                // Никто не залогинен
                $user_id = sql_getValue("SELECT user_id FROM {$this->_table_socials} WHERE provider='{$provider}' AND identifier='{$user_profile->identifier}'");
                if (!$user_id) {
                    $user_id = $this->createUserByProvider($provider, $user_profile);
                    if ($user_id === false) {
                        Hybrid_Error::setError("Error create user in table {$this->_table}" . ". Line=" . __LINE__);
                        return false;
                    }
                }
                // авторизовать на сайте
                $this->login($user_id);
            }
            return true;
        } catch (Exception $e) {
            Hybrid_Error::setError($e->getMessage() . ". Line=" . __LINE__);
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Привязка соц. сети к пользователю
     * @param $provider
     * @param $profile
     * @return bool
     */
    function createUserByProvider($provider, $profile) {
        $login = $provider . '_' . $profile->identifier;
        $columns = sql_getRows("SHOW COLUMNS FROM `auth_users`", true);
        if (isset($columns['login']) && $columns['login']['Key'] == 'UNI') {
            $row = sql_getRow("SELECT * FROM auth_users WHERE login='" . mysql_real_escape_string($login) . "'");
            if ($row) {
                $this->setError("error_login_exists");
                return false;
            }
        }
        if ($profile->email && isset($columns['email']) && $columns['email']['Key'] == 'UNI') {
            $row = sql_getRow("SELECT * FROM auth_users WHERE email='" . mysql_real_escape_string($profile->email) . "'");
            if ($row) {
                $this->setError("error_email_exists");
                return false;
            }
        }
        $name = '';
        if ($profile->firstName) {
            $name = $profile->firstName;
            if ($profile->lastName) $name .= ' ' . $profile->lastName;
        }
        else {
            if ($profile->displayName) $name = $profile->displayName;
        }
        $user_data = array(
            'login' => $login,
            'auth' => 1,
            'reg_date' => date('Y-m-d H:i:s'),
            'visible' => 1,
            'name' => $name,
            'root_id' => ROOT_ID
        );
        $win1251 = iconv('utf-8', 'windows-1251', $user_data['name']);
        if ($win1251) $user_data['name'] = $win1251;
        if ($profile->email) {
            $user_data['email'] = $profile->email;
        }
        $user_id = sql_insert($this->_table, $user_data);
        if (!is_int($user_id)) {
            $this->setError("error_create_user");
            return false;
        }

        /**
         * @var User $model
         */
        $model = new $this->_model($user_id);

        if (defined('DEFAULT_ROLE_ID') && DEFAULT_ROLE_ID > 0) {
            // Назначаем роль по умолчанию на всех сайтах
            global $site_domains;
            foreach ($site_domains as $site) {
                foreach ($site['langs'] as $l) {
                    $model->addRole(DEFAULT_ROLE_ID, $l['root_id']);
                }
            }
        }

        // Привязка соц. профиля
        $link_data = array(
            'provider' => $provider,
            'identifier' => $profile->identifier,
            'profileURL' => $profile->profileURL,
            'photoURL' => $profile->photoURL,
            'name' => $user_data['name']
        );
        $link_id = $model->createSocialLink($link_data);
        if (!is_int($link_id)) {
            $this->setError("error_create_user");
            return false;
        }

        return $user_id;
    }

    /**
     * Возвращает строковую константу
     * @param $code
     * @return mixed
     */
    protected function getMessage($code) {
        return isset($this->_messages[$code]) ? $this->_messages[$code] : $code;
    }

    /**
     * Установка ошибки
     * @param $error - код ошибки (из строковой константы)
     */
    protected function setError($error) {
        $this->_errors[] = $error;
    }

    /**
     * Возврат ошибок
     * @return array
     */
    protected function getErrors() {
        $ret = array();
        if ($this->_errors) {
            foreach ($this->_errors as $v) {
                $ret[] = $this->getMessage($v);
            }
        }
        return implode("<br>", $ret);
    }
}