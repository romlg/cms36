<?php
/* $Id: bills.php,v 1.1 2009-02-18 13:09:08 konovalova Exp $
 */


class Tbills extends TTable {

	var $name = 'bills';
	var $table = 'bills';

	########################

	function Tbills() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			/*'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> "cnt.deleteItems('".$this->name."',null,0)",
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			'create' => &$actions['table']['create'],
			'moveup' => &$actions['table']['moveup'],
			'movedown' => &$actions['table']['movedown'],
	*/
		);
		// экшены для формы редактирования
		$actions[$this->name.'.editform'] = array(
			'apply' => array(
				'Применить',
				'Apply',
				'link'	=> 'cnt.ApplySubmit()',
				'img' 	=> 'icon.kb.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'close' => array(
			    'Закрыть',
			    'Close',
			    'link' => 'window.top.close()',
			    'img' => 'icon.close.gif',
			    'display' => 'block',
			    'hint' => array(
			            'Закрыть окно',
			            'Close window',
			        ),
			    'show_title' => '1',
			),
			'add' => array(
				'Поступление денег',
				'Adding',
				'link'	=> 'cnt.AddSubmit()',
				'img' 	=> 'icon.discounts.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
            'printbill' => array(
                'Распечатать',
                'Print bill',
                'link'    => 'cnt.PrintSubmit()',
                'img'     => 'icon.print.gif',
                'display'    => 'block',
                'show_title' => true,
            ),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title' 		 => array('Счета','bills',),
			'bill_id'    => array('№','bill',),
			'adding'     => array('Поступившие средства','Add money',),
			'name'       => array('Плательщик','name',),
			'order_id'   => array('№ заказа','order',),
			'type'       => array('Тип счета','Type',),
			'create'       => array('Создать','Create',),
			'status'     => array('Статус','Status',),
			'date_issue' => array('Выставлен','date_issue',),
			'total'      => array('Сумма','Total',),
			'received'   => array('Получено','Received',),
			'date_pay'   => array('Оплачен','date_pay',),
			'all'   		 => array('не важно','all',),
			'rec_big'     => array('У вас нет прав вводить поступления, больше чем стоимость заказа.','You do not have rights to enter receipt, it is more than cost of the order. ',),
			'rec_big2'     => array('По данному счету уже поступило достаточно средств для оплаты.','Under the given account enough means for payment already have acted.',),
			'error' 		 => array('У вас не достаточно прав для этой операции.','ERROR!!',),
			'saved' 		 => array('Даные были успешно сохранены','Data has been saved successfully',),
			 ));
		$this->statuses = array(
			'new' => array('выставлен','new',),
			'paid' => array('оплачен','paid',),
			'canceled' => array('аннулирован','canceled',),
		);
		$str[get_class_name($this)]+=$this->statuses;
		$this->types = array(
			'full'     => array('без скидки','full',),
			'discount' => array('со скидкой','discount',),
			'bonus'    => array('получения бонуса','bonus',),
		);
		$str[get_class_name($this)]+=$this->types;
	}

################################################
##########     Окно редактора     ##############
################################################

	function EditForm(){
		$id = (int)get('id');
		if ($id) $row['bill'] = $this->getRow($id);
		$row['id']=$id;
		$row['root']=is_root();

		$date_issue = &$row['bill']['date_issue'];
		$date_issue = substr($date_issue, 8, 2).".".substr($date_issue, 5, 2).".".substr($date_issue, 0, 4)." ".substr($date_issue, 11, 2).":".substr($date_issue, 14, 2).":".substr($date_issue, 17, 2);
		$date_pay = &$row['bill']['date_pay'];		
		$date_pay = substr($date_pay, 8, 2).".".substr($date_pay, 5, 2).".".substr($date_pay, 0, 4)." ".substr($date_pay, 11, 2).":".substr($date_pay, 14, 2).":".substr($date_pay, 17, 2);
    
		$row['bill']['type_ru']= $this->str($row['bill']['type']);

		foreach ($this->statuses as $key=>$value){
			$row['status'][$key]=$this->str($key);
		}
		if (!$row['root'] & $row['bill']['status']=='canceled'){
			unset($row['status']);
			$row['status']['canceled'] = $this->str('canceled');
		}
        // Если поступили средства на счет, то не разрешаем аннулировать, иначе - не разрешаем ставить статус "оплачен"
        if ($row['bill']['received'] > 0)
            unset($row['status']['canceled']);
        else unset($row['status']['paid']);

         $sql = "SELECT CONCAT(p.id,'_',c.num) as nid,c.quantity,c.num, c.customer_price,c.discount,p.*, m.name as manufacturer 
		FROM cart AS c 
		LEFT JOIN products AS p ON p.id = c.product_id
		LEFT JOIN orders AS o ON o.id = c.order_id
		LEFT JOIN manufacturers as m ON p.manufacturer_id = m.id
		WHERE c.order_id = ".$row['bill']['order_id'];
	
		$pr = sql_getRows($sql,true);
	
		//if (empty($pr)){redirect('/cabinet/registration');}
		
		$str = "";
		foreach ($pr as $k=>$v){
			if ($v['num'] != 0){
				//собираем запрос к доподнительным объектам
				$str .= " OR ( order_id = '".$row['bill']['order_id']."' AND product_id = '".$v['id']."' AND num = '".$v['num']."' )";
			}
		}

		if (!empty($str)){
			$sql = "
			SELECT 
				cc.product_id,
				cc.num,
				cc.elem_id,
				cc.count,
				p.price,
				p.name
			FROM cart_composition AS cc
			LEFT JOIN products AS p ON p.id = cc.elem_id
			WHERE ".substr($str, 4)." ORDER BY cc.num,p.name";
			$products = sql_getRows($sql);
			
			$old_num = "";
			foreach ($products as $k=>$v){
				if ($old_num != $v['num'])	$delim = " (";
				$old_num = $v['num'];
				$pr[$v['product_id']."_".$v['num']]['name'] .= $delim.$v['name']." (".$v['count'].")"; 
				$pr[$v['product_id']."_".$v['num']]['price'] += $v['price']*$v['count'];
				$delim = " / ";
			}		
		}
		
		$price = 0;
		$count = 0;
		foreach ($pr as $k=>$v){
			$price += $pr[$k]['customer_price'] * $pr[$k]['quantity'];
			$count += $pr[$k]['quantity'];
			$pr[$k]['name'] = $pr[$k]['name'].")";	
		}
    	$row['product_list'] = $pr;
    	$row['count'] = $count;
        /*
		$row['product_list']=sql_getRows("SELECT * FROM cart as c LEFT JOIN products as p on c.product_id=p.id WHERE c.order_id=".$row['bill']['order_id']);

		$row['count']=0;
		foreach ($row['product_list'] as $key=>$item){
    		$row['count']+=$item['quantity'];
		}
		*/
		
        $row['order'] = sql_getRow("SELECT * FROM orders WHERE id=".$row['bill']['order_id']);

        $r = $this->getShipping($row, $row['product_list'], $row['bill']['shipping_type']);

        $row['bill'] = $r['bill'];
        $row['product_list'] = $r['product_list'];
		$row['currency']=sql_getRow("SELECT * FROM currency WHERE name='RUR'");
		$row['history']=sql_getRows("SELECT DATE_FORMAT(FROM_UNIXTIME(date),'%d.%m.%y/%H:%i:%s') as date,text FROM history WHERE ((pid=".$row['bill']['order_id']." AND type='order') OR (pid=".$row['bill']['id']." AND type='bill')) AND client_id=".$row['bill']['client_id']." ORDER BY id DESC");
		
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	//-----------------------------------------------------------------------------------
	
    function EditParam(){
		$id = (int)get('id');
		$row['id']=$id;
	  $this->AddStrings($row);
	  return $this->Parse($row, $this->name.'.editparamform.tmpl');
	}
	
	//-----------------------------------------------------------------------------------
	
	function EditCreateBill(){
		$row['order_id'] = get('id', 0, 'g');
		$row['type'] = get('payment_type', 'Bank', 'g');
		
			
		$disc = sql_getValue("SELECT r_comp_name FROM bills_accounts WHERE type='discount'");
		$full = sql_getValue("SELECT r_comp_name FROM bills_accounts WHERE type='full'");
		
		if ($row['type'] == 'Bank'){
			$row['types'] = array(
				'1' => 'для физ. лица ('.$disc.')',
				'2' => 'для юр. лица со скидкой ('.$disc.')',
				//'3' => 'для юр. лица без скидки ('.$full.')',
			);
		} else {
			$row['types'] = array(
				'1' => 'для физ. лица ('.$disc.')',
				'2' => 'для юр. лица ('.$disc.')',
			);			
		}
		$this->AddStrings($row);	
		return $this->Parse($row, $this->name.'.createbill.tmpl');
	}

	//-----------------------------------------------------------------------------------
		
	function EditCreateBill2(){
		
		$row['payment_type'] = get('payment_type', '', 'g');
		$row['type'] = get('type', '', 'g');
		$row['order_id'] = get('id', 0, 'g');
		
		$id = $row['order_id'];
		
		if ($id) $row['order'] = sql_getRow('SELECT * FROM orders WHERE id='.$id);
		$row['id']=$id;
		 // получаем счет по данному заказу
		$row['bill']=sql_getRow("SELECT * FROM bills WHERE status!='canceled' AND order_id=".$row['id']);
		if (!$row['bill']){
			$product_list = sql_getRows("SELECT * FROM cart as c LEFT JOIN products as p on c.product_id=p.id WHERE c.order_id=".$row['order_id']);
			$row['bill']['shipping_type'] = $row['order']['shipping_type'];
            $row['bill']['shipping'] = $row['order']['shipping'];
            $row['bill']['sum'] = $row['order']['sum'];
            $row['bill']['total'] = $row['order']['total'];
            $row['bill']['tax'] = $row['order']['tax'];
            $row['bill']['bonus'] = $row['order']['bonus'];
            $row['bill']['otkat'] = $row['order']['otkat'];
            $row['bill']['type'] = ($row['type'] == '2' || $row['type'] == '1') ? 'discount' : 'full';

            // Считаем сумму счета, с учетом стоимости доставки и типа счета
            $r = $this->getShipping($row,$product_list,"new");

            $bill = $r['bill'];

			$bill['client_id']=$row['order']['client_id'];
			$bill['order_id']=$row['order']['id'];
			$bill['status'] = "new";

			$bill['purpose']="Плата заказа ".$bill['order_id']." на сумму ".$bill['total'];
			my_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$bill['client_id'].',\''.$bill['order_id'].'\',\'order\',\'Менеджером создан счет на сумму '.$bill['total'].'\',\''.time().'\')');
			$user=sql_getRow("SELECT * FROM auth_users WHERE id=".$bill['client_id']);
			
			switch ($row['type']) {
					case '1':  //для физ лица
							  $bill['client_type'] = 'fiz';
							  $bill['fiz_name']=$user['name'];
							  $bill['fiz_lname']=$user['lname'];
							  $bill['fiz_tname']=$user['tname'];
							  $bill['fiz_phone']=$user['phone'];
							  $bill['fiz_fax']=$user['fax'];
							  $bill['fiz_email']=$user['email'];
							  $r_comp=sql_getRow("SELECT * FROM bills_accounts WHERE type='discount'");
						 break;
					case '2': $r_comp=sql_getRow("SELECT * FROM bills_accounts WHERE type='discount'");
							  $bill['type'] = 'discount';
					case '3':  //для юр лица
							 if (empty($r_comp)) {
							 	$r_comp=sql_getRow("SELECT * FROM bills_accounts WHERE type='full'");
							 	$bill['type'] = 'full';
							 }
							 $bill['client_type'] = 'jur';
							 $bill['comp_name']=$user['comp_name'];
							 $bill['comp_fname']=$user['comp_fname'];
							 $bill['comp_inn']=$user['comp_inn'];
							 $bill['comp_kpp']=$user['comp_kpp'];
							 $bill['comp_zip']=$user['comp_zip'];
							 $bill['comp_email']=$user['comp_email'];
							 $bill['comp_bank']=$user['comp_bank'];
							 $bill['comp_addr']=$user['comp_addr'];
							 $bill['comp_paddr']=$user['comp_paddr'];
							 $bill['comp_bik']=$user['comp_bik'];
							 $bill['comp_ks']=$user['comp_ks'];
							 $bill['comp_rs']=$user['comp_rs'];
							 $bill['comp_phone']=$user['comp_phone'];
							 $bill['comp_fax']=$user['comp_fax'];
						 break;
						 
			}
			$bill['r_comp_name']=$r_comp['r_comp_name'];
			$bill['r_comp_fname']=$r_comp['r_comp_fname'];
			$bill['r_comp_inn']=$r_comp['r_comp_inn'];
			$bill['r_comp_kpp']=$r_comp['r_comp_kpp'];
			$bill['r_comp_zip']=$r_comp['r_comp_zip'];
			$bill['r_comp_addr']=$r_comp['r_comp_addr'];
			$bill['r_comp_paddr']=$r_comp['r_comp_paddr'];
			$bill['r_comp_phone']=$r_comp['r_comp_phone'];
			$bill['r_comp_fax']=$r_comp['r_comp_fax'];
			$bill['r_comp_email']=$r_comp['r_comp_email'];
			$bill['r_comp_bank']=$r_comp['r_comp_bank'];
			$bill['r_comp_bik']=$r_comp['r_comp_bik'];
			$bill['r_comp_ks']=$r_comp['r_comp_ks'];
			$bill['r_comp_rs']=$r_comp['r_comp_rs'];
			
			$key="";$values="";
			foreach ($bill as $k=>$v){
				$key=$key."`".$k."`,";
				$values=$values."'".$v."',";
			}
			$key.="`date_issue`";
			$values.="NOW()";

			my_query("INSERT INTO bills(".$key.") VALUES(".$values.")");
			
			$bill_id = mysql_insert_id();

		}
		echo "<script>
			window.top.opener.opener.top.parent.opener.location.reload();
			window.top.opener.opener.top.parent.location.reload();
			window.top.opener.close();
			window.top.location = '/admin/ced.php?page=bills&do=editform&id=".$bill_id."';
		</script>";
	}	

################################################
##########	 Сохранение изменений ##############
################################################

	function Edit() {
		$root=is_root();
		$whom=$_SESSION['user']['login'];
		$id = $_POST['fld']['id'];
		$temp=sql_getRow("SELECT * FROM bills WHERE id=".$id);

		  if ($_POST['fld']['status']!=$temp['status']){
		  		if ($_POST['fld']['status'] == 'canceled' & $temp['received']>0){
		  			return $this->Error('По данному счету были поступления средств');	
		  		} 	 
		  	 	
				//--- notify ---
				$this->emailNotify($temp,'bills_status');
				//--------------
				$this->in_history( 'change_status', 'bill', $temp['client_id'], $id, $_POST['fld']);
		  }
			//---- Сохранение ----
			$res = $this->Commit();
			
			//---- Добавляем удаление аннулированных счетов, которым больше месяца
			$time = date("Ymd");
				$y = substr($time,0,4);
				$f = substr($time,4,2);  //месяц
				$d = substr($time,6,2);
			if ($d>28){ $d = 28;}	
			if ($f == '01'){
				$f = '12';
				$y--;
			} else {
				$f--;
				if (strlen($f)==1) { $f = '0'.$f; }
			}
			$delete_date = $y.$f.$d.'000000';
			my_query("DELETE FROM bills WHERE date_issue<'".$delete_date."' AND status='canceled' AND id!=".$id);
			//----------------------------------------------------------------------
			
			if (is_int($res)) return "<script>alert('".$this->str('saved')."');try{window.parent.top.opener.location.reload();window.parent.location.reload();}catch(e){}finally{}</script>";
			return $this->Error($res);

	}
//-----------------------------------------------------------------------------------------
	function Editp() {
			$root=is_root();
			$whom = $_SESSION['user']['login'];		
			
			$temp = sql_getRow("SELECT *,(total - bonus) as total FROM bills WHERE id=".$_POST['id']);
			$client = sql_getRow("SELECT * FROM auth_users WHERE id=".$temp['client_id']);

			$order = sql_getRows("SELECT * FROM cart as c LEFT JOIN products as p on c.product_id=p.id WHERE c.order_id=".$temp['order_id']);

			if (!$root){
				if ($temp['received'] < $temp['total']){
					if (($_POST['fld']['received'] + $temp['received'])>$temp['total']){
						return "<script>alert('".$this->str('rec_big')."');</script>";
					}
				} else {
						return "<script>alert('".$this->str('rec_big2')."');</script>";
				}
			}

			if ($_POST['fld']['received']>0 || $root){
			 //--- notify ---
			$this->emailNotify($temp,'bills_money');
			 //--------------
				$temp['received']+=$_POST['fld']['received'];
				if ($_POST['fld']['received']!=0){
					$this->in_history( 'change_money', 'bill', $temp['client_id'], $temp['id'], $_POST['fld']);
					
					if ($temp['received'] >= $temp['total'] & !empty($order)){
						 $_POST['fld']['status'] = 'paid';
										 
						 //--- notify ---
						 $this->emailNotify($temp,'bills_status');
						 //--------------
						 $this->in_history( 'auto_ch_status', 'bill', $temp['client_id'], $temp['id'], $_POST['fld']);
					}
					elseif ($temp['received'] < $temp['total']) {
						$_POST['fld']['status'] = 'new';
						if($_POST['fld']['status']!=$temp['status']){
							//--- notify ---
							$this->emailNotify($temp,'bills_status');
							//--------------
							$this->in_history( 'auto_ch_status_no_money', 'bill', $temp['client_id'], $temp['id'], $_POST['fld']);
						}
					}
					elseif (empty($order)) {
						$_POST['fld']['status'] = 'new';
						//--- notify ---
						$this->emailNotify($temp,'bills_status');
						//--------------
						if($_POST['fld']['status']!=$temp['status']){
						 $this->in_history( 'auto_ch_status_no_bill', 'bill', $temp['client_id'], $temp['id'], $_POST['fld']);
						}
					}
				}
			}
		  //только root может отнимать от суммы
		  if ($_POST['fld']['received']<0 && !$root){return "<script>alert('".$this->str('error')."');</script>";}
		
		  /*Бонусная система*/
		  if ($client['otkat'] != 0){
			//убираем участие в бриз клубе и перестаем считать откаты
			if ($client['briz_club'] == 1) my_query("UPDATE auth_users SET briz_club = 0, bonus_bill = 0 WHERE id=".$client['id']);
		  } else {
			  //проверяем, является ли клиент участником клуба
			 if ($client['briz_club'] != 1){
			 	//смотри, если текущих денег хватит для оплаты, то не собираем все счета за 2 месяца
			 	if ($temp['received'] <3000){
			 		//проверяем оплаченные счета за 2 месяца, на предмет добавления в клуб
			 		$date = date('Y-m-d 00:00:00', time() - 60*60*24*61); //время 61 день назад
			 		$sql = 'SELECT sum(total) FROM `bills` WHERE client_id='.$client['id'].' and status = "paid" and date_pay > "'.$date.'"';
			 		if (sql_getValue($sql) >= 3000) $client['briz_club'] = 1;
			 	} else $client['briz_club'] = 1;
			 	//елси его добавляем в клуб то устанавливаем значение для $client['briz_club']
			 	if ($client['briz_club'] == 1) my_query("UPDATE auth_users SET bonus_bill = 0, briz_club = 1 WHERE id=".$client['id']);		
			 	
			 } 
			 if ($client['briz_club'] == 1){
			 	 //зачисляем бонусы на счет
			 	 
			 	 $money = $_POST['fld']['received'];
			 	 if (!empty($money)){				 	 	
					if (abs($money)>=3000 && abs($money)<4999) $bonus = $money * 0.01;						 	 	
					if (abs($money)>=5000 && abs($money)<9999) $bonus = $money * 0.02;						 	 	
					if (abs($money)>=10000) $bonus = $money * 0.03;
					if (isset($bonus)){
						$bonus = round($bonus); //округляем	
						//зачисляем на счет		
						my_query("UPDATE auth_users SET bonus_bill = bonus_bill + ".$bonus." WHERE id=".$client['id']);		
						//--- notify ---
						$array = array(
							'bonus' => $bonus,
							'client' => $client,
						);
				 		SendNotify('SEND_ADD_BONUS', $client['id'], $array); 	 	
					}
			 	 }
			 }
		  }
						 
		  if ($_POST['fld']['received']=="0"){$_POST['fld']['date_pay']="0";}
		  else{$_POST['fld']['date_pay']=date('Y-m-d H:i:s');}

		  $_POST['fld']['received']=$temp['received'];
			//---- Сохранение ----
			$res = $this->Commit();
			if (is_int($res)) return "<script>alert('".$this->str('saved')."');try{window.parent.top.opener.location.reload();window.parent.location.reload();}catch(e){window.parent.location.reload();}finally{}</script>";
			return $this->Error($res);

	}
################################################
//---------------- history ---------------------
	function in_history( $mes_type, $type, $client_id, $pid, $fld){
		switch($mes_type) {
			case 'change_status': $text = 'изменил  статус на:'.$this->str($fld['status']); break;
			case 'change_money': $text = 'изменил поступления на:'.$fld['received'];break;
			case 'auto_ch_status': $text = 'программа автоматически изменила статус на:'.$this->str($fld['status']).' в связи с поступлением достаточной для оплаты суммы на счет.'; break;
			case 'auto_ch_status_no_money': $text = 'программа автоматически изменила статус на:'.$this->str($fld['status']).' в связи с недостаточной суммой на счете или если счет был удален.'; break;
			case 'auto_ch_status_no_bill': $text = 'программа автоматически изменила статус на:'.$this->str($fld['status']).' в связи с несуществованием счета.'; break;
			default: $text = 'Неизвестный тип операции';
		}
	 return my_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$client_id.',\''.$pid.'\',\''.$type.'\',\''.$text.'\',\''.time().'\')');
	}

################################################
//---------------- Notify ---------------------
	function emailNotify($temp,$template, $prms = array()){
		/*$email_notify = new TEmailNotify;
		if (!empty($temp['fiz_lname'])) {
		$mail_to = 'клиенту:'.$temp['fiz_lname']." ".$temp['fiz_name']." ".$temp['fiz_tname'];
		}
		else {
		 $mail_to = 'клиенту:'.$temp['comp_fname'];
		}
		if (!empty($temp['fiz_email'])) {
			$email_notify->Notify($template,$mail_to,$temp['fiz_email'],$prms); //изменение статуса
		}
		elseif (!empty($temp['comp_email']))  {
			$email_notify->Notify($template,$mail_to,$temp['comp_email'],$prms); //изменение статуса
		}
		*/
	}
################################################
##########	 Построение таблицы   ##############
################################################
	function table_get_order_id(&$value, &$column, &$row) {
		if (!empty($value)){
			return "<a href='javascript:OpenOrders(\"".$value."\")'>№ ".$value."</a>";
		}
		else {
			return "удален";
		}
	}
	########################
	function table_get_type(&$value, &$column, &$row) {
		return $this->str($value);
	}
	########################
	function table_get_date(&$value, &$column, &$row) {
	 if ($value!="0") return date('d.m.Y', $value);
	 else return "";
	}
	########################
	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}

		$client_id = get('client_id','','gp');
		if (!empty($client_id)){ $client_str = " client_id = '".$client_id."' "; }
		else { $client_str = '';}

		//добавляем удаление аннулированных, больше месяца назад
		
		
		require_once(core('ajax_table'));

		$status=array();
		foreach ($this->statuses as $key=>$value){
		$status[$key]=$this->str($key);
		}
		 // строим таблицу
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'		=> 'id',
					'display'	=> 'id',
					'as'			=> 'id',
					'width'		=> '1',
					'type'		=> 'checkbox',
				),
				array(
					'select'		=> 'id',
					'display'	=> 'bill_id',
					'as'			=> 'bill_id',
					'width'		=> '1',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(date_issue)',
					'display'	=> 'date_issue',
					'as'			=> 'date_issue',
					'type'      => 'date',
					'flags'		=> FLAG_SORT,
					'width'		=> '1',
				),

				array(
					'select'		=> 'total-bonus',
					'display'	=> 'total',
					'as'			=> 'total',
					'width'		=> '1',
					'align'		=> 'right',
				),
				array(
					'select'		=> 'received',
					'display'	=> 'received',
					'as'			=> 'received',
					'width'		=> '1',
					'align'		=> 'right',
				),
			  /*	array(
					'select'	=> 'client_id',
					'display'	=> 'client_id',
					'as'			=> 'client_id',
					'width'		=> '1',
				),
				array(
					'select'	=> 'type',
					'display'	=> 'type',
					'as'			=> 'type',
					'type'		=> 'type',
					'flags'		=> FLAG_FILTER | FLAG_SORT,
					'filter_display'	=> 'type',
					'filter_type'	=> 'array',
					'filter_value'	=> array(
													'' => $this->str('all'),
													'full' => $this->str('full'),
													'discount' => $this->str('discount'),
													'bonus' => $this->str('bonus'),
													),
					'filter_field' => 'type',
					'filter_str'	=> false,
					'width'		=> '1',
				),*/
				array(
					'select'	=> 'status',
					'display'	=> 'status',
					'as'			=> 'status',
					'type'      => 'type',
					'flags'		=> FLAG_FILTER | FLAG_SORT,
					'filter_display'	=> 'status',
					'filter_type'	=> 'array',
					'filter_value'	=> array('' => $this->str('all'))+$status,
					'filter_field' => 'status',
					'filter_str'	=> false,
					'width'		=> '1',
				),
				array(
					'select'	   => 'order_id',
					'display'	=> 'order_id',
					'as'			=> 'order_id',
					'width'		=> '1',
					'type'      => 'order_id',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'IF (comp_name="",fiz_lname,comp_name)',
					'display'	=> 'name',
					'as'			=> 'name',
					'width'		=> '1',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				/*array(
					'select'	=> 'UNIX_TIMESTAMP(date_pay)',
					'display'	=> 'date_pay',
					'as'			=> 'date_pay',
					'flags'		=> FLAG_SORT,
					'type'      => 'date',
				),*/
			),
			'from'		=> $this->table,
			'orderby'	=> ' date_issue DESC',
			// всегда передается это
			'params'	=> array('page' => $this->name, 'do' => 'show','client_id'=>$client_id),
			'where'		=> $client_str,
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => true,
		), $this);

		$this->AddStrings($data);

		return $this->Parse($data, $this->name.'.tmpl');
	}
	######################

################################################
#########    Окно печати счета     #############
################################################
    function ShowPrintForm() {
        $id = (int)get('id');
        if ($id) $row['bill'] = $this->getRow($id);
        $row['id']=$id;

        // получаем счет по данному заказу
        $row['bill']['r_comp'] = sql_getRow("SELECT r_comp_fname, r_comp_inn, r_comp_kpp, r_comp_rs, r_comp_ks, r_comp_bank, r_comp_bik, r_comp_addr FROM bills WHERE id=".$row['bill']['id']);
        $row['bill']['r_comp']['kind_of_payment'] = sql_getValue("SELECT kind_of_payment FROM bills_accounts WHERE type='".$row['bill']['type']."'");
        $row['bill']['r_comp']['r_comp_sign'] = sql_getValue("SELECT r_comp_sign FROM bills_accounts WHERE type='".$row['bill']['type']."'");
        if ($row['bill']['client_type'] == 'jur') $row['bill']['sum_pr'] = @$this->SumProp($row['bill']['total']);

        // Заказа
        $order = sql_getRow("SELECT * FROM orders WHERE id=".$row['bill']['order_id']);
        // получаем значение currency
        $row['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$order['currency']."'");
        // получем список товаров по данному заказу
        $row['product_list']=sql_getRows("SELECT * FROM cart as c LEFT JOIN products as p on c.product_id=p.id WHERE c.order_id=".$order['id']);

        $row['order'] = $order;
        $r = $this->getShipping($row, $row['product_list'], $row['bill']['shipping_type']);
        $row['bill'] = $r['bill'];
        $row['product_list'] = $r['product_list'];

        // считаем кол-во заказов
        $row['count']=0;
        foreach ($row['product_list'] as $key=>$item){
            $row['count']+=$item['quantity'];
        }
        $row['host'] = $_SERVER['HTTP_HOST'];
        $row['text'] = $row;
      $this->AddStrings($row);
      return Parse($row, '../templates/print_kvit.html');
    }

    function SumProp($sum)
    {
        // Проверка ввода
        $sum = str_replace(' ','',$sum);
        $sum = trim($sum);
        if ((!(@eregi('^[0-9]*'.'[,\.]'.'[0-9]*$', $sum)||@eregi('^[0-9]+$', $sum)))||($sum=='.')||($sum==',')) return "Это не деньги: $sum";
        // Меняем запятую, если она есть, на точку
        $sum = str_replace(',','.',$sum);
        if($sum >= 1000000000)    return "Максимальная сумма &#151 один миллиард рублей минус одна копейка";

        // Обработка копеек
        $rub = floor($sum);
        $kop = 100*round($sum-$rub,2);
        $kop .= " коп.";
        if (strlen($kop) == 6)    $kop="0".$kop;

        // Выясняем написание слова 'рубль'
        $one = substr($rub, -1);
        $two = substr($rub, -2);
        if ($two > 9 && $two < 21) $namerub = "рублей";
        elseif ($one == 1) $namerub = "рубль";
        elseif ($one > 1 && $one < 5)    $namerub = " рубля";
        else $namerub = "рублей";
        if($rub == "0") return "Ноль рублей $kop";

        //----------Сотни
        $sotni = substr($rub, -3);
        $nums = $this->Number($sotni);
        if ($rub < 1000) return ucfirst(trim("$nums $namerub $kop"));

        //----------Тысячи
        if ($rub < 1000000) $ticha = substr(str_pad($rub,6,"0",STR_PAD_LEFT),0,3);
        else $ticha = substr($rub,strlen($rub)-6,3);
        $one = substr($ticha, -1);
        $two = substr($ticha, -2);
        if ($two > 9 && $two < 21) $name1000 = " тысяч";
        elseif ($one == 1) $name1000 = " тысяча";
        elseif ($one > 1 && $one < 5) $name1000 = " тысячи";
        else $name1000 = " тысяч";
        $numt = $this->Number($ticha);
        if ($one == 1 && $two != 11) $numt = str_replace('один','одна',$numt);
        if ($one == 2) {
            $numt = str_replace('два','две',$numt);
            $numt = str_replace('двед','двад',$numt);
        }
        if  ($ticha!='000') $numt .= $name1000;
        if ($rub<1000000) return ucfirst(trim("$numt $nums $namerub $kop"));

        //----------Миллионы
        $million = substr(str_pad($rub,9,"0",STR_PAD_LEFT),0,3);
        $one = substr($million, -1);
        $two = substr($million, -2);
        if ($two > 9 && $two < 21) $name1000000 = " миллионов";
        elseif ($one == 1) $name1000000 = " миллион";
        elseif ($one > 1 && $one < 5) $name1000000 = " миллиона";
        else $name1000000 = " миллионов";
        $numm = $this->Number($million);
        $numm .= $name1000000;

        return ucfirst(trim("$numm $numt $nums $namerub $kop"));
    }

    function Number($c)
    {
        $arr1 = array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот');
        $arr2 = array('0'=>'','10'=>'десять','11'=>'одиннадцать','12'=>'двенадцать','13'=>'тринадцать','14'=>'четырнадцать','15'=>'пятнадцать','16'=>'шестнадцать','17'=>'семнадцать','18'=>'восемнадцать','19'=>'девятнадцать');
        $arr3 = array('2'=>'двадцать','3'=>'тридцать','4'=>'сорок','5'=>'пятьдесят','6'=>'шестьдесят','7'=>'семьдесят','8'=>'восемдесят','9'=>'девяносто');
        $arr4 = array('','один','два','три','четыре','пять','шесть','семь','восемь','девять');
        $c = str_pad($c,3,"0",STR_PAD_LEFT);
        //---------сотни
        $d[0] = $arr1[$c[0]];
        //--------------десятки
        if ($c[1] == '0' || $c[1] == '1') {$temp = $c[1].$c[2]; $d[1] = $arr2[$temp];}
        else $d[1] = $arr3[$c[1]];
        //--------------единицы
        if ($c[1] != 1) $d[2] = $arr4[$c[2]];
        else $d[2] = "";
        return $d[0].' '.$d[1].' '.$d[2];
    }

    function getShipping($row, $product_list, $status='') {
        $nds = sql_getValue("SELECT value FROM strings WHERE name='NDS' LIMIT 1");
        $nds_type = sql_getValue("SELECT value FROM strings WHERE name='nds_type' LIMIT 1");
        $type = $row['bill']['shipping_type'];

        if ($row['bill']['type'] == 'full' && $status == 'new') {
            $row['bill']['sum'] = 0;
            // Пересчитываем сумму
            foreach ($product_list as $key=>$item){
                $row['bill']['sum'] += $product_list[$key]['price'] * $item['quantity'];
            }

            $row['bill']['tax'] = $nds*$row['bill']['sum'];
            if (intval($nds_type) === 0) {
               $row['bill']['total'] = (1+$nds)*$row['bill']['sum'];
            } else {
                $row['bill']['total'] = $row['bill']['sum'];
                $row['bill']['sum'] = $row['bill']['total']-$nds*$row['bill']['sum'];
            }
        }

        if ($type == 'in_order') {
            // Включаем в стоимость заказа
            if ($status == 'new') $row['bill']['total'] += $row['bill']['shipping'];

        } elseif ($type == 'in_products') {
            // Раскидываем по стоимости продуктов
            $count_product = count($product_list);
            $row['bill']['sum'] = 0;
            foreach ($product_list as $key=>$item){
                $x = ($row['bill']['shipping'] / $count_product) / $item['quantity']; // это сколько нужно накинуть на единицу продукции
                $product_list[$key]['price'] += round($x,2);
                $product_list[$key]['customer_price'] += round($x,2);
                if ($row['bill']['type'] == 'discount')
                    $row['bill']['sum'] += $product_list[$key]['customer_price'] * $item['quantity'];
                else
                    $row['bill']['sum'] += $product_list[$key]['price'] * $item['quantity'];
            }
			$row['bill']['tax'] = $nds*$row['bill']['sum'];

            if (intval($nds_type) === 0) {
               $row['bill']['total'] = (1+$nds)*$row['bill']['sum'];
            } else {
                $row['bill']['total'] = $row['bill']['sum'];
                $row['bill']['sum'] = $row['bill']['total']-$nds*$row['bill']['sum'];
            }
        } else {
        }

        return array('bill' => $row['bill'], 'product_list' => $product_list);
    }

 }

$GLOBALS['bills'] =  & Registry::get('Tbills');

?>