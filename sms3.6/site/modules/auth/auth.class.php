<?php

/**
 * ����� ��� �����������/����������� �� �����
 */

class TAuth
{

    /**
     * @var string �������� ������ ��� ������������
     */
    protected $_model = 'User';
    /**
     * @var Object ������ ������������
     */
    protected $_user = null;
    /**
     * @var string ������� ��� �������� �������������
     */
    protected $_table = 'auth_users';
    /**
     * @var string ��� ������� � �������
     */
    protected $_identityField = 'login';
    /**
     * @var string ��� ������� � �������
     */
    protected $_credentialField = 'password';
    /**
     * @var string ��� ������� � e-mail
     */
    protected $_emailField = 'email';
    /**
     * @var string ��� ������� � ������ - �������� ��� ��� ������ �� ����
     */
    protected $_authField = 'auth';
    /**
     * @var string ������� ��� �������� ������ �� ���. ����
     */
    protected $_table_socials = 'auth_users_socials';
    /**
     * @var ��������� ���������
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

        // ����������� ���������� HybridAuth ��� ����������� ����� ���. ����
        $this->init_hybridauth();
    }

    /**
     * ����������� ����� �����������
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
                // ������� �������������� ����� ���. ����
                $redirect = true;
            }
            elseif ($errors = $this->getErrors()) {
                // ������
                $domains = $this->getDomainForCookie();
                foreach ($domains as $domain) {
                    setcookie('msg', $errors, time() + 24 * 3600 * 30, '/', $domain);
                }
                $redirect = true;
            }
            elseif (isset($_GET['provider_auth']) && $_GET['provider_auth'] == 1) {
                // �����-�� ������
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
     * ���������� ������������ �� ������ � ������
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
     * ���������� ������������ �� ID
     * @param $id
     * @return true|string
     */
    public function login($id) {
        $this->setUser($id);
        $this->setLoginCookie($id);
    }

    /**
     * ������������ ������������
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
     * ������� ���������, ����������� �� ������������ ��� ���
     * ���������� ���� ������ ������������, ���� false
     * @return object|false
     */
    public function checkUser($params = array()) {
        if (isset($params['model'])) $this->_model = $params['model'];
        $ret = false;
        $user_id = (int)get(AUTH_USER_COOKIE_NAME, 0, 'c');
        if ($user_id > 0 && $this->checkCookie($user_id)) {
            $this->_user = new $this->_model($user_id);
            //��������� ����� ���������� ������� �� ����
            $this->_user->setData(array('last_visited' => time()));
            $this->_user->update();
            // ���������� ����
            $this->setLoginCookie($user_id);
            // ��������� ������ ������������
            $this->setUser($user_id);
            // ���������� ������ ������������
            $ret = $this->_user;
        }
        return $ret;
    }

    /**
     * ������ ������ �������� ������������� ������������
     * @return int
     */
    public function getCurrentUser() {
        return $this->_user;
    }

    /**
     * ������ id �������� ������������� ������������
     * @return int
     */
    public function getCurrentUserId() {
        if (!$this->_user) {
            return 0;
        }
        return $this->_user->getId();
    }

    /**
     * ���������� ������� �������� ������������
     * @return array
     */
    public function getCurrentUserData() {
        if (!$this->_user) {
            return array();
        }
        return $this->_user->getData();
    }

    // ��������� ������������ ��������� �� �������� ���-����
    protected function checkCookie($uid) {
        $right_str = $this->constructCookieStr($uid);
        return (isset($_COOKIE[AUTH_HASH_COOKIE_NAME]) && $_COOKIE[AUTH_HASH_COOKIE_NAME] == $right_str);
    }

    /**
     * ������� ���-���� �� id ������������
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
     * ������������� ����, ���������������� ������������
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
     * ������� ��������������� ����
     */
    protected function unsetLoginCookie() {
        $domains = $this->getDomainForCookie();
        foreach ($domains as $domain) {
            setcookie(AUTH_HASH_COOKIE_NAME, '', 0, '/', $domain);
            setcookie(AUTH_USER_COOKIE_NAME, '', 0, '/', $domain);
        }
    }

    /**
     * ���������� ������ �������, ��� ������� ����
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
     * ��������� ������������� ��������� (���� � ������ ���� ����� ����� �������� ������)
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
     * ���������� ������ ������������ � ���������� ������ "������ �������� ������������"
     * @param $uid
     */
    protected function setUser($uid) {
        $this->_user = new $this->_model($uid);
    }

    /**
     * ��������� ������������� ������������ �� ������
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
    // ��������� ������, ���� ������������ ����� ������
    // --------------------------------------------------------------

    /**
     * �������� ���� � ���������� ��� � ��
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
     * �������� ����
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
     * �������� ����������� �� ������� ��� ����� ������
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
     * ������� ��������� ������
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
     * ������������� ���������� HybridAuth
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
     * ����������� �� ���������� OpenID � OAuth � ������������� ���������� HybridAuth
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

        // ��������������
        $provider = get('provider', '', 'g');
        if ($provider) {
            $providers = $hybridauth->getProviders();
            // ���������, �������� �� ������ ���������
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
     * �������������� HybridAuth
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
                // ���� ������� ������������ ������������, ���� ��������� � ����
                // ���� �� ��������� ��� � ������� ������������
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
                // ����� �� ���������
                $user_id = sql_getValue("SELECT user_id FROM {$this->_table_socials} WHERE provider='{$provider}' AND identifier='{$user_profile->identifier}'");
                if (!$user_id) {
                    $user_id = $this->createUserByProvider($provider, $user_profile);
                    if ($user_id === false) {
                        Hybrid_Error::setError("Error create user in table {$this->_table}" . ". Line=" . __LINE__);
                        return false;
                    }
                }
                // ������������ �� �����
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
     * �������� ���. ���� � ������������
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
            // ��������� ���� �� ��������� �� ���� ������
            global $site_domains;
            foreach ($site_domains as $site) {
                foreach ($site['langs'] as $l) {
                    $model->addRole(DEFAULT_ROLE_ID, $l['root_id']);
                }
            }
        }

        // �������� ���. �������
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
     * ���������� ��������� ���������
     * @param $code
     * @return mixed
     */
    protected function getMessage($code) {
        return isset($this->_messages[$code]) ? $this->_messages[$code] : $code;
    }

    /**
     * ��������� ������
     * @param $error - ��� ������ (�� ��������� ���������)
     */
    protected function setError($error) {
        $this->_errors[] = $error;
    }

    /**
     * ������� ������
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