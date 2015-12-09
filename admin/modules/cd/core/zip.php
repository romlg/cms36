<?php
//Файл архивации
$arch=date('Ymdhis').'.zip';
$backup_dir="../../../backup";
$topdir=getcwd();
require_once('../libs/pclzip.lib.php');
chdir($backup_dir);
$zip = new PclZip($arch);
$dir=opendir('cd');
$str = '';
while($file=readdir($dir)){
	if(($file!='.')&&($file!='..'))
		$str .= ',cd/'.$file;	
}
closedir($dir);
$str=substr($str,1);
$zip->create($str);
copy($arch,'presentations/'.$arch);
unlink($arch);
chdir($topdir);
unlink("info.txt");

?>