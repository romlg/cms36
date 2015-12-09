<html>
<head>
<link href="main2.css" rel="stylesheet" type="text/css">
<title><?php if (isset($_GET['title'])) echo $_GET['title']; else echo 'Увеличенное изображение';?></title>
</head>
<body marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" bottommargin="0" rightmargin="0">
<table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 10px 5px 5px 5px">
<tr>
<?
$img = $_GET['img'];
$size = array();
if (substr($img, 0, strlen('http://')) != 'http://') {
	$size = @GetImageSize("." . $img);
} else {
	$size = @GetImageSize($img);
}
$src = $_GET['img'];
if ($size)
echo "
<script language='JavaScript'>
<!--
	window.resizeTo(".($size[0]+50).",".($size[1]+70).");
//-->
</script>"
?>
<td align="center"><img src="<?=$src?>"  alt="<?=@$_GET['title']?>" border="0"></td>
</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td align="center" width="33%">&nbsp;</td>
<td align="center" width="33%"><a href="javascript:window.close();">Закрыть</a></td>
<td align="center" width="33%">&nbsp;</td>
</tr>
</table>
</body>
</html>