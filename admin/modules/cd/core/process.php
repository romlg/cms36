<?php
clear_dir('../../../backup/cd/');
$a=fopen('console.txt','w');
fclose($a);
$a=fopen('info.txt','w');
fclose($a);
//���������� ����, ���� ���� � ����� ��������� �� ������
include "../../../connect.php";
//������ ������� ��������������� ����� ����� � ����� ���� ���� � ������ �����������
//������� ������ ������� �� ���������� ��-�����������
mysql_query('drop table if exists tree_cd');
mysql_query('drop table if exists tree_cd_links');
//���� �������� ������ � �������������� �� ����� ����� � �������, �� ���� ��������� �� ������� �������
$a=explode(';',$_POST['a']);
//unset($a[sizeof($a)-1]);	//������ ��������� ������� - �� ������
//����� ������� ��� id'����� � ������� ��� ��� ������ where �� ������� �������� �������
$where_str=" where";
foreach ($a as $varname => $varvalue){	//$varname - ��� ���������� ����������, $varval - ��������
	if (empty($varvalue)) continue;
	/* ����� � ��������, �������� ��� �������� ������������ �������� ��� ���.
	* ���� ��� - ������ ���� id �� �������
	* TODO: ����� ���������� ��������� ����� ��� ��� �� ����� javascript 
	*/
	/******************************/
	if (isVisible($varvalue, $a)) {
		$varvalue=substr($varvalue,8);
		$where_str=$where_str." id=".$varvalue." or";
	}
}
//�������� ���� (�� ����� ������ ����� �*:%��� ����� and)
$where_str=substr($where_str,0,strlen($where_str)-3);
//� ���� � ���� ����� ������� ������
mysql_query('create table tree_cd as select * from tree'.$where_str);
mysql_query('create table tree_cd_links (id int not null auto_increment, url VARCHAR(255) UNIQUE, done int, primary key(id))');	//�������� ������� ��� ������ � ��������� (������ �� ���������� �� ���� ����)
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