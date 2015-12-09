<?php

/**
 * Назначение скрипта - записать сведения о посетителе сайта в базу
 */

// Использовать новый алгоритм сбора статистики или нет
if (!defined('NEW_STAT')) define ('NEW_STAT', false);

#### Основной подсчет статистики #############################

if (!class_exists('TStatClass')) include_once 'sms3.6/common/classes/stat.class.php';
$stat = new TStatClass();

$sess_id = $stat->getSessId();

// Если нет номера сессии, то не считаем статистику (в старом способе)
if (!NEW_STAT && !$sess_id) return;

/***********************************************************/
// Определение статуса документа
/***********************************************************/
$document_status = $GLOBALS['document_status'];
if (empty($document_status)) {
    $document_status = $_SERVER['REDIRECT_STATUS'];
}
if (empty($document_status)) {
    $document_status = 200;
}

/***********************************************************/
// Определение ID пользователя
/***********************************************************/
$client_id = isset($GLOBALS['client_id']) ? (int)$GLOBALS['client_id'] : 0;

/***********************************************************/
// Определение хоста и uri страницы
/***********************************************************/
$host = $_SERVER['HTTP_HOST'];
if (preg_match('/^www\.(.*)$/', $host, $regs)) $host = $regs[1];
$uri = $_SERVER['REQUEST_URI'];
if(isset($_REQUEST['dirs'])) {
    $uri = $uri.'/'.$_REQUEST['dirs'];
    $uri = str_replace('//','/', $uri);
    $uri = str_replace('//','/', $uri);
}
// Добавление последнего слэша если он отсутствует в uri.
if (substr($uri,-1,1) != '/' && !stristr($uri,'.') && !stristr($uri,'?')){
    $uri .= '/';
}

/***********************************************************/
// Определение referer адреса
/***********************************************************/
$referer = $_SERVER['HTTP_REFERER'];

/***********************************************************/
// Определение IP
/***********************************************************/
$ip = $stat->STAT_GetIpAddress();
if(isset($GLOBALS['statlog_new_session']) && !empty($GLOBALS['statlog_new_session']))
	$is_new_session = 1;
else 
	$is_new_session = 0;

/***********************************************************/
// Сохраняем в БД
/***********************************************************/
if (!NEW_STAT) {
    list($page_id, $ref_id) = $stat->updateLog($sess_id, time(), $document_status, $host, $uri, $referer, isset($GLOBALS['statlog_session']));
    $stat->updateSession($sess_id, time(), $document_status, $client_id, $page_id, $ref_id, ip2long($ip));
}
else {
    $stat->writeTempData($sess_id, time(), $document_status, $client_id, $host, $uri, $referer, $_SERVER['HTTP_USER_AGENT'], ip2long($ip), $is_new_session);
}