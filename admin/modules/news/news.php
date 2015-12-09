<?php
class TNews extends TTable {
    var $name = 'news';
    var $table = 'publications';
    var $colums_default = '';
    var $elements = array('elem_gallery');
    var $pid = null;

    function TNews() {
        global $actions, $str;
        $this->pid = $this->get_news_pid();

        TTable::TTable();

        $actions[$this->name] = array(
            'create' => $actions['table']['create'],
            'edit' => $actions['table']['edit'],
            'delete' => $actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
          'save' => array(
            'title' => array(
              'ru' => '���������',
              'en' => 'Save',
            ),
            'onclick' => 'document.forms[\'editform\'].submit(); return false;',
            'img' => 'icons.save.gif',
            'display' => 'block',
            'show_title' => true,
          ),
          'cancel' => array(
              'title' => array(
                  'ru' => '������',
                  'en' => 'Cancel',
              ),
          'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
          'img' => 'icons.close.gif',
          'display' => 'block',
          'show_title' => true,
          )
        );

        if ((int)$_GET['id']) {
           $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
           $temp = "��������";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('�������', 'News',),
            'title_editform' => array('�������: '. $temp, 'news : '. $temp,),
            'name' => array('���������', 'Title',),
            'date' => array('����','Date',),
            'notice' => array('�����','Announcement'),
            'text' => array('�����','Text',),
            'visible' => array('��������','Visible',),
            'image' => array('�����������','Preview',),
            'saved' => array(
                '������ ������� ���������','Data has been saved successfully',
            ),
        ));
    }


    function Show() {
       // define ('DEV_MODE', true);
        if(!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if($actions) return $this->$actions();
        }

        // ���������� �������
        require_once(core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'n.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'n.name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                    'type' => 'text',
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(n.date)',
                    'display' => 'date',
                    'flags' => FLAG_FILTER | FLAG_SORT,
                    'type' => 'date',
                    'filter_type' => 'date',
                    'filter_display' => '����������� �� ����'
                ),
                array(
                    'select' => 'n.notice',
                    'display' => 'text',
                ),
                array(
                    'select' => 'n.text',
                    'display' => 'text',
                ),
                array(
                    'select' => 'n.visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT,
                ),
            ),
            'from' => $this->table . " AS n",
            'where' => ('n.pid='.$this->pid),
            'orderby' => 'n.date DESC',
            'params' => array('page' => $this->name,'do' => 'show'),
            'click' => 'ID=cb.value',
        ), $this);

        $data['table'] .= "<script type='text/javascript'>
        $('ul.navPanel').find('a:first').attr('onclick','').click(function(){
            $('.createbox').show().find('input[type=text]').focus();
        });
        </script>
        <style type='text/css'>
        .createbox {
            width:307px;
            height:100px;
            background-color:#fff;
            border:2px solid #FAAE3E;
            position:fixed;
            left:550px;
            top:400px;
            display:none;
            padding:10px;
            box-shadow:5px 5px 5px rgba(0,0,0,0.5);
        }
            .createbox .close {
            text-decoration:none;
                position:relative;
                top:-7px;
                left:304px;
                font-size:16px;
                color:#f00;
            }
        </style>
        <div class='createbox'>
            <a href='javascript:void(0);' onclick='$(\".createbox\").hide();' class='close'>X</a>
            <form action='' method='post'>
                <input type='hidden' name='page' value='{$this->name}' />
                <input type='hidden' name='do' value='editCreate' />
                <!--input type='hidden' name='ref' value='/admin/editor.php?page={$this->name}' /-->
                <label>������� �������� ����� ����������:</label>
                <input type='text' class='text' name='fld[name]' id='fld_name' value='' />
                <a href='javascript:void(0);' onclick='if($(this).parent().find(\"#fld_name\").val()) $(this).parent().submit(); else alert(\"�� �� ����� �������� ����������\");' class='button' style='position:relative;left:90px;top:5px;'><span>�������</span></a>
            </form>
        </div>";

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

    function editCreate() {
        $name = str_replace("&", "=+=+=+=", $_POST['fld']['name']);
        $name = htmlspecialchars($name);
        $name = str_replace("=+=+=+=", "&", $name);
        $id = sql_insert($this->table, array('name' => $name, 'date' => date('Y-m-d H:i:s')));
        # ��������� src
        $ret = sql_query("UPDATE " . $this->table . " SET pid=" . $this->pid . " WHERE id=" . $id);
        if (!$ret) {
            die('"UPDATE error: ' . addslashes(sql_getError()) . '"');
        }
        if (is_int($id)) {
            HeaderExit("/admin/editor.php?page={$this->name}&id=" . $id);
        }
        else {
            die($id);
        }
    }

    function get_news_pid() { // ����� ��� ��� �� ��������
        $temp = sql_getValue("SELECT id FROM tree WHERE pid=100 and page='news'");
        return $temp;
    }
}
$GLOBALS['news'] = & Registry::get('TNews');
?>