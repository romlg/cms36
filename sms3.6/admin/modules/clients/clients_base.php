<?php


class TClients_base extends TTable {
	
	var $name = 'clients';
	var $table = 'auth_users';
	var $selector = false; # show language selector ?

	//-----------------------------------------------------------

	function TClients_base() {
		global $str, $actions;
		TTable::TTable();

		# �������� ���������. #
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'              => array('������ � ���������',				'Clients',),
			'email'              => array('E-mail',                         'E-mail',),
			'system_info'        => array('��������� ����������',			'System Information',),
			'company_info'       => array('���������� � ��������',			'Company information',),
			'crm'                => array('����',                           'Menu',),
			'client_info'        => array('���������� � �������',			'Client Information',),
			'fullname'           => array('������ ���',                     'fullname',),
			'bill'               => array('��������� �����',				'Balance',),
			'id'                 => array('������ ID',                      'Client ID',),
			'client_id'          => array('������ ID',                      'Client ID',),
			'recieve_bills'      => array('�������� ������',				'Recieve bills',),
			'paid_bills'         => array('�������� ������',				'Paid bills',),
			'paid_bills_sum'     => array('����� ���������� ������',        'Sum of paid bills',),
			'bonus'              => array('���������� �������',				'Bonuses',),
			'bonus_sum'          => array('����� �������',                  'Sum of bonuses',),
			'orders'             => array('����� �������',                  'Orders',),
			'ready_orders'       => array('��������� �������',				'Closed orders',),
			'orders_sum'         => array('����� ����������� �������',		'Sum of orders',),
			'title_'             => array('������ #',        				'Client #',),
			'name'               => array('���',                            'Name',),
			'lname'              => array('�������',                        'Last name',),
            'addr'               => array('�����',                          'Address',),
            'cell_phone'         => array('��������� �������',				'Cell phone',),
			'phone'              => array('�������',                        'Phone',),
			'pass1'              => array('����� ������',                   'New password',),
			'pass2'              => array('��������� ������',				'Re-enter password',),
			'fax'                => array('����',                           'Fax',),
			'comp_name'          => array('��������',                       'Company Name',),
            'comp_fname'         => array('������ ��� ��������',            'Full company name',),
            'comp_inn'           => array('���',                            'INN',),
			'comp_kpp'           => array('���',                            'KPP',),
			'comp_zip'           => array('������',                         'ZIP',),
            'comp_addr'          => array('����������� �����',              'Company adress',),
			'comp_paddr'         => array('����������� �����',              'Company adress',),
			'comp_phone'         => array('������� �������',                'Work Phone',),
			'comp_fax'           => array('������� ����',                   'Work fax',),
	        'comp_email'         => array('������� e-mail',                	'Work e-mail',),
     	    'comp_bank'          => array('������������ �����',             'Bank name',),
            'comp_bik'           => array('���',                            'BIK',),
            'comp_ks'            => array('���. ����',                      'KS',),
			'comp_rs'            => array('���������� ����',				'RS',),
			'tname'              => array('��������',                       'Surname',),
			'login'              => array('�����',                          'Login',),
			'email'              => array('E-mail',                         'E-mail',),
			'balance'            => array('������',                        	'Balance',),
			'group'              => array('������',                        	'Group',),
			'count'              => array('���-��',                         'Count',),
			'sum'                => array('�����',                          'Sum',),
			'header'             => array('����������',                     'Information',),
			'saved'              => array('������� ���������!',				'Succesfully Saved!',),
			'passwords_neq'      => array('������ �� ���������!',			'Passwords are not equal!',),
			'error'              => array('������!',                        'Error!',),
			'reg_date'           => array('���� �����������',				'Register date',),
			'allow'              => array('�������',                        'Aviable',),
			'active'             => array('�������',                        'Active',),
			'inactive'           => array('������������',                   'Blocked',),
			'last_access'        => array('���� ���������� �����',        	'Last access time',),		
			'requests'        	 => array('������� � ��������',        	'Tender requests',),		
			'subscribe'          => array('��������',                       'Subscribed',),
			'prod_types'         => array('������',				    		'Discounts',),
			'discnow'            => array('������� ������',				    'Discount',),
			'discount'           => array('������',                         'Discount',),
			'discount_group'     => array('��������� ������',				'Discount group',),
			'briz_club'     	 => array('�������� ���� �����',			'briz club',),
			'otkat'     	 	 => array('������� ������',					'Discount',),
		));

		# actions.. #
		$actions[$this->name] = array(
			'save' => array(
				'���������',
				'Save',
				'link'	=> 'cnt.mySubmit(\''.$this->name.'\')',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'�������',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			/*'create_email' => array(
				'���������&nbsp;������',
				'Create E-mail',
				'link'	=> 'cnt.createEmail()',
				'img' 	=> 'icon.email_templates.gif',
				'display'	=> 'none',
				'show_title'	=> true,
			),
            'create_order' => array(
                '�������&nbsp;�����',
                'Create order',
                'link'    => 'cnt.createOrder()',
                'img'     => 'icon.orders.gif',
                'display'    => 'block',
                'show_title'    => true,
            ),
			'mail2all' => array(
				'��������&nbsp;�����&nbsp;����',
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
	// ���������
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