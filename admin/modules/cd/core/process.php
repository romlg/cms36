<?php
clear_dir('../../../backup/cd/');
$a=fopen('console.txt','w');
fclose($a);
$a=fopen('info.txt','w');
fclose($a);
//Подключаем базу, этот файл в общем отдельный от движка
include "../../../connect.php";
//Теперь создаем кастрированнную копию сайта и может быть даже с левыми параметрами
//Сначала удалим таблицу от предыдущей ЦД-презентации
mysql_query('drop table if exists tree_cd');
mysql_query('drop table if exists tree_cd_links');
//Сюда приходит строка с яваскриптовыми ид через точку с запятой, их надо разобрать на местные идшники
$a=explode(';',$_POST['a']);
//unset($a[sizeof($a)-1]);	//Высечь последний элемент - он пустой
//Далее поймаем все id'шники и запишем для них строку where из аццкого сложного запроса
$where_str=" where";
foreach ($a as $varname => $varvalue){	//$varname - имя полученной переменной, $varval - значение
	if (empty($varvalue)) continue;
	/* Здесь я проверяю, включены для элемента родительские элементы или нет.
	* Если нет - удаляю этот id из массива
	* TODO: нужна нормальная обработка здесь или еще на этапе javascript 
	*/
	/******************************/
	if (isVisible($varvalue, $a)) {
		$varvalue=substr($varvalue,8);
		$where_str=$where_str." id=".$varvalue." or";
	}
}
//Обрезаем хлам (на конце строки стоит б*:%кое слово and)
$where_str=substr($where_str,0,strlen($where_str)-3);
//И даем в базу аццки сложный запрос
mysql_query('create table tree_cd as select * from tree'.$where_str);
mysql_query('create table tree_cd_links (id int not null auto_increment, url VARCHAR(255) UNIQUE, done int, primary key(id))');	//Создание таблицы для ссылок с вопросами (ссылки со страницами на саму себя)
//mysql_query('update tree_cd set visible=1');
echo("[System message:]\nCD-presentation is generating... Press OK and do not close window.");

function isVisible($id, $ids) {
	$id=substr($id,8);
	$pid = array_shift(mysql_fetch_row(mysql_query('SELECT pid FROM tree WHERE id='.$id)));
	$pid = 'cb_node_'.$pid;
	if (!in_array($pid, $ids)) return false;
	
	if ($pid != 'cb_node_'.$id) return isVisible($pid, $ids);
	return true;
}

function clear_dir($dir){
	//debug_no_info();
	$directory=opendir($dir);
	while($file=readdir($directory)){
		if(($file!='.')&&($file!='..')&&($file!='gecko_browser')&&($file!='autorun.inf')&&($file!='start.bat')){
			//echo realpath($dir.$file)."<br>";
			if(is_dir($dir.$file)){
				clear_dir($dir.$file.'/');
				rmdir($dir.$file);
			}else{
				unlink($dir.$file);
			}
		}
	}
	closedir($directory);
}
?>