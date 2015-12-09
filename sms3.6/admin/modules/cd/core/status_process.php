<?php
include "../../../connect.php";
$in_base=mysql_num_rows(mysql_query("select * from tree_cd"));//$_POST['in_base'];

switch ($_POST['action']){

case "percent":
	$blablabla=@file_get_contents("console.txt");
	$rows=sizeof(explode("\n",$blablabla))-2;
	
	if($rows<1)$rows=0;
	
	$percent=round(($rows*100)/$in_base);
	
	if($percent>98) $percent=99;
	
	if ($blablabla=="THE END OF CD PRESENTATION") $percent=100;
	
	if ($percent!=0){
	echo '
	<table width=300 height=28 cellspacing=0 cellpadding=0 style="border: 1px solid #AAAAAA; background-color: #FFFFFF; "><tr>
		<td align="center" width='.$percent.'% background="modules/cd/img/loader_bg.jpg"><h5 style="color: #FFFFFF; ">'.$percent.'%</td><td>&nbsp;</td>
	</tr></table>';
	}else{
		
	echo'
	<table width=300 height=28 cellspacing=0 cellpadding=0 style="border: 1px solid #AAAAAA; background-color: #FFFFFF; "><tr>
		<td align="center" width=20 background="modules/cd/img/loader_bg.jpg"><h5 style="color: #FFFFFF; ">'.$percent.'%</td><td>&nbsp;</td>
	</tr></table>';
		
	}
	break;

case "log":
	echo @file_get_contents("console.txt");
break;

case "test":
	echo "TEST";
break;

}
?>