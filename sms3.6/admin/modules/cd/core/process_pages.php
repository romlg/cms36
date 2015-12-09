<?php
	$backupdir = '../../../backup/cd/';
	function del_from_left($str){
	return(substr($str,1,strlen($str)-1));
}

function del_from_right($str){
	return(substr($str,0,strlen($str)-1));
}

function show_html($str){
	
	return str_replace('<','[',str_replace('>',']',$str));
}

class go_inside_www_result{
	var $str;
	var $need_name;
	var $need_link;
}



function go_inside_www($f,$url,$host,$text){
	
	$result=new go_inside_www_result;
	
	$sufix=array(".html",".htm",".php");
	$file_sufix=array(".jpg",".jpeg",".gif",".zip",".rar",".doc",".xls",".avi",".mpg",".mpeg",".txt",".rtf",".png",".tga",".psd",".pdf",".swf",".css");
	
	$f_new=$f;
	
	if(substr($url,strlen($url)-1,1)=="/"){
		$new_url=del_from_right($url);
	}else{
		$new_url=$url;
	}
	
	foreach ($sufix as $str) {
		if(substr($url,strlen($url)-strlen($str),strlen($str))==$str){
			$new_url=$url;
			
			for($i=strlen($url)-1;$i>=0;$i--){
				if(substr($new_url,$i,1)=='/'){
					$a=$i;
					$i=-1;	
				}
			}
			$new_url=substr($new_url,0,$a);
			
		
		}
	}
	
	echo "$new_url\n\n";
	
	
	//echo $new_url."/<br>";
	if (substr($new_url,strlen($new_url)-1,1)!='/'){
		$new_url=$new_url."/";
	}
	
	if (strpos($url,'?')){
		//echo "<div style='color: red;'>? found in url!!</div>";
		die();
	}
	


	$old_f=$f;

	//echo "<b>обрабатываем ".$url."</b><hr>";

	$buff=explode($text,$f);
	//echo sizeof($buff)." сцылок найденно<hr>";

	$new_buff[0]=$buff[0];
	for($i=1;$i<sizeof($buff);$i++){
		
		//echo "сцылка №".$i."=>  \n";
		
		$tmp1=$buff[$i];
		
		$first=substr($tmp1,0,1);
	
		if($first=='"'){
			$sep='"';
			$tmp2=substr($tmp1,1,strlen($tmp1)-1);
		}elseif ($first=="'") {
			$sep="'";
			$tmp2=substr($tmp1,1,strlen($tmp1)-1);
		}elseif ($first=="(") {
			$sep=")";
			$tmp2=del_from_left($tmp1);
		}else{
			$sep=' ';
			if(strpos($tmp1,'>')<strpos($tmp1,' ')){
				$sep='>'; 
			}
			$tmp2=$tmp1;
		}
	
		if (substr($$tmp2,0,11)!="javascript:"){
			$tmp3=explode($sep,$tmp2);
			
			$link=$tmp3[0];
			//echo $link;
			
			//elseif (substr($link,0,1)=='/'){
				
			
				//echo "absolute link: ".$link." ===> ".$new_link."\n";
			
			if(substr($link,0,7)=='http://'){
				
				$new_link=$link;
			
			}else{
				if(substr($link,0,1)!='/'){
					$new_link=$host."/".$link;
				}else{
					
					$new_link=$host.$link;
				}
				//$new_link=$new_url.$link;
				//echo "relative link $link ===> $new_link \n";
			}
			
			
			
			
			
			
			if( substr($new_link,0,strlen($host))==$host  ){
				
				$cd_link=substr($new_link,strlen($host),strlen($new_link)-strlen($host));
				//echo $link." => ".$cd_link."<br>";
				
				
			}else{
			//	echo $link." => сцылка наружу - будем бить, возможно даже ногами";
				$cd_link=$link;
			}
			
			//echo $cd_link."===";
			$need=false;
			
			foreach ($file_sufix as $file_end){
				//echo substr($cd_link,strlen($cd_link)-strlen($file_end),strlen($file_end))."==".$file_end."<br>";
				if (strtolower(substr($cd_link,strlen($cd_link)-strlen($file_end),strlen($file_end)))==$file_end) {
					$need=true;
					//echo "need ".$cd_link;
					
				}
			}
			
			//if ((substr($cd_link,0,1)!='/')&&substr($cd_link,0,7)!="http://") {
			//	echo $cd_link."\n";
			//	$cd_link='/'.$cd_link;
			//}
			
			//if($cd_link=='images/main_header_03.gif'){
			//	echo "\n!!!!!!!!!!!!!!\n";
			//}
			
			if($need){
				$result->need_name[]=$cd_link;
				$result->need_link[]=$new_link;
			}
			
			// обратная сборка
			$tmp3[0]=$cd_link;
			$tmp2=implode($sep,$tmp3);
			
			
			if(($sep=='"')||($sep=="'")){
				$tmp1=$sep.$tmp2;
			}elseif ($sep==')'){
				$tmp1='('.$tmp2;
			}
			
			$new_buff[$i]=$tmp1;
			
			//echo "<hr>link looks like <b>$link</b> and resolved as $new_link<hr>";
			
			$f_new=str_replace($link.$sep,$cd_link.$sep,$f_new);
			
		}else{
			//echo "javascript link\n";
		}
	
	
		//echo "<hr>\n";
	}
	$result->str=implode($text,$new_buff);
	//$result->str=$f_new;
	//show_html($f_new);
	$buff=explode('<script',$result->str);
	for($i=1;$i<sizeof($buff);$i++){
		$tmp=explode('</script>',$buff[$i]);
		$buff[$i]=$tmp[1];
	}
	
	$result->str=implode('',$buff);
	return $result;
}


function wget($f,$url,$host){
	$result1=new go_inside_www_result;
	$result2=new go_inside_www_result;
	$result3=new go_inside_www_result;
	$result=new go_inside_www_result;
	
	$result1=go_inside_www($f,$url,$host,'href=');
	$result2=go_inside_www($result1->str,$url,$host,'src=');
	$result3=go_inside_www($result1->str,$url,$host,'style="background-image: url');
	
	$result->str=$result2->str;
	
	foreach ($result3->need_name as $name){
		$result->need_name[]=$name;
	}

	foreach ($result3->need_link as $link){
		$result->need_link[]=$link;
	}
	
	
	foreach ($result1->need_name as $name){
		$result->need_name[]=$name;
	}

	foreach ($result1->need_link as $link){
		$result->need_link[]=$link;
	}

	foreach ($result2->need_name as $name){
		$result->need_name[]=$name;
	}

	foreach ($result2->need_link as $link){
		$result->need_link[]=$link;
	}
	
	return $result;
}

	function mega_unlink($dir,&$prefix){
		$dir_name=$dir;
		$dir=opendir($dir);
		while ($file = readdir($dir)) {
			if(($file!=".")&&($file!="..")){
				if(is_file($dir_name.$file)){
					unlink($dir_name.$file);
				}else{
					mega_unlink($dir_name.$file."/");
					rmdir($dir_name.$file);
				}
			}
		}
		closedir($dir);
	}
	function make_the_dir($dir){
		global $backupdir;
			unset($dirmas);
			$dirmas=explode('/',$dir);
			for($i=0;$i<sizeof($dirmas);$i++){
				$page=$backupdir;
				for($j=0;$j<($i+1);$j++){
					$page=$page.$dirmas[$j]."/";
				};
				mkdir($page);
			}
	}
	function webOpen($id){
		$query=mysql_fetch_array(mysql_query("select * from tree where id=$id")) ;
		if($id!=$query['pid']){
		$page=$query['page'];
		$pid=$query['pid'];
		if(($pid!=100)&&($pid!=98)&&($pid!=99)){
			$j=0;
			while(($pid!="100")&&($pid!="98")&&($pid!="99")){
				$j++;
				$query2=mysql_fetch_array(mysql_query("select page,pid from tree where id=".$pid));
				$pid=$query2['pid'];
				$page=$query2['page']."/".$page;
				if($j>10){
					$pid=100;
				}
			}
		}
		make_the_dir($page);
		}
	}
	
	function read_and_put($dir,&$prefix){
		global $backupdir;
		global $server;
		$server='maletti';
		if(!isset($prefix)){
			$prefix=$dir;
		}
		$dir_name=$dir;
		$dir=opendir($dir);
		while ($file = readdir($dir)) {
			if(($file!=".")&&($file!="..")){
				if(is_file($dir_name.$file)){
					//unlink($dir_name.$file);
				}else{
					read_and_put($dir_name.$file."/",$prefix);
				}
			}
		}
		closedir($dir);		
	
	$download_path="http://".$server."/".substr($dir_name.$file,strlen($prefix))."/";
	$txt=file_get_contents($download_path);
	$result=wget($txt,$download_path,"http://maletti");
	$txt=$result->str;
	$download_path= del_from_right($download_path);
	for($i=0;$i<sizeof($result->need_name);$i++){
		
		
		//echo "need file ".$result->need_link[$i]." and save it as ".del_from_right($backupdir).$result->need_name[$i]."\n\n";
		$file_mas=explode('/',$result->need_name[$i]);
		unset($file_mas[sizeof($file_mas)-1]);
		$file_mas=implode('/',$file_mas);
		make_the_dir($file_mas);

		
		if(($result->need_name[$i]=='/main.css')&&(!is_file(del_from_right($backupdir).$result->need_name[$i]))){
			echo "\nMAINCSS\n";
			echo "\n ==> ".$download_path." <==\n";
			$css_text=file_get_contents($result->need_link[$i]);
			
			//$css_result=wget($css_text,$result->need_link[$i],"http://maletti");
			$css_result=go_inside_www($css_text,$result->need_link[$i],"http://maletti",'background-image: url');

			//echo $css_result->str;
			
			$css_file=fopen(del_from_right($backupdir).$result->need_name[$i],"w");
			fputs($css_file,$css_result->str);
			fclose($css_file);
			
			
			
		}else{
			copy($result->need_link[$i],del_from_right($backupdir).$result->need_name[$i]);
		}
		
		
		
		//echo(is_file($del_from_right($backupdir).$result->need_name[$i]));
		//if(substr($result->need_name[$i],0,1)=='/'){$result->need_name[$i]=substr($result->need_name[$i],1);};
		
		//};
		//make_the_dir();
	
		//if(($result->need_name[$i]=='/main.css')&&(!is_file(del_from_right($backupdir).$result->need_name[$i]))){
		//	echo "\nMAINCSS\n";
		//}
	
	}
	$newfile=fopen($dir_name.$file."/index.html","w");
	fputs($newfile,$txt);
	fclose($newfile);
	}

	include "../../../connect.php";
	$a=explode(';',$_POST['a']);
	mega_unlink($backupdir);
	foreach($a as $str){
		$id=substr($str,8);
		if($id!=''){
			webOpen($id);
		}
	}
	read_and_put($backupdir);
	
	
	
	echo "\nВ теории готово";
?>