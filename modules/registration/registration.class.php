<?php

/**
 * Класс для регистрации пользователя
 */

class TRegistration
{

    /**
     * @var TAuth
     */
    var $auth = null;

    /**
     * @var строковые константы
     */
    private $_messages = array();

    function TRegistration() {
        $this->auth = & Registry::get('TAuth');
        $this->_messages = sql_getRows("
        SELECT
            IF(s1.name IS NOT NULL, s1.name, s2.name) AS name,
            IF(s1.value IS NOT NULL, s1.value, s2.value) AS value
        FROM strings AS s1, strings AS s2
        WHERE
            s1.root_id=" . ROOT_ID . "
            AND s2.root_id=" . getMainRootID() . "
            AND s1.module='registration' AND s2.module='registration'",
            true);
    }

    /**
     * Отображение формы регистрации
     * @return array
     */
    function showRegForm() {
        if ($this->auth->getCurrentUserId() > 0) redirect('/');

        $ret = array('form' => true);

        if (isset($_GET['auth']) && $_GET['auth'] == 1) {
            if (($err = $this->checkAuthHash()) !== true) {
                // если проверка не прошла, покажем сообщение, а форму регистрации скроем
                $ret = array('form' => false);
                $ret['errors']['global'][] = $err;
                return $ret;
            } else {
                // Все ОК, на главную страницу
                redirect('/');
            }
        }

        if (isset($_GET['forgetpass']) && $_GET['forgetpass'] == 1) {
            $ret = $this->forgetPassword();
            return $ret;
        }

        $fld = get('fld', array(), 'p');
        if ($fld) {
            $errors = $this->checkRegData($fld);
            if ($errors) {
                $ret['errors'] = $errors;
            } else {
                if (($user_id = $this->register($fld)) === false) {
                    $ret['errors']['global'][] = $this->getMessage('error_create_user');
                } else {
                    if ($this->sendAuthHash($user_id) === false) {
                        $ret['errors']['global'][] = $this->getMessage('error_send_auth_mail');
                    } else {
                        $_POST = null;
                        $ret['form'] = false;
                        $ret['text_message'] = $this->getMessage('auth_text');
                    }
                }
            }
        }
        $ret['fld'] = $fld;

        return $ret;
    }

    /**
     * Проверка данных с формы регистрации
     * @param $fld
     * @return array
     */
    function checkRegData($fld) {

        $error = array();

        $req_fields = array(
            'login', 'email', 'password1', 'password2', 'name',
        );
        foreach ($req_fields as $val) {
            if (empty($fld[$val]))
                $error[$val] = $this->getMessage('error_' . $val . '_empty');
        }

        if (!empty($fld['login'])) {
            $count = sql_getValue("SELECT COUNT(*) FROM auth_users WHERE login='" . h($fld['login']) . "'");
            if ($count) {
                $error['login'] = $this->getMessage('error_login_exists');
            }
        }
        if ($fld['email']) {
            if (!CheckMailAddress($fld['email'])) {
                $error['email'] = $this->getMessage('error_email_incorrect');
            }
            $count = sql_getValue("SELECT COUNT(*) FROM auth_users WHERE email='" . h($fld['email']) . "'");
            if ($count) {
                $error['email'] = $this->getMessage('error_email_exists');
            }
        }
        if ($fld['password1'] && $fld['password1'] != $fld['password2']) {
            $error['password1'] = $this->getMessage('error_passwords');
        }

        return $error;
    }

    /**
     * Создание новой учетной записи
     * @param $fld
     * @return int|bool
     */
    function register($fld) {

        $data = array(
            'login' => $fld['login'],
            'auth' => 0,
            'reg_date' => date('Y-m-d H:i:s'),
            'visible' => 1,
            'name' => $fld['name'],
            'email' => $fld['email'],
            'password' => md5($fld['password1']),
            'root_id' => ROOT_ID
        );
        $user = new User();
        $user->setData($data);
        $id = $user->create();

        if (!is_int($id)) return false;

        return $id;
    }

    /**
     * Отправка письма со ссылкой для подтверждения регистрации
     * @param $id
     * @return bool
     */
    function sendAuthHash($id) {
        $user = new User($id);

        $rnd = mt_rand(0, 999);
        $hash = $this->auth->createChPassHash($user->get('email') . $rnd);

        $user->setData(array('confirm_email_hash' => $hash));
        $user->update();

        $data = array(
            'site_name' => $_SERVER['HTTP_HOST'],
            'user_id' => $id,
            'hash' => $hash
        );
        return Notify("SEND_AUTH_MAIL", $user->get('email'), $data);
    }

    /**
     * Проверка хеш-строки из ссылки для завершения авторизации
     * @return bool|string
     */
    function checkAuthHash() {
        $user_id = (int)get('id', 0, 'g');
        $hash = get('hash', '', 'g');
        $id = (int)sql_getValue("SELECT id FROM auth_users WHERE id={$user_id}");
        if (!$id) {
            return $this->getMessage('error_user_id');
        }
        $user = new User($user_id);
        if ($hash != $user->get('confirm_email_hash')) {
            return $this->getMessage('error_auth_hash');
        }
        // Все ОК, авторизуем
        $user->setData(array('auth' => 1));
        $user->update();
        $this->auth->login($user_id);
        return true;
    }

    /**
     * Функция "Забыли пароль"
     * @return array
     */
    function forgetPassword() {
        $res = array('form_forgetpassword' => 1, 'form' => 0);

        $login = get('login', '', 'p');
        if ($login) {
            $r = $this->auth->sendChangePassNotify($login);
            if ($r !== true) {
                $res['errors']['global'][] = $r;
            } else {
                $res = array('form_forgetpassword' => 0, 'form' => 0);
                $res['text_message'] = $this->getMessage('forget_password_text');
            }
            return $res;
        }

        $hash = get('hash', '', 'gp');
        $user_id = (int)get('id', 0, 'gp');
        if ($user_id || $hash) {
            $id = (int)sql_getValue("SELECT id FROM auth_users WHERE id={$user_id}");
            if (!$id) {
                $res['errors']['global'][] = $this->getMessage('error_user_id');
            }
            elseif ($this->auth->checkChPassHash($user_id, $hash) === false) {
                $res['errors']['global'][] = $this->getMessage('error_auth_hash');
            }
            elseif ($id && $hash) {
                $res = array('form_forgetpassword' => 0, 'form' => 0, 'form_changepassword' => 1);
                if ($_POST) {
                    $password1 = get('password1', '', 'p');
                    $password2 = get('password2', '', 'p');
                    if (!$password1) {
                        $res['errors']['global'][] = $this->getMessage('error_password1');
                    }
                    elseif (!$password2) {
                        $res['errors']['global'][] = $this->getMessage('error_password2');
                    }
                    elseif ($password1 != $password2) {
                        $res['errors']['global'][] = $this->getMessage('error_passwords');
                    }
                    else {
                        if ($this->auth->changePassword($id, $password1, $hash) === false) {
                            $res['errors']['global'][] = $this->getMessage('error_auth_hash');
                        } else {
                            $res = array('form_forgetpassword' => 0, 'form' => 0, 'form_changepassword' => 0);
                            $res['errors']['global'][] = $this->getMessage('change_pwd');
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Возвращает строковую константу
     * @param $code
     * @return mixed
     */
    function getMessage($code) {
        return isset($this->_messages[$code]) ? $this->_messages[$code] : $code;
    }
}