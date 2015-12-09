<?php

function del_from_left($str,$count){
	return(substr($str,$count,strlen($str)-$count));
}

function del_from_right($str,$count){
	return(substr($str,0,strlen($str)-$count));
}

function show_html($str){
	
	return str_replace("[br]","<br>",str_replace('<','[',str_replace('>',']',$str)));
}

function if_sufix($str,$sufix){
	
	$str_len=strlen($str);
	$sufix_len=strlen($sufix);
	if(substr($str,$str_len-$sufix_len,$sufix_len)==$sufix){
		$result=true;
	}else{
		$result=false;
	}
	return $result;
	
}

function if_prefix($str,$prefix){
	
	$str_len=strlen($str);
	$prefix_len=strlen($prefix);
	if(substr($str,0,$prefix_len)==$prefix){
		$result=true;
	}else{
		$result=false;
	}
	return $result;
	
}

function first_char($str){

	return substr($str,0,1);

}

function last_char($str){
	
	$str_len=strlen($str);
	return substr($str,$str_len-1,1);
	
}

?>