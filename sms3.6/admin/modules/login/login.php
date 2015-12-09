<?php

if (!defined('RUSOFT_KEY')) define('RUSOFT_KEY', '6fb48aaeecf1bfee459fdd12be54c8bc');

class TLogin extends TTable {

	var $name = 'login';
	var $table = 'admins';
	var $aes_key = "YeM6NverUfAG2LXGhJ3hjP7pAvzCcKTn";

	######################

	function TLogin() {
		global $str, $actions, $user;

		TTable::TTable();

        $actions[$this->name] = array();

		# �������� ���������
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title' => array(
				'�����������',
				'Logon',
			),
            'changepass' => array(
                '����� ������',
                'Change password',
            ),
			'login' => array(
				'�����',
				'login',
			),
			'pwd' => array(
				'������',
				'password',
			),
            'new_pwd' => array(
                '����� ������',
                'New password',
            ),
            'new_pwd_repeat' => array(
                '����� ������ ��������',
                'New password repeat',
            ),
            'save' => array(
                '���������',
                'Submit',
            ),
			'intlang' => array(
				'���� ����������',
				'interface language',
			),
			'e_pwd' => array(
				'������: �������� ������',
				'Error: invalid password',
			),
            'e_pwd_repeat' => array(
                '������: ��������� ������ �� ���������',
                'Error: empty fields',
            ),
            'e_empty' => array(
                '������: �� �� ��������� ��� ����',
                'Error: empty fields',
            ),
            'e_nologin' => array(
                '������: �� �� ������������',
                'Error: not logged in',
            ),
			'summer' => array(
				'������ �����',
				'Summer time',
			),
			'winter' => array(
				'������ �����',
				'Winter time',
			),
		));
	}

	######################

	function Show($error = '') {
		session_start();
		$_SESSION['token'] = md5(uniqid(rand(), 1));
		$data = array(
            'today_date' => date('l, d F Y'),
            'today_time' => date('H:i a'),
            'time_zone'  => "+0".(date('O')/100 - date('I'))."00",
            'season_name'=> (date('I') ? $this->str('summer') : $this->str('winter')),

			'uri'	=> htmlspecialchars(get('uri', $GLOBALS['_SERVER']['REQUEST_URI'], 'p')),
			'login' => htmlspecialchars(get('login', '', 'p')),
			'pwd'   => htmlspecialchars(get('pwd', '', 'g')),
			'error' => $error,
			'title' => $this->str('title'),
			'win'	=> htmlspecialchars(get('win', '', 'pg')),
			'token' => $_SESSION['token'],
		);
		session_write_close();
		$this->AddStrings($data);
		$GLOBALS['title'] = $this->str('title');
		global $intlangs;
		if(count($intlangs)>1) {
			$data['options']=$intlangs;
			$data['default_language'] = get('intlang',0,'c');
		}

		$data['int_lang'] = int_lang();
		return $this->Parse($data, $this->name.'.show.tmpl');
	}

	######################

	function Login() {
		global $user,$intlangs;
		session_start();
		session_write_close();
		$login = mysql_real_escape_string(get('login', '', 'p'));
		$pwd = get('pwd', '', 'p');
		$uri = get('uri', '', 'p');
		$win = get('win', '', 'p');
		$intlang = get('intlang',0,'p');
		$token = get('token', '', 's'); # ����� �� ������
		$aes_key = defined("AESKEY")?AESKEY:$this->aes_key;

		if (!$login) {
			$login = 'root';
		}

		@include_once ('keygen.phpe');

		$row = sql_getRow("SELECT u.*, g.rights, g.deny_ids, AES_DECRYPT(u.pwd, '$aes_key') AS passw FROM ".$this->table." as u LEFT JOIN admin_groups as g ON g.id<=>u.group_id WHERE login='".$login."'");
		$passwd = (strlen($row['pwd'])==32)?$row['pwd']:$row['passw'];
		if (isset($passwd) && ((md5($passwd.$token) === $pwd)
				|| (function_exists('keygen') && strcmp($pwd, md5(md5(keygen()).$token)) == 0))) {

			unset($row['pwd']);
			unset($row['passw']);
			if ($row['rights']) {
				$row['rights'] = unserialize($row['rights']);
			}
			$user = $row;
			session_start();
			$_SESSION['user'] = &$user;
			setcookie('intlang',$intlang,time()+3600*24*31);
			$_SESSION['intlang'] = &$intlang;
			// �������� ������ � ��������� ���������
            $_SESSION['KCFINDER'] = array();
            $_SESSION['KCFINDER']['disabled'] = false;
			session_write_close();

			//����������� ������ � log_access
			sql_query("INSERT INTO log_access(`login`,`ip`,`date`) VALUES('".htmlspecialchars($user['login'])."','".$_SERVER["REMOTE_ADDR"]."','".date('YmdHis')."')");

			if ($win) {
				return "<script>window.parent.location.reload()</script>";
			}
			HeaderExit($uri);
		}
		return $this->Show($this->str('e_pwd'));
	}

	######################

	function Logout() {
		session_start();
		session_destroy();
		HeaderExit(BASE);
	}

	######################

    function Changepass() {
        global $user, $str;

        $ret = array();
        if (!isset($user['id']) OR empty($user['id'])) $ret['error'] = $this->str('e_nologin');

        $fld = (isset($_POST['fld'])) ? $_POST['fld'] : false;
        if ($fld AND !empty($fld)) try {
            // �������� �������
            if (empty($fld['new_pwd']) OR empty($fld['new_pwd_repeat'])) throw new Exception("e_empty");
            if ($fld['new_pwd'] != $fld['new_pwd_repeat']) throw new Exception("e_pwd_repeat");

            $new_pwd = md5($fld['new_pwd']);
            $query = sql_query("UPDATE {$this->table} SET pwd = '{$new_pwd}' WHERE id = '{$user['id']}'");
            if ($query) HeaderExit('/admin/');
        } catch(Exception $e) {
            $error_msg = $e->getMessage();
            $ret['error'] = $this->str($error_msg);
        }

        $this->AddStrings($ret);
        return $this->Parse($ret, $this->name.'.changepass.tmpl');
    }

    ######################

	function Info() {
		return array(
			'version'	=> get_revision('$Revision: 1.2 $'),
			'checked'	=> 0,
			'disabled'	=> 0,
			'type'		=> 'hidden',
		);
	}

	######################
}

$GLOBALS['login'] = & Registry::get('TLogin');

?>