<?php
require_once("watermark.php");

class TFm2 extends TTable {
    var $name = 'fm2';
    var $dir_id = 0;
    var $deny = array('.htaccess', '.php', '.phtml', '.php3', '.php4', '.shtml');
    //массив ошибок накладывания водяного знака
    var $waterMark_errors = array();

    function TFm2() {
        global $str, $actions;

        TTable::TTable();

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
        'title'    => array('Файловый менеджер','File Manager',),
        ));
        $field = get('field',false,'gp');
        if ($field){
            $actions[$this->name]['return'] = array(
            'OK',
            'OK',
            'link'        => "cnt.sendValues(); ",
            'img'         => '../third/restore_f2.png',
            'display'        => 'none',
            );
        }
        $actions[$this->name]['load'] = array(
        'Загрузить',
        'Load',
        'link'        => "
				 	  cnt.document.getElementById('createForm').style.visibility='hidden';
				  	  cnt.direction = -1;
					  cnt.startMainLoad();
					  cnt.showDownloadFrom();
				  ",
				  'img'         => '../third/filesave.png',
				  'display'        => 'none',
				  );
				  $actions[$this->name]['create'] = array(
				  'Создать директорию',
				  'Create',
				  'link'        => "
				 	  cnt.document.getElementById('downloadForm').style.visibility='hidden';
				  	  cnt.direction = -1;
					  cnt.startMainLoad();
					  cnt.showCreateFrom();",
					  'img'         => '../third/add_section.png',
					  'display'        => 'none',
					  );
					  $actions[$this->name]['delete'] = array(
					  'Удалить',
					  'Delete',
					  'link'        => "cnt.Delete();",
					  'img'         => '../third/cancel_f2.png',
					  'display'        => 'none',
					  );
					  $actions[$this->name]['rename'] = array(
					  'Переименовать',
					  'Rename',
					  'link'        => "
				 	  cnt.document.getElementById('changenameForm').style.visibility='hidden';
				  	  cnt.direction = -1;
					  cnt.startMainLoad();
					  cnt.showChangenameForm();",
					  'img'         => '../third/menu.png',
					  'display'        => 'none',
					  );
					  $data = $this->ReadIni('modules/'.$this->name.'/module.ini');
					  if (isset($data['module']['watermark']) && $data['module']['watermark']){
					      $actions[$this->name]['watermark'] = array(
					      'Водяной знак',
					      'Water Mark',
					      'link'        => "
					 	  cnt.document.getElementById('watermarkForm').style.visibility='hidden';
					  	  cnt.direction = -1;
						  cnt.startMainLoad();
						  cnt.showWatermarkForm();",
						  'img'         => '../third/generic.png',
						  'display'        => 'block',
						  );
					  }
					  $data['dirs'] = array();
					  $data['defaults'] = array();

					  $dir = FILES_DIR.domainRootId();
					  if (!is_dir($dir) || is_root()) {
					      $dir = FILES_DIR;
					  }

					  $data['dirs'][] = $dir;
					  $data['defaults']['dir'] = $dir;

					  $this->config = $data;
    }

    //-------------------------------------------------------------------

    function Show() {
        if (!empty($_POST)) {
            $actions = get('actions', 'show', 'p');
            if ($actions) {
                return $this->$actions();
            }
        }
        $sdir = get('sdir', false, 'pg');
        session_start();
        if ($sdir){
            $_SESSION['sdir'] = $sdir;
        } else {
            $_SESSION['sdir'] = 'null';
        }
        session_write_close();

        $data = $this->config;
        foreach ($data['dirs'] as $k=>$v){
            $t = $this->verifyDir($v);
            if ($t){
                $ret['dirs'][$t['dir']] = $t['folder'];
                $dirs[$t['dir']] = $t['dir'];
            } else {
                unset($data['dirs'][$k]);
            }
        }
        $dir_id = $this->getStartDir();
        $f = false;

        foreach ($dirs as $k=>$v){
            if ($k == $dir_id['dir']){
                $ret['dir_id'] = $k;
                $f = true;
                break;
            }
        }
        if (!$f){
            $ret['dir_id'] = key($dirs);
        }

        $ret['field'] = get('field',false,'gp');
        $ret['formname'] = get('formname',false,'gp');
        $ret['dir'] = $this->getDir($dir_id['dir']);
        $ret['thisname'] = $this->name;
        $ret['watermark'] = isset($data['module']['watermark_file']) ? $data['module']['watermark_file'] : null;
        $ret['dir_id'] = $this->getDir($dir_id['dir']);
        return $this->Parse($ret, $this->name.'.tmpl');
    }

    //-------------------------------------------------------------------

    function EditSendValue(){
        $down_dir = substr(get('down_dir','','pg'),2);
        $field = get('field',false,'gp');
        $formname = get('formname',false,'gp');
        $ids = get('ids',array(),'pg');
        if (empty($ids)){
            $ret = $down_dir;
        } else {
            $ret = $down_dir.implode(",".$down_dir, $ids);
        }
        echo "
			<script>
			try
			{
			top.opener.document.forms.".$formname.".elements['".$field."'].value = '".mysql_escape_string($ret)."';
			}
			catch(e){}
			window.parent.parent.parent.close();
			</script>
		";
    }

    //-------------------------------------------------------------------

    function getDir($default){
        $dir = get('dir',$default,'pg');
        $info = $this->verifyDir($dir);
        if (!is_array($info)){
            $sdir = $this->getStartDir();
            $info = $this->verifyDir($sdir['dir']."/".$dir);
        }
        if (!is_array($info)){
            $info = $this->verifyDir($default);
        }
        return $info['dir'];
    }

    //-------------------------------------------------------------------
    //функция поиска директории
    function verifyDir($dir){
        $flag = false;

        $t = explode('/', $dir);
        foreach ($t as $k=>$v){
            if ($v != ".." && $v != "." && $v != ""){ $t2[] = $v;}
        }

        if (isset($t2) && !empty($t2)){
            $dir = "../".implode("/", $t2)."/";

            if (substr($dir, 0, strlen(FILES_DIR)) != FILES_DIR) {
                // Пытаются обратиться к папке не files
                return false;
            }

            $last = $t2[count($t2)-1];
        } else {
            return false;
        }

        $flag = is_dir($dir);
        if (!$flag){
            return false;
        }
        return array(
        'dir' => $dir,
        'folder' => $last,
        );

    }

    //-------------------------------------------------------------------

    function getStartDir(){
        $sdir = get('sdir', 'null', 'gps');

        $cfg = $this->config;
        if ($sdir == 'null'){
            $t = get('dir', 'null', 'gps');
            $nosdir = get('nosdir', false, 'gps');
            if ($nosdir == false){
                $t = $this->verifyDir($t);
                if (in_array($t['folder'], $cfg['dirs'])){
                    $sdir = $t['dir'];
                }
            }
        }
        if ($sdir != 'null'){
            $f = false;
            foreach ($cfg['dirs'] as $k=>$v){
                $v = $this->verifyDir($v);
                $val = realpath($v['dir']);
                $startdir = $this->verifyDir($sdir);
                if ($val == substr(realpath($startdir['dir']), 0, strlen($val))){
                    $dir = $startdir;
                    $f = true;
                    break;
                }
            }
        } else {
            $dir = $this->verifyDir($cfg['defaults']['dir']);
            $f = true;
        }
        if (!isset($dir) || $f == false){
            $dir = $this->verifyDir($cfg['defaults']['dir']);
        }
        return $dir;

    }

    //------------------------------------------------------------------

    function Delete(){
        $ids = get('ids',array(),'gp');

        $dir = get('down_dir','null','gp');
        $vdir = $this->verifyDir($dir);
        if (!is_array($vdir)){
            $vdir = $this->getStartDir();
        }
        $dir = $vdir['dir'];

        if (empty($ids)){
            $res = rmdir($dir);
            $dot = strrpos($dir,"/");
            $dir = substr($dir,0,$dot);
            $dot = strrpos($dir,"/");
            $ndir = substr($dir,0,$dot);
            if ($res){
                echo "<script>alert('Удаление прошло успешно!');parent.getTree('".$ndir."');</script>";
            } else {
                echo "<script>alert('Ошибка! У вас нет прав на удаление, или директория не пустая!');</script>";
            }
        } else {
            $t = true;
            foreach ($ids as $k=>$v){
                $filename = $this->SlashSep($dir,$v);
                if (substr($filename ,-1 ,1) == "/"){
                    $filename = substr($filename,0 ,-1);
                }
                $res = unlink($filename);
                if ($res == false){
                    $t = false;
                }
            }
            if ($t){
                echo "<script>alert('Удаление прошло успешно!');parent.openDir('".$dir."');</script>";
            } else {
                echo "<script>alert('".$this->SlashSep($dir,$v)." Ошибка при удалении файлов! Проверьте Ваши права на удаление.');</script>";
            }
        }
    }

    //-------------------------------------------------------------------

    function editchangePerm(){
        $dir = $this->getDir('null');
        $value = get('value','null','gp');
        $who = get('who','null','gp');
        $file = get('file','','gp');
        if (!empty($file)){
            $dir .= $file;
        }
        $perm = file_GetPermsInfo(fileperms($dir));

        $perm[substr($who,0,1)][substr($who,1)] = (($value == 'true')? 1 : 0);

        $pr = "";
        foreach ($perm as $k=>$v){
            if ($k!='type'){
                $val = 0;
                if ($v['r'] == 1){$val+=4;}
                if ($v['w'] == 1){$val+=2;}
                if ($v['x'] == 1){$val+=1;}
                $pr .= decoct($val);
            }
        }
        chown($dir, fileowner($_SERVER['SCRIPT_FILENAME']));
        chgrp($dir, filegroup($_SERVER['SCRIPT_FILENAME']));
        $flag = chmod($dir, intval($pr, 8));
        clearstatcache();

        $perm = file_GetPermsInfo(fileperms($dir));

        $return = array();
        if (!$flag){
            $return['error'] = 1;
        }
        $return['perm'] = $perm;

        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();
    }

    //-------------------------------------------------------------------

    function editgetFolderInfo(){
        $dir = $this->getDir('null');

        $sdir = $this->getStartDir();
        if ($dir == $sdir['dir']){
            $data['current_dir'] = mysql_escape_string($sdir['folder']);
            $data['dir'] = mysql_escape_string($dir);
            $data['filectime'] = (@filectime($dir) == 315522000)?'нет данных':date("H:i:s d.m.Y ", @filectime($dir));
            $data['fileatime'] = (@fileatime($dir) == 315522000)?'нет данных':date("H:i:s d.m.Y ", @fileatime($dir));
            $data['filemtime'] = (@filemtime($dir) == 315522000)?'нет данных':date("H:i:s d.m.Y ", @filemtime($dir));
            $data['permission'] = file_GetPermsInfo(@fileperms($dir));
        } else {
            $bez_slesha = substr($dir,0,-1);
            $data['current_dir'] = mysql_escape_string(substr($bez_slesha,strrpos($bez_slesha,"/")+1));
            $data['dir'] = mysql_escape_string($dir);
            $data['filectime'] = date("H:i:s d.m.Y ", @filectime($dir));
            $data['fileatime'] = date("H:i:s d.m.Y ", @fileatime($dir));
            $data['filemtime'] = date("H:i:s d.m.Y ", @filemtime($dir));
            $data['permission'] = file_GetPermsInfo(@fileperms($dir));
        }

        $return = array();
        $return['info'] = $data;

        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();
    }

    //-------------------------------------------------------------------

    function editgetFileInfo(){
        $dir = $this->getDir('null');
        $file = get('file', 'null', 'gp');
        if (substr($dir,-1)!="/"){
            $dir .= "/";
        }
        $data['dir'] = mysql_escape_string($dir);

        /*$nf = 'Наши Стандарты  -  Вёрстка Требования  -  Html (@Shwacko).chm';
        pr($file);
        pr($nf);
        pr(is_file($dir.$file));
        pr(is_file($dir.$nf));*/


        $data['file'] = e(h($file));
        $data['filectime'] = date("H:i:s d.m.Y ", @filectime($dir.$file));
        $data['fileatime'] = date("H:i:s d.m.Y ", @fileatime($dir.$file));
        $data['filemtime'] = date("H:i:s d.m.Y ", @filemtime($dir.$file));
        $data['permission'] = file_GetPermsInfo(@fileperms($dir.$file));
        $data['size'] = $this->formatSize(@filesize($dir.$file));

        $images = array('jpeg','jpg','gif','png');

        if (in_array(strtolower(substr($file,strrpos($file,'.')+1)), $images)){
            $data['imgsize'] = @getimagesize($dir.$file);
            foreach ($data['imgsize'] as $k=>$v){
                if (is_numeric($k)){
                    $data['imgsize']['a'.$k] = $data['imgsize'][$k];
                    unset($data['imgsize'][$k]);
                }
            }
        }

        $return = array();
        $return['info'] = $data;
        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();
    }

    //-------------------------------------------------------------------

    function editgetTree(){
        $dir = $this->getDir('null');

        if (substr($dir,-1)=="/"){
            $dir = substr($dir,0,-1);
        }
        $sdir = $this->getStartDir();

        if ($dir == 'null'){
            $dirs = $this->getDirs($sdir['dir']);
        } else {
            $dr = substr($dir,strlen($sdir['dir']));

            $drs = explode('/', $dr);
            $dirs = $this->getDirs($sdir['dir']);
            $put = $sdir['dir'].current($drs)."/";
            $this->RGetTree($dirs, $drs, $put);
        }

        $return = array();
        $return['basetree']['name'] = $sdir['folder'];
        $return['basetree']['dir'] = ($dir=='null')?$sdir['dir']:$dir;
        $return['tree'] = $dirs;

        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();
    }
    //------------------------------------------------------------------
    function RGetTree(&$dirs, &$drs, $put, $level = 2){
        $dirname = current($drs);
        foreach ($dirs as $k2=>$v2){
            if ($v2['name'] == $dirname){
                $dirs[$k2]['attach'] = $this->getDirs($put, $level);
                array_shift($drs);
                $put = $put.current($drs)."/";
                $level++;
                $this->RGetTree($dirs[$k2]['attach'], $drs, $put, $level);
                break;
            }
        }
    }
    //------------------------------------------------------------------
    function editgetDirFiles(){
        $dir = $this->getDir('null');
        $sdir = $this->getStartDir();
        if ($dir == 'null'){
            $dir = $sdir['dir'];
        }
        $files = $this->getFiles($dir);
        $current_dir = e(substr($dir,strlen($sdir['dir'])));
        if (substr($current_dir,-1)!="/"){
            $current_dir .= "/";
        }
        if (substr($current_dir,0,1)!="/"){
            $current_dir = "/".$current_dir;
        }

        $return = array();
        $return['current_dir'] = $current_dir;
        $return['files'] = $files;

        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();

    }
    //------------------------------------------------------------------
    function editgetNode(){
        $dir = $this->getDir('null');
	// странная замена... закомментировала ее, т.к. с ней не работало построение подпапок для папки MotoGP2011
	//$dir = str_replace(array("P"),array("&"),$dir);
        $level = get('level',1,'gp');
        $level++;
        $name = get('name','null','gp');

        $dirs = array_reverse($this->getDirs($dir, $level));
        //pr($dirs);
        $sdir = $this->getStartDir();
        $current_dir = mysql_escape_string(substr($dir,strlen($sdir['dir'])));
        if (substr($current_dir,0,1) != "/"){
            $current_dir = "/".$current_dir;
        }
        if (substr($current_dir,-1) != "/"){
            $current_dir .= "/";
        }

        $return = array();
        $return['current_dir'] = $current_dir;
        $return['node'][$name] = $dirs;

        $json = & Registry::get('json');
        $ret = $json->encode($return);
        echo $ret;
        die();
    }
    //------------------------------------------------------------------
    //получает содержание директории
    function getDirs($dir, $level = 1){
        $dirs = array();
        if (substr($dir,-1)!="/"){
            $dir .= "/";
        }
        if ($handle = @opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.' && is_dir($dir.$file)) {
                    //смотрим, пустая ли папка
                    $handle2 = @opendir($dir.$file);
                    $next = 0;
                    while (false !== ($file2 = readdir($handle2))) {
                        if ($file2 != '.' && $file2 != '..' && substr($file2, 0, 1) != '.' && is_dir($dir.$file."/".$file2)) {
                            $next = 1;
                            break;
                        }
                    }
                    closedir($handle2);

                    //может быть проблема с str_replace, еслди после замены недопустимых символов, совпадут id шники папок..все должно быть уникальным, но пока не придумал еще как:(
                    $dirs[] = array(
                    'id'		=> $this->dir_id,
                    'next'		=> $next,
                    'name'		=> h($file),//str_replace(" ","&nbsp;",$file),
                    'level'		=> $level,
					'dir'		=> h($dir.$file),
					// странная замена... закомментировала ее, т.к. с ней не работало построение подпапок для папки MotoGP2011
					/* h(str_replace(
                    array("&"),
                    array("P"),
					$dir.$file)),*/
                    'dir_id'	=> md5($dir.$file),/*htmlspecialchars(str_replace(
                    array("$","&","(",")","!",".","/"," ","{","}","0","1","2","3","4","5","6","7","8","9"),
                    array("Q","P","W","U","S","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O"),
                    $dir.$file)),*/
                    'is_last'	=> 0,
                    'perms'		=> fileperms($dir.$file),
                    );
                    $this->dir_id++;
                }
            }
            $end = end(array_keys($dirs));
            if (isset($dirs[$end]['is_last'])){
                $dirs[$end]['is_last'] = 1;
            }
            closedir($handle);
        }
        usort($dirs, "sort_dirs");
        return $dirs;
    }
    //------------------------------------------------------------------
    //получает содержание директории
    function getFiles($dir){
        if (substr($dir,-1)!="/"){
            $dir .= "/";
        }
        $files = array();
        $k = 1;
        if ($handle = @opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.' && !is_dir($dir.$file)) {
                    $size = is_file($dir.$file) ? filesize($dir.$file) : -1;
                    $size = $this->formatSize($size);
                    $files[] = array(
                    'id_num'	=> $k,
                    'id'		=> e(h($file)),
                    'name'		=> h($file),
                    'size'		=> $size,
                    'ext'		=> substr(get_file_ext($file),1),
                    'perms'		=> file_GetPermsInfo(@fileperms($dir.$file)),
                    );
                    $k++;
                }
            }
            closedir($handle);
        }
        usort($files, "sort_files");
        return $files;
    }
    //-------------------------------------------------------------------

    function editcreateDir(){
        $dir_main = get("down_dir",'null','gp');
        $vdir = $this->verifyDir($dir_main);
        if (!is_array($vdir)){
            $vdir = $this->getStartDir();
        }

        $dir_main = $vdir['dir'];

        $dir = get('dir','null','gp');
        $cdir = $this->SlashSep($dir_main,$dir);
        if (!is_dir($cdir)) {
            if (!mkdir($cdir, DIRS_MOD)) {
                echo "<script>alert('Не могу создать директорию!');</script>";
            }
            chmod($cdir, DIRS_MOD);
            chown($cdir, fileowner($_SERVER['SCRIPT_FILENAME']));
            chgrp($cdir, filegroup($_SERVER['SCRIPT_FILENAME']));
        }

        echo "
		<script>
		parent.getTree('".$cdir."');
		parent.hideCreateFrom();
		parent.stopMainLoad();
		</script>
		";
    }

    //-------------------------------------------------------------------

    function Editchange_name(){
        $dir = get("change_dir",'','gp');
        $file = get("change_file",'','gp');
        $name = get("file_name",'','gp');
        $odir = "";
        if ($file){
            if (is_file(realpath($dir)."/".$file)){
                $ret = rename(realpath($dir)."/".$file, realpath($dir)."/".$name);
                $odir = $dir;
            }
        } else {
            //если папка корневая, то мы не можем ее переименовывать
            $ret = false;
            $sdir = $this->getStartDir();
            if (realpath($dir) != realpath($sdir['dir'])){
                $pos = strrpos(realpath($dir),'/');
                if (!$pos){ $pos = strrpos(realpath($dir),'\\');}
                $ret = rename(realpath($dir), substr(realpath($dir), 0, $pos+1).$name);
                $pos = strrpos(substr($dir,0,-1),'/');
                if (!$pos){ $pos = strrpos(substr($dir,0,-1),'\\');}
                $odir = substr($dir, 0, $pos+1).$name;
            }
        }
        echo "
		{literal}
		<script>
		";
        if ($ret){
            //echo "alert('Изменения сохранены!')";
        } else {
            echo "alert('Ошибка при изменении!')";
        }
        echo "
		parent.getTree('".$odir."');
		parent.hideChangenameForm();
		parent.stopMainLoad();
		parent.openDir('".$odir."');
		</script>
		{/literal}
		";
    }

    //-------------------------------------------------------------------

    function EditWaterMark(){
        $ids = get('ids',array(),'gp');
        $watermark = get('watermark',false,'gp');
        if (!is_file("..".$watermark)){
            echo "<script>alert('Не найден файл с водяным знаком!');</script>";
            die();
        }

        $dir = get('down_dir','null','gp');
        $vdir = $this->verifyDir($dir);
        if (!is_array($vdir)){
            $vdir = $this->getStartDir();
        }
        $dir = $vdir['dir'];

        if (empty($ids)){
            //на файлы с разрешениями картинок накладываем водяной знак
            //получаем список файлов в директории

            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && !is_dir($dir . $file)) {
                        $files[] = $file;
                    }
                }
                closedir($handle);
            }
            foreach ($files as $k=>$filename){
                $this->addWaterMark("..".$dir . $filename, "..".$watermark);
            }

        } else {
            $t = true;
            foreach ($ids as $k=>$v){
                $filename = $this->SlashSep($dir, $v);
                if (substr($filename ,-1 ,1) == "/"){
                    $filename = substr($filename,0 ,-1);
                }
                $this->addWaterMark($filename, "..".$watermark);
            }
        }
        echo "
		<script>
		parent.hideWatermarkForm();
		parent.stopMainLoad();
		parent.watermark = 1;
		parent.watermark_rang++;
		</script>
		";
        die();
    }

    //-------------------------------------------------------------------

    function addWaterMark($file, $wmark){
        //проверяем, является ли переданный файл, файлом изображения
        $images = array('jpeg','jpg','gif','png');
        $format = strtolower(substr($file,strrpos($file,'.')+1));

        if (in_array($format, $images)){
            $wmark_info = getimagesize($wmark);
            $file_info = getimagesize($file);

            if ($wmark_info[0] > $file_info[0]) {$this->waterMark_errors[$file] = "Ширина изображения меньше чем ширина водяного знака"; return;}
            if ($wmark_info[1] > $file_info[1]) {$this->waterMark_errors[$file] = "Высота изображения меньше чем высота водяного знака"; return;}

            $handle = new RWatermark($file_info[2], $file);
            if (!isset($this->config['module']['watermark_position'])) $this->config['module']['watermark_position'] = "CM";
            $handle->SetPosition($this->config['module']['watermark_position']);
            $handle->SetTransparentColor(255, 0, 255);
            if (!isset($this->config['module']['watermark_transparency'])) $this->config['module']['watermark_transparency'] = "60";
            $handle->SetTransparency($this->config['module']['watermark_transparency']);

            switch ($wmark_info[2]){
                case '1': $handle->AddWatermark(FILE_GIF, $wmark); break;
                case '2': $handle->AddWatermark(FILE_JPEG, $wmark); break;
                case '3': $handle->AddWatermark(FILE_PNG, $wmark); break;
            }

            // Определяем примерно, с каким качеством сохранять картинки
            $size = filesize($file);
            if ($size <= '10000') {
                $quality = '90';
            } else if ($size > '10000' && $size <= '30000') {
                $quality = '90';
            } else $quality = '85';

            if ($file_info[2]==1) {
                imagegif($handle->marked_image,$file);
            } elseif ($file_info[2]==2) {
                imagejpeg($handle->marked_image,$file, $quality);
            } elseif ($file_info[2]==3) {
                imagepng($handle->marked_image,$file);
            }
            $handle->Destroy();
        }

    }


    //-------------------------------------------------------------------

    function EditDownload()  {
        $dir = get("down_dir",'null','gp');
        $vdir = $this->verifyDir($dir);
        if (!is_array($vdir)){
            $vdir = $this->getStartDir();
        }
        $dir = $vdir['dir'];

        $width = get("width", 0, 'gp');
        $height = get("height", 0, 'gp');
        $file = (isset($_FILES['file']) ? $_FILES['file']: "");

        if ($width!=0 || $height!=0)
        $size = array($width,$height);

        if (!empty($file)){
            # находим несуществующее имя
            $file_name = GetPureName($file['name']);
            # уменьшаем рассширение файла, для номрального просмотра в fm
            $dot = strrpos($file_name, '.');
            $ext = strtolower(substr($file_name, $dot, (strlen($file_name)-$dot)));
            if (in_array($ext, $this->deny)){
                echo "<script>alert('Вы не можете закачивать файлы с таким расширением.');</script>";
                return;
            }
            $file_name = substr($file_name, 0, $dot).$ext;

            if (file_exists($this->SlashSep($dir, $file_name))) {
                $num = 1;
                while (file_exists($this->SlashSep($dir, new_file_name($file_name, '['.$num.']')))) {
                    $num++;
                }
                $file_name = new_file_name($file_name, '['.$num.']');
            }
            $file_put = $this->SlashSep($dir, $file_name);
            if (!move_uploaded_file($file['tmp_name'], $file_put)) {
                echo "<script>alert('Ошибка загрузки файла');</script>";
            }
            @chmod($file_put, FILES_MOD);
            @chown($file_put, fileowner($_SERVER['SCRIPT_FILENAME']));
            @chgrp($file_put, filegroup($_SERVER['SCRIPT_FILENAME']));
            # Ресайз
            if (!empty($size)){
                $quality = sql_getValue('SELECT value FROM strings WHERE name="resize_quality" AND module="site" AND root_id='.domainRootId().' AND lang="'.lang().'"');
                if (!$quality) $quality = 85;
                $file_name = ResampleImage($dir, $file_name, $size, false, $quality);
            }
            if (get('watermark', false, 'gp')){
                $data = $this->config;
                $watermark = $data['module']['watermark_file'];

                if (!is_file("..".$watermark)){
                    echo "<script>alert('Не найден файл с водяным знаком!');</script>";
                    die();
                }
                $this->addWaterMark($file_name, "..".$watermark);
            }
        }
        echo "
		<script>
		parent.openDir('".$dir."');
		parent.hideDownloadFrom();
		parent.stopMainLoad();
		</script>
		";

    }

    //-------------------------------------------------------------------

    function SlashSep($dir1, $dir2 = '') {
        if ($dir1 && substr($dir1, -1) == '/') {
            $dir1 = substr($dir1, 0, -1);
        }
        if ($dir2 && substr($dir2, 0, 1) == '/') {
            $dir2 = substr($dir2, 1);
        }
        if ($dir2 && substr($dir2, -1) != '/' && ((strrpos($dir2, '.') < strlen($dir2) - 5) || strlen($dir2) < 5)) {
            $dir2 .= '/';
        }
        return $dir1.'/'.$dir2;
    }

    //-------------------------------------------------------------------

    function formatSize($size){
        if ($size/1024<1){
            return $size."&nbsp;b";
        }
        if ($size/1024>1){
            if ($size/1024/1024<1){
                return round($size/1024,2)."&nbsp;kb";
            } else {
                return round($size/1024/1024,2)."&nbsp;mb";
            }
        }
    }
    //-------------------------------------------------------------------

    function ArrayToXML($data, $str, $tab = ""){
        foreach ($data as $k=>$v){
            if (is_array($v)){
                if (is_int($k)){ $k = "a".$k;}
                $str .= $tab."<".$k.">\n";
                $tab .= "\t";
                $str = $this->ArrayToXML($v, $str, $tab);
                $tab = substr( $tab, 0, -1);
                $str .= $tab."</".$k.">\n";
            } else {
                $str .= $tab."<".$k.">".$v."</".$k.">\n";
            }
        }
        return $str;
    }

    //-------------------------------------------------------------------
    //---------------------------------------------------------------------------------
    // учим работать с файлами настроек
    // работа c ini
    //---------------------------------------------------------------------------------
    // функция чтения ini файла
    // возвращает ассоциативный массив
    function ReadIni($filename){

        return ini_read($filename);
    }

    //---------------------------------------------------------------------------------

    //фунция записи в ini файл
    function WriteIni($filename, $data){

        ini_write($filename, $data);
        return true;
    }

    //---------------------------------------------------------------------------------

    //фунция изменении данных в ini файле
    function ChangeIni($filename, $data){

        return ini_change($filename, $data);

    }

    //---------------------------------------------------------------------------------

}

function sort_files($a, $b) {
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}
function sort_dirs($a, $b) {
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}

$GLOBALS['fm2'] = & Registry::get('TFm2');
?>