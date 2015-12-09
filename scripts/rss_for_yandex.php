<?php

$domain = $_SERVER['HTTP_HOST'];
if (!in_array($domain, array('smsm.ru', 'www.smsm.ru', 'smsm-portal'))) {
    exit;
}
$root_id = 0;
switch ($domain) {
	case 'smsm.ru':
	case 'www.smsm.ru':
	case 'smsm-portal':
		$root_id = 100;
		break;
	default:
	    exit;
		break;
}

header('Content-type: text/xml; charset=windows-1251');
include("./connect.php");

$table = 'elem_news';

$logo = 'images/logo.gif';

$res = mysql_query('SELECT name, value FROM strings WHERE name LIKE "rss_%" AND root_id='.$root_id);
$strings = array();
while ($row = mysql_fetch_assoc($res)) {
    $strings[$row['name']] = $row['value'];
}
$host = $strings['rss_url'];

$lastBuildDate = current(mysql_fetch_assoc(mysql_query('SELECT MAX(UNIX_TIMESTAMP(date)) FROM elem_news WHERE visible>0 AND root_id="'.$root_id.'"')));

$output = '<?xml version="1.0" encoding="Windows-1251" ?>
	<rss version="2.0" xmlns="http://backend.userland.com/rss2" xmlns:yandex="http://news.yandex.ru">
	<channel>
		<title>'.$strings['rss_title'].'</title>
		<link>'.$strings['rss_url'].'</link>
		<description>'.$strings['rss_description'].'</description>
		<language>ru</language>
		<lastBuildDate>'.date("r", $lastBuildDate).'</lastBuildDate>
		<image>
			'.($logo ? '<url>'.$host.$logo.'</url>' : '').'
			<title>'.$strings['rss_description'].'</title>
			<link>'.$host.'</link>
		</image>
';

$sql = "SELECT id, date, name, description, text, CONCAT('archive/news/?id=', id) AS link FROM ".$table." WHERE visible>0 AND root_id='".$root_id."' ORDER BY date DESC";

$result = mysql_query($sql);
while($row = @mysql_fetch_assoc($result)) {
    $link = replace_symbols($host.$row['link']);
    $output .= '<item>';
    $output .= '<title>'.replace_symbols(strip_tags($row['name'])).'</title>';
    $output .= '<link>'.$link.'</link>';
    $output .= '<description>'.replace_symbols(strip_tags($row['description'])).'</description>';
    $output .= '<pubDate>'.date("r",strtotime($row['date'])).'</pubDate>';
    $output .= "<yandex:full-text>".replace_symbols(strip_tags($row['text']))."</yandex:full-text>";
    $output .= '</item>';
}
$output .= '</channel></rss>';

echo $output;

function replace_symbols($value){
    $value = str_replace('&quot;', '"', $value);
    return str_replace(array('&', "\r\n", "<", ">", "'", "\""), array('&amp;', "", "&lt;", "&gt;", "&apos;", "&quot;"), $value);
}