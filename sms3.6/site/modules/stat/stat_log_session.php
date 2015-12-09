<?php

/**
 * Ќазначение скрипта - определить id сессии, после чего сохранить его в куки и
 * в переменную $GLOBALS['statlog_session'] или $GLOBALS['statlog_new_session'], если сесси€ нова€
 */

// врем€ через которое считаетс€ сесси€ (мин.)
if (!defined('STAT_SESS_TIME')) define('STAT_SESS_TIME', 30);
// »спользовать новый алгоритм сбора статистики или нет
if (!defined('NEW_STAT')) define ('NEW_STAT', false);

#####################################################

if (!class_exists('TStatClass')) include_once 'sms3.6/common/classes/stat.class.php';
$stat = new TStatClass();

// ѕровер€ем, забанен этот ip или нет
if ($stat->isUserBanned() && $_SERVER['REQUEST_URI'] != '/access_denied') redirect("/access_denied");

// ѕроверка сессии пользовател€
if (isset($_COOKIE['statlog_session']) && (int)$_COOKIE['statlog_session']) {
	// ≈сли нашли cookie, пишем statlog_session в глобальную переменную
	$GLOBALS['statlog_session'] = $_COOKIE['statlog_session'];
	if (NEW_STAT) {
	    // ѕишем еще раз, чтобы продлить ее врем€
	    setcookie('statlog_session', $GLOBALS['statlog_session'], time() + STAT_SESS_TIME * 60, '/');
	}
}
else {

    if (NEW_STAT) {
        // Ќе нашли - пытаемс€ сгенерировать (случайна€ строка длиной 32 символа)
        $sess_id = md5(uniqid(rand(), true));
        if (setcookie('statlog_session', $sess_id, time() + STAT_SESS_TIME * 60, '/') === false) {
            $sess_id = 0;
        }
        $GLOBALS['statlog_new_session'] = $sess_id;
    }
    else {
        // ≈сли сессии в куках нет, то пытаемс€ узнать это новый пользователь или у него просто нет cookie.
        // Ёто старый пользователь, если выполн€ютс€ след. услови€:
        // 1. был запрос с этого ip за последние STAT_SESS_TIME минут
        // 2. совпадает HTTP_AGENT последнего запроса

        // ќпределение ip адреса
        $ip = $stat->STAT_GetIpAddress();
        $long_ip = ip2long($ip);

        // јгент
        $agent_id = $stat->getAgentId();

        // ’ост
        $host = $_SERVER['HTTP_HOST'];
        if (preg_match('/^www\.(.*)$/', $host, $regs)) $host = $regs[1];

        $sess_row = array();

        // ѕровер€ем последнюю сессию с таким ip, agent_id и host
        if ($agent_id && $long_ip) {
            $sess_row = $stat->getLastSession($long_ip, $agent_id, $host);
        }

        // ≈сли така€ сесси€ есть, то просто пишем ее номер в глобальную переменную
        if (isset($sess_row['time_last']) && $sess_row['time_last'] > time() - STAT_SESS_TIME * 60) {

            // «апоминаем номер сессии и пишем в куки
            $GLOBALS['statlog_session'] = $sess_row['sess_id'];
            setcookie('statlog_session', $sess_row['sess_id'], null, '/');
        }

        // ≈сли сессии нет, значит это новый человек
        else {

            // «аписываем новую строчку в stat_sessions
            $id = $stat->newSession($long_ip, $agent_id, time(), $sess_row);

            // «апоминаем номер сессии и пишем в куки
            $GLOBALS['statlog_new_session'] = $id;
            setcookie('statlog_session', $id, null, '/');

            // «апрещаем обрывать выполнение скрипта если была нажата кнопка "STOP"
            ignore_user_abort(true);
        }
    }

}