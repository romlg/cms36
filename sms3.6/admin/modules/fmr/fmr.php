<?
require_once("watermark.php");

class TFmr extends TTable {

    var $name = 'fmr';
    var $table = 'images';
    var $deny = array('.htaccess', '.php', '.phtml', '.php3', '.php4', '.shtml');
    var $selector = false;
    ######################

    function TFmr() {
        global $str, $actions;

        TTable::TTable();

        $actions = array();
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title'     => array('Файловый менеджер','File Manager',),
            'file'		=> array('Файл','File'),
            '_file'		=> array('Макс. размер: ','Max size'),
            'resample'	=> array('Масштабировать','Scale'),
            '_resample'	=> array('только для JPEG формата','only for JPEG files'),
            'watermark'	=> array('наложить водяной знак','add watermark'),
            'edit'		=> array('Параметры файла','File params'),
            'add'		=> array('Добавить файл','Add file'),
            'name'		=> array('Название','Title'),
            '_name'		=> array('По-умолчанию равно названию файла','Default value is the name of the file'),
            'name_'		=> array('Картинка','Picture'),
            'date'		=> array('Дата','Date'),
            'size'		=> array('Размер','Size'),
            'width'		=> array('Ширина','Width'),
            'height'	=> array('Высота','Height'),
            'dimensions'	=> array('ШxВ','w*h'),
            'description'	=> array('Описание<br>(255 символов)','Description<br>(255 symbol)'),
            'wait'		=> array('Подождите, идет отправка...','Please wait, sending...'),
            'link'		=> array('Ссылка','Link'),
            'e_rename'	=> array('Произошла ошибка при переименовывании файла.','An error occurred while renaming the file.'),
            'create'	=> array('Добавить','Create'),
            'picture'	=> array('Просмотр','View'),
            'thumbnail'	=> array('Картинка со ссылкой','Image with reference'),
            'no_pic'	=> array('<span class=note>не выбрано</span>','<span class=note>not selected</span>'),
            'setpic'	=> array('Посмотреть','View'),
//            'select'	=> array('&nbsp;OK&nbsp;','&nbsp;OK&nbsp;'),
            'set_pic'	=> array('В редактор &gt;&gt;','In a editor &gt;&gt;'),
            'set_thumb'	=> array('В редактор &gt;&gt;','In a editor &gt;&gt;'),
            'set_small'	=> array('Поставить как маленькую картинку','Set as a small picture'),
            'set_big'	=> array('Поставить как картинку для pop-up окна','Set a pop-up picture'),
            'small'		=> array('<span title=Поставить&nbsp;как&nbsp;маленькую&nbsp;картинку>&lt;М&gt;</span>','<span title="Set&nbsp;as&nbsp;a&nbsp;small&nbsp;picture">&lt;М&gt;</span>'),
            'big'		=> array('Доп.','Доп.'),
            'pic'		=> array('&nbsp;','&nbsp;'),
            'upload'	=> array('Закачать новый файл','Upload new file'),
            'submit'	=> array('Закачать','Upload'),
            'paste'		=> array('Поместить в редактор','Поместить в редактор Put to the editor'),
            'paste_head'	=> array('Поместить картинку в редактор','Put picture to the editor'),
            'big_pic'	=> array('Картинка для pop-up','Picture for pop-up'),
            'small_pic'	=> array('Основная картинка','Main picture'),
            'folder'	=> array('&lt;папка&gt;','&lt;folder&gt;'),
            'del'		=> array('Удалить&nbsp;выделенное?','Delete&nbsp;selected?'),
            'foldername'=> array('Введите имя папки:','Enter the name of the folder:'),
            'createfolder'=>array('Создать папку','Create folder'),
            'error'		=> array('Ошибка: ','Error:'),
            'emakedir'	=> array('не удается создать папку: папка с таким именем уже существует','can not create a folder: a folder with that name already exists'),
            'edeletedir'=> array('не удается удалить непустую папку','unable to remove a nonempty folder'),
            'search'	=> array('поиск','search'),
            'reset'		=> array('cброс','clear'),
            'back'		=> array('вернуться назад','back'),
            'display'   => array('Показывать по','Show on'),
            'pages'     => array('Страницы','Pages'),
            'im_date'   => array('Дата', 'Date'),
            'im_size'   => array('Размер', 'Size'),
            'im_all'    => array('Все', 'All'),
            'im_pic'    => array('Картинки', 'Picture'),
            'im_nopic'  => array('Не картинки', 'Not picture'),
            'e_no_pic'	=> array('Вы не выбрали картинку.','You have not selected a picture'),
            'e_no_dirname'	=> array('Введите имя директории','Enter the name of the directory'),
            'no'		=> array('-','-'),
            'e_no_file'	=> array('Вы не выбрали файл.','You have not selected a file.'),
            'e_not_writable'=> array('Ошибка записи в файловую директорию (отсутствует или запрещена запись)','Error writable'),
            'e_upload'	=> array('Ошибка переноса файла в директорию пользователя','Error upload'),
            'paste'	    => array('Поместить в редактор','Paste'),
        ));

        $data['dirs'] = array();
        $data['defaults'] = array();

        $dir = FILES_DIR.domainRootId();
        if (!is_dir($dir)) {
            $dir = FILES_DIR;
        }

        $data['dirs'][] = $dir;
        $data['defaults']['dir'] = $dir;
    }

    function Show() {
        if (!isset($_GET['dir']) && isset($_SESSION['dir']) && $_SESSION['dir']) {
            $dir = $_SESSION['dir'];
        } else {
            $dir = isset($_GET['dir']) ? $_GET['dir'] : '';
            $_SESSION['dir'] = $dir;
        }

        $dirs_arr = $this->getDirPath($dir);
        $ret = $this->ListFiles();

        $err = get('err', '', 'g');
        if ($err) {
            $ret['error'] = $this->showError($err);
        }

        $GLOBALS['_SESSION']['ref'] = "?page=".$this->name;

        $resamle_options = array_keys($GLOBALS['resamle_options']);
        $options = "<option value='0'>".$this->str('no')."</option>";
        foreach ($resamle_options AS $k=>$v) {
            $options .= "<option value='".$v."'>".$v."</option>";
        }
        $ret['resamle_options'] = $options;

        $ret['filesdir'] = FILES_DIR;
        $ret['err_no_file'] = $this->str('e_no_file');
        $ret['wait'] = $this->str('wait');
        $ret['err_no_dirname'] = $this->str('e_no_dirname');
        $ret['dirname'] = $_SERVER["PHP_SELF"]."?page=".$this->name."&do=makedir&newdir=' + dirname + '&dir=".$dir."&image=".(isset($_GET['image']) ? $_GET['image'] : '')."&aimage=".(isset($_GET['aimage']) ? $_GET['aimage'] : '');
        $ret['name'] = $this->name;
        $ret['dir'] = $dir;
        $ret['dirs_arr'] = $dirs_arr;
        $ret['str_upload'] = $this->str('upload');
        $ret['str_watermark'] = $this->str('watermark');
        $ret['watermark_cfg'] = $GLOBALS['watermark_cfg'];
        $ret['str_resample'] = $this->str('resample');
        $ret['str_note_resample'] = $this->str('_resample');

        return $this->Parse($ret, $this->name.'.tmpl');
    }

    ######################

    function cmp_size($a, $b) {
        if ($a["size"]==$b["size"]) {
            return strcasecmp($a["name"], $b["name"]);
        } else {
            return $a["size"] - $b["size"];
        }
    }
    function cmp_size_desc($a, $b) {
        if ($a["size"]==$b["size"]) {
            return strcasecmp($a["name"], $b["name"]);
        } else {
            return $b["size"] - $a["size"];
        }
    }
    function cmp_name($a, $b) {
        if($a['type']=='dir' || $b['type']=='dir') {
            if($a['type']=='dir' && $b['type']!='dir') return -1;
            if($a['type']!='dir' && $b['type']=='dir') return 1;
            if($a['type']=='dir' && $b['type']=='dir') return strcasecmp($a["name"], $b["name"]);
        } else {
            return strcasecmp($a["name"], $b["name"]);
        }
    }
    function cmp_name_desc($a, $b) {
        if($a['type']=='dir' || $b['type']=='dir') {
            if($a['type']=='dir' && $b['type']!='dir') return -1;
            if($a['type']!='dir' && $b['type']=='dir') return 1;
            if($a['type']=='dir' && $b['type']=='dir') return -strcasecmp($a["name"], $b["name"]);
        } else {
            return -strcasecmp($a["name"], $b["name"]);
        }
    }

    function cmp_date($a, $b) {
        return $a["date"] - $b["date"];
    }
    function cmp_date_desc($a, $b) {
        return $b["date"] - $a["date"];
    }

    function flt_name($val) {
        $ff = isset($_GET["FF"]) ? strtolower($_GET["FF"]) : null;
        return strpos(strtolower($val["name"]), $ff)!==false;
    }

    ######################

    function ListFiles($select='') {
        $FF       = get('FF',        '',    'g');
        $offset   = get('offset',    0,     'g');
        $limit    = get('limit',     10,    'g');
        $dir      = get('dir',       '',    'g');
        $sort     = get('sort',      'name','g');
        $image    = get('image',     '',    'g');
        $aimage   = get('aimage',    '',    'g');
        $cur_image= get('cur_image', '',    'g');
        $fsort    = get('file_sort', '',    'g');
        $fldname  = get('field',     '',    'g');

        if ($dir == '.') $dir = '';

        if (!empty($_GET['formname'])) {
            // по умолчанию всегда считается что в начале пути стоит /files/ отсюда и пляшем

            // определим что это путь с файлом или без
            if (is_file ($dir)) {
                $dir = dirname($dir);
            }
            if (substr($dir, 0, 6) == '/files') {
                $dir = substr($dir, 6);
            }
        } else {
            if (!isset($_GET['dir']) && $_SESSION['dir']) $dir = $_SESSION['dir']; else $_SESSION['dir']=$dir;
        }

        preg_match("/([\d\D]+)(\/)([\d\D]+)$/i", $dir , $prres);
        $updir = isset($prres[1]) ? $prres[1] : null;

        if ($dir) {
            if (get_magic_quotes_gpc()) $dir = stripslashes($dir);
            $dir = preg_replace("`/$`", '', $dir).'/';
        }

        $path = FILES_DIR.$dir;
        $files = $this->ReadDirectory($path, $sort, $FF);
        //var_Dump($files);

        $editLink = "";

        if (!empty($cur_image)) {
            foreach ($files as $k=>$v) {
                if ($v['name'] == $cur_image) {
                    $offset = floor($k/$limit)*$limit;
                }
            }
        }

        $fields = array(
            "name" => $this->str('name'),
            "ext"  => $this->str('big'),
            "date" => $this->str('im_date'),
            "size" => $this->str('im_size')
        );

        if ($select) $fields['select'] = '&nbsp;';

        reset($fields);
        $first_field = each($fields);
        if (!isset($editField) || !$editField) $editField = $first_field[0];
        if (!isset($sortField) || !$sortField) $sortField = $first_field[0];

        # get count
        $count = count($files);

        # get column types
        $column_types = array(
            "size" => "double",
            "name" => "char",
            "date" => "int",
            "ext"  => "char"
        );

        # align array
        $align = array(
            "bit"	=> "center",
            "int"	=> "left",
            "double"=> "right",
            "char"	=> "left"
        );

        $selected = array(1 => '', 2 => '');
        if (!empty($_GET['file_sort'])) $selected[$_GET['file_sort']] = 'selected';

        $option_search_img  = array(
            0 => array(
                'value'    => '',
                'title'    => $this->str('im_all'),
                'selected' => '',
            ),
            1 => array(
                'value'    => '1',
                'title'    => $this->str('im_pic'),
                'selected' => $selected[1],
            ),
            2 => array(
                'value'    => '2',
                'title'    => $this->str('im_nopic'),
                'selected' => $selected[2],
            ),
        );

        $header_titles = array();
        foreach ($fields as $key=>$val) {
            $al = $align[$column_types[$key]];
            if ($key == 'ext') {
                if ($select=='selectthumb') {
                    $header_titles[] = array(
                        'class' => '',
                        'href'  => '',
                        'str_sort_this' => '',
                        'value' => $val,
                    );
                }
            } elseif ($key == 'select') {
                $header_titles[] = array(
                    'class' => "",
                    'href'  => "",
                    'str_sort_this' => "",
                    'value' => $val,
                );
            } else {
                $order = "";
                $sort_key = "";
                if ($sort == $key) {
                    $order = "sortTop";
                    $sort_key = "-".$key;
                } else if ($sort == "-".$key) {
                    $order = "sortBottom";
                    $sort_key = $key;
                } else {
                    $sort_key = $key;
                }

                $header_titles[] = array(
                    'class' => $order,
                    'href'  => "?page=".$this->name."&do=".$select."&FF=".$FF."&sort=".$sort_key."&limit=".$limit."&image=".$image."&dir=".$dir."&aimage=".$aimage."&file_sort=".$fsort,
                    'str_sort_this' => $this->str('sort_this'),
                    'value' => $val,
                );
            }
        }

        # data rows
        $offset = (int)$offset;
        $counter = 0;
        $files_arr = array();
        $dir_link = array();
        if ($dir) {
            $dir_link = array(
                'sizeof_fields' => sizeof($fields)-1,
                'href'          => "?page=".$this->name."&do=".$select."&dir=".$updir."&field=".$fldname."&image=".$image."&limit=".$limit."&aimage=".$aimage."&file_sort=".$fsort,
                'select'        => $select,
            );
        }

        if (sizeof($files)) {
            for($i=$offset; $i<$offset+$limit; $i++) {
                if (isset($files[$i]) && sizeof($files[$i])) {
                    $row = $files[$i];
                    if ($row['type'] == 'dir') {
                        $files_arr[] = array(
                            'name'      => $row["name"],
                            'href'      => "?page=".$this->name."&do=".$select."&dir=".$dir.$row['name']."&field=".$fldname."&image=".$image."&limit=".$limit."&aimage=".$aimage."&file_sort=".$fsort,
                            'select'    => $select,
                            'date'      => date("Y-m-d", $row['date']),
                            'type_file' => $this->str('folder'),
                            'type'      => $row['type'],
                            'img'       => $row['img'],
                        );
                    } else {
                        $files_arr[] = array(
                            'name'      => $row["name"],
                            'href'      => "javascript:SelectPic(\"".$dir.$row['name']."\", ".$row['img'].")",
                            'select'    => $select,
                            'date'      => date("Y-m-d", $row['date']),
                            'type_file' => $row['size'],
                            'type'      => $row['type'],
                            'img'       => $row['img'],
                        );
                    }
                    $counter++;
                }
            }
        }

        $ret = array(
            'FF'            => $FF,
            'name'          => $this->name,
            'select'        => $select,
            'sort'          => $sort,
            'limit'         => $limit,
            'dir'           => $dir,
            'image'         => $image,
            'aimage'        => $aimage,
            'offset'        => $offset,
            'cur_image'     => $cur_image,
            'fsort'         => $fsort,
            'option_search' => $option_search_img,

            'counter'       => $counter,
            'sizeof_fields' => sizeof($fields)+1,
            'dir_link'      => $dir_link,
            'files_arr'     => $files_arr,
            'header_titles' => $header_titles,
            'limit_arr'     => $this->GetArrayOptions(array(10=>10,30=>30,50=>50), $limit),
            'navig'         => $this->Navigation(array('page'=>$this->name,'do'=>$select,'limit'=>$limit,'sort'=>$sort,'FF'=>$FF,'image'=>$image,'dir'=>$dir,'aimage'=>$aimage,'file_sort'=>$fsort,'field'=>$fldname), $count, $offset, $limit),

            'str_empty'     => $this->str('empty'),
            'str_set_big'   => $this->str('set_big'),
            'str_del'       => $this->str('del'),
            'str_display'   => $this->str('display'),
        );

        return $ret;
    }

    ######################

    function ReadDirectory($path, $sort="name", $filter="") {

        $fsort = isset($_GET['file_sort']) ? $_GET['file_sort'] : '';
        $types = array('jpg','jpeg','gif','png');

        $d = @dir($path);
        $files = array();
        if (!$sort) $sort = "name";
        if($d) {
            while ($entry = $d->read()) if ($entry!="." && $entry!="..") {

                $p = pathinfo($entry);

                if    ($fsort == 1 && !in_array($p['extension'], $types) && is_file($path.$entry)) continue;
                elseif($fsort == 2 && in_array($p['extension'], $types) &&is_file($path.$entry)) continue;
                else

                $files[] = array(
                "size"	=> sprintf("%.1fkb", filesize($path.$entry)/1024),
                "name"	=> $entry,
                "date"	=> filemtime($path.$entry),
                "type"	=> is_file($path.$entry)?'file':'dir',
                "img"   => isset($p['extension']) && in_array($p['extension'], $types)?1:0,
                );
            }
            $d->close();
        }
        if ($filter) $files = array_filter($files, Array( &$this, 'flt_name'));
        $func = substr($sort, 0, 1) == "-" ? "cmp_".substr($sort, 1)."_desc" : "cmp_".$sort;
        usort($files, Array( &$this, $func));
        return $files;
    }

    ######################

    function TableNavigation($navActionStr, $navCount, $navOffset, $navLimit) {
        $part = 5;
        $ret = "";
        $cPage = floor($navOffset/$navLimit);
        $startPage = floor($cPage/$part)*$part;
        $endPage = $startPage + $part - 1;
        if ($endPage>floor(($navCount-1)/$navLimit)) $endPage = floor(($navCount-1)/$navLimit);
        if ($endPage) {
            $ret.= "<div class='paging'>";
            $ret.= str('pages').": ";
            if ($startPage>1) $ret.= sprintf("<a href=\"%s&offset=%d\">...</a> ", $navActionStr, ($startPage-$part)*$navLimit);
            for ($i=$startPage; $i<=$endPage; $i++) {
                $new_offset=$i*$navLimit;
                if ($new_offset==$navOffset) $ret.= sprintf("<span>%d</span>", $i+1);
                else $ret.= sprintf("<a href=\"%s&offset=%d\">%d</a>", $navActionStr, $new_offset, $i+1);
            }
            if ($endPage<floor(($navCount-1)/$navLimit)) {
                $ret.= sprintf("<a href=\"%s&offset=%d\">...</a>", $navActionStr, ($endPage+1)*$navLimit);
            }
            $ret.= "</div>";
        }
        return $ret;
    }

    ######################

    function SelectThumb() {
        define('NO_MENU', true);
        $GLOBALS['limit'] = 10;
        $GLOBALS['_SESSION']['ref'] = "?page=".$this->name."&do=selectthumb";

        function fmt_link($val) {
            return "<a href='".FILES_DIR."/".$val."' target=_blank><img src='images/icons/view.png' width=16 height=16 border=0 hspace=0></a>";
        }

        if (!isset($_GET['dir'])&&$_SESSION['dir'])
        $dir=$_SESSION['dir'];
        else {
            $dir=$_GET['dir'];
            $_SESSION['dir']=$dir;
        }

        echo "
		<script>
		var smallSetted = false;
		var bigSetted = false;
		function SelectPic(name) {
		    if (name!='') document.getElementById('pic').src = '".FILES_DIR."/'+name;
		    document.forms.pasteform.small_pic.value = name;
		    document.cookie = \"image_small = \"+name;
		}
		function ViewPic(name) {
		    if (!name) return;
		    document.getElementById('pic').src = '".FILES_DIR."/'+name;
		}
		function SmallPic(name) {
		    document.all.smallname.innerText = name;
		    document.all.smallname.title = name;
		    document.all.smalltd.background = '".FILES_DIR."/'+name;
		    document.all.smalltab.bgColor = 'blue';
		    smallSetted = true;
		    if (bigSetted) document.all.thumb.disabled = false;
		}
		function BigPic(name) {
		    document.forms.pasteform.big_pic.value = name;
		    document.cookie = \"image_big = \"+name;
		}
		function SetPic() {
		    if (opener) {
		        if (document.forms.pasteform.small_pic.value && document.forms.pasteform.big_pic.value) {
		            opener.InsertTemplate('', '<a href=\"".CFILES_DIR."/'+
		            document.forms.pasteform.big_pic.value+
		            '\" onclick=\"openImage(this.href); return false;\" target=_blank><img src=\"".CFILES_DIR."/'+
		            document.forms.pasteform.small_pic.value+
		            '\" border=0></a>');
		            SelectPic('');
		            BigPic('');
		            opener.focus();
		        } else if (document.forms.pasteform.small_pic.value) {
		            opener.InsertTemplate('', '<img class=border src=\"".CFILES_DIR."/'+
		            document.forms.pasteform.small_pic.value+
		            '\" border=0>');
		            SelectPic('');
		            BigPic('');
		            opener.focus();
		        } else alert('".$this->str('e_no_pic')."');
		    } else alert('No opener window');
		}
		function startProc(item) {
		    if (!item.file.value) {
		        alert('".$this->str('e_no_file')."');
		        return false;
		    }
		    item.sbm.disabled = true;
		    if (item.file.value) item.sbm.value = '".$this->str('wait')."';
		    return true;
		}
		function mkdir() {
		    dirname = document.getElementById('createfolder').value;
		    if (!dirname) {
		        alert('".$this->str('e_no_dirname')."');
		        return false;
		    }
		    location.href = '".$_SERVER["PHP_SELF"]."?page=".$this->name."&do=makedir&select=SelectThumb&newdir=' + dirname + '&dir=".$dir."&image=$image&aimage=$aimage';
		}
		</script>
		";

        cHeader($this->name);

        # Блок выбранных картинок
        echo "
		<table cellpadding=4 cellspacing=0 width=100% height=100%><tr><td valign=top nowrap>
		";

        function fmt_name($val) {
            global $row;
            $href = $row['dimensions'] != 'n/a' ? "SelectPic(\"".$row['name']."\")" : "";
            return "<a href='javascript:".$href."' title='".str('setpic', 'images')."'>".$row['name']."</a>";
        }

        function fmt_big($val) {
            global $row;
            $href = $row['dimensions'] != 'n/a' ? "BigPic(\"".$row['name']."\")" : "";
            return "<a href='javascript:".$href."' title='".str('set_big', 'images')."'><img src='".BASE."images/icons/big.gif' width=16 height=16 border=0></a>";
        }

        #Блок отображения файлов
        $this->ListFiles('selectthumb');

        # Блок отправки в редактор
        echo "
		<h5>".$this->str('paste_head')."</h5>
		<form name=pasteform>
		<table cellpadding=0 cellspacing=0>
		<tr><td>".$this->str('small_pic').":&nbsp;</td><td><input class=disabled type=text name=small_pic READONLY value=\"".$_COOKIE['image_small']."\"> <a href='javascript:ViewPic(document.forms.pasteform.small_pic.value)'><img src='".BASE."images/icons/green_arrow.gif' width=16 height=16 border=0 alt='".$this->str('setpic')."'></a></td></tr>
		<tr><td nowrap>".$this->str('big_pic')."<img align=absmiddle src='".BASE."images/icons/big.gif' width=16 height=16 border=0 hspace=4>:&nbsp;</td><td nowrap><input class=disabled type=text name=big_pic READONLY value=\"".$_COOKIE['image_big']."\"> <a href='javascript:ViewPic(document.forms.pasteform.big_pic.value)'><img src='".BASE."images/icons/green_arrow.gif' width=16 height=16 border=0 alt='".$this->str('setpic')."'></a></td></tr>
		</table>
		<br>
		<input type=button value='".$this->str('paste')."' onclick='SetPic()'>
		<input type=button value='".$this->str('reset')."' onClick=\"SelectPic(''); BigPic('');\">
		</form>
		";

        # Блок закачки
        echo "
		</td><td valign=top width=100%>
			<div align=left><b>".$this->str('picture')."</b></div>
			<br>
			<div align=left style='border: 1px solid silver; width: 100%; height: 90%; overflow-y: scroll; overflow-x: scroll;'>
			<img id=pic src=images/s.gif>
			</div>
		</td></tr>
		<tr><td colspan=2>
			<hr size=1>

			<h5>".$this->str('createfolder')."</h5>
			<input type=text name=createfolder size=20 id=createfolder>
			&nbsp;<button onclick='mkdir();' title='".$this->str('createfolder')."'>".$this->str('createfolder')."</button>

			<h5>".$this->str('upload')."</h5>
			<form method=post name=edit onsubmit='return startProc(this)' enctype='multipart/form-data'>
			<input type=file name=file size=20>
			<input type=submit name=sbm value='".$this->str('submit')."'>
			<input type=hidden name=do value=edit>
			<input type=hidden name=page value=".$this->name.">
			<input type=hidden name=dir value=".$dir.">";
        if ($GLOBALS['resamle_options']) echo "
				<br>".$this->str('resample').": <select name=resample><option value=0>".$this->str('no')." ".$this->GetArrayOption($GLOBALS['resamle_options'],'')."</select> <span class=note>".$this->str('_resample')."</span>";
        echo "
			</form>
		</td></tr>
		</table>
		";
        cFooter();
    }

    ######################

    function Select() {
        $dir = get('dir', '', 'g');
        if (!isset($_GET['dir']) && isset($_SESSION['dir']) && $_SESSION['dir']) {
            $dir = $_SESSION['dir'];
        } else {
            $_SESSION['dir'] = $dir;
        }

        $image   = get('image', 'image', 'g');
        $aimage  = get('aimage', '', 'g');

        $GLOBALS['_SESSION']['ref'] = "?page=".$this->name."&do=select&aimage=".$aimage."&image=".$image;

        $fldname = get('field', '', 'g');
        $GLOBALS['_SESSION']['fld'] = $fldname ? $fldname : (isset($GLOBALS['_SESSION']['fld']) ? $GLOBALS['_SESSION']['fld'] : '');

        $formname = get('formname', '', 'g');
        $GLOBALS['_SESSION']['editorform'] = $formname ? $formname : (isset($GLOBALS['_SESSION']['editorform']) ? $GLOBALS['_SESSION']['editorform'] : '');

        if (!$GLOBALS['_SESSION']['editorform']) $GLOBALS['_SESSION']['editorform'] = 'edit';

        #Блок отображения файлов
        $ret = $this->ListFiles('select');

        $err = get('err', '', 'g');
        if ($err) {
            $ret['error'] = $this->showError($err);
        }

        $resamle_options = array_keys($GLOBALS['resamle_options']);
        $options = "<option value='0'>".$this->str('no')."</option>";
        foreach ($resamle_options AS $k=>$v) {
            $options .= "<option value='".$v."'>".$v."</option>";
        }
        $ret['resamle_options'] = $options;

        $ret['filesdir'] = substr(FILES_URL, 0, -1);
        $ret['dirname'] = $_SERVER["PHP_SELF"]."?page=".$this->name."&do=makedir&newdir=' + dirname + '&dir=".$dir."&image=".(isset($_GET['image']) ? $_GET['image'] : '')."&aimage=".(isset($_GET['aimage']) ? $_GET['aimage'] : '');
        $ret['name'] = $this->name;
        $ret['dir'] = $dir;
        $ret['watermark_cfg'] = $GLOBALS['watermark_cfg'];
        $ret['fld'] = $GLOBALS['_SESSION']['fld'];
        $ret['image'] = $image;
        $ret['aimage'] = $aimage;
        $ret['whatdo'] = 'select';
        $ret['field'] = $fldname;


        $ret['err_no_file'] = $this->str('e_no_file');
        $ret['wait'] = $this->str('wait');
        $ret['err_no_dirname'] = $this->str('e_no_dirname');
        $ret['str_upload'] = $this->str('upload');
        $ret['str_watermark'] = $this->str('watermark');
        $ret['str_resample'] = $this->str('resample');
        $ret['str_note_resample'] = $this->str('_resample');
        $ret['str_paste'] = $this->str('paste');

        return $this->Parse($ret, $this->name.'_select.tmpl');
    }


    ######################

    function Edit() {
        global $watermark_cfg;

        $ref = $GLOBALS['_SESSION']['ref'];
        $FF       = get('FF',        '',    'gp');
        $offset   = get('offset',    0,     'gp');
        $limit    = get('limit',     10,    'gp');
        $dir      = get('dir',       '',    'gp');
        $sort     = get('sort',      'name','gp');
        $image    = get('image',     '',    'gp');
        $aimage   = get('aimage',    '',    'gp');
        $cur_image= get('cur_image', '',    'gp');
        $fsort    = get('file_sort', '',    'gp');
        $fldname  = get('field',     '',    'gp');
        $page     = get('page',      '',    'gp');
        $fld      = get('fld',     array(), 'gp');
        $whatdo   = get('whatdo',    '',    'gp');
        $formname = get('formname',  '',    'gp');

        // определим с какого скрипта вызов
        $whatpage = basename($_SERVER['PHP_SELF']);
        if ($whatpage == 'index.php') $whatpage = "";

        $fullreferer = NULL;
        $info = getimagesize($GLOBALS['_FILES']['file']['tmp_name']);
        $allowed_formats=array(
            1 => FILE_GIF,
            2 => FILE_JPEG,
            3 => FILE_PNG
        );

        # если файл не закачался, выдать сообщение, чтобы проверили размер файла
        if ($_FILES['file']['name'] && !$_FILES['file']['size']) {
            Header("Location: /admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo."&err=3");
        }

        # проверка на расширение
        if (!$this->Allowed($_POST['file'])) {
            Header("Location: /admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo."&err=4");
        }

        # Надо проверить, что это не CMYK
        if ($info['channels'] == 4) {
            Header("Location: /admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo."&err=2");
        }

        # Закачанный файл
        $quality = @array_shift(@mysql_fetch_row(@mysql_query('SELECT value FROM params WHERE name="resize_quality"')));
        if (!$quality) $quality = 85;
        if ($GLOBALS['_FILES']['file']['size']) {
            $fname = $this->GetUploadedFile(
                $GLOBALS['_FILES']['file']['tmp_name'],
                $GLOBALS['_FILES']['file']['name'],
                FILES_DIR.$dir,
                $GLOBALS['_POST']['resample'],
                $quality
            );
        }
        $fname = FILES_DIR.'/'.$dir.'/'.$fname;
        $info = getimagesize($fname);

        if (
            $_POST['watermark'] &&
            class_exists('RWatermark')&&
            $watermark_cfg&&
            in_array($info[2], array_keys($allowed_formats))&&
            is_file($watermark_cfg['watermark'])&&
            ($info[0]>$watermark_cfg['width']||$info[1]>$watermark_cfg['height'])
        )
        {
            $handle = new RWatermark($allowed_formats[$info[2]], $fname);
            $handle->SetPosition("CM");
            $handle->SetTransparentColor(255, 0, 255);
            $handle->SetTransparency($watermark_cfg['transparency']?$watermark_cfg['transparency']:60);
            $handle->AddWatermark(FILE_PNG, $watermark_cfg['watermark']);
            //$file=GetPureName($GLOBALS['_FILES']['file']['name']);

            if ($info[2]==1) {
                imagegif($handle->marked_image,$fname);
            } elseif ($info[2]==2) {
                imagejpeg($handle->marked_image,$fname,($watermark_cfg['quality']?$watermark_cfg['quality']:60));
            } elseif ($info[2]==3) {
                imagepng($handle->marked_image,$fname);
            }
            $handle->Destroy();
        }

        Header("Location: /admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo);
    }

    ######################
    function get_file_ext($name) {
        return strrpos($name, '.') !== false ? substr($name, strrpos($name, '.')) : '';
    }

    ########################
    function Allowed($filename) {
        return !in_array($this->get_file_ext($filename), $this->deny);
    }

    ######################
    function Delete() {
        $referer = isset($GLOBALS['HTTP_REFERER']) ? $GLOBALS['HTTP_REFERER'] : '/admin/?page='.$this->name.'&do='.$_GET['do'].'&image='.$_GET['image'].'&aimage='.$_GET['aimage'].'&cur_image='.$_GET['cur_image'].'&limit='.$_GET['limit'].'&FF='.$_GET['FF'].'&file_sort='.$_GET['file_sort'].'&sort='.$_GET['sort'];
        $files = $GLOBALS['_POST']['files'];
        $dir = $_POST['dir'];

        # удаление файлов
        if (sizeof($files)) {
            if (is_array($files)) {
                foreach($files as $key=>$val) {
                    $file = FILES_DIR.$dir.'/'.$val;
                    if (!is_dir($file)) {
                        @unlink($file);
                    } else {
                        if (!@rmdir($file)) {
                            $this->error('edeletedir', $referer);
                            exit;
                        }
                    }
                }
            }
        }
        Header("Location: /admin/?page=".$this->name."&dir=".$dir);
    }

    ######################

    //создание dir
    ######################
    function makedir() {
		$newdir   = isset($_POST['createfolder']) ? $_POST['createfolder'] : $_GET['newdir'];
		$select   = isset($_POST['select']) ? $_POST['select'] : $_GET['select'];

        $FF       = get('FF',        '',    'gp');
        $offset   = get('offset',    0,     'gp');
        $limit    = get('limit',     10,    'gp');
        $dir      = get('dir',       '',    'gp');
        $sort     = get('sort',      'name','gp');
        $image    = get('image',     '',    'gp');
        $aimage   = get('aimage',    '',    'gp');
        $cur_image= get('cur_image', '',    'gp');
        $fsort    = get('file_sort', '',    'gp');
        $fldname  = get('field',     '',    'gp');
        $page     = get('page',      '',    'gp');
        $fld      = get('fld',     array(), 'gp');
        $whatdo   = get('whatdo',    '',    'gp');
        $formname = get('formname',  '',    'gp');

        // определим с какого скрипта вызов
        $whatpage = basename($_SERVER['PHP_SELF']);
        if ($whatpage == 'index.php') $whatpage = "";

        if (preg_match ("/^[a-z0-9_][a-z0-9_]*$/", $newdir) == 0){
            Header("Location: /admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo."&err=1");
            exit;
        }

        if ($dir) $dir = $dir.'/';
        if (!@mkdir(FILES_DIR.$dir.$newdir, 0775)) {
            $this->error('emakedir', "/admin/".$whatpage."?FF=".$FF."&formname=".$formname."&offset=".$offset."&limit=".$limit."&dir=".$dir."&sort=".$sort."&image=".$image."&aimage=".$aimage."&cur_image=".$cur_image."&file_sort=".$fsort."&field=".$fldname."&page=".$page."&do=".$whatdo."&err=1");
            exit;
        }
        @chmod(FILES_DIR.$dir.$newdir, 0775);

        Header("Location: ".$_SERVER["PHP_SELF"]."?page=$this->name&do=$select&dir=".$dir."&image=$image&aimage=$aimage&fldname=".$fldname."&formname=".$formname);
        Exit;
    }
    ######################

    //обработка ошибок
    ######################
    function error($name='', $ref) {
        echo $this->str('error').$this->str($name)."<br> <a href=\"".$ref."\">".$this->str('back')."</a>";
        die("надо придумать отображение, фун-и error");
    }
    ######################

    function showError($err) {
        $toRet = "";
        switch ($err) {
            case 1:
                $toRet = "<p><span style='color:red;'>Не верно задано имя каталога</span> (имя каталога может состоять из маленьких букв латинского алфавита, цифр и символа _.)</p>";
                break;
            case 2:
                $toRet = "<p><span style='color:red;'>Это CMYK-файл, он не будет показываться в Internet Explorer! Сохраните изображение как RGB и загрузите снова.</p>";
                break;
            case 3:
                $upload_max_filesize = ini_get('upload_max_filesize');
                $post_max_size = ini_get('post_max_size');
                $max_size = $upload_max_filesize < $post_max_size ? $upload_max_filesize : $post_max_size;
                $toRet = "<p><span style='color:red;'>Файл не был загружен. Проверьте размер файла: максимально допустимый размер ".intval($max_size)."Мб</p>";
                break;
            case 4:
                $toRet = "<p><span style='color:red;'>Закачивать файлы данного типа запрещено</p>";
                break;
            case 5:
                $toRet = "<p><span style='color:red;'>".$this->str('e_not_writable')."</p>";
                break;
            case 6:
                $toRet = "<p><span style='color:red;'>".$this->str('e_upload')."</p>";
                break;
            default:
                $toRet = "<p><span style='color:red;'>Не верно задано имя каталога</span> (имя каталога может состоять из маленьких букв латинского алфавита, цифр и символа _.)</p>";
                break;
        }
        return $toRet;
    }

    #####################
    function getDirPath ($dirs=""){
        if (!$dirs) {
            return array('0'=>array('name'=>'files','tag'=>'span','href'=>''));
        } else {
            $dirs = explode("/", $dirs);
            $path = array('0'=>array('name'=>'files','tag'=>'a','href'=>'href="?page='.$this->name.'&dir="'));
            $count = count($dirs);
            $i = 1;
            $href = "";

            foreach ($dirs AS $k=>$val) {
                $href .= $val;
                $path[] = array(
                    'name'  => $val,
                    'tag'   => ($count!=$i) ? 'a' :'span',
                    'href'  => ($count!=$i) ? 'href="?page='.$this->name.'&dir='.$href.'"' :'',
                );
                $href .= "/";
                $i++;
            }
            return $path;
        }
    }

    #####################
    function GetUploadedFile($file, $file_name, $file_dir = 'files', $resample = 0, $quality = '85') {
    	if (!is_writable($file_dir)) {
            Header("Location: ".$_SERVER["PHP_SELF"]."?page=".$this->name."&dir=".$_POST['dir']."&err=5");
            exit;
    	}

    	$file_dir = preg_replace("~(/$)~", "", $file_dir);
    	$file_name = GetPureName($file_name);
    	if ($file) {
    		if (!$resample || 1) {
    			# find first unexisting filename
    			$counter = 0;
    			$dot = strrpos($file_name, '.');
    			$fbase = substr($file_name, 0, $dot);
    			$fext = substr($file_name, $dot);

    			while (is_file($file_dir.'/'.$file_name)) {
    				$counter++;
    				$file_name = $fbase."_".$counter.$fext;
    			}
    		}

    		if ($resample) {
                $size = $GLOBALS['resamle_options'][$resample];
    		}

    		if (!move_uploaded_file($file, $file_dir.'/'.$file_name)) {
                Header("Location: ".$_SERVER["PHP_SELF"]."?page=".$this->name."&dir=".$_POST['dir']."&err=6");
                exit;
    		    //or die(str('e_upload')." (".$file_dir."/".$file_name.")");
    		}
    		@chmod($file_dir.'/'.$file_name, 0775);

    		# resample image
    		if ($resample) return ResampleImage($file_dir, $file_name, $size, false, $quality);
    		else return $file_name;
    	}
    }

}

$GLOBALS['fmr'] = & Registry::get('TFmr');
?>