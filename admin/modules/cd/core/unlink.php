<?php
//�������� ����� �����������
if(unlink("../../../".$_POST['dir']."/presentations/".$_POST['file'])){
	echo "���� ".$_POST['file'].' ������� ������ � �������.';
}else{
	echo "���� ".$_POST['file'].' �� ��� ������ �� �������, ����������� ������������.';
	echo getcwd();
}
?>