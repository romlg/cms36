<?php

include_once 'modules/forms.php';
include_once 'modules/object/object.class.php';
include_once 'phpmailer/class.phpmailer.php';

define ('DIR_SIZE_LIMIT',	1024*1024*20); // Ограничение на размер папки

class TCabinet {

	function getParams(){

		$param = &$this->param;

		$param['offset'] = (int)get('offset', 0, 'gp');
		$param['limit'] = (int)get('limit', 0, 'gp');

        $param['sort_by'] 		=	 get('sort_by',NULL,'pg');
        $param['sort_type'] 	=	 get('sort_type',NULL,'pg');

        $param['login'] = get('login','','pg');
		$param['password'] = get('password','','pg');
		$param['hash'] = get('hash','','pg');
		$param['user_id'] = get('smsu','','c');

		if (empty($param['method'])){
			$page_obj = & Registry::get('TPage');
			if (!empty($page_obj->pids['2']['page'])){
				$param['method'] = $page_obj->pids['2']['page'];
			} else {
				$param['method'] = 'checklogin';
			}
		}

        if (!empty($_GET))  foreach($_GET as $key=>$val) $param[$key] = $val;
        if (!empty($_POST))  foreach($_POST as $key=>$val) $param[$key] = $val;

        return $param;
	}

	//---------------------------------------------------------------------
	function showLoginForm(&$params) {
		$auth_obj = & Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();
		if ($profile['id']) {
			if (isset($_POST['login']) && isset($_POST['password'])) {
				redirect('/cabinet/list/');
			}
			// Все объявления
			$profile['all_count'] = (int)sql_getValue('SELECT COUNT(*) FROM objects WHERE client_id='.$profile['id'].' AND visible > -1');
			// Объявления, которые видны в каталоге посетителям сайта
			$profile['count'] = (int)sql_getValue('SELECT COUNT(*) FROM objects AS o, auth_users AS a WHERE a.id=o.client_id AND o.client_id='.$profile['id'].' AND o.visible > 0 AND a.stop <> 1 AND (o.confirm > 0 OR a.trusted > 0) AND (o.expire_time > "'.date('Y-m-d H:i:s').'" OR a.free > 0)');
		}
		return array('profile' => $profile);
	}

	//---------------------------------------------------------------------
	function showContent(&$params){

		$param = $this->getParams();

		//проверяем, залогинены ли мы
		$auth_obj = & Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();
		//if (!$profile['id']) redirect('/login_registration/');
		if (!$profile['id']) redirect('/registruser/');

		$page = & Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'cabinet');

		switch($page->content['page']) {
			case 'form'			:
				$form = $this->getMyProfile();
				break;
			case 'list'			:
				$block = $this->showMyListFirstPage($param);
				break;
			case 'rent_room':
			case 'rent_house':
			case 'rent_commerce':
			case 'sale_room':
			case 'sale_house':
			case 'sale_commerce':
				$block = $this->getMyList($param);
				break;
			case 'change_password' :
				$form = $this->getChangePwdForm();
				break;
			case 'balance' :
			    $block = $this->showBalance($param);
			    break;
			case 'bills' :
			    $block = $this->getMyBills($param);
			    break;
			case 'new_bill' :
			    $block = $this->newBill($param);
			    break;
			case 'print_kvit' :
			    $block = $this->printBill($param);
			    break;
			case 'sms' :
			    $block = $this->paySms();
			    break;
		}

		$ret = array(
		'params' => $param, // параметры
		'form'	 => $form, // строящаяся, уже отпарсенная форма
		'block'	 => $block,
		);
		return $ret;

	}

	//---------------------------------------------------------------------
	function getMyProfile() {
		$page = & Registry::get('TPage');
		$auth_obj = & Registry::get('TUserAuth');
		$post = isset($_POST['fld']) ? $_POST['fld'] : array();

		$user_id = $auth_obj->getCurrentUserId();
		$profile = $auth_obj->getCurrentUserData();

		$form = new TForm(null, $this);
		$form->form_name = 'registration';
		$form->elements = array(
			'fio' => array('name' => 'fio',
				'type' => 'text',
				'req' => 1,
				'group' => 'private',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['fio']) ? $post['fio'] : $profile['fio'],
			),
			'comp_name' => array('name' => 'comp_name',
				'type' => 'text',
				'req' => 0,
				'group' => 'private',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_name']) ? $post['comp_name'] : $profile['comp_name'],
			),
			'phone' => array('name' => 'phone',
				'type' => 'text',
				'req' => 0,
				'group' => 'private',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['phone']) ? $post['phone'] : $profile['phone'],
			),
			/*'addr' => array('name' => 'addr',
				'type' => 'text',
				'req' => 0,
				'group' => 'private',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['addr']) ? $post['addr'] : $profile['addr'],
			),
			'comp_fname' => array('name' => 'comp_fname',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_fname']) ? $post['comp_fname'] : $profile['comp_fname'],
			),
			'comp_email' => array('name' => 'comp_email',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_email']) ? $post['comp_email'] : $profile['comp_email'],
			),
			'comp_zip' => array('name' => 'comp_zip',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_zip']) ? $post['comp_zip'] : $profile['comp_zip'],
			),
			'comp_addr' => array('name' => 'comp_addr',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_addr']) ? $post['comp_addr'] : $profile['comp_addr'],
			),
			'comp_paddr' => array('name' => 'comp_paddr',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_paddr']) ? $post['comp_paddr'] : $profile['comp_paddr'],
			),
			'comp_phone' => array('name' => 'comp_phone',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_phone']) ? $post['comp_phone'] : $profile['comp_phone'],
			),
			'comp_fax' => array('name' => 'comp_fax',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_fax']) ? $post['comp_fax'] : $profile['comp_fax'],
			),
			'comp_inn' => array('name' => 'comp_inn',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_inn']) ? $post['comp_inn'] : $profile['comp_inn'],
			),
			'comp_kpp' => array('name' => 'comp_kpp',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_kpp']) ? $post['comp_kpp'] : $profile['comp_kpp'],
			),
			'comp_bank' => array('name' => 'comp_bank',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_bank']) ? $post['comp_bank'] : $profile['comp_bank'],
			),
			'comp_bik' => array('name' => 'comp_bik',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_bik']) ? $post['comp_bik'] : $profile['comp_bik'],
			),
			'comp_ks' => array('name' => 'comp_ks',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_ks']) ? $post['comp_ks'] : $profile['comp_ks'],
			),
			'comp_rs' => array('name' => 'comp_rs',
				'type' => 'text',
				'req' => 0,
				'group' => 'company',
				'atrib' => 'class="registration" style="width: 300px"',
				'value' => isset($post['comp_rs']) ? $post['comp_rs'] : $profile['comp_rs'],
			),*/
			/*'password' => array('name' => 'password',
				'type' => 'password',
				'req' => 0,
				'check' => '=confirm_password',
				'onerror' => '{#msg_err_pwd_not_match#}',
				'group' => 'private',
				'atrib' => 'class="registration"',
			),
			'confirm_password' => array('name' => 'confirm_password',
				'type' => 'password',
				'req' => 0,
				'group' => 'private',
				'atrib' => 'class="registration"',
			),*/
			array('name' => 'button1',
				'type' => 'submit',
				'group' => 'system',
				'value' => 'Сохранить',
			),
		);
		$fdata = $form->generate();
		$fdata['form']['action'] = $page->content['href'];

		if (!empty($post) && empty($fdata['form']['errors'])){

			if (get_magic_quotes_gpc()) {
				foreach ($post as $key => $value) {
					if (!is_array($value)) $post[$key] = stripslashes($value);
				}
			}
			foreach ($post as $key => $value) if (!is_array($post[$key])) $post[$key] = h($post[$key]);


			$str = "";
			unset($post['confirm_password']);
			foreach ($post as $k=>$v){
				if ($k == 'password' && empty($v)) continue;
				if ($k == 'password') $v = md5($v);
				$str .= ", `".$k."`='".e($v)."' ";
			}

			$sql= "UPDATE auth_users SET ".substr($str,1)." WHERE id=".$user_id;
			$r = sql_query($sql);
			if ($r !== true) {
				sql_query('ROLLBACK');
				$_GET['msg'] = 'msg_fail';
			}
			else {
				sql_query('COMMIT');
				$_GET['msg'] = 'msg_update_success';
			}

		}

		$page->tpl->assign(array('fdata' => $fdata,
		));
		$ret = $page->tpl->fetch('form.html');

		return $ret;
	}

	//---------------------------------------------------------------------
	function getChangePwdForm() {
		$page = & Registry::get('TPage');
		$auth_obj = & Registry::get('TUserAuth');
		$post = isset($_POST['fld']) ? $_POST['fld'] : array();

		$user_id = $auth_obj->getCurrentUserId();
		$profile = $auth_obj->getCurrentUserData();

		$form = new TForm(null, $this);
		$form->form_name = 'registration';
		$form->elements = array(
			'password' => array('name' => 'password',
				'type' => 'password',
				'req' => 0,
				'check' => '=confirm_password',
				'onerror' => '{#msg_err_pwd_not_match#}',
				'group' => 'private',
				'atrib' => 'class="registration"',
			),
			'confirm_password' => array('name' => 'confirm_password',
				'type' => 'password',
				'req' => 0,
				'group' => 'private',
				'atrib' => 'class="registration"',
			),
			array('name' => 'button1',
				'type' => 'submit',
				'group' => 'system',
				'value' => 'Сохранить',
			),
		);
		$fdata = $form->generate();
		$fdata['form']['action'] = $page->content['href'];

		if (!empty($post) && empty($fdata['form']['errors'])){

			if (get_magic_quotes_gpc()) {
				foreach ($post as $key => $value) {
					if (!is_array($value)) $post[$key] = stripslashes($value);
				}
			}
			foreach ($post as $key => $value) if (!is_array($post[$key])) $post[$key] = h($post[$key]);

			$str = "";
			unset($post['confirm_password']);
			foreach ($post as $k=>$v){
				if ($k == 'password' && empty($v)) continue;
				if ($k == 'password') $v = md5($v);
				$str .= ", `".$k."`='".e($v)."' ";
			}

			$sql= "UPDATE auth_users SET ".substr($str,1)." WHERE id=".$user_id;
			$r = sql_query($sql);
			if ($r !== true) {
				sql_query('ROLLBACK');
				$msg = 'msg_fail';
			}
			else {
				sql_query('COMMIT');
				$msg = 'msg_update_success';
			}
            redirect('/cabinet/change_password?msg='.$msg);
		}

		$page->tpl->assign(array('fdata' => $fdata,
		));
		$ret = $page->tpl->fetch('form.html');

		return $ret;
	}

	//---------------------------------------------------------------------

	function showMyListFirstPage(&$param) {
	    $page = & Registry::get('TPage');
    	$ret['sell_types'] = sql_getRows('SELECT id, name FROM obj_transaction', true);
    	$ret['obj_types'] = array(
    		'room'		=> 'Квартиры и комнаты',
    		'house'		=> 'Коттеджи и земельные участки',
    		'commerce1'	=> 'Офисные помещения',
    		'commerce2'	=> 'Магазины и рестораны/кафе',
    		'commerce3'	=> 'Склады и гаражи',
    	);
		$page->tpl->assign($ret);
		$ret = $page->tpl->fetch('my_list_first_page.html');
		return $ret;
	}

	/**
	 * "Мои объявления"
	 *
	 * @param array $param
	 * @return string
	 */
	function getMyList(&$param) {

		$page = & Registry::get('TPage');
		$auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

		if (isset($_POST['act'])) {
			$rows = $_POST['objects'];
			if ($_POST['act'] == 'delete' && empty($rows)) redirect($page->content['href'].'?msg=cabinet_err_choose_items');
			if ($_POST['act'] == 'start' && empty($rows) && !$_POST['all']) redirect($page->content['href'].'?msg=cabinet_err_choose_items');
			$func = $_POST['act'].'Item';
			return $this->$func($rows);
		}


		list($sell_type, $obj_type) = explode('_', $page->content['page']);
		$sell_type = $sell_type == 'rent' ? '1' : '2';

		$obj = &Registry::get('TObject');
		$obj->sql = ' AND obj_type_id="'.$obj_type.'" AND sell_type_id='.$sell_type.' AND client_id='.$client['id'].' AND o.visible > -1 ';

		$_GET['fld']['obj_type_id'] = $obj_type;

		$ret = $obj->Show($param);

		if ($ret['objects']) {
		    foreach ($ret['objects'] as $key=>$val) {
		        $ret['objects'][$key]['short_description'] .= '
		        <br />
		        <div class="floatRight"><a href="/cabinet/add?object_id='.$val['id'].'&from=cabinet&offset='.$ret['offset'].'&limit='.$ret['limit'].'">Редактировать</a></div>
		        <div class="floatLeft"><span style="color: #D60000">Дата публикации: '.date('d.m.Y', strtotime($val['create_time'])).'</span></div>
		        ';
		        $ret['objects'][$key]['publ'] = ($val['visible'] > 0 && !$client['stop'] && ($val['confirm'] > 0 || $client['trusted']) && ($val['expire_time'] > date('Y-m-d H:i:s') || $client['free'])) ? true : false;
		    }
    		$ret['sum_start_publ'] = $this->getBillingSumForOne();
    		// Сколько всего объявлений требуют продления
    		$ret['all_count'] = sql_getValue('SELECT COUNT(*) FROM objects WHERE visible > -1 AND client_id='.$client['id'].' AND expire_time < "'.(date('Y-m-d', time()+60*60*24)).' 02:00"');
    		// Сколько объявлений требуют продления в данной категории
    		$ret['all_count_in_category'] = (int)sql_getValue('SELECT COUNT(*) FROM objects AS o WHERE 1 '.$obj->sql.' AND expire_time < "'.(date('Y-m-d', time()+60*60*24)).' 02:00"');
		}


		$page->tpl->assign($ret);
		return $page->tpl->fetch('my_list.html');
	}

	/**
	 * Продление публикации выбранных объявлений
	 *
	 */
	function prolongItem($rows) {
		$page = & Registry::get('TPage');

		$time = time() + 60*60*24*30;		// Продлеваем на 30 дней
		$sql = 'UPDATE objects SET expire_time="'.date('Y-m-d H:i:s', $time).'" WHERE id IN ('.implode(',', array_keys($rows)).')';
		sql_query($sql);
		$err = sql_getError();
		if (empty($err)) redirect($page->content['href'].'?msg=cabinet_success_prolong');
		redirect($page->content['href'].'?msg=msg_fail');
	}

	/**
	 * Удаление объявлений
	 *
	 */
	function deleteItem($rows) {
		$page = & Registry::get('TPage');

		$ids = implode(',', array_keys($rows));

		$sql = 'DELETE FROM objects WHERE id IN ('.$ids.')';
		sql_query($sql);
		$err = sql_getError();
		if (empty($err)) {
			$this->deleteImages(array_keys($rows));
			redirect($page->content['href'].'?msg=cabinet_success_delete');
		}
		redirect($page->content['href'].'?msg=msg_fail');
	}

	/**
	 * Приостановка публикации объявлений
	 *
	 */
	function stopItem($rows) {
	    $page = & Registry::get('TPage');
        $auth_obj = & Registry::get('TUserAuth');
        $client_id = $auth_obj->getCurrentUserId();
	    sql_query('UPDATE auth_users SET stop=1 WHERE id='.$client_id);
		$err = sql_getError();
		if (empty($err)) {
			redirect($page->content['href'].'?msg=cabinet_success_stop');
		}
		redirect($page->content['href'].'?msg=msg_fail');
	}

	/**
	 * Начало публикации объявлений
	 *
	 */
	function startItem($rows) {
	    $page = & Registry::get('TPage');
        $auth_obj = & Registry::get('TUserAuth');
        $client = $auth_obj->getCurrentUserData();
        // Если публикация приостановлена, то даем стартовать только все объявления сразу
        if ($client['stop']) $rows = array();
        // Считаем, достаточно ли средств
	    $balance = $this->calcBalance($client['id']);
	    $sum = $this->getBillingSum($client['id'], array_keys($rows));
	    if ($sum > $balance) {
	        redirect($page->content['href'].'?msg=balance_not_enough');
	    }

	    if ($client['stop'] == '1') sql_query('UPDATE auth_users SET stop=0 WHERE id='.$client['id']);

	    sql_query('BEGIN');

	    // Надо списать деньги...
	    $ret = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/billing.php?client_id='.$client['id'].($rows ? '&ids='.implode(',', array_keys($rows)) : ''));
	    if (!$ret) {
    	    sql_query('ROLLBACK');
       	    if ($client['stop'] == '1') sql_query('UPDATE auth_users SET stop=1 WHERE id='.$client['id']);
	        redirect($page->content['href'].'?msg=msg_fail');
	    }
	    if (strpos($ret, 'успешно') === false) {
    	    sql_query('ROLLBACK');
       	    if ($client['stop'] == '1') sql_query('UPDATE auth_users SET stop=1 WHERE id='.$client['id']);
	        redirect($page->content['href'].'?msg=msg_fail');
	    }

	    $this->updateBalance($client['id'], $balance);
	    sql_query('COMMIT');
		redirect($page->content['href'].'?msg=cabinet_success_play');
	}

	/**
	 * Удаление изображений
	 *
	 * @param array $ids - id объектов
	 */
	function deleteImages($ids) {
		if (!$ids) return;
		foreach ($ids as $id) {
			$dir = 'files/objects/'.$id;
			if (is_dir($dir)) {
				$d = dir($dir);
				while (false !== ($entry = $d->read())) {
    				if (is_file($entry)) @unlink($entry);
				}
			}
			@rmdir($dir);
		}
	}

	/**
	 * Меню для личного кабинета
	 *
	 * @param array $params
	 * @return array
	 */
	function show_menu(&$params) {
	    $auth_obj = & Registry::get('TUserAuth');
	    $client = $auth_obj->getCurrentUserData();
	    if (!$client['id']) return;

	    $menu_obj = & Registry::get('TMenu');
	    $menu = $menu_obj->show_menu($params);

	    $delete = array();
	    foreach ($menu['menu'] as $key=>$val) {
	        if (in_array($val['page'], array('balance', 'bills'))) {
	            if ($client['free']) {
	                $delete[] = $key;
	            } else {
	                if ($val['page'] == 'balance') {
    	                // На всякий случай обновляем значение баланса
    	                $balance = $this->calcBalance($client['id']);
                	    $this->updateBalance($client['id'], $balance);
                	    $auth_obj->setUserdata($client['id']);
                	    $client = $auth_obj->getCurrentUserData();
    	                $menu['menu'][$key]['name'] .= " (".number_format($client['balance'], 2, '.', ' ')." руб.)";
	                }
	            }
	        }
	    }

	    if ($delete) {
	        foreach ($delete as $key) {
	            unset($menu['menu'][$key]);
	        }
            $menu['menu'] = array_merge($menu['menu'], array());
	    }
	    return $menu;
	}

	/**
	 * Страница детализации баланса
	 *
	 * @param array $param
	 * @param integer $client_id
	 * @return string
	 */
	function showBalance(&$param, $client_id = 0) {
	    if (!$client_id) {
	        $auth_obj = & Registry::get('TUserAuth');
	        $client_id = $auth_obj->getCurrentUserId();
	    }
	    $client = $auth_obj->getCurrentUserData();
	    //	    if ($client['free']) redirect('/404/');

	    $page = & Registry::get('TPage');

	    if (!$client['free']){
	        $comment1 = $page->tpl->get_config_vars('billing_comment1');
	        $comment2 = $page->tpl->get_config_vars('billing_comment2');
	        $comment3 = $page->tpl->get_config_vars('billing_comment3');

	        $ret['list'] = sql_getRows("SELECT * FROM billing WHERE client_id=".$client_id." AND DATE_ADD(date, INTERVAL 3 MONTH) > NOW() ORDER BY date DESC");
	        echo mysql_error();
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
	    }
	    $ret['balance'] = number_format($client['balance'], 2, '.', ' ');

	    //$sql = 'SELECT COUNT(*) FROM objects AS o, auth_users AS a WHERE a.id=o.client_id AND o.client_id='.$client['id'].' AND o.visible > 0 AND a.stop <> 1 AND (o.confirm > 0 OR a.trusted > 0) AND (o.expire_time > "'.date('Y-m-d H:i:s').'" OR a.free > 0)';
	    $sql = 'SELECT COUNT(*) FROM objects AS o, auth_users AS a WHERE a.id=o.client_id AND o.client_id='.$client['id'].' AND o.visible > 0 AND a.stop <> 1 AND (o.confirm > 0 OR a.trusted > 0)';

	    $ret['count_objects'] = (int)sql_getValue($sql);
	    $ret['sum_per_day'] = $this->getBillingSumForOne($client['id']);
	    $ret['sum'] = $ret['sum_per_day']*$ret['count_objects'];
	    $ret['free'] = $client['free'];
	    $ret['fio'] = $client['fio'];

	    $page->tpl->assign($ret);
	    $ret = $page->tpl->fetch('balance.html');
	    return $ret;
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

	/**
	 * Считаем баланс
	 *
	 * @param integer $client_id
	 * @return double
	 */
	function calcBalance($client_id = 0) {
	    if (!$client_id) {
	        $auth_obj = & Registry::get('TUserAuth');
	        $client_id = $auth_obj->getCurrentUserId();
	    }

	    $sum = doubleval(sql_getValue("SELECT SUM(sum) FROM billing WHERE client_id=".$client_id));
	    return $sum;
	}

	/**
	 * Обновление баланса
	 *
	 * @param integer $client_id
	 * @param double $balance
	 */
	function updateBalance($client_id, $balance) {
	    sql_query('UPDATE auth_users SET balance="'.$balance.'" WHERE id='.$client_id);
	}

	/**
	 * Подсчет суммы для списания за сутки
	 *
	 * @param integer $client_id
	 * @param array $ids - ID объявлений
	 * @return double
	 */
	function getBillingSum($client_id = 0, $ids = array()) {
	    if (!$client_id) {
	        $auth_obj = & Registry::get('TUserAuth');
	        $client_id = $auth_obj->getCurrentUserId();
	    }
	    $count = (int)sql_getValue('SELECT COUNT(*) FROM objects WHERE visible > -1 AND client_id='.$client_id.($ids ? ' AND id IN ('.implode(',', $ids).')' : '').' AND expire_time < "'.(date('Y-m-d', time()+60*60*24)).' 02:00"');
	    if (!$count) return 0;
	    $price = (double)sql_getValue('SELECT g.price FROM auth_groups AS g, auth_users_groups AS aug WHERE aug.group_id=g.id AND aug.user_id='.$client_id);
	    if (!$price) return 0;
	    return $count*$price;
	}

	/**
	 * Подсчет суммы для списания за сутки за одно объявление
	 *
	 * @param integer $client_id
	 * @return double
	 */
	function getBillingSumForOne($client_id = 0) {
	    if (!$client_id) {
	        $auth_obj = & Registry::get('TUserAuth');
	        $client_id = $auth_obj->getCurrentUserId();
	    }
	    $price = (double)sql_getValue('SELECT g.price FROM auth_groups AS g, auth_users_groups AS aug WHERE aug.group_id=g.id AND aug.user_id='.$client_id);
	    return $price;
	}

	/**
	 * Список счетов или счет подробно
	 *
	 * @param array $params
	 * @return string
	 */
	function getMyBills($params) {
	    $auth_obj = & Registry::get('TUserAuth');
	    $client = $auth_obj->getCurrentUserData();
	    //if ($client['free']) redirect('/404/');
	    $bills_obj = & Registry::get('TBills');
	    return $bills_obj->showBills($params);
	}

	/**
	 * Выставление нового счета
	 *
	 * @param array $params
	 * @return string
	 */
	function newBill($params) {
	    $auth_obj = & Registry::get('TUserAuth');
	    $client = $auth_obj->getCurrentUserData();
	    //if ($client['free']) redirect('/404/');
	    $bills_obj = & Registry::get('TBills');
	    return $bills_obj->newBill($params);
	}

	/**
	 * Распечатка счета
	 *
	 * @param array $params
	 * @return string
	 */
	function printBill($params) {
	    $auth_obj = & Registry::get('TUserAuth');
	    $client = $auth_obj->getCurrentUserData();
	    //if ($client['free']) redirect('/404/');
	    $bills_obj = & Registry::get('TBills');
	    return $bills_obj->printVersion($params);
	}

	//---------------------------------------------------------------------

	/**
	* Настройки уведомлений
	*/
	function notifications() {
	        $auth_obj = & Registry::get('TUserAuth');
	        $user_id = $auth_obj->getCurrentUserId();
		if(!$user_id) return;

		$types=array('view_order','announcement_ends');
		$methods=array('email','sms');

		if(isset($_POST['fld']['notification_settings'])) {
			sql_query("DELETE FROM `notify_user_settings` WHERE user_id=".$user_id);
			$ins="";
			foreach($_POST['fld']['notification_settings'] as $type=>$items) {
				if(!in_array($type,$types)) continue;
				foreach($items as $method=>$smth) {
					if(!in_array($method,$methods)) continue;
					$ins.="('$user_id','$type','$method'),";
				}
			}
			if(!empty($ins)) {
				$query="INSERT INTO `notify_user_settings` (`user_id`,`type`,`method`) VALUES ".substr($ins,0,-1);
				sql_query($query);
			}
		}

		$rows=sql_getRows("SELECT `type`,`method` FROM `notify_user_settings` WHERE user_id=".$user_id);
		$ret=array();
		foreach($rows as $row) $ret[$row['type']][$row['method']]=1;
		return array('notification_settings'=>$ret);
	}

	//---------------------------------------------------------------------

	/**
	* Пополнение баланса через sms
	*/
	function paySms() {
	    if (!$client_id) {
	        $auth_obj = & Registry::get('TUserAuth');
	        $client_id = $auth_obj->getCurrentUserId();
	    }
	    $profile = $auth_obj->getCurrentUserData();
	    $page = & Registry::get('TPage');

		if(isset($_POST['fld']['code']) && !empty($_POST['fld']['code'])) {
			$code=mysql_escape_string($_POST['fld']['code']);
			if(empty($code)) {
				$errors[]="Укажите полученный код!";
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			}
			else {
				sql_query("UPDATE `sms_payment` SET `is_used`='1' WHERE `is_used`=0 AND `is_available`>0 AND `tousercode`='$code'");
				if(!mysql_affected_rows()) {
					$query=sql_getRow("SELECT * FROM `sms_payment` WHERE `tousercode`='$code'");
					if(!$query) $errors[]="Неверный код!";
					else {
						if(!$query['is_available']) $errors[]="Код недоступен!<br />Причина:&nbsp;<tt>".$query['error']."</tt>";
						if($query['is_used']) $errors[]="Этот код уже использован!";
					}
					$page->tpl->assign(array('errors' => $errors));
					$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
					$page->tpl->clear_assign('errors_box');
				}
				else {
					//TODO: тут надо прописать в каких случаях на сколько пополнять баланс, сейчас стоит пополнение на 100 рублей
					$rubli=100.00;
					$balance = floatval($profile['balance']) + $rubli;
					sql_query('UPDATE auth_users SET balance="'.$balance.'" WHERE id='.$client_id);
					sql_query('INSERT INTO billing (client_id,sum,date,balance) VALUES("'.$client_id.'","'.$rubli.'",CURRENT_TIMESTAMP,"'.$balance.'")');
					redirect('/cabinet/bills/ok');
				}
			}
		}

	    $page->tpl->assign($ret);
	    $ret = $page->tpl->fetch('paysms.html');
	    return $ret;
	}
}
?>