<?php

require_once 'sms3.6/admin/modules/site_users/site_users_base.php';

/**
 *
 * Модуль "Пользователи сайта" (главный элемент формы)
 *
 * @package    admin/modules
 */
class TSite_users extends TSite_usersBase {


}

$GLOBALS['site_users'] = & Registry::get('TSite_users');

?>