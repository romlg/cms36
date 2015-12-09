<?php
require_once(inc('modules/clients/clients_base.php'));

class TClients extends TClients_base {

    //-----------------------------------------------------------
    function TClients(){
        TClients_base::TClients_base();
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
        'trusted'		=> array('Доверенный',			'Trusted'),
        'count_objects'	=> array('Кол-во объявлений',	'Count'),
        'free'		    => array('Бесплатный',			'Free'),
        ));
    }
    //-----------------------------------------------------------
    function table_get_last_access($value) {
        if (empty($value)) return '';
        return date("d.m.Y H:i:s", $value);
    }
    function table_get_reg_date($value) {
        return date("d.m.Y", strtotime($value));
    }

    # Отображение списка клиентов. #
    function Show() {
        if (!empty($_POST))
        {
            $actions = get('actions', '', 'p');
            if ($actions)
            {
                return $this->$actions();
            }
        }
        require_once(core('ajax_table'));
        $ret['table'] = ajax_table(array(
        'columns'	=> array(
        array(
        'select'	=> 'cl.id',
        'display'	=> 'id',
        'type'		=> 'checkbox',
        'width'		=> '1px',
        ),
        array(
        'select'	=> 'login',
        'display'	=> 'login',
        'flags'		=> FLAG_SEARCH | FLAG_SORT,
        'width'		=> '1px',
        ),
        array(
        'select'	=> 'cl.id',
        'display'	=> 'id',
        'flags'		=> FLAG_SEARCH | FLAG_SORT,
        'width'		=> '1px',
        ),
        array(
        'select'	=> 'fio',
        'as'		=> 'name',
        'display'	=> 'fullname',
        'flags'		=> FLAG_SEARCH | FLAG_SORT,
        'width'		=> '100%',
        ),
        array(
        'select'	=> 'last_visited',
        'type'		=> 'last_access',
        'display'	=> 'last_access',
        'flags'  	=> FLAG_SORT,
        ),
        array(
        'select'	=> 'reg_date',
        'type'		=> 'reg_date',
        'display'	=> 'reg_date',
        'flags'   	=> FLAG_SORT,
        ),
        array(
        'select'	=> 'trusted',
        'display'	=> 'trusted',
        'type'		=> 'visible',
        'flags'   	=> FLAG_SORT,
        ),
        array(
        'select'	=> 'COUNT(o.id)',
        'as'		=> 'sum',
        'display'	=> 'count_objects',
        'flags'   	=> FLAG_SORT,
        ),
        array(
        'select'	=> 'cl.balance',
        'display'	=> 'balance',
        'flags'   	=> FLAG_SORT,
        ),
        ),
        'from'		=> $this->table.' as cl LEFT JOIN objects AS o ON o.client_id=cl.id',
        'where'     => 'cl.deleted=0',
        'params'	=> array('page' => $this->name, 'do' => 'show'),
        'orderby'	=> 'last_visited DESC',
        'groupby'	=> 'cl.id',
        'click'		=> 'ID=cb.value',
        'dblclick'	=> 'editItem(id)',
        ), $this);
        # сохранение в сессию
        session_start();
        $_SESSION['client_selection'] = $GLOBALS['where'];
        session_write_close();
        $ret['thisname'] = $this->name;
        return $this->Parse($ret, $this->name.'.tmpl');
    }

    //-----------------------------------------------------------
    function Delete() {
        $id = $_POST['id'];

        $sql = 'UPDATE objects SET visible=-1 WHERE client_id IN ('.implode(',', $id).')';
        sql_query($sql);
        $err = sql_getError();
        if (!empty($err)) return '<script>alert("Ошибка: '.mysql_escape_string($err).'"); if (window.parent.navigate) window.parent.navigate(); else window.parent.location.reload();</script>';

        $sql = 'UPDATE '.$this->table.' SET deleted=1, auth=0 WHERE id IN ('.implode(',', $id).')';
        sql_query($sql);
        $err = sql_getError();
        if (!empty($err)) return '<script>alert("Ошибка: '.mysql_escape_string($err).'"); if (window.parent.navigate) window.parent.navigate(); else window.parent.location.reload();</script>';

        return '<script>if (window.parent.navigate) window.parent.navigate(); else window.parent.location.reload();</script>';

    }

    //-----------------------------------------------------------
    /* страницы */
    function EditSystem()	{
        global $str,$cfg,$lang;
        $actions[$this->name]="";
        $client_id = $this->GetClientID();
        $row = sql_getRow("SELECT * FROM ".$this->table." WHERE id=".$client_id);

        $groups = sql_getRows("SELECT id,name FROM auth_groups");
        foreach ($groups as $k=>$v) {
            $row['groups'][$v['id']] = $v['name'];
        }

        $row['selected_group'] = sql_getValue("SELECT group_id FROM auth_users_groups WHERE user_id=".$client_id);

        $row['trusted_checked'] = (@$row['trusted']==1) ? 'checked=checked' : '';
        $row['free_checked'] = (@$row['free']==1) ? 'checked=checked' : '';
        $row['auth_checked'] = (@$row['auth']==1) ? 'checked=checked' : '';

        $row['thisname'] = $this->name;

        $this->AddStrings($row);
        return $this->Parse($row, 'editsystem.tmpl');
    }

    //-----------------------------------------------------------

    function EditClient()	{
        global $str,$cfg,$lang;
        $client_id = $this->GetClientID();
        $row = sql_getRow("SELECT * FROM ".$this->table." WHERE id=".$client_id);
        foreach ($str[get_class_name($this)] as $key => $value)
        $row['STR_'.strtoupper($key)] = $value[$lang];
        $row['thisname'] = $this->name;
        return $this->Parse($row, 'editclient.tmpl');
    }

    //-----------------------------------------------------------

    function ShowClientInfo() {

        if((int)$this->GetClientID() > 0)
        {
            global $str,$cfg,$lang;
            $actions[$this->name]="";
            $client_id = $this->GetClientID();
            $row = sql_getRow("SELECT * FROM ".$this->table." WHERE id=".$client_id);
            $row['trusted'] = $row['trusted'] ? '+' : '-';
            $row['free'] = $row['free'] ? '+' : '-';
            $row['auth'] = $row['auth'] ? '+' : '-';

            if ($row['balance'] > 0) {
            	$row['balance_style'] = 'style="color: green"';
            } else {
            	$row['balance_style'] = 'style="color: red"';
            }
            //$row['group'] = sql_getValue('SELECT g.name FROM auth_groups AS g LEFT JOIN auth_users_groups AS aug ON aug.group_id=g.id AND aug.user_id='.$client_id);
            $row['thisname'] = $this->name;
            $this->AddStrings($row);
            return $this->Parse($row, 'showclientinfo.tmpl');
        }
        else {
            global $str,$cfg,$lang;
            $this->AddStrings($row);
            $row['thisname'] = $this->name;
            return $this->Parse($row, 'add.tmpl');
        }
    }

    //-----------------------------------------------------------

    function EditObjects()	{
        global $str,$cfg,$lang;
        $client_id = $this->GetClientID();
        $sql = "SELECT * FROM ".$this->table." WHERE id=".$client_id;
        $row = sql_getRow($sql);
        foreach ($str[get_class_name($this)] as $key => $value)
        $row['STR_'.strtoupper($key)] = $value[$lang];
        $row['thisname'] = $this->name;
        return $this->Parse($row, 'editobjects.tmpl');
    }

    //-----------------------------------------------------------
    function showBalance() {
        $client_id = (int)$_GET['client_id'];

        $comment1 = sql_getValue('SELECT value FROM strings WHERE module="site" AND name="billing_comment1"');
        $comment2 = sql_getValue('SELECT value FROM strings WHERE module="site" AND name="billing_comment2"');
        $comment3 = sql_getValue('SELECT value FROM strings WHERE module="site" AND name="billing_comment3"');

        $ret['list'] = sql_getRows("SELECT * FROM billing WHERE client_id=".$client_id." AND DATE_ADD(date, INTERVAL 3 MONTH) > NOW() ORDER BY date DESC");
	    foreach ($ret['list'] as $key=>$val) {
	        $admin_comment = "";
	        if (($pos = strpos($val['comment'], '|')) !== false) $admin_comment = substr($val['comment'], $pos+1);
	        if ($val['sum'] > 0) $ret['list'][$key]['comment'] = $comment1;
	        else {
	           if (substr($val['comment'], 0, 3) == 'By ') $ret['list'][$key]['comment'] = $comment2;
	           elseif (!$val['bill_id']) {
	               $count = count(explode(',',$val['comment']));
	               $ret['list'][$key]['comment'] = $comment3.' '.$count.' '.$this->getWord($count, 'объявления', 'объявлений', 'объявлений');
	           }
	        }
	        if ($admin_comment) $ret['list'][$key]['comment'] .= ' | '.$admin_comment;
	        $ret['list'][$key]['sum'] = number_format($val['sum'], 2, '.', ' ');
	        if ($ret['list'][$key]['sum'] < 0) $ret['list'][$key]['sum'] = str_replace('-', '- ', $ret['list'][$key]['sum']);
	        $ret['list'][$key]['balance'] = number_format($val['balance'], 2, '.', ' ');
	    }

        $ret['balance'] = (int)sql_getValue("SELECT SUM(sum) FROM billing WHERE client_id=".$client_id);
        $sql = 'UPDATE auth_users SET balance='.$ret['balance'].' WHERE id='.$client_id;
        sql_query($sql);

        $ret['count_objects'] = (int)sql_getValue('SELECT COUNT(*) FROM objects AS o, auth_users AS a WHERE a.id=o.client_id AND o.client_id='.$client_id.' AND o.visible > 0 AND a.stop <> 1 AND (o.confirm > 0 OR a.trusted > 0) AND (o.expire_time > "'.date('Y-m-d H:i:s').'" OR a.free > 0)');
        $ret['sum_per_day'] = (double)sql_getValue('SELECT g.price FROM auth_groups AS g, auth_users_groups AS aug WHERE aug.group_id=g.id AND aug.user_id='.$client_id);
        $ret['sum'] = $ret['sum_per_day']*$ret['count_objects'];

        $tmpl = '
	    <link href="/css/style.css" rel="stylesheet" type="text/css" />
        <link href="/css/fonts.css" rel="stylesheet" type="text/css" />
	    <!--[if lte IE 6]><link href="/css/style_ie6.css" rel="stylesheet" type="text/css" ><![endif]-->
	    <style>
	    td, th {font-size: 80%}
	    </style>
	    <script>
	    var ID = 0;
        var thisname = "'.$this->name.'";
        var A'.$this->name.'0 = new Array(0,0,0,0,1,0);
        window.parent.elemActions(thisname, ID==0?0:1);
        function AddMoney(){
            window.open("act.php?page='.$this->name.'&do=editaddMoney&client_id='.$client_id.'", "addmoney", "width=650, height=600, status=no;");
        }
	    </script>
	    '.Parse($ret, './modules/clients/balance.tmpl');

        $tmpl_1 = substr($tmpl, 0, strpos($tmpl, '<ul class="buttons"'));
        $tmpl_2 = substr($tmpl, strpos($tmpl, '</ul>', strpos($tmpl, '<ul class="buttons"')));
        $tmpl = $tmpl_1.$tmpl_2;

        return $tmpl;
    }
    function getWord($num, $word1, $word2, $word3) {
        $num = $num % 100;
        if ($num>20 || $num<5)
            switch ($num%10) {
                case 1: return $word1;
                case 2: case 3: case 4: return $word2;
            }
        return $word3;
    }
    //-----------------------------------------------------------
    // Форма для зачисления/списания средств и само зачисление/списание
    function editAddMoney(){
        if (isset($_POST['sum']) && doubleval($_POST['sum']) == 0) {
            echo "<script>alert('Введите сумму!');</script>";
            die();
        }
        if (isset($_POST['sum']) && doubleval($_POST['sum']) != 0 && (int)$_POST['client_id'] > 0) {
            sql_query('BEGIN');

            $client_id = (int)$_POST['client_id'];
            $sum = str_replace(",", ".", doubleval($_POST['sum']));
            $admin = $_SESSION['user']['login'];
            $date = date('Y-m-d H:i:s');
            $admin_comment = mysql_escape_string(strip_tags($_POST['admin_comment']));

            $balance = sql_getValue('SELECT SUM(sum) FROM billing WHERE client_id='.$client_id);
            $balance = str_replace(",", ".", doubleval($balance));

            $sql = 'INSERT INTO billing (client_id, sum, date, comment, balance) VALUES (
    	        "'.$client_id.'",
    	        "'.$sum.'",
    	        "'.$date.'",
    	        "By '.$admin.'|'.$admin_comment.'",
    	        "'.($balance+$sum).'"
    	        )';
            sql_query($sql);
            $err = sql_getError();
            if (!empty($err)) {
                sql_query('ROLLBACK');
                return $this->Error($err);
            }

            $sql = 'UPDATE auth_users SET balance="'.($balance+$sum).'" WHERE id='.$client_id;
            sql_query($sql);
            $err = sql_getError();
            if (!empty($err)) {
                sql_query('ROLLBACK');
                return $this->Error($err);
            }

            sql_query('COMMIT');

            $tmpl = 'billing_'.($sum > 0 ? 'add' : 'rem').'_money_by_admin';

            $data = array(
            'date'  => $date,
            'fio'   => sql_getValue('SELECT fio FROM auth_users WHERE id='.$client_id),
            'sum'   => $sum > 0 ? $sum : -$sum,
            'admin_comment' => $admin_comment
            );
            $dir = getcwd();
            chdir('../modules/');
            $r = sendEmail(
            sql_getValue('SELECT login FROM auth_users WHERE id='.$client_id),
            sql_getValue('SELECT value FROM strings WHERE name="robot_email"'),
            sql_getValue('SELECT value FROM strings WHERE name="'.$tmpl.'_subj"'),
            sql_getValue('SELECT value FROM strings WHERE name="'.$tmpl.'_mail"'),
            $data);
            chdir($dir);

            return "<script>
                alert('".$this->str('saved')."');
                try{
                    window.parent.top.opener.location.reload();
                    window.parent.top.close();
                }catch(e){}
            </script>";
        }

        $client_id = (int)get('client_id', 0, 'gp');
        $row['client_id'] = $client_id;

        $client = sql_getRow('SELECT * FROM '.$this->table.' WHERE id='.$client_id);

        $actions = array('add', 'rem');
        foreach ($actions as $key=>$action) {
            $template = sql_getValue('SELECT value FROM strings WHERE name="billing_'.$action.'_money_by_admin_mail"');
            $template = $this->parseTemplate($template, array('fio' => $client['fio'], 'date' => time(), 'sum' => '{$sum}', 'admin_comment' => '<i><span style="color: silver">Ваш комментарий будет находиться здесь...</span></i>'), 'abc'.$key);
            $row['template'.($key+1)] = $template;
        }

        $this->AddStrings($row);
        return $this->Parse($row, 'editaddmoney.tmpl');
    }

    //-----------------------------------------------------------
    function delObjects(){
        $clients_ids = $_POST['id']; if (!$clients_ids) return "<script>alert('Нет выбранных клиентов!');</script>";
        $objects_ids = sql_getColumn('SELECT id FROM objects WHERE client_id IN ("'.join('", "', $clients_ids).'")');
        if (!$objects_ids) return "<script>alert('Объекты для выбранных клиентов не найдены!');</script>";

        $sql = "DELETE FROM objects WHERE id IN ('".join("', '", $objects_ids)."')";
        sql_query($sql);
        $err = sql_getError();
        if (!empty($err)) return "<script>alert('Ошибка: ".e($err)."');</script>";

        require_once 'modules/objects_func.php';
        objects_deleteImages($objects_ids);

        touch_cache('objects');
        return "<script>alert('Успешно удалено!'); window.parent.location.reload();</script>";
    }

    //-----------------------------------------------------------
    /**
    * Парсинг шаблона, хранящегося в строке
    *
    * @param string $template
    * @param array $fld
    * @param string $name
    * @return mixed
    */
    function parseTemplate($template, $fld, $name) {
        // Делаем временный файл, чтобы потом пропарсить
        $filename = '../cache/tmp_adm/'.session_id().'/'.$name.'.html';
        if (($fp = fopen($filename, 'w'))===false) return false;
        fwrite($fp, $template);
        fclose($fp);
        return Parse($fld, '../'.$filename);
    }

}
$GLOBALS['clients'] = & Registry::get('TClients');
?>