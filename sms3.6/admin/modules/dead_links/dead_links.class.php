<?php

class TDeadLinks extends TTable {
    var $name="dead_links";
    var $table="dead_links";
    var $table_settings="dead_links_settings";

    function Show() {

        function fmt_link($val) {
            return "<a href=\"$val\">$val</a>";
        }

        function fmt_linktype($val) {
            if(!$val) return "";
            return @$GLOBALS['str']['dead_links']['link_type_'.$val];
        }

        function fmt_checkdate($val) {
            if(!$val) return "не проверено";
            return $val;
        }

        cHeader($this->name);
        echo "<label style='float:left;margin-right:10px;'>Фильтр по типу:</label>
              <select id='type_filter'>
                <option value=''>Все</option>
                <option value='inner_link' ".(@$_GET['filter']=="l.type='inner_link'"?'selected':'').">Локальные ссылки</option>
                <option value='outer_link' ".(@$_GET['filter']=="l.type='outer_link'"?'selected':'').">Внешние ссылки</option>
                <option value='file' ".(@$_GET['filter']=="l.type='file'"?'selected':'').">Файлы</option>
              </select>
              <script type='text/javascript'>
              $(function(){
                $('#type_filter').change(function(){
                    window.location.href=window.location.href.replace(new RegExp(\"\\\\b\"+'&filter'+\"=[^&;]+[&;]?\",'gi'),'')+($(this).val()?('&filter=l.type=\''+$(this).val()+'\''):'');
                });
              });
              </script>";
        TTable::Show(array(
            'select'    => "l.link, l.type, s.title, CONCAT('<a href=\"',REPLACE(s.url_format,'{id}',l.item_id),'\">#',l.item_id,'</a>'), l.date",
            'id'        => "l.id",
            'titles'    => $this->str('','link,type,module,item_id,date'),
            'from'      => "{$this->table} AS `l` LEFT JOIN {$this->table_settings} AS `s` ON s.id=l.module",
            'proc_view' => array(
                0 => 'fmt_link,%s',
                1 => 'fmt_linktype,%s',
                4 => 'fmt_checkdate,%s',
            ),
            'noedit'    => true,
            'nocreate'  => true,
            'sort' => array(
                1 => 'l.link',
                2 => 'l.type',
                3 => 's.title',
                4 => 'l.item_id',
                5 => 'l.date',
            ),
            'allow_filter' => array(1),
        ));
        echo "<script type='text/javascript'>
        function checklinks() {
            $('#checklinkbox').show();
            window.frames['checklinkframe'].location='?page=dead_links&do=check';
        }
        function closechecklinkbox() {
            $('#checklinkbox').hide();
            window.frames['checklinkframe'].location='about:blank';
        }
        </script>
        <div id='checklinkbox' class='block' style='display:none;width:200px;height:100px;position:fixed;background-color:#fff;border:2px solid #E78F08;left:607px;top:250px;overflow:hidden;padding:10px;border-radius:2px;z-index:1000;'>
            <iframe src='about:blank' name='checklinkframe' style='width:100%;height:95px;border:none;overflow:hidden;'></iframe>
        </div>";
        echo "<div style='float:left;margin-right:15px;'>".ControlLink("?page={$this->name}&do=search",'icons/search.gif',$this->str('search_links'))."</div>";
        echo "<div style='float:left;margin-right:15px;'>".ControlLink("javascript:void(0);' onclick='checklinks();",'icons/type3.gif',$this->str('check_links'))."</div>";
        echo "<div style='float:left;margin-right:15px;'>".ControlLink("?page={$this->name}&do=settings",'icons/params.gif',$this->str('settings'))."</div>";
        echo "<div class='clear'></div>";
        cFooter();
    }

    function check() {
        set_time_limit(0);
        ini_set('memory_limit','64M');
        @ob_end_clean();
        @ini_set('zlib.output_compression',0);
        @ini_set('implicit_flush',1);
        ob_implicit_flush(1);
        header("Connection: close");
        header("Pragma: no-cache");
        header("X-Accel-Buffering: no");
        echo "<style>body { margin:0; } .wrap { padding:0; min-width:0; } .outer { margin:0; }</style>";
        echo "<script type='text/javascript'>
        function closeme() {
            stop();
            parent.closechecklinkbox();
        }
        function upd(pc) {
            document.getElementById('pc').innerHTML=pc;
        }
        </script>";
        echo "<p style='text-align:center;width:100%;'>Проверка ссылок...</p>";
        echo "<div style='width:200px;margin-top:10px;text-align:center;'><span style='font-family:Arial;font-size:32px;' id='pc'>0%</span></div>";
        echo "<div style='width:200px;margin-top:15px;text-align:center;'><a style='font-family:Arial;font-size:12px;' href='javascript:void(0);' onclick='closeme();'>Закрыть</a></div>";
        ob_flush();
        $totalrows=(int)$this->getValue("SELECT COUNT(*) FROM `{$this->table}`");
        $cnt=0;
        $pc="0";
        $q=mysql_query("SELECT `id`,`link` FROM `{$this->table}` ORDER BY ISNULL(`date`) DESC, `date` DESC");
        $update_ids=array();
        $delete_ids=array();
        if($q && mysql_num_rows($q)) while($row=mysql_fetch_assoc($q)) {
            if($this->checklink($row['link'])) $delete_ids[]=$row['id'];
            else $update_ids[]=$row['id'];
            $cnt++;
            if(count($update_ids)>100 || $cnt==mysql_num_rows($q)) {
                mysql_query("UPDATE `{$this->table}` SET `date`=CURRENT_TIMESTAMP() WHERE `id` IN (".implode(',',$update_ids).")");
                $update_ids=array();
            }
            elseif(count($delete_ids)>100 || $cnt==mysql_num_rows($q)) {
                mysql_query("DELETE FROM `{$this->table}` WHERE `id` IN (".implode(',',$delete_ids).")");
                $delete_ids=array();
            }
            $newpc=number_format($cnt/$totalrows*100,1,'.','');
            if($newpc!=$pc) {
                echo "<script>upd('$newpc%')</script>";
                ob_flush();
            }
            $pc=$newpc;
        }
        if(!empty($update_ids)) mysql_query("UPDATE `{$this->table}` SET `date`=CURRENT_TIMESTAMP() WHERE `id` IN (".implode(',',$update_ids).")");
        if(!empty($delete_ids)) mysql_query("DELETE FROM `{$this->table}` WHERE `id` IN (".implode(',',$delete_ids).")");
        unset($update_ids,$delete_ids);
        echo "<script>upd('Готово')</script>";
        ob_end_flush();
        exit;
    }

    function search() {
        cHeader($this->name);
        echo "<script type='text/javascript'>
        function setframesrc() {
            var src='/admin?page=dead_links&do=searchlinks';
            $('input[name=modules\[\]]:checked').each(function(){ src+='&id[]='+$(this).val(); });
            window.frames['process_frame'].location=src;
        }
        </script>";
        echo "<iframe name='process_frame' src='about:blank' scrolling='yes' frameborder='0' marginheight='0' marginwidth='0' border='0' style='display:block;width:100%;height:400px;'></iframe>";
        echo "<fieldset style='margin-top:10px;'><legend>".$this->str('search_options')."</legend>";
        $modules=$this->getRows("SELECT `id`,`title` FROM `{$this->table_settings}`",true);
        foreach($modules as $k=>$v) echo "<input type='checkbox' name='modules[]' value='$k' checked /><label style='display:inline;'>$v</label><br />\n";
        echo "<br /><a href='javascript:void(0);' onclick='setframesrc();'; return false' class='button'><span>Начать поиск</span></a>";
        echo "</fieldset>";
        cFooter();
    }

    function searchlinks() {
        set_time_limit(0);
        ob_start();
        echo "<style>body { margin-left:-200px; }</style>";
        if(!isset($_GET['id']) || !$_GET['id'] || !is_array($_GET['id'])) exit;
        $settings=$this->getRows("SELECT * FROM `{$this->table_settings}` WHERE `id` IN (".implode(',',$_GET['id']).")",true);
        mysql_query("TRUNCATE TABLE `{$this->table}`");
        foreach($settings as $module_id=>$module) {
            echo "<h6>Поиск в модуле \"{$module['title']}\"</h6>";
            ob_flush();
            $struct=$this->getRows("DESCRIBE `{$module['table']}`",false);
            if(!$struct) { echo "<p style='color:red;'><strong>Таблица не найдена! Проверьте настройки модуля!</strong></p>"; continue; }
            echo "<table><tr><th>Объект</th><th>Ссылка</th></tr>";
            ob_flush();
            $links=array();
            foreach($struct as $column) {
                if(strpos($column['Type'],'text')===false && strpos($column['Type'],'varchar')===false) continue;
                $q=mysql_query("SELECT `id`,`{$column['Field']}` FROM `{$module['table']}` WHERE `{$column['Field']}` LIKE '%http://%'");
                if($q && mysql_num_rows($q)) while($row=mysql_fetch_row($q)) {
                    preg_match_all('/<a[\s]+[^>]*href\s*=\s*([\"\']+)([^>]+?)(\1|>)/i',$row[1],$matches);
                    foreach($matches[2] as $link) {
                        $link=trim(preg_replace("/(\.\.\/)*(\.\/)*/i","",$link),'/');
                        if(substr($link,0,1)=='#' || substr($link,0,7)=='mailto:') continue;
                        preg_match("/((http?|https?|ftp)\:\/\/)?/i",$link,$protocol);
                        if(empty($protocol[0])) $link="http://{$_SERVER['HTTP_HOST']}/$link";
                        $type="";
                        if(strpos($link,'files/')!==false) $type="file";
                        elseif(strpos($link,"http://")===0 && strpos($link,"http://{$_SERVER['HTTP_HOST']}")===false) $type="outer_link";
                        elseif(strpos($link,"http://")===false || strpos($link,"http://{$_SERVER['HTTP_HOST']}")===0) $type="inner_link";
                        $links[]=array('link'=>$link,'item_id'=>$row[0],'type'=>$type);
                        echo "<tr><td><a target='_blank' href='".str_replace('{id}',$row[0],$module['url_format'])."'>#{$row[0]}</a></td><td><a href='$link'>$link</a></td></tr>";
                        ob_flush();
                    }
                    unset($matches,$itemlinks);
                }
            }
            if(!empty($links)) {
                $insertvalues=array(); foreach($links as $link) $insertvalues[]="('{$link['link']}','{$module_id}','{$link['item_id']}','{$link['type']}')";
                mysql_query("INSERT IGNORE INTO `{$this->table}` (`link`,`module`,`item_id`,`type`) VALUES ".implode(', ',$insertvalues));
                unset($insertvalues);
            }
            echo "</table>";
            echo "<p>Всего найдено: ".count($links)."</p>";
            ob_flush();
        }
        ob_end_flush();
        exit;
    }

    function checklink($url) {
        $handle=curl_init($url);
        curl_setopt($handle,CURLOPT_NOBODY,true);
        curl_setopt($handle,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($handle,CURLOPT_CONNECTTIMEOUT,3);
        curl_setopt($handle,CURLOPT_TIMEOUT,7);
        curl_exec($handle);
        $ret=(curl_getinfo($handle,CURLINFO_HTTP_CODE)<400?true:false);
        curl_close($handle);
        return $ret;
    }

    function settings() {
        cHeader($this->name,'settings');
        TTable::Show(array(
            'select'    => "title",
            'titles'    => $this->str('','module_title'),
            'from'      => "{$this->table_settings}",
            'proc_view' => array(),
            'edit'        => 0,
            'edit_method' => 'editsettings',
        ));
        cFooter();
    }

    function editsettings() {
        cHeader($this->name,'editsettings');
        $id=$GLOBALS['_GET']['id'];
        echo "
        <script src='ce.js'></script>
        <script src='content.js'></script>
        <script>".GetTemplFunc()."</script>
        ";
        if($id) $row=$this->getRow("SELECT * FROM `{$this->table_settings}` WHERE `id`='$id'");
        $form=$this->form->AddForm($this,$id,'savesettings');
        $this->form->addHiddenFields(array('ref'=>'?page=dead_links&do=settings','type'=>'0','do'=>'savesettings'));
        $tab_main_html=forms_get_dev_fields(@$row,$this->name)."
        <label class='float'>".$this->str('title')."<span>*</span>:</label><input class='text' type='text' name='fld[title]' value='".@$row['title']."' maxlength='255' /><br />
        <label class='float'>".$this->str('table')."<span>*</span>:</label><input class='text' type='text' name='fld[table]' value='".@$row['table']."' maxlength='255' /><br />
        <label class='float'>".$this->str('url_format')."<span>*</span>:</label><input class='text' type='text' name='fld[url_format]' value='".@$row['url_format']."' maxlength='255' /><br />";
        $tab_main=$this->form->AddTab($tab_main_html,'main',$this->str('main'));
        echo $this->form->draw();
        cFooter();
    }

    function savesettings() {
        if($_POST['id']) {
            mysql_query("UPDATE `{$this->table_settings}` SET
                        `title`='".mysql_real_escape_string($_POST['fld']['title'])."',
                        `table`='".mysql_real_escape_string($_POST['fld']['table'])."',
                        `url_format`='".mysql_real_escape_string($_POST['fld']['url_format'])."'
                         WHERE `id`='".(int)$_POST['id']."'");
        }
        else {
            mysql_query("INSERT INTO `{$this->table_settings}` (`title`,`table`,`url_format`) VALUES
                         ('".mysql_real_escape_string($_POST['fld']['title'])."',
                          '".mysql_real_escape_string($_POST['fld']['table'])."',
                          '".mysql_real_escape_string($_POST['fld']['url_format'])."',)");
        }
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit;
    }

}
$dead_links=new TDeadLinks;