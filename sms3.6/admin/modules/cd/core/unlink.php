<?php
//Удаление файла презентации
if(unlink("../../../".$_POST['dir']."/presentations/".$_POST['file'])){
	echo "Файл ".$_POST['file'].' успешно удален с сервера.';
}else{
	echo "Файл ".$_POST['file'].' не был удален по причине, неизвестной человечеству.';
	echo getcwd();
}
?>