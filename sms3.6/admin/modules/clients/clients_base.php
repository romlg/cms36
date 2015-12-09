<?php


class TClients_base extends TTable {
	
	var $name = 'clients';
	var $table = 'auth_users';
	var $selector = false; # show language selector ?

	//-----------------------------------------------------------

	function TClients_base() {
		global $str, $actions;
		TTable::TTable();

		# Языковые константы. #
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'              => array('Работа с клиентами',				'Clients',),
			'email'              => array('E-mail',                         'E-mail',),
			'system_info'        => array('Системная информация',			'System Information',),
			'company_info'       => array('Информация о компании',			'Company information',),
			'crm'                => array('Меню',                           'Menu',),
			'client_info'        => array('Информация о Клиенте',			'Client Information',),
			'fullname'           => array('Полное имя',                     'fullname',),
			'bill'               => array('Состояние счёта',				'Balance',),
			'id'                 => array('Клиент ID',                      'Client ID',),
			'client_id'          => array('Клиент ID',                      'Client ID',),
			'recieve_bills'      => array('Получено счетов',				'Recieve bills',),
			'paid_bills'         => array('Оплачено счетов',				'Paid bills',),
			'paid_bills_sum'     => array('Сумма оплаченных счетов',        'Sum of paid bills',),
			'bonus'              => array('Количество бонусов',				'Bonuses',),
			'bonus_sum'          => array('Сумма бонусов',                  'Sum of bonuses',),
			'orders'             => array('Всего заказов',                  'Orders',),
			'ready_orders'       => array('Выполнено заказов',				'Closed orders',),
			'orders_sum'         => array('Сумма выполненных заказов',		'Sum of orders',),
			'title_'             => array('Клиент #',        				'Client #',),
			'name'               => array('Имя',                            'Name',),
			'lname'              => array('Фамилия',                        'Last name',),
            'addr'               => array('Адрес',                          'Address',),
            'cell_phone'         => array('Мобильный телефон',				'Cell phone',),
			'phone'              => array('Телефон',                        'Phone',),
			'pass1'              => array('Новый пароль',                   'New password',),
			'pass2'              => array('Повторите пароль',				'Re-enter password',),
			'fax'                => array('Факс',                           'Fax',),
			'comp_name'          => array('Компания',                       'Company Name',),
            'comp_fname'         => array('Полное имя компании',            'Full company name',),
            'comp_inn'           => array('ИНН',                            'INN',),
			'comp_kpp'           => array('КПП',                            'KPP',),
			'comp_zip'           => array('Индекс',                         'ZIP',),
            'comp_addr'          => array('Юридический адрес',              'Company adress',),
			'comp_paddr'         => array('Фактический адрес',              'Company adress',),
			'comp_phone'         => array('Рабочий телефон',                'Work Phone',),
			'comp_fax'           => array('Рабочий факс',                   'Work fax',),
	        'comp_email'         => array('Рабочий e-mail',                	'Work e-mail',),
     	    'comp_bank'          => array('Наименования банка',             'Bank name',),
            'comp_bik'           => array('БИК',                            'BIK',),
            'comp_ks'            => array('Кор. счёт',                      'KS',),
			'comp_rs'            => array('Рассчётный счёт',				'RS',),
			'tname'              => array('Отчество',                       'Surname',),
			'login'              => array('Логин',                          'Login',),
			'email'              => array('E-mail',                         'E-mail',),
			'balance'            => array('Баланс',                        	'Balance',),
			'group'              => array('Группа',                        	'Group',),
			'count'              => array('Кол-во',                         'Count',),
			'sum'                => array('Сумма',                          'Sum',),
			'header'             => array('Информация',                     'Information',),
			'saved'              => array('Успешно сохранено!',				'Succesfully Saved!',),
			'passwords_neq'      => array('Пароли не совпадают!',			'Passwords are not equal!',),
			'error'              => array('Ошибка!',                        'Error!',),
			'reg_date'           => array('Дата регистрации',				'Register date',),
			'allow'              => array('Включен',                        'Aviable',),
			'active'             => array('Активен',                        'Active',),
			'inactive'           => array('Заблокирован',                   'Blocked',),
			'last_access'        => array('Дата последнего входа',        	'Last access time',),		
			'requests'        	 => array('Участие в тендерах',        	'Tender requests',),		
			'subscribe'          => array('Подписан',                       'Subscribed',),
			'prod_types'         => array('Скидки',				    		'Discounts',),
			'discnow'            => array('Текущая скидка',				    'Discount',),
			'discount'           => array('Скидка',                         'Discount',),
			'discount_group'     => array('Скидочная группа',				'Discount group',),
			'briz_club'     	 => array('Участник Бриз клуба',			'briz club',),
			'otkat'     	 	 => array('Жесткая скидка',					'Discount',),
		));

		# actions.. #
		$actions[$this->name] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.mySubmit(\''.$this->name.'\')',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			/*'create_email' => array(
				'Отправить&nbsp;письмо',
				'Create E-mail',
				'link'	=> 'cnt.createEmail()',
				'img' 	=> 'icon.email_templates.gif',
				'display'	=> 'none',
				'show_title'	=> true,
			),
            'create_order' => array(
                'Создать&nbsp;заказ',
                'Create order',
                'link'    => 'cnt.createOrder()',
                'img'     => 'icon.orders.gif',
                'display'    => 'block',
                'show_title'    => true,
            ),
			'mail2all' => array(
				'Рассылка&nbsp;писем&nbsp;всем',
				'Mailing to all',
				'link'	=> 'cnt.createEmail()',
				'img' 	=> 'icon.email_templates.gif',
				'display'	=> 'block',
				'show_title'	=> true,
			),*/
		);
		# crm_menu #
		$this->client_id = $this->GetClientID();
		require_once(inc('modules/'.$this->name.'/config.php'));
	}
	
	//-----------------------------------------------------------

	# gettitle.. #
	function GetTitle() {
		global $_elems, $cfg;
		$title = TTable::GetTitle();
		$id = $this->GetClientID();
		
		$_elems = false;
		if($id){
			$_elems = true;
			$title = $this->getClientTitle($id);
		}		
		return $title;
	}
	
	//-----------------------------------------------------------
	
	function getClientTitle($id){
		global $_name;
		$row = sql_getRow("SELECT id, login, CONCAT(name,CHAR(32),lname) as name FROM ".$this->table." WHERE id=".$id);
		$_name = $row['name'];
		$title = $this->str('title_').$row['id'].' '.$row['name'].' ('.$row['login'].')';
		return $title;
	}
	
	//-----------------------------------------------------------
	
    # elements #
	function GetBasicElement() {
		global $elements, $intlang;
		$crm_menu = $this->crm_menu['client'];
		$x = '';
		
		foreach ($crm_menu as $key=>$val)
		{
			$menu[$key]['name'] = utf($val[LangID()]);
			if (isset($val['link'])) {
				$menu[$key]['href'] = array('link' => $val['link']);
				$menu[$key]['open'] = BASE.$val['link'] == $_SERVER['REQUEST_URI'] ? 'open' : '';
			}
			if (isset($val['items'])) foreach ($val['items'] as $items)
				$menu[$key]['items'][] = array(
					'link' => $items['link'],
					'target' => 'x',
					'open' => BASE.$items['link'] == $_SERVER['REQUEST_URI'] ? 'open' : '',
					'name' => utf($items[LangID()]),
					);
		}
		
		$block = array
		(
			'basic_caption'	=> $this->str('crm'),
			'basic_icon'	=> 'box.page.gif',
			'src'			=> $GLOBALS['_SERVER']['QUERY_STRING'],
			'menu' 		  	=> $menu,
			'client_info' 	=> $this->GetClientInfo(),
		);
		return $block;
	}
	
	//-----------------------------------------------------------
	# get client id #
	function GetClientID() {
		$client_id	= (int)get('client_id', 0, 'g');
		return $client_id;
	}
	
	//-----------------------------------------------------------

    # get client info #
	function GetClientInfo() {		
		
		return array(
			'basic_caption'	=> $this->str('client_info'),
			'basic_icon'	=> 'box.clients.gif',
			'src'			=> $GLOBALS['_SERVER']['QUERY_STRING'],
			'details'		=> $this->getClientDetails($this->GetClientID()),
		);
	}
	//-----------------------------------------------------------
	
	function getClientDetails($id){
		
		$client = sql_getRow("SELECT id, CONCAT(name,' ',lname) AS fullname FROM ".$this->table." WHERE reg_date!=0 AND id=".$id);
		if (!is_array($client)) return '';

		$details = array(
			array(
				'name' => $this->str('client_id'),
				'value' => $client['id'],
			),
			array(
				'name' => $this->str('fullname'),
				'value' => $client['fullname'],
			),
		);
		return $details;
	}
	
	//-----------------------------------------------------------

	function doAdd() {
		if(@$_POST['fld']['pass1'] or @$_POST['fld']['pass2']) {
			if(($_POST['fld']['pass1']==$_POST['fld']['pass2']) AND ($_POST['fld']['pass1']!='' AND $_POST['fld']['pass2']!=''))  {
				$_POST['fld']['pass']=md5($_POST['fld']['pass1']);
			}
			else {
			return "<script>alert('".$this->str('passwords_neq')."');</script>";
			}
		}
		unset($_POST['fld']['pass1']);
		unset($_POST['fld']['pass2']);

		$str   = '`reg_date`,';
		$str2  = 'now(),' ;
		$delim = ' , ';

		$_POST['fld']['password'] = $_POST['fld']['pass'];
		unset($_POST['fld']['pass']);

		foreach($_POST['fld'] as $k=>$v)	{
			if (end($_POST['fld']) === $v){ $delim = ""; }
			$str  .= "`".$k."`".$delim;
			$str2 .= "'".$v."'".$delim;
		}

		$res = sql_query("INSERT INTO ".$this->table." (".$str.")  VALUES(".$str2.")");
		$err = sql_getError();
		$client_id = sql_getLastId();
		if (!$client_id) return "<script>alert('".$this->str('error').": ".e($err)."');</script>";

		$def_id = sql_getValue("SELECT id FROM auth_groups ORDER BY priority DESC");
		$res = sql_query("INSERT INTO auth_users_groups (`user_id`,`group_id`)  VALUES('".$client_id."','".$def_id."')");

		return "<script>alert('".$this->str('saved')."'); window.top.opener.location.reload(); window.top.location.href = 'crm.php?page=".$this->name."&do=showclientinfo&client_id=".$client_id."';</script>";
	}
	
	//-----------------------------------------------------------	
	// сохраняем
	function Edit() {
		$fld = get('fld',array(),'p');
		$client_id = (int)get('client_id', 0, 'p');
		
		if($client_id > 0) {		
			
			// updating password
			if(@$fld['pass1'] or @$fld['pass2']) {
					if(($fld['pass1']==$fld['pass2']) AND ($fld['pass1']!='' AND $fld['pass2']!=''))  {
						$fld['password']=md5($fld['pass1']);
					}
					else {
					return "<script>alert('".$this->str('passwords_ne')."');</script>";
					}
			}
			unset($fld['pass1']);
			unset($fld['pass2']);
			$reload = "";
			// updating group
			if(isset($fld['group'])) {
				$group = (int) sql_getValue("SELECT group_id FROM auth_users_groups WHERE user_id=".$client_id);
				if($group>0) {
					if($group!=$fld['group'])
					sql_query("UPDATE auth_users_groups SET group_id=".(int)$fld['group']." WHERE user_id=".$client_id);
				}
				else {
					sql_query("INSERT INTO auth_users_groups (user_id,group_id) VALUES (".$client_id.",".(int)$fld['group'].")");
				}
				unset($fld['group']);
				$reload = "window.top.location.reload();";
				
			}

			// preparing visible parametr
			if(isset($fld['login'])) {
				if(@$fld['visible']) $fld['visible']=1;
				else $fld['visible']=0;

				// preparing subscribe parametr
				if(@$fld['subscribe']) $fld['subscribe']=1;
				else $fld['subscribe']=0;
				
				// preparing subscribe parametr
				if(@$fld['enable']) $fld['enable']=1;
				else $fld['enable']=0;
			}

			// updating information
			foreach($fld as $k=>$v)	{
				$res = sql_query("UPDATE $this->table SET $k=\"".htmlspecialchars($v)."\" WHERE id=\"$client_id\"");
				if (!$res) return "<script>alert('".$this->str('error').": ".mysql_error()."');</script>";
			}

			return "<script>alert('".$this->str('saved')."');".$reload."</script>";
		}
		else {
			return "<script>alert('".$this->str('error')."');".$reload."</script>";
		}
	}
	//-----------------------------------------------------------
	
	function Send_Email()
	{
		$client_id = $this->GetClientID();
		header("location: cnt.php?page=send_email&client_id=".$client_id."&id[]=".$client_id);
	}
	
	//-----------------------------------------------------------
}
?>