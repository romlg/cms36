<?php

/**
 * ���������� ������� - �������� �������� � ���������� ����� � ����
 */

// ������������ ����� �������� ����� ���������� ��� ���
if (!defined('NEW_STAT')) define ('NEW_STAT', false);

#### �������� ������� ���������� #############################

if (!class_exists('TStatClass')) include_once 'sms3.6/common/classes/stat.class.php';
$stat = new TStatClass();

$sess_id = $stat->getSessId();

// ���� ��� ������ ������, �� �� ������� ���������� (� ������ �������)
if (!NEW_STAT && !$sess_id) return;

/***********************************************************/
// ����������� ������� ���������
/***********************************************************/
$document_status = $GLOBALS['document_status'];
if (empty($document_status)) {
    $document_status = $_SERVER['REDIRECT_STATUS'];
}
if (empty($document_status)) {
    $document_status = 200;
}

/***********************************************************/
// ����������� ID ������������
/***********************************************************/
$client_id = isset($GLOBALS['client_id']) ? (int)$GLOBALS['client_id'] : 0;

/***********************************************************/
// ����������� ����� � uri ��������
/***********************************************************/
$host = $_SERVER['HTTP_HOST'];
if (preg_match('/^www\.(.*)$/', $host, $regs)) $host = $regs[1];
$uri = $_SERVER['REQUEST_URI'];
if(isset($_REQUEST['dirs'])) {
    $uri = $uri.'/'.$_REQUEST['dirs'];
    $uri = str_replace('//','/', $uri);
    $uri = str_replace('//','/', $uri);
}
// ���������� ���������� ����� ���� �� ����������� � uri.
if (substr($uri,-1,1) != '/' && !stristr($uri,'.') && !stristr($uri,'?')){
    $uri .= '/';
}

/***********************************************************/
// ����������� referer ������
/***********************************************************/
$referer = $_SERVER['HTTP_REFERER'];

/***********************************************************/
// ����������� IP
/***********************************************************/
$ip = $stat->STAT_GetIpAddress();
if(isset($GLOBALS['statlog_new_session']) && !empty($GLOBALS['statlog_new_session']))
	$is_new_session = 1;
else 
	$is_new_session = 0;

/***********************************************************/
// ��������� � ��
/***********************************************************/
if (!NEW_STAT) {
    list($page_id, $ref_id) = $stat->updateLog($sess_id, time(), $document_status, $host, $uri, $referer, isset($GLOBALS['statlog_session']));
    $stat->updateSession($sess_id, time(), $document_status, $client_id, $page_id, $ref_id, ip2long($ip));
}
else {
    $stat->writeTempData($sess_id, time(), $document_status, $client_id, $host, $uri, $referer, $_SERVER['HTTP_USER_AGENT'], ip2long($ip), $is_new_session);
}