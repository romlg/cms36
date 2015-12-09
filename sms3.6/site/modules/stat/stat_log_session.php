<?php

/**
 * ���������� ������� - ���������� id ������, ����� ���� ��������� ��� � ���� �
 * � ���������� $GLOBALS['statlog_session'] ��� $GLOBALS['statlog_new_session'], ���� ������ �����
 */

// ����� ����� ������� ��������� ������ (���.)
if (!defined('STAT_SESS_TIME')) define('STAT_SESS_TIME', 30);
// ������������ ����� �������� ����� ���������� ��� ���
if (!defined('NEW_STAT')) define ('NEW_STAT', false);

#####################################################

if (!class_exists('TStatClass')) include_once 'sms3.6/common/classes/stat.class.php';
$stat = new TStatClass();

// ���������, ������� ���� ip ��� ���
if ($stat->isUserBanned() && $_SERVER['REQUEST_URI'] != '/access_denied') redirect("/access_denied");

// �������� ������ ������������
if (isset($_COOKIE['statlog_session']) && (int)$_COOKIE['statlog_session']) {
	// ���� ����� cookie, ����� statlog_session � ���������� ����������
	$GLOBALS['statlog_session'] = $_COOKIE['statlog_session'];
	if (NEW_STAT) {
	    // ����� ��� ���, ����� �������� �� �����
	    setcookie('statlog_session', $GLOBALS['statlog_session'], time() + STAT_SESS_TIME * 60, '/');
	}
}
else {

    if (NEW_STAT) {
        // �� ����� - �������� ������������� (��������� ������ ������ 32 �������)
        $sess_id = md5(uniqid(rand(), true));
        if (setcookie('statlog_session', $sess_id, time() + STAT_SESS_TIME * 60, '/') === false) {
            $sess_id = 0;
        }
        $GLOBALS['statlog_new_session'] = $sess_id;
    }
    else {
        // ���� ������ � ����� ���, �� �������� ������ ��� ����� ������������ ��� � ���� ������ ��� cookie.
        // ��� ������ ������������, ���� ����������� ����. �������:
        // 1. ��� ������ � ����� ip �� ��������� STAT_SESS_TIME �����
        // 2. ��������� HTTP_AGENT ���������� �������

        // ����������� ip ������
        $ip = $stat->STAT_GetIpAddress();
        $long_ip = ip2long($ip);

        // �����
        $agent_id = $stat->getAgentId();

        // ����
        $host = $_SERVER['HTTP_HOST'];
        if (preg_match('/^www\.(.*)$/', $host, $regs)) $host = $regs[1];

        $sess_row = array();

        // ��������� ��������� ������ � ����� ip, agent_id � host
        if ($agent_id && $long_ip) {
            $sess_row = $stat->getLastSession($long_ip, $agent_id, $host);
        }

        // ���� ����� ������ ����, �� ������ ����� �� ����� � ���������� ����������
        if (isset($sess_row['time_last']) && $sess_row['time_last'] > time() - STAT_SESS_TIME * 60) {

            // ���������� ����� ������ � ����� � ����
            $GLOBALS['statlog_session'] = $sess_row['sess_id'];
            setcookie('statlog_session', $sess_row['sess_id'], null, '/');
        }

        // ���� ������ ���, ������ ��� ����� �������
        else {

            // ���������� ����� ������� � stat_sessions
            $id = $stat->newSession($long_ip, $agent_id, time(), $sess_row);

            // ���������� ����� ������ � ����� � ����
            $GLOBALS['statlog_new_session'] = $id;
            setcookie('statlog_session', $id, null, '/');

            // ��������� �������� ���������� ������� ���� ���� ������ ������ "STOP"
            ignore_user_abort(true);
        }
    }

}