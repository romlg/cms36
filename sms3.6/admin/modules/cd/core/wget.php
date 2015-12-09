<?php

set_time_limit(0);

if (!$_POST && $_GET) $_POST = $_GET;

session_save_path('../../../../cache/sessions_adm');

$info_file='info.txt';

$url="http://".$_SERVER['HTTP_HOST']."/cd";
$backup_dir="../../../backup/cd";

if (is_file('console.txt')) {
	$console_file=@fopen('console.txt','r');
	$console_content = @fread($console_file, filesize('console.txt'));
	if ($console_content == "THE END OF CD PRESENTATION") {
		echo 'END';
		die();
	}
}

include_once('str_work.php');
include_once('../../../connect.php');

function debug_no_info(){
	global $info_file,$console_file;
	if(!is_file($info_file)){
		fclose($console_file);
		$console_file=fopen("console.txt","w");
		fputs($console_file,"THE END OF CD PRESENTATION");
		fclose($console_file);
		//$debug_str="[Орет wget:]\nКто-то украл файл процесса...";
		//echo $debug_str;
		//unlink($info_file);
		die();
	}
}

function path_by_id($id){
	debug_no_info();
	$path=array();
	$query_root=mysql_query("select id from tree where id=pid");
	while($str=mysql_fetch_array($query_root)){

	}
	$ended=false;
	while(!$ended){
		$query=mysql_query("select * from `tree` where id=$id");
		$result=mysql_fetch_array($query);
		$id=$result['pid'];
		array_unshift($path,$result['page']);
		if($result['page']=="home"){
			//array_unshift($path,'catalogs');
			unset($path[0]);
		}
		if($result['id']==$result['pid']){
			$ended=1;
		}
	}
	return implode('/',$path);
}

function id_by_path($url){
	debug_no_info();
	$url = explode('?', $url);
	$url = $url[0];
	$url_parts = array_filter(explode('/', $url));
	$pid = 100;
	$ids = array();
	foreach ($url_parts as $page) {
		$sql = 'SELECT * FROM tree WHERE pid='.$pid.' AND page="'.$page.'" AND visible >= 0 LIMIT 1';
		$struct = mysql_fetch_array(mysql_query($sql));
		if (empty($struct)) {
			break;
		}
		$ids[] = $struct['id'];
		$pid = $struct['id'];
	}
	return end($ids);
}

function name_path_by_id($id){
	debug_no_info();
	$path=array();
	$query_root=mysql_query("select id from tree where id=pid");
	while($str=mysql_fetch_array($query_root)){

	}
	$ended=false;
	while(!$ended){
		$query=mysql_query("select * from `tree` where id=$id");
		$result=mysql_fetch_array($query);
		$id=$result['pid'];
		array_unshift($path,$result['name']);
		if($id==98){
			array_unshift($path,'Каталог');
		}
		if($result['id']==$result['pid']){
			$ended=1;
		}
	}
	return implode('-->',$path);
}

class wget_result{
	var $text;
	var $download_name;
	var $download_link;
	var $parse_name;
	var $parse_link;
	var $href_name;
	var $href_link;
}

function &new_wget($text,$url,$mod_rewrite_enabled,$id_array){
	debug_no_info();
	global $log_file;
	$result=new wget_result;

	$web_sufix=array(".html",".php",".htm");
	$address_symbol=array(".","#");
	$separators=array("''",'""',"()",">"," ");
	$hrefs=array("href=","src=","background-image: url","background: url","window.open(");
	$download_file_sufix=array(".gif",".jpg",".doc",".xls",".js",".zip",".css",".swf");
	$parse_file_sufix=array(".html",".htm");

	//получаем имя хоста в виде http://host.domain
	$host=del_from_left($url,strlen("http://"));
	$buff=explode("/",$host);
	$host="http://".$buff[0];

	// Надо заменить конструкицю вида onclick="openImage(this.href);return false;"
	$text = preg_replace("/(onclick=\"openImage\(this.href\);return false;\") href=\"(.*)/", " href=\"/scripts/popup.php?img=\\2", $text);

	//echo "$host\n\n\n\n\n\n";

	//получаем ссылку на серве:
	$server_link=del_from_left($url,strlen($host));

	//получаем рабочую директорию на серве, если не включен мод-реврайт
	if($mod_rewrite_enabled){
		$path_for_relative=$host.'/';
		$clean_url="/";
	}else {

		//ищем гадские символы . ? # которые портят адресную строку
		foreach ($address_symbol as $symbol){
			if(strpos($server_link,$symbol)){
				$positions[]=strpos($server_link,$symbol)-1;
			}
		}

		if(sizeof($positions)>1){
			$min=$positions[0];
			foreach($positions as $pos){
				if ($pos<$min){
					$min=$pos;
				}
			}
		}elseif (sizeof($positions)==1){
			$min=$positions[0];
		}else {
			$min=strlen($server_link);
		}

		//нашли первое вхождение гадского символа - и получили урл без него
		$clean_url=del_from_right($server_link,strlen($server_link)-$min-1);
		if($clean_url==$server_link){
			//небыло гадских символов - числый урл
			if(last_char($clean_url)!='/'){
				$clean_url=$server_link."/";
			}
		}else{
			if(isset($tmp)){
				unset($tmp);
			}
			//удаляем весь хлам из конца строки до последнего слэша
			while($tmp!='/'){
				$tmp=last_char($clean_url);
				$clean_url=del_from_right($clean_url,1);
			}
			//доваляем только что стертый слэш ;]
			$clean_url=$clean_url.'/';
		}
		$path_for_relative=$host.$clean_url;
	}

	//обнуляем хлам
	unset($tmp);
	unset($pos)	;
	unset($min);

	//обработка урла закончена - начинаем делать мозги с заменой ссылок.
	foreach ($separators as $sep){
		if (strlen($sep)==2){
			$double_sep[]=$sep;
		}else{
			$single_sep[]=$sep;
		}
	}
	//debug
	//echo "found ".sizeof($double_sep)." double separators and ".sizeof($single_sep)." single separators<br>";



	foreach ($hrefs as $href_name){
		$buff=explode($href_name,$text);
		//начинаем перебор всех ссылок
		for($i=1;$i<sizeof($buff);$i++){
			//для начала найдем какой из разделителей встречается первым

			//проверяем начинается ли строка с одного из двойных разделителей
			unset($current_sep);
			foreach ($double_sep as $sep){
				if (first_char($buff[$i])==first_char($sep)){
					$current_sep=$sep;
				}
			}
			//а если не начинается - выбираем одинарный разделитель
			if(!isset($current_sep)){
				$single_sep_pos=strlen($buff[$i]);
				foreach ($single_sep as $sep){
					if(strpos($buff[$i],$sep)&&($single_sep_pos>strpos($buff[$i],$sep))){
						$single_sep_pos=strpos($buff[$i],$sep);
						$current_sep=$sep;
					}
				}
			}

			//разделитель выбран - начинаем получение кода ссылки

			//откуда начинается код ссылки
			if(strlen($current_sep)==2){
				$pos_start=1;
			}else {
				$pos_start=0;
			}

			//где заканчивается код ссылки
			$tmp=del_from_left($buff[$i],1);
			$pos_end=strpos($tmp,last_char($current_sep));

			$src_link=substr($buff[$i],$pos_start,$pos_end);

			//ссылка из кода и ее позиции получены, начинаем преобразование в ссылку для cd
			//echo $src_link."\n\n";
			if(first_char($src_link)=='/'){
				//первый символ ссылки - слэш - ссылка абсолютная в пределах домена

				$cd_link=$src_link;
				$full_link=$host.$cd_link;
			}elseif(if_prefix($src_link,'http://')){
				//ссылка совсем абсолютная
				if(if_prefix($src_link,$host)){
					//ссылка совсем абсолютная но ведет на наш домен
					$cd_link=del_from_left($src_link,strlen($host));
					$full_link=$src_link;
				}else{
					//ссылка совсем абсолютная и ведет наружу - обрезаем
					$cd_link='index.html';
					$full_link='';
				}
			}
			elseif (substr($src_link, 0, strlen('../images/')) == '../images/') {
				$src_link = substr($src_link, strlen('../'));
				$cd_link=$clean_url.$src_link;
				$full_link=$host.$cd_link;
			}else{
				//ссылка относительная, подставляем рабочий каталог
				$cd_link=$clean_url.$src_link;
				$full_link=$host.$cd_link;
			}

			if (strpos($url, '.css') !== false) {
				// Заменяем в css-файле пути к картинкам с /images на ../images
				if (first_char($src_link)=='/') {
					$cd_link = '/..'.$src_link;
					$full_link = $host.$src_link;
				} else if (substr($src_link, 0, strlen('images/')) == 'images/') {
					$cd_link = '/../'.$src_link;
					$full_link = $host.'/'.$src_link;
				}
			}

			//echo $cd_link."\n";

			//проверяем куда эта ссылка - если на файл - добавляем в список файлов на закачкуы

			$need=false;
			$is_cd = "";
			if($cd_link!='#'){
				if (strpos($cd_link, 'popup.php') !== false) {

				}
				else {
				foreach ($download_file_sufix as $suf){
					if(if_sufix($cd_link,$suf)){
						$result->download_name[]=$cd_link;
						$result->download_link[]=$full_link;
						$need=true;
						$cd_link=del_from_left($cd_link,1);
					}
				}
				foreach ($parse_file_sufix as $suf){
					if(if_sufix($cd_link,$suf)){
						$result->parse_name[]=$cd_link;
						$result->parse_link[]=$full_link;
						$need=true;
						$cd_link=del_from_left($cd_link,1);
					}
				}
				}

				if(!$need){
					if((substr($src_link,0,3)=="/cd")) { $src_link=substr($src_link,3); $is_cd = "/cd"; }
					if((substr($src_link,0,2)=="cd")) { $src_link=substr($src_link,2); $is_cd = "cd"; }
					if((substr($src_link,0,1)=="/")) $src_link=substr($src_link,1);

					if((substr($cd_link,0,3)=="cd/")) $cd_link=substr($cd_link,3);
					if((substr($cd_link,0,3)=="/cd")) $cd_link=substr($cd_link,3);

					if($cd_link=="/"){
						$cd_link="index.html";
					}else{
						$match=false;
						if (strpos($cd_link, 'popup.php') !== false) {
							$match = true;
						}
						else {
						$cur_id = id_by_path($cd_link);
						if ($cur_id && in_array($cur_id, $id_array)) {
							$match = true;
						}
						}

						// Если это ссылка с параметрами (и эта ссылка включена в презентацию), то получаем и ее тоже в отдельный файл, а ссылку переименовываем по имени файла
						if (strpos($src_link, '?') !== false) {
							$_temp = explode('?', $src_link);
							$cur_id = id_by_path($_temp[0]);
							if (strpos($cd_link, 'popup.php') !== false || ($cur_id && in_array($cur_id, $id_array))) {
								mysql_query("insert into tree_cd_links (url,done) values ('{$src_link}',0)");
								$match=true;
							}
						}
						//echo $cd_link." -->";
						if(!$match){
							$cd_link='#';
						} else {
							if ($cd_link[0] == '/') $cd_link = substr($cd_link, 1);
							$cd_link = str_replace(array('?', '/'),'_',$cd_link);
							if (substr($cd_link, -1) == '_') $cd_link = substr($cd_link, 0, strlen($cd_link)-1);
							$cd_link .= ".html";
						}
						//echo $cd_link." ($match) \r\n";

					}

					if(!if_prefix($cd_link,"javascript")){
						$result->href_name[]=$cd_link;
						$result->href_link[]=$full_link;
					}
					//fputs($log_file,$cd_link."  ====>>>> $match<br>");
				}
			}


			if (strlen($current_sep)==2){
				$link_in_code=first_char($current_sep).$src_link.last_char($current_sep);
				$new_link_in_code=first_char($current_sep).$cd_link.last_char($current_sep);
			}else{
				$link_in_code=$src_link.$current_sep;
				$new_link_in_code=$cd_link.$current_sep;
			}

			if (!isset($is_cd)) $is_cd = '';
			if ($is_cd) $link_in_code = $is_cd.$link_in_code;
			$tmp=del_from_left($buff[$i],strlen($link_in_code));
			if ($tmp[0] == '"' || $tmp[0] == "'") $tmp = substr($tmp, 1);
			$buff[$i]=$new_link_in_code.$tmp;
			$text=implode($href_name,$buff);
		}

	}
	$result->text = $text;
	return $result;
}

function copy_the_file($from,$to){
	debug_no_info();
	global $backup_dir,$log_file;
	$newdir=explode('/',$backup_dir.$to);
	unset($newdir[sizeof($newdir)-1]);
	make_the_dir($newdir);
	//fputs($log_file,"download ".$from." and save as ".$backup_dir.$to."\n");
	if(!is_file($backup_dir.$to)){
		if(!@copy($from,$backup_dir.$to)) {
			//echo "bad file $from => $backup_dir$to\n";

		}
	}
}

function make_the_dir($dir){
	debug_no_info();
	for ($i=0;$i<sizeof($dir);$i++){
		$newdir='';
		for($j=0;$j<($i+1);$j++){
			$newdir=$newdir.$dir[$j].'/';
		}
		if(!is_dir($newdir)){
			mkdir($newdir);
			chmod($newdir, 0777);
		}
	}
}

function wget($id, $console_file){
	global $url, $id_array, $backup_dir;

	$path=path_by_id($id);
	$namepath=name_path_by_id($id);
	fputs($console_file,"$namepath<br>\n");

	$a = file_get_contents($url.'/'.$path);

	$result=new_wget($a,$url.'/'.$path,true,$id_array);

	if(is_file($backup_dir.'/'.$path)){
		unlink($backup_dir.'/'.$path);
	}
	if(str_replace('/','_',$path).'.html'=='.html'){
		$path='index';
	}

	$fp=fopen($backup_dir.'/'.str_replace('/','_',$path).'.html','wb');
	fputs($fp,$result->text);

	for($i=0;$i<sizeof($result->download_name);$i++){
		copy_the_file($result->download_link[$i],$result->download_name[$i]);
	};
}

function wget2($adddr, $orig, $console_file){
	global $url, $id_array, $backup_dir;

	$result=new_wget(@file_get_contents($adddr),$adddr,true,$id_array);

	$fp=fopen($backup_dir.'/'.str_replace('?','_',str_replace('/','_',$orig)).'.html','wb');
	fputs($fp,$result->text);

	for($i=0;$i<sizeof($result->download_name);$i++){
		copy_the_file($result->download_link[$i],$result->download_name[$i]);
	};
}

$query=mysql_query("select * from tree_cd");
while($str=mysql_fetch_array($query)){
	$id_array[]=$str['id'];
}

if($_POST['type']=="id"){
	$console_file=fopen('console.txt','w');
	mysql_query("update tree_cd set uptime=0 where id=".$_POST['id']);
	wget($_POST['id'],$console_file);

	echo "Раздел за нумером ".$_POST['id']." готов.";

	fclose($console_file);
}

elseif($_POST['type']=="css"){
	$css = array('fonts.css', 'main2.css', 'menu.css', 'print.css', 'style.css', 'style_ie6.css');
	foreach ($css as $k=>$v) {
		$path = "css/".$v;
		$result=new_wget(file_get_contents("http://".$_SERVER['HTTP_HOST'].'/'.$path),"http://".$_SERVER['HTTP_HOST'].'/'.$path,true,$id_array);
		if (!$result->text) continue;

		if (!is_dir($backup_dir.'/css')) {
			mkdir($backup_dir.'/css');
			chmod($backup_dir.'/css', 0777);
		}
		$fp=fopen($backup_dir.'/'.$path,'w');
		fputs($fp,$result->text);

		for($i=0;$i<sizeof($result->download_name);$i++){
			$name = basename($result->download_name[$i]);
			copy_the_file($result->download_link[$i], $backup_dir.'/images/'.$name);
		};
	}
}

elseif($_POST['type']=="menu.js"){
	$path = "javascripts/menu.js";
	$js = @file_get_contents("http://".$_SERVER['HTTP_HOST'].'/'.$path);
		if (!$js) continue;
	$result=new_wget($js,"http://".$_SERVER['HTTP_HOST'].'/'.$path,true,$id_array);
		if (!$result->text) continue;

		if(is_file($backup_dir.'/'.$path)){
			unlink($backup_dir.'/'.$path);
		}

		$fp=@fopen($backup_dir.'/'.$path,'wb');
		@fputs($fp,$result->text);

		for($i=0;$i<sizeof($result->download_name);$i++){
			copy_the_file($result->download_link[$i],$result->download_name[$i]);
		};
	}

elseif($_POST['type']=="all_rest"){
	$console_file=fopen('console.txt','w');
	$query=mysql_query("select * from tree_cd_links where done=0 limit 0,1");
	if(mysql_num_rows($query)){
		$str=mysql_fetch_array($query);
		wget2($url.'/'.$str['url'],$str['url'],$console_file);
		mysql_query('update tree_cd_links set done=1 where id='.$str['id']);
		echo $str['url'];
	}else{
		echo "OK";
	}

	fclose($console_file);
}

elseif($_POST['type']=="get_count_pages"){
	echo array_shift(mysql_fetch_row(mysql_query('select count(*) from tree_cd_links where done=0')));
}

elseif ($_POST['type'] == "info") {
	$info=fopen($info_file,'w');
	fputs($info,'making_cd');
	fclose($info);
}

?>