<?php

class TAdmins extends TTable
{

    var $name = 'admins';
    var $table = 'admins';
    var $domain_selector = false;
    var $selector = false;
    var $aes_key = "YeM6NverUfAG2LXGhJ3hjP7pAvzCcKTn";

    ########################

    function TAdmins() {
        global $str, $actions;

        TTable::TTable();

        if (!empty($_GET['id'])) {
            $temp = sql_getValue("SELECT login FROM " . $this->table . " WHERE id=" . $_GET['id']);
        } else {
            $temp = "Новый пользователь";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                'Администраторы',
                'Users',
            ),
            'title_editform' => array(
                "Администраторы : " . $temp,
                'Users : ' . $temp,
            ),
            'login' => array(
                'Логин',
                'Login',
            ),
            'fullname' => array(
                'Имя',
                'Name',
            ),
            'email' => array(
                'E-mail',
                'E-mail',
            ),
            'phone' => array(
                'Телефон',
                'Phone',
            ),
            'cellphone' => array(
                'Сотовый Телефон',
                'Cell Phone',
            ),
            'description' => array(
                'Описание',
                'Description',
            ),
            'pwd' => array(
                'Установить пароль',
                'Set Password',
            ),
            'pwd2' => array(
                'Пароль еще раз',
                'Password Again',
            ),
            'group' => array(
                'Группа',
                'Group',
            ),
            'department' => array(
                'Раздел',
                'Department',
            ),
            'saved' => array(
                'Данные успешно сохранены',
                'Data has been saved successfully'
            ),
            'error_login' => array(
                'Пользователь с таким логиным уже зарегистрирован',
                'User with such loginym is already registered',
            ),
            'subst' => array(
                'Замена',
                'Substitute',
            ),
            '_subst' => array(
                'Используйте клавишу CTRL для выделения нескольких записей',
                'Use CTRL key to select multiple records',
            ),
        ));

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
            'apply' => array(
                'title' => array(
                    'ru' => 'Сохранить',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'apply\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'save_close' => array(
                'title' => array(
                    'ru' => 'Сохранить и закрыть',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'save\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => 'Отмена',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

    }

    ########################

    function Show() {
        if (!empty($_POST)) {
            $action = get('actions', '', 'p');
            if ($action) {
                if ($this->Allow($action)) {
                    return $this->$action();
                } else {
                    return $this->alert_method_not_allowed();
                }
            }
        }

        require_once (core('list_table'));

        $columns = array(
            array(
                'select' => 'u.id',
                'display' => 'id',
                'type' => 'checkbox',
            ),
            array(
                'select' => 'u.login',
                'display' => 'login',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'g.name',
                'display' => 'group',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'u.fullname',
                'display' => 'fullname',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'u.email',
                'display' => 'email',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'u.phone',
                'display' => 'phone',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'u.description',
                'display' => 'description',
                'flags' => FLAG_SEARCH,
            ),
        );

        $admins_columns = sql_getRows("SHOW COLUMNS FROM `admins`", true);
        if (isset($admins_columns['department_id'])) {
            $columns[] = array(
                'select' => '(SELECT name FROM tree WHERE id=u.department_id)',
                'as' => 'department',
                'display' => 'department',
                'flags' => FLAG_SEARCH,
            );
        }

        $ret['table'] = list_table(array(
            'columns' => $columns,
            'from' => "$this->table as u LEFT JOIN admin_groups as g ON g.id=u.group_id",
            'params' => array('page' => $this->name, 'do' => 'show'),
            'orderby' => 'u.group_id, u.id',
            'click' => 'ID=cb.value',
            'dblclick' => 'editItem(id)',
        ), $this);

        $ret['thisname'] = $this->name;
        return $this->Parse($ret, LIST_TEMPLATE);
    }

    ########################

    function Edit() {

        $table = get('page', 'admins', 'p');
        $resultC = mysql_query("SHOW COLUMNS FROM $table like 'pwd'");
        $rowC = mysql_fetch_row($resultC);
        preg_match("/\d+/i", $rowC[1], $res);
        if ($res[0] == 32) {
            //старый код
            $id = $this->Commit(array('login'));
        } else {
            //новый код с дополнительным шифрованием
            $pwd = $fld['pwd'];
            unset($fld['pwd']);
            foreach ($fld As $key => $field) {
                $upd .= ($field) ? $key . "='" . mysql_escape_string($field) . "', " : "";
                $isrt_k .= ($field) ? $key . ", " : "";
                $isrt_v .= ($field) ? "'" . mysql_escape_string($field) . "', " : "";
            }
            $aes_key = defined("AESKEY") ? AESKEY : $this->aes_key;
            $upd .= ($pwd) ? " pwd=AES_ENCRYPT('" . $pwd . "','$aes_key')" : substr($upd, 0, -2);
            $isrt_k .= ($pwd) ? " pwd" : substr($isrt_k, 0, -2);
            $isrt_v .= ($pwd) ? " AES_ENCRYPT('" . $pwd . "','$aes_key')" : substr($isrt_v, 0, -2);

            if ($id) {
                mysql_query("UPDATE $table SET $upd WHERE id='$id'");
            } else {
                mysql_query("INSERT INTO $table ($isrt_k) Values ($isrt_v)");
                $id = mysql_insert_id();
            }
        }

        $reload = mysql_affected_rows() ? "window.parent.location.reload()" : "";
        if (is_int($id)) {
            return "<script>alert('" . $this->str('saved') . "'); $reload</script>";
        }
        //return $this->Error($id);
        return $this->Error($this->str('error_login'));
    }

    ########################

    function Info() {
        return array(
            'version' => get_revision('$Revision: 1.1 $'),
            'checked' => 1,
            'disabled' => 1,
            'type' => 'checkbox',
        );
    }

    ########################
}

$GLOBALS['admins'] = & Registry::get('TAdmins');

?>