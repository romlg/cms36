<?php
require_once(inc('modules/clients/clients_base.php'));

class TClients extends TClients_base {

	//-----------------------------------------------------------
	function TClients(){
		TClients_base::TClients_base();
	}
	//-----------------------------------------------------------
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
					'select'	=> 'id',
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
					'select'	=> 'id',
					'display'	=> 'id',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '1px',
				),
				array(
					'select'	=> 'CONCAT(lname,CHAR(32),name,CHAR(32),tname)',
					'as'		=> 'name',
					'display'	=> 'fullname',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '100%',
				),
				array(
					'select'	=> 'email',
					'display'	=> 'email',
				),
				array(
					'select'	=> 'DATE_FORMAT(last_visited,"%H:%i:%s&nbsp;%d.%m.%Y")',
					'display'	=> 'last_access',
					'flags'  	=> FLAG_SORT,
				),
				array(
					'select'	=> 'DATE_FORMAT(reg_date,"%d.%m.%Y")',
					'display'	=> 'reg_date',
					'flags'   	=> FLAG_SORT,
				),
				array(
					'select'	=> 'subscribe',
					'display'	=> 'subscribe',
					'flags'		=> FLAG_SORT,
					'align'		=> 'center',
					'type'		=> 'visible',
				),
			),
			'from'		=> $this->table,
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'orderby'	=> 'last_visited DESC',
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
	
	function getClientTitle($id){
		global $_name;
		$row = sql_getRow("SELECT id, login, CONCAT(name,CHAR(32),lname) as name FROM ".$this->table." WHERE id=".$id);
	
		$_name = $row['name'];
		$title = $this->str('title_').$row['id'].' '.$row['name'].' ('.$row['login'].')';
		return $title;
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
		
		$recieved	= sql_getValue('select sum(received) from bills  where type!=\'bonus\' and status="paid" and client_id = '.$id);
		$bonus 		= sql_getValue('select sum(total) 	 from bills  where type=\'bonus\'  and client_id = '.$id);
		$total 		= sql_getValue('select sum(total)    from orders where status!=\'New\' and status!=\'Canceled\' and client_id = '.$id);
		$balance = $recieved - $bonus - $total;
		$details[] = array(
			'name' => $this->str('balance'),
			'value' => $balance
		);
		return $details;
	}
	
	//-----------------------------------------------------------
	/* страницы */	
	function EditSystem()	{
		global $str,$cfg,$lang;
		$actions[$this->name]="";
		$client_id = $this->GetClientID();
		$row = sql_getRow("SELECT * FROM ".$this->table." WHERE reg_date!=0 AND id=".$this->getClientID());
		$groups = sql_getRows("SELECT id,name FROM auth_groups");
		foreach ($groups as $k=>$v) {
			$row['groups'][$v['id']] = $v['name'];
		}
		
		$row['selected_group'] = sql_getValue("SELECT group_id FROM auth_users_groups WHERE user_id=".$this->GetClientID());
		$row['allow_checked'] = (@$row['visible']==1) ? 'checked=checked' : '';
		$row['subscribe_checked'] = (@$row['subscribe']==1) ? 'checked=checked' : '';
		
		$row['thisname'] = $this->name;

		$this->AddStrings($row);
		return $this->Parse($row, 'editsystem.tmpl');
	}

	//-----------------------------------------------------------	
	function EditCompany()	{
		global $str,$cfg,$lang;
		$client_id = $this->GetClientID();
		$row = sql_getRow("SELECT * FROM ".$this->table." WHERE reg_date!=0 AND id=".$this->GetClientID());
		$this->AddStrings($row);		
		$row['thisname'] = $this->name;
		return $this->Parse($row, 'editcompany.tmpl');
	}
	
	//-----------------------------------------------------------

	function EditClient()	{
		global $str,$cfg,$lang;
		$client_id = $this->GetClientID();
		$row = sql_getRow("SELECT * FROM ".$this->table." WHERE reg_date!=0 AND id=".$this->GetClientID());
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
			$row = sql_getRow("SELECT * FROM ".$this->table." WHERE reg_date!=0 AND id=".$client_id);
			$row['recieve_bills']=(int)sql_getValue("select count(id) from bills where client_id=".$client_id);
			$row['recieve_bills_sum']=(int)sql_getValue('select sum(received) from bills where client_id = '.$client_id);
			$row['paid_bills']=(int)sql_getValue("select count(id) from bills where client_id=$client_id and type!=\"bonus\" and status=\"paid\"");
			$row['paid_bills_sum']=(int)sql_getValue('select sum(received) from bills where type!=\'bonus\' and status="paid" and client_id = '.$client_id);
			$row['bonus'] = &$row['bonus_bill'];
			//$row['bonus']=(int)sql_getValue('select count(id) from bills where type=\'bonus\' and client_id = '.$client_id);
			//$row['bonus_sum']=(int)sql_getValue('select sum(total) from bills where type=\'bonus\' and client_id = '.$client_id);
			$row['orders']=(int)sql_getValue('select count(id) from orders where client_id = '.$client_id);
			$row['orders_sum']=(int)sql_getValue('select sum(total) from orders where client_id = '.$client_id);
			$row['ready_orders']=(int)sql_getValue('select count(id) from orders where status!=\'New\' and status!=\'Canceled\' and client_id = '.$client_id);
			$row['ready_orders_sum']=(int)sql_getValue('select sum(total) from orders where status!=\'New\' and status!=\'Canceled\' and client_id = '.$client_id);
			$row['balance'] = sql_getValue('select sum(received) from bills where type!=\'bonus\' and status="paid" and client_id = '.$client_id)-sql_getValue('select sum(total) as x from bills where type=\'bonus\' and client_id = '.$client_id)-sql_getValue('select sum(total) from orders where status!=\'New\' and status!=\'Canceled\' and client_id = '.$client_id);
			$row['status'] = ($row['visible']>0) ? $this->str('active') : $this->str('inactive');
			// добавляем вывод скидочной системы
			$row['dis_group'] = sql_getRow('select auth_groups.id, auth_groups.name,auth_groups.type from auth_groups,auth_users_groups where auth_groups.id = auth_users_groups.group_id and auth_users_groups.user_id='.$client_id);
			
			if ($row['dis_group']['type'] != 'volume'){
				$row['dis_group']['pr_types'] = sql_getRows('
				SELECT
				pdg.name,
				d.discount
				FROM
				product_discount_groups AS pdg
				LEFT OUTER JOIN
				discounts as d ON d.product_discount_group_id = pdg.id AND d.user_discount_group_id='.$row['dis_group']['id']);
			} else {
				$row['dis_group']['pr_volume'] = sql_getRows('
				SELECT
				volume,
				discount
				FROM
				discounts_volume 
				WHERE auth_group_id='.$row['dis_group']['id'].' ORDER BY volume');	
				$total = sql_getValue('select sum(received) from bills where type!=\'bonus\' and status="paid" and client_id = '.$client_id);		
				$row['dis_group']['now'] = 0;
				foreach ($row['dis_group']['pr_volume'] as $k=>$v){
					if ($v['volume']<=(int)$total){
						$now = $v['discount'];
						$row['dis_group']['now'] = $now;
					}
				}
				
			}
			$row['thisname'] = $this->name;
			$this->AddStrings($row);
			return $this->Parse($row, 'showclientinfo.tmpl');
		}
		else {
			global $str,$cfg,$lang;
			$client_id = $this->GetClientID();
			$row = sql_getRow("SELECT * FROM ".$this->table." WHERE reg_date!=0 AND id=".$this->GetClientID());
			$this->AddStrings($row);
			$row['thisname'] = $this->name;
			return $this->Parse($row, 'add.tmpl');
		}
	}	
	
}
$GLOBALS['clients'] = & Registry::get('TClients');
?>