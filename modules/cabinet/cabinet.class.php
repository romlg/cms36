<?php

/**
 * Личный кабинет
 * Проверка роли: $this->user->hasRole(ACL_ROLE_EXPERT)
 * Проверка прав: $this->user->isAllowed(ACL_RESOURCE_LAW, ACL_ACTION_COMMENT)
 */

class TCabinet
{

    /**
     * @var TAuth $auth
     */
    var $auth = null;

    /**
     * @var User $user
     */
    var $user = null;

    function TCabinet() {
    }

    /**
     * Маршрутизатор
     * @return mixed
     */
    function router() {
        $this->auth = & Registry::get('TAuth');
        $this->user = $this->auth->getCurrentUser();
        if (!is_object($this->user)) redirect('/registration/');

        $page = & Registry::get('TPage');
        switch ($page->content['page']) {
            case 'cabinet':
                break;
            case 'profile': // Профиль
                return $this->router_profile();
                break;
            case 'social': // Соц. сети
                return $this->router_social();
                break;
            case 'exit': // Разлогинивание
                $this->router_exit();
                break;
            default:
                redirect('/404');
        }
        return false;
    }

    /**
     * Редактирование профиля пользователя
     * @return array
     */
    function router_profile() {
        include_once ENGINE_VERSION . '/site/modules/form_generator/form_generator.class.php';
        $generator = new TForm_generator();
        $generator->data_var = 'profile';

        $fields = array('name', 'email', 'password');

        $elements = array(
            'name' => array(
                'name' => 'name',
                'type' => 'text',
                'text' => 'Имя',
                'key' => 'name',
                'req' => 1,
            ),
            'email' => array(
                'name' => 'email',
                'type' => 'text',
                'text' => 'E-mail',
                'key' => 'email',
                'req' => 1,
                'check' => 'email',
            ),
            'password' => array(
                'name' => 'password',
                'type' => 'text',
                'text' => 'Пароль',
                'key' => 'password',
                'req' => 0,
            )
        );
        $elements = $generator->formatElements($elements);

        $page = & Registry::get('TPage');

        // Начинаем собирать с помощью Zend_Form
        $generator->zend_form->setAction('/' . $page->content['href'])
                ->setMethod('POST')
                ->setAttrib('accept-charset', 'windows-1251')
                ->setAttrib('enctype', 'multipart/form-data');

        // Генерация элементов формы
        $generator->generateElements($elements);

        // Подстановка в поля формы значений по умолчанию
        $default = $this->user->getData();
        foreach ($default as $k => $v) {
            if (!in_array($k, $fields) || $k == 'password') unset($default[$k]);
            else {
                if (is_string($v)) $default[$generator->data_var . $k] = iconv('windows-1251', 'utf-8', $v);
            }
        }
        $generator->zend_form->setDefaults($default);

        // Проверка - пришли ли данные с формы
        if (!empty($_POST)) {
            if ($generator->zend_form->isValid($_POST)) {
                $values = $generator->zend_form->getValues();

                // Дополнительные проверки
                $email_check = sql_getValue("SELECT id FROM auth_users WHERE id<>" . $this->user->getId() . " AND email='" . mysql_real_escape_string($values[$generator->data_var . 'email']) . "'");
                if ($email_check) {
                    redirect($page->content['href'] . '?message=error_email_exists');
                }

                if ($values[$generator->data_var . 'password'] != '') {
                    $values[$generator->data_var . 'password'] = md5($values[$generator->data_var . 'password']);
                } else {
                    unset($values[$generator->data_var . 'password']);
                }

                foreach ($values as $k => $v) {
                    $field = substr($k, strlen($generator->data_var));
                    if (in_array($field, $fields)) {
                        $this->user->set($field, $v);
                    }
                }

                // Обновление профиля пользователя
                if ($this->user->update() === false) {
                    redirect($page->content['href'] . '?message=msg_fail');
                } else {
                    // заново логиним, чтобы обновились куки (зависят от пароля)
                    $this->auth->login($this->user->getId());
                    redirect($page->content['href'] . '?message=msg_success');
                }
            }
        }

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        return array('profile_form' => iconv('utf-8', 'windows-1251', $generator->zend_form->render($view)));
    }

    /**
     * Страница "Социальные профили"
     * @return array
     */
    function router_social() {
        global $hybridauth_config;

        if (isset($_POST['act']) && $_POST['act'] == 'del') {
            return $this->deleteSocialLink((int)$_POST['id']);
        }

        $links = $this->user->getSocialLinks();
        $user_providers = array();
        if ($links) foreach ($links as $link) $user_providers[] = strtolower($link['provider']);

        $providers = array();
        foreach ($hybridauth_config['providers'] as $k => $v) {
            if ($k == 'OpenID' || !in_array(strtolower($k), $user_providers)) $providers[] = strtolower($k);
        }

        return array(
            'socials_list' => $links,
            'providers' => $providers
        );
    }

    /**
     * Удаление ссылки на соц. сети
     * @param $link_id
     */
    function deleteSocialLink($link_id) {
        $this->user->delSocialLink($link_id);
        ob_clean();
        echo 1;
        die();
    }

    /**
     * Разлогинивание
     */
    function router_exit() {
        $this->auth->unlogin();
        redirect('/');
    }
}