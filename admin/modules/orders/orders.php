<?php

/* $Id: orders.php,v 1.1 2009-02-18 13:09:11 konovalova Exp $
 */

class Torders extends TTable {

	var $name = 'orders';
	var $table = 'orders';

	//-------------------------------------------------------------------------------------

	function Torders() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
            'create_order' => array(
                '�������&nbsp;�����',
                'Create order',
                'link'    	 => 'cnt.createOrder()',
                'img'     	 => 'icon.orders.gif',
                'display'    => 'block',
                'show_title' => true,
            ),
		);
		
	  ########## STATUSES #########

	  $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'				=> array('������',	'orders',),
			'title_one'			=> array('�����',		'order',),
			'total' 			=> array('�����',		'Total',),
			'name'				=> array('���',		'Name'),
			'id'			   	=> array('�',			'id'),
			'company'			=> array('��������',	'company'),
			'status'			=> array('������',	'status'),
			'no_paid'			=> array('�� �������','no paid'),
			'order_date'		=> array('���� ������','order_date'),
			'saved' 			=> array('���������� ������ �������','Good save'),
			'bill'				=> array('����','Bill'),
			'all'   		 	=> array('�� �����','all',),
			'client_id'   	 	=> array('ID �������','Client ID',),
			'Cache'   		 	=> array('��������','Cache',),
			'Bank'   		 	=> array('���������� �������','Bank',),
			'client_comment'   	=> array('�����������','Comment',),
			'address'   		=> array('����� ��������','Address',),
            'shipping'          => array('��������','Shipping',),

			#--------- status ---------#
			
			'new' 		=> array('���������','new',),
			'paid' 		=> array('�������','paid',),
			'canceled' 	=> array('�����������','canceled',),
		));

    	//��� ������� ������(������ �� ������������������� ����� � �������)

		$this->statuses = array(
			'New' 						=> array('�����','New',),
			'ProcessingAwaitingPaiment' => array('� ��������� (���� ������)','ProcessingAwaitingPaiment',),
			'CompletePaid' 		        => array('������������� (�������)','CompletePaid',),
			'CompleteAwaitingPaiment' 	=> array('������������� (���� ������)','CompleteAwaitingPaiment',),
			'ReadyAwaitingPaiment' 		=> array('����� (���� ������)','ReadyAwaitingPaiment',),
			'ReadyPaid' 				=> array('����� (�������)','ReadyPaid',),
			'Delivered' 				=> array('���������','Delivered',),
			'Canceled' 					=> array('�������','Canceled',),
		);

		//������� � ������� ����� ������� ��� �������� �������
		 $this->normal_status = array(
			  'New',
			  'CompleteAwaitingPaiment',
			  'ReadyAwaitingPaiment',
			  'ProcessingAwaitingPaiment',
			  'Canceled',
		 );
		
		 // ��������� ������� ��� ������ ����� ������. 
		 // ����� ������ ������� �� ������������ ������� � ����������, ���������� ������������ �����.
		 // 0 - ���� �� ���������� ������� 
		 // 1 - ���� ���������� �������
		 
		$this->actions = array(
		  'Cache'     => array(
			'0' => array(
				 'New' 				        => array('CompleteAwaitingPaiment','Canceled',),
				 'CompleteAwaitingPaiment'  => array('ReadyAwaitingPaiment','Canceled',),
				 'ReadyAwaitingPaiment' 	=> array('Delivered','Canceled',),
				 'Delivered' 			    => array('',),
				 'Canceled' 		        => array('',),
			),
			'1' => array(
				 'New' 				        => array('CompletePaid','Canceled',),
				 'CompleteAwaitingPaiment'  => array('ReadyPaid','Canceled',),
				 'CompletePaid'			    => array('ReadyPaid','Canceled',),
				 'ReadyAwaitingPaiment' 	=> array('Delivered','Canceled',),
				 'ReadyPaid' 				=> array('Delivered','Canceled',),
				 'Delivered' 			    => array('',),
				 'Canceled' 		        => array('',),
			),
		),
		  'Bank'=> array(
			'0' => array(
				 'New' 						  => array('ProcessingAwaitingPaiment','Canceled',),
				 'ProcessingAwaitingPaiment'  => array('CompletePaid','Canceled',),
				 'CompletePaid' 			  => array('ReadyPaid','Canceled',),
				 'ReadyPaid' 				  => array('Delivered','Canceled',),
				 'Delivered' 				  => array('',),
				 'Canceled' 	   		      => array('',),
				 ),
			'1' => array(
				 'New' 						  => array('CompletePaid','Canceled',),
				 'ProcessingAwaitingPaiment'  => array('CompletePaid','Canceled',),
				 'CompletePaid' 			  => array('ReadyPaid','Canceled',),
				 'ReadyPaid' 				  => array('Delivered','Canceled',),
				 'Delivered' 	    		  => array('',),
				 'Canceled' 				  => array('',),
				 ),
			),
		);


		$str[get_class_name($this)] += $this->statuses;
		
		// ������ ��� ����� ��������������
		$actions[$this->name.'.editform'] = array(
		   'AddProducts' => array(
				'�������� ������',
				'Add Products',
				'link'    => 'cnt.AddProducts()',
				'img'     => 'icon.create.gif',
				'display'    => 'block',
				'show_title' => false,
			),
		   'Delete' => array(
				'�������',
				'Delete',
				'link'	=> 'cnt.Del()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'block',
				'show_title' => false,
			),
			'apply' => array(
				'���������',
				'Apply',
				'link'	=> 'cnt.ApplySubmit();cnt.ApplySubmit2()',
				'img' 	=> 'icon.kb.gif',
				'display'	=> 'block',
				'show_title' => false,
			),
		);
		//������ ��� ������
		$this->bill_actions = array(
			'printbill' => array(
				'�����������&nbsp;����',
				'Print bill',
				'link'	=> 'cnt.PrintSubmit()',
				'img' 	=> 'icon.print.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'deletebill' => array(
				'������������&nbsp;����',
				'Create bill',
				'link'	=> 'cnt.DelBill()',
				'img' 	=> 'icon.vat.gif',
				'display'	=> 'block',
				'show_title' => true,
			),			
			'createbill' => array(
				'��������&nbsp;����',
				'Create bill',
				'link'	=> 'cnt.CreateSubmit()',
				'img' 	=> 'icon.vat.gif',
				'display'	=> 'block',
				'show_title' => true,
			),		
		);
		//������ ��� ��������� �������
		$this->order_actions = array(
			'New' => array(
				'�����',
				'New',
				'link'	=> 'cnt.Switch(\'New\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'ProcessingAwaitingPaiment' => array(
				'�&nbsp;���������&nbsp;(����&nbsp;������)',
				'Processing&nbsp;Awaiting&nbsp;Paiment',
				'link'	=> 'cnt.Switch(\'ProcessingAwaitingPaiment\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'CompletePaid' => array(
				'�������������&nbsp;(�������)',
				'Complete&nbsp;Paid',
				'link'	=> 'cnt.Switch(\'CompletePaid\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'CompleteAwaitingPaiment' => array(
				'�������������&nbsp;(����&nbsp;������)',
				'Complete&nbsp;Awaiting&nbsp;Paiment',
				'link'	=> 'cnt.Switch(\'CompleteAwaitingPaiment\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'ReadyAwaitingPaiment' => array(
				'�����&nbsp;(����&nbsp;������)',
				'Ready&nbsp;Awaiting&nbsp;Paiment',
				'link'	=> 'cnt.Switch(\'ReadyAwaitingPaiment\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'ReadyPaid' => array(
				'�����&nbsp;(�������)',
				'Ready&nbsp;Paid',
				'link'	=> 'cnt.Switch(\'ReadyPaid\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'Delivered' => array(
				'���������',
				'Delivered',
				'link'	=> 'cnt.Switch(\'Delivered\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'Canceled' => array(
				'��������',
				'Canceled',
				'link'	=> 'cnt.Switch(\'Canceled\')',
				'img' 	=> 'icon.desc.gif',
				'display'	=> 'block',
				'show_title' => true,
			),		
		);
		// ���������� ������ ��������� ������
		$actions[$this->name.'.editform'] += $this->order_actions;
		// ���������� ������ ������
		$actions[$this->name.'.editform'] += $this->bill_actions;
					
        $actions[$this->name.'.editformfromclients'] = $actions[$this->name.'.editform'];

	}
	
	//-------------------------------------------------------------------------------------

	function EditStatus() {
		
		 $status = get('to','');
		 $balans = get('bal','');
		 $from   = get('from','');

		 //payment_type
		 if (!empty($status)) {
			if (!in_array($status,$this->normal_status) && $from != 'ReadyPaid' && $from != 'CompletePaid') {
				if ($balans < '0') {
					return "<script>alert('������: �� ������� ������� ������������ �����. �������� ��������.');</script>";
				}
			}
		 $res = sql_query("UPDATE orders SET status='".$status."' WHERE id=".$_GET['id']);
		 $this->emailNotify($_GET['id'], 'orders_status');
		 }
		 
	return "<script>window.parent.top.opener.location.reload();window.parent.location.reload();</script>";
	}

	//-------------------------------------------------------------------------------------

	function EditDel() {
	     $order = sql_getRow("SELECT *,FROM_UNIXTIME(order_date) as order_date FROM orders WHERE id=".$_GET['id']);
        //$order['cart'] = $cart;
        $order['payment_type_display'] = $this->str($order['payment_type']);
        $order['status2'] = $this->str($order['status']);
        $order['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$order['currency']."'");
        global $directories;
        foreach ($directories['shipping_type'] as $k=>$v){
            $order['shipping_types'][$k] = $v;
        }
        $order['delivery_type'] = $directories['delivery_type'][$order['delivery_type']];
		$order['product_list']=sql_getRows("SELECT cart.*,p.*,m.name as manufacturer 
            	FROM cart  
            	LEFT JOIN products as p on cart.product_id=p.id 
            	LEFT JOIN manufacturers as m on m.id=p.manufacturer_id 
            	WHERE cart.order_id=".$order['id']);
            
		SendNotify('ORDER_DELETE_TO_CLIENT', $temp['client_id'], array('data'=>$order));
		sql_query("DELETE FROM orders WHERE id=".$_GET['id']);
	return "<script>alert('�������� ������ �������!');window.parent.top.opener.location.reload();window.parent.top.close();</script>";
	
	}
	
	//-------------------------------------------------------------------------------------
	//-----------------------    ���� ���������     ---------------------------------------
	//-------------------------------------------------------------------------------------

    function EditFormFromClients(){
    	
        $client_id = (int)get('client_id');
        $client = sql_getRow("SELECT * FROM auth_users WHERE id=".$client_id);
        
        $res = sql_query("
        	INSERT INTO orders(
        	client_id, status, currency, 
        	order_date, name, lname, 
        	tname, addr, email,	phone, 
        	cell_phone,	comp_name, comp_inn, 
        	comp_kpp, comp_email, comp_bank, 
        	comp_addr, comp_paddr, comp_bik, 
        	comp_ks, comp_rs, comp_phone, 
        	comp_fax, history)
        	VALUES(
        	'".$client_id."','New','Base',
        	".time().",'".$client['name']."','".$client['lname']."',
        	'".$client['tname']."','".$client['addr']."','".$client['email']."',
        	'".$client['phone']."','".$client['cell_phone']."','".$client['comp_name']."', 
        	'".$client['comp_inn']."','".$client['comp_kpp']."', '".$client['comp_email']."', 
        	'".$client['comp_bank']."', '".$client['comp_addr']."','".$client['comp_paddr']."',
        	'".$client['comp_bik']."', '".$client['comp_ks']."', '".$client['comp_rs']."',
        	'".$client['comp_phone']."','".$client['comp_fax']."','".date("Y-m-d H:i:s")." ��� ������� ������ �����')");
        
        if ($res) {
	        $order = sql_getRow("SELECT *,FROM_UNIXTIME(order_date) as order_date FROM orders WHERE id=".mysql_insert_id());
	        //$order['cart'] = $cart;
	        $order['payment_type_display'] = $this->str($order['payment_type']);
	        $order['status2'] = $this->str($order['status']);
	        $order['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$order['currency']."'");
	        global $directories;
	        foreach ($directories['shipping_type'] as $k=>$v){
	            $order['shipping_types'][$k] = $v;
	        }
	        $order['delivery_type'] = $directories['delivery_type'][$order['delivery_type']];
	        $this->getProductList($order);
				            
			SendNotify('ORDER_CREATE_FROM_USERS', $client_id, array('data'=>$order));
            return "<script>window.location.href('cnt.php?page=orders&do=editform&id=".mysql_insert_id()."&new=1');</script>";
        } else {
            return "<script>alert('������ ��������� ������!');</script>";
        }
        
    }
    
	//-------------------------------------------------------------------------------------

    function EditForm(){
        global $directories;
		$id = (int)get('id');
		if ($id) $row['order'] = $this->getRow($id);
		$row['id']=$id;
		$row['root']=is_root();

        $new = (int)get('new');
        if ($new) {
            $row['new'] = $new;
            $row['payment_types'] = $directories['payment_type'];
        }
        ##################################################
        // ���� ���������� ������
        $row['order']['order_date']=date('d.m.Y', $row['order']['order_date']);
        // ������� ������ ������� �� ������� ������
        if (isset($row['order']['id'])) {
        	$this->getProductList($row, $row['order']['id']);
			
        	/*
            $row['product_list']=sql_getRows("
            	SELECT c.*,p.*,m.name as manufacturer 
            	FROM cart as c 
            	LEFT JOIN products as p on c.product_id=p.id 
            	LEFT JOIN manufacturers as m on m.id=p.manufacturer_id 
            	WHERE c.order_id=".$row['order']['id']);
            // ������� ���-�� ��������� � ������
            $row['count']=0;
               foreach ($row['product_list'] as $key=>$item){
                $row['count']+=$item['quantity'];
            }
            pr($row['product_list']);
            */
            
        }

        ################ �������� #################
        $i = 1;
        if ($row['order']['payment_type'] == 'Bank')
            foreach ($directories['shipping_type'] as $k=>$v){
                $row['shipping_types'][$k] = $v." (".$i.")";
                $i++;
            }
        else $row['shipping_types']['none'] = $directories['shipping_type']['none']." (3)";

        $shipping_type = get('shipping_type',NULL,'g');
        if (!isset($shipping_type)) $shipping_type = $row['order']['shipping_type'];

        $shipping = get('shipping',NULL,'g');
        if (!isset($shipping)) $shipping = $row['order']['shipping'];

        $row['shipping_type'] = $shipping_type;
        $row['shipping'] = $shipping;

        $row['delivery_type'] = $directories['delivery_type'][$row['order']['delivery_type']];
        #############################################

        
		##################### ������ ######################
				//------ ���������� �������� �� ������ �������
		$row['balans']['received']=sql_getValue("SELECT SUM(received) FROM bills WHERE `client_id`=".$row['order']['client_id']." AND `type`!='bonus'");
				//------ ����� ���� ���������� �������
		$row['balans']['orders']=sql_getValue("SELECT SUM(total) - SUM(bonus) FROM orders WHERE `client_id`=".$row['order']['client_id']." AND (`status`='CompletePaid' OR `status`='ReadyPaid' OR `status`='Delivered')");
		//------ ����� �������� ������
		//$row['balans']['bonus']=sql_getValue("SELECT SUM(total) FROM bills WHERE `client_id`=".$row['order']['client_id']." AND `type`='bonus'");
				//------ ������ �������
		$row['balans']['total']=$row['balans']['received']-$row['balans']['orders'];//-$row['balans']['bonus'];
				// �������� ���� �� ������� ������ (������ ���� ����� ���� �� ����������)
		$row['bill'] = sql_getRow("SELECT * FROM bills WHERE status != 'canceled' AND order_id=".$row['id']);
		//pr($row['bill']);
				// �������� �������� currency
		$row['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$row['order']['currency']."'");
		#################### Actions #####################
		if (($row['balans']['total'] - $row['order']['total']*$row['currency']['value']) >= '0' || !in_array($row['order']['status'],$this->normal_status )) {
			$temp = 1;
		}
		else {
			$temp = 0;
		}
		// ������� ������ � ���� ��� ������	
		if ($row['root']) { // ���� �����

            if (empty($row['bill']))
    			$row['upactions'] = '1,1,1';
            else $row['upactions'] = '0,1,1'; // �� ���� ��������� ������, ���� ���� �������

            foreach ($this->actions[$row['order']['payment_type']]['0'] as $key=>$value) {
				$row['actions'][$key] = $this->str($key);
			}
			foreach ($this->actions[$row['order']['payment_type']]['1'] as $key=>$value) {
				$row['actions'][$key] = $this->str($key);
			}

			foreach ($this->order_actions as $key=>$value){
				$row['upactions'] .= ',0';
			}
		}
		else { // ���� ����� ����� ������

            if (empty($row['bill']))
                $row['upactions'] = '1,0,1';
            else $row['upactions'] = '0,0,1'; // �� ���� ��������� ������, ���� ���� �������

			foreach ($this->statuses as $k=>$v) {
				if (in_array($k,$this->actions[$row['order']['payment_type']][$temp][$row['order']['status']])) {
					$row['upactions'] .= ',1';
				}
				else {
					$row['upactions'] .= ',0';
				}
			}
		}

		// ��������� ������ ��� ������
		$row['edit'] = true;
		if ($row['order']['status'] != 'Canceled') {
		  if (!empty($row['bill'])) {
              // ���� ��������� ��������, �� �� ��������� ������������
              if ($row['bill']['received'] > 0)
                  $row['upactions'] .= ',1,0,0';
              else
                  $row['upactions'] .= ',1,1,0';
			  $row['edit'] = false;
		  }
		  else {
			  $row['upactions'] .= ',0,0,1';
		  }
		}
		else {
		  $row['upactions'] .= ',0,0,0';
		}		

		$row['order']['status2'] = $this->str($row['order']['status']);
		
		$row['order']['payment_type_display'] = $this->str($row['order']['payment_type']);
		$row['order']['payment_types'] = $directories['payment_type'];

		// ��������� � ������ ���������� � ������
		$this->AddStrings($row);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	//-------------------------------------------------------------------------------------
	//������������� �����
	function DelBill(){
		$id = get('id', false, 'g');
		$bill = sql_getRow("SELECT * FROM bills WHERE status != 'canceled' AND order_id=".$id);
		
		if ($bill['received']>0){
			return "<script>alert('".$this->str('�� ������� ����� ���� ����������� �������')."');</script>";
		}
		if (sql_query("UPDATE bills SET status='canceled' WHERE id=".$bill['id'])){
			SendNotify('ORDER_DEL_BILL', $bill['client_id'], array('data'=>$bill));
			return "<script>alert('".$this->str('saved')."');window.parent.top.opener.location.reload();window.parent.location.reload();</script>";
		} 
	}

	//-------------------------------------------------------------------------------------
	
################################################
#########    ���� ������ �����     #############
################################################
	function ShowPrintForm() {
		$id = (int)get('id');
		if ($id) $row['order'] = $this->getRow($id);
		$row['id']=$id;
		// �������� ���� �� ������� ������
		$row['bill'] = sql_getRow("SELECT * FROM bills WHERE status!='canceled' AND order_id=".$row['id']);
        $row['bill']['r_comp'] = sql_getRow("SELECT r_comp_fname, r_comp_inn, r_comp_kpp, r_comp_rs, r_comp_ks, r_comp_bank, r_comp_bik, r_comp_addr FROM bills WHERE id=".$row['bill']['id']);
        $row['bill']['r_comp']['kind_of_payment'] = sql_getValue("SELECT kind_of_payment FROM bills_accounts WHERE type='".$row['bill']['type']."'");
        $row['bill']['r_comp']['r_comp_sign'] = sql_getValue("SELECT r_comp_sign FROM bills_accounts WHERE type='".$row['bill']['type']."'");
		$row['bill']['total'] -= $row['bill']['bonus'];
		// ���� ���������� ������
		$row['order']['order_date']=date('d.m.Y', $row['order']['order_date']);
		// �������� �������� currency
		$row['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$row['order']['currency']."'");
		// ������� ������ ������� �� ������� ������
		$row['product_list']=sql_getRows("SELECT * FROM cart as c LEFT JOIN products as p on c.product_id=p.id WHERE c.order_id=".$row['order']['id']);
		// ������� ���-�� �������
		$row['count']=0;
		foreach ($row['product_list'] as $key=>$item){
    		$row['count']+=$item['quantity'];
		}
        $row['host'] = $_SERVER['HTTP_HOST'];
        if ($row['bill']['client_type'] == 'jur') $row['bill']['sum_pr'] = @$this->SumProp($row['bill']['total']);

        $row['text'] = $row;
	  $this->AddStrings($row);
	  return Parse($row, '../templates/print_kvit.html');
	}

    function SumProp($sum)
    {
        // �������� �����
        $sum = str_replace(' ','',$sum);
        $sum = trim($sum);
        if ((!(@eregi('^[0-9]*'.'[,\.]'.'[0-9]*$', $sum)||@eregi('^[0-9]+$', $sum)))||($sum=='.')||($sum==',')) return "��� �� ������: $sum";
        // ������ �������, ���� ��� ����, �� �����
        $sum = str_replace(',','.',$sum);
        if($sum >= 1000000000)    return "������������ ����� &#151 ���� �������� ������ ����� ���� �������";

        // ��������� ������
        $rub = floor($sum);
        $kop = 100*round($sum-$rub,2);
        $kop .= " ���.";
        if (strlen($kop) == 6)    $kop="0".$kop;

        // �������� ��������� ����� '�����'
        $one = substr($rub, -1);
        $two = substr($rub, -2);
        if ($two > 9 && $two < 21) $namerub = "������";
        elseif ($one == 1) $namerub = "�����";
        elseif ($one > 1 && $one < 5)    $namerub = " �����";
        else $namerub = "������";
        if($rub == "0") return "���� ������ $kop";

        //----------�����
        $sotni = substr($rub, -3);
        $nums = $this->Number($sotni);
        if ($rub < 1000) return ucfirst(trim("$nums $namerub $kop"));

        //----------������
        if ($rub < 1000000) $ticha = substr(str_pad($rub,6,"0",STR_PAD_LEFT),0,3);
        else $ticha = substr($rub,strlen($rub)-6,3);
        $one = substr($ticha, -1);
        $two = substr($ticha, -2);
        if ($two > 9 && $two < 21) $name1000 = " �����";
        elseif ($one == 1) $name1000 = " ������";
        elseif ($one > 1 && $one < 5) $name1000 = " ������";
        else $name1000 = " �����";
        $numt = $this->Number($ticha);
        if ($one == 1 && $two != 11) $numt = str_replace('����','����',$numt);
        if ($one == 2) {
            $numt = str_replace('���','���',$numt);
            $numt = str_replace('����','����',$numt);
        }
        if  ($ticha!='000') $numt .= $name1000;
        if ($rub<1000000) return ucfirst(trim("$numt $nums $namerub $kop"));

        //----------��������
        $million = substr(str_pad($rub,9,"0",STR_PAD_LEFT),0,3);
        $one = substr($million, -1);
        $two = substr($million, -2);
        if ($two > 9 && $two < 21) $name1000000 = " ���������";
        elseif ($one == 1) $name1000000 = " �������";
        elseif ($one > 1 && $one < 5) $name1000000 = " ��������";
        else $name1000000 = " ���������";
        $numm = $this->Number($million);
        $numm .= $name1000000;

        return ucfirst(trim("$numm $numt $nums $namerub $kop"));
    }

    function Number($c)
    {
        $arr1 = array('','���','������','������','���������','�������','��������','�������','���������','���������');
        $arr2 = array('0'=>'','10'=>'������','11'=>'�����������','12'=>'����������','13'=>'����������','14'=>'������������','15'=>'����������','16'=>'�����������','17'=>'����������','18'=>'������������','19'=>'������������');
        $arr3 = array('2'=>'��������','3'=>'��������','4'=>'�����','5'=>'���������','6'=>'����������','7'=>'���������','8'=>'����������','9'=>'���������');
        $arr4 = array('','����','���','���','������','����','�����','����','������','������');
        $c = str_pad($c,3,"0",STR_PAD_LEFT);
        //---------�����
        $d[0] = $arr1[$c[0]];
        //--------------�������
        if ($c[1] == '0' || $c[1] == '1') {$temp = $c[1].$c[2]; $d[1] = $arr2[$temp];}
        else $d[1] = $arr3[$c[1]];
        //--------------�������
        if ($c[1] != 1) $d[2] = $arr4[$c[2]];
        else $d[2] = "";
        return $d[0].' '.$d[1].' '.$d[2];
    }

 ################################################
#########    �������� �����        #############
################################################
	function EditBillForm() {
		$id = (int)get('id');
		if ($id) $row['order'] = $this->getRow($id);
		$row['id']=$id;

		 // �������� ���� �� ������� ������
		$row['bill']=sql_getRow("SELECT * FROM bills WHERE status!='canceled' AND order_id=".$row['id']);
		if (!$row['bill']){
			$bill['client_id']=$row['order']['client_id'];
			$bill['order_id']=$row['order']['id'];
			$bill['status']="new";
			$bill['sum']=$row['order']['sum'];
			$bill['tax']=$row['order']['tax'];
			$bill['total']=$row['order']['total'];
			$bill['bonus']=$row['order']['bonus'];
			//$bill['currency']=$row['order']['currency'];
			$bill['purpose']="����� ������ ".$bill['order_id']." �� ����� ".$bill['total'];
			sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$bill['client_id'].',\''.$bill['order_id'].'\',\'order\',\'���������� ������ ���� �� ����� '.$bill['total'].'\',\''.time().'\')');
			$user=sql_getRow("SELECT * FROM auth_users WHERE id=".$bill['client_id']);
	
			switch ($row['order']['payment_type']) {
					case 'Cache':  //��� ������-��������
							  $bill['fiz_name']=$user['name'];
							  $bill['fiz_lname']=$user['lname'];
							  $bill['fiz_tname']=$user['tname'];
							  $bill['fiz_phone']=$user['phone'];
							  $bill['fiz_fax']=$user['fax'];
							  $bill['fiz_email']=$user['email'];
						 break;
					case 'Bank':  //��� ������-����
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
			$r_comp=sql_getRow("SELECT * FROM bills_accounts WHERE type='discount'");
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
			//$key=substr($key,0,strlen($key)-1);
			//$values=substr($values,0,strlen($values)-1);
			sql_query("INSERT INTO bills(".$key.") VALUES(".$values.")");
			//pr("INSERT INTO bills(".$key.") VALUES(".$values.")");
			$row['bill']=sql_getRow("SELECT * FROM bills WHERE status!='canceled' AND order_id=".$row['id']);
		}
		// ���� ���������� ������
		$row['order']['order_date']=date('d.m.Y', $row['order']['order_date']);
		// �������� �������� currency
		$row['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$row['order']['currency']."'");
		// ������� ������ ������� �� ������� ������
		$row['product_list']=sql_getRows("
				SELECT cart.*,p.*,m.name as manufacturer 
            	FROM cart  
            	LEFT JOIN products as p on cart.product_id=p.id 
            	LEFT JOIN manufacturers as m on m.id=p.manufacturer_id 
            	WHERE cart.order_id=".$row['order']['id']);
				
		// ������� ���-�� �������
		$row['count']=0;
		foreach ($row['product_list'] as $key=>$item){
			$row['count']+=$item['quantity'];
		}
		SendNotify('ORDER_CR_BILL', $row['bill']['client_id'], array('data'=>$row));
	  $this->AddStrings($row);
	  return $this->Parse($row, $this->name.'.editprintform.tmpl');
	}
################################################
##########	 ���������� ��������� ##############
################################################

	function Edit() {
		$this->emailNotify($_POST['id'], 'orders_status');
		$res = $this->Commit();
//		if (is_int($res)) return "<script>alert('".$this->str('saved')."');window.parent.top.opener.location.reload();window.parent.location.reload();</script>";
		if (!is_int($res)) return $this->Error($res);
	}
################################################
//---------------- Notify ---------------------
	function emailNotify($id,$template){
		 $temp = sql_getRow("SELECT * FROM orders WHERE id=".$id);
		 $mail_to = '�������:'.$temp['lname']." ".$temp['name']." ".$temp['tname'];
		 $send=array_merge($temp,$_POST['fld']);

		//$send['cart'] = $cart;
        $send['payment_type_display'] = $this->str($send['payment_type']);
        $send['status2'] = $this->str($send['status']);
        $send['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$send['currency']."'");
        global $directories;
        foreach ($directories['shipping_type'] as $k=>$v){
            $order['shipping_types'][$k] = $v;
        }
        $send['delivery_type'] = $directories['delivery_type'][$send['delivery_type']];
		$send['product_list']=sql_getRows("
				SELECT cart.*,p.*,m.name as manufacturer 
            	FROM cart  
            	LEFT JOIN products as p on cart.product_id=p.id 
            	LEFT JOIN manufacturers as m on m.id=p.manufacturer_id 
            	WHERE cart.order_id=".$send['id']);

         $flag=0;   
         if (!isset($_POST['fld']['addr'])){
			 if ($temp['status']!=$_POST['fld']['status']){
				
			 	if($flag==0){SendNotify('ORDER_CH_STATUS', $temp['client_id'], array('data'=>$send));}
				$flag=1;
				sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ������ ������ �� '.$this->str($_POST['fld']['status']).'\',\''.time().'\')');
			 }
			 if ($temp['payment_type']!=$_POST['fld']['payment_type']){
				if($flag==0){SendNotify('ORDER_CH_TYPE_MONEY', $temp['client_id'], array('data'=>$send));}
				$flag=1;
				sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ��� ������ �� '.$this->str($_POST['fld']['payment_type']).'\',\''.time().'\')');
			 }
			 if ($temp['shipping']!=$_POST['fld']['shipping'] | $temp['shipping_type']!=$_POST['fld']['shipping_type']){
			 	if($flag==0){SendNotify('ORDER_CH_TYPE_DELIV', $temp['client_id'], array('data'=>$send));}
				$flag=1;
			 }
			 if ($temp['delivery_type']!=$_POST['fld']['delivery_type']){
				sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ��� �������� �� '.$this->str($_POST['fld']['delivery_type']).'\',\''.time().'\')');
			 }
			 if ($temp['shipping']!=$_POST['fld']['shipping']){
				sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ��������� �������� �� '.$this->str($_POST['fld']['shipping']).'\',\''.time().'\')');
			 }
			 if ($temp['shipping_type']!=$_POST['fld']['shipping_type']){
				sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ��� ��������� �������� �� '.$this->str($_POST['fld']['shipping']).'\',\''.time().'\')');
			 }
         } else {
			 if ($temp['addr'] != $_POST['fld']['addr'] & !empty($_POST['fld']['addr'])){
			 	if($flag==0){SendNotify('ORDER_CH_ADDRES_DELIV', $temp['client_id'], array('data'=>$send));}
				$flag=1;
			 	sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$temp['client_id'].',\''.$_GET['id'].'\',\'order\',\'�������� ������� ����� �������� �� '.$this->str($_POST['fld']['addr']).'\',\''.time().'\')');
			 }
         }
		 
		 
	}
################################################
################################################
##########	 ���������� �������   ##############
################################################
	function table_get_status(&$value, &$column, &$row) {
	 return $this->str($value);
	}
	########################
	function table_get_date(&$value, &$column, &$row) {
	 if ($value!="0") return date('d.m.Y', $value);
	 else return "";
	}
	########################

	########################
	function table_get_statusbill(&$value, &$column, &$row) {
	 $temp=explode("-",$value);
	 if (!empty($temp['1'])){return "� ".$temp[0]." (".$this->str($temp['1']).")";}
	 else {return "���� �� �������";}
	}
	########################

	function Show() {
/*�������� ��������
$users = sql_getRows("SELECT * FROM auth_users");
$events = sql_getRows("SELECT * FROM notify_events WHERE recipient='client'");

foreach ($users as $k=>$v){
	foreach ($events as $k2=>$v2){
		sql_query("REPLACE INTO notify_user(user_id,event_id,notify_id) VALUES(".$v['id'].",".$v2['id'].",'2')");
		pr(sql_getError());
	}
}
*/
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));

		$statuses=array();
		foreach ($this->statuses as $key=>$value){
		$statuses[$key]=$this->str($key);
		}
		$client_id = (int)get('client_id');

		 // ������ �������
		if($client_id) {
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'o.id',
					'display'	=> 'id',
					'as'		=> 'id',
					'width'		=> '1px',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'o.id',
					'display'	=> 'id',
					'as'		=> 'id',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'			=> 'o.status',
					'display'			=> 'status',
					'as'				=>'status',
					'type'				=>'status',
					'flags'	  			=> FLAG_FILTER | FLAG_SORT,
					'filter_display'	=> 'status',
					'filter_type'		=> 'array',
					'filter_value'		=> array('' => $this->str('all'))+$statuses,
					'filter_field' 		=> 'o.status',
					'filter_str'		=> false,
					'width'				=> '1px',
				),
				array(
					'select'	=> 'o.order_date',
					'display'	=> 'order_date',
					'as'		=>'order_date',
					'type'		=>'date',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'CONCAT(o.total,"(",o.bonus,")")',
					'display'	=> 'total',
					'as'		=> 'total',
					'nowrap'		=> true,
				),
				array(
					'select'	=> 'if (b.status is null,CONCAT(b2.id,"-",b2.status) , CONCAT(b.id,"-",b.status)) ',
					'display'	=> 'bill',
					'as'		=>'bill',
					'type'		=>'statusbill',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'from' => $this->table.' AS o 
			LEFT JOIN auth_users AS c ON o.client_id=c.id 
			LEFT JOIN bills AS b ON o.id=b.order_id AND b.status IN ("new","paid")
			LEFT JOIN bills AS b2 ON o.id=b2.order_id AND b2.status="canceled"',
			'groupby'	=> 'o.id',			
			'orderby'	=> ' o.order_date DESC ',
			// ������ ���������� ���
			'params'	=> array('page' => $this->name, 'do' => 'show','client_id'=>$client_id),
			'where'		=> 'c.id='.get('client_id'),
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => 1,
		), $this);
		}
		else {
			
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'		=> 'o.id',
					'display'	=> 'id',
					'as'			=> 'id',
					'width'		=> '1px',
					'type'		=> 'checkbox',
				),
				array(
					'select'		=> 'o.id',
					'display'	=> 'id',
					'as'			=> 'id',
					'width'		=> '1px',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'				=> 'o.status',
					'display'			=> 'status',
					'as'					=>'status',
					'type'				=>'status',
					'flags'	  			=> FLAG_FILTER | FLAG_SORT,
					'filter_display'	=> 'status',
					'filter_type'		=> 'array',
					'filter_value'		=> array('' => $this->str('all'))+$statuses,
					'filter_field' 	=> 'o.status',
					'filter_str'		=> false,
					'width'				=> '1px',
				),
				array(
					'select'		=> 'o.order_date',
					'display'	=> 'order_date',
					'as'			=>'order_date',
					'type'		=>'date',
					'flags'		=> FLAG_SORT,
					'width'		=> '1px',
				),
				array(
					'select'	=> 'CONCAT(o.total,"(",o.bonus,")")',
					'display'	=> 'total',
					'as'			=> 'total',
					'width'		=> '1px',
					'nowrap'		=> true,
				),
                array(
                    'select'        => 'o.shipping',
                    'display'    => 'shipping',
                    'as'            => 'shipping',
                    'width'        => '1px',
                ),
				array(
					'select'	=> 'c.id',
					'display'	=> 'client_id',
					'as'		=> 'client_id',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
					'width'		=> '1px',
				),
				array(
					'select'	=> 'CONCAT(c.lname,CHAR(32),c.name)',
					'display'	=> 'name',
					'as'		=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'c.comp_name',
					'display'	=> 'company',
					'as'		=>'company',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'	=> 'if (b.status is null,CONCAT(b2.id,"-",b2.status) , CONCAT(b.id,"-",b.status)) ',
					'display'	=> 'bill',
					'as'		=>'bill',
					'type'		=>'statusbill',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 'o.addr',
					'display'	=> 'address',
				),
				array(
					'select'	=> 'o.client_comment',
					'display'	=> 'client_comment',
				),
			),
			'from' => $this->table.' AS o 
			LEFT JOIN auth_users AS c ON o.client_id=c.id 
			LEFT JOIN bills AS b ON o.id=b.order_id AND b.status IN ("new","paid")
			LEFT JOIN bills AS b2 ON o.id=b2.order_id AND b2.status="canceled"',
			'groupby'	=> 'o.id',
			'orderby'	=> ' o.order_date DESC ',
			// ������ ���������� ���
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'where'		=> '',
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => true,
		), $this);

		}

        $data['client_id'] = $client_id;
		$this->AddStrings($data);

		return $this->Parse($data, $this->name.'.tmpl');
	}

	########################
	
	function ShowProcessing() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));

		 // ������ �������
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'		=> 'o.id',
					'display'	=> 'id',
					'as'			=> 'id',
					'width'		=> '1px',
					'type'		=> 'checkbox',
				),
				array(
					'select'		=> 'o.id',
					'display'	=> 'id',
					'as'			=> 'id',
					'width'		=> '1px',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'				=> 'o.status',
					'display'			=> 'status',
					'as'					=>'status',
					'type'				=>'status',
					'flags'	  			=> FLAG_SORT,
					'filter_str'		=> false,
				),
				array(
					'select'		=> 'o.order_date',
					'display'	=> 'order_date',
					'as'			=>'order_date',
					'type'		=>'date',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'		=> 'o.total',
					'display'	=> 'total',
					'as'			=> 'total',
				),
				array(
					'select'	=> 'if (b.status is null,CONCAT(b2.id,"-",b2.status) , CONCAT(b.id,"-",b.status)) ',
					'display'	=> 'bill',
					'as'			=>'bill',
					'type'		=>'statusbill',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'from' => $this->table.' AS o 
			LEFT JOIN auth_users AS c ON o.client_id=c.id 
			LEFT JOIN bills AS b ON o.id=b.order_id AND b.status IN ("new","paid")
			LEFT JOIN bills AS b2 ON o.id=b2.order_id AND b2.status="canceled"',
			'groupby'	=> 'o.id',
			'orderby'	=> '',
			// ������ ���������� ���
			'params'	=> array('page' => $this->name, 'do' => 'showpro'),
			'where'		=> 'c.id='.get('client_id').'
			AND 
			(
			o.status="ProcessingAwaitingPaiment" OR
			o.status="CompletePaid" OR
			o.status="CompleteAwaitingPaiment" OR
			o.status="ReadyAwaitingPaiment" OR
			o.status="ReadyPaid")
			',
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
		'_sql' => true,
		), $this);

		$this->AddStrings($data);

		return $this->Parse($data, $this->name.'.tmpl');
	}
	
	######################
	
    ################################################
    ########## �������� ������� �� �� ##############
    ################################################
    function getCart($order_id){
        $this->getProductList($temp, $order_id);
        //pr($temp);
    	/*$rows = sql_getRows("SELECT DISTINCT p.id, p.discount_group_id, c.quantity, c.price, c.customer_price FROM products AS p
                                                LEFT JOIN cart AS c ON c.product_id=p.id
                                                WHERE c.order_id=".$order_id);
        
		*/
    	$rows = $temp['product_list'];
        $cart['order_id'] = $order_id;
        // ������� ���-�� �������
        foreach ($rows as $key=>$val) {
            $cart['ids'][$val['nid']] = $rows[$key];
        }
        $cart = $this->calculateCart($cart);
      
        return $cart;
    }

    ################################################
    ##########     �������� ������      ############
    ################################################

	function calculateCart($cart){
        // ���������� ����� ����������
        $cart['all_sum'] = 0;
        $cart['all_count'] = 0;

        // ������� ���-�� � ����� ������� ������
		foreach ($cart['ids'] as $key => $val) {
			$cart['ids'][$key]['sum'] = $val['quantity']*$val['customer_price'];
			$cart['ids'][$key]['sum'] = str_replace(",",".",$cart['ids'][$key]['sum']);
		}

		// ����� ����� � ���-��
		foreach ($cart['ids'] as $key => $val) {
			$cart['all_sum'] += $val['sum'];
			$cart['all_count'] += $val['quantity'];
		}
        $cart['all_sum'] = str_replace(",",".",$cart['all_sum']);
        return $cart;
    }

    function Editset(){
        $order_id = $_POST['id'];
       
        $products = $_POST['fld']['products'];
		if (isset($_POST['fld']['otkat'])){
			sql_query("UPDATE orders SET otkat=".$_POST['fld']['otkat']." WHERE id=".$order_id);
		}
        if (!empty($products)){
            $cart = $this->getCart($order_id);
            
	        $client_id = sql_getValue("SELECT client_id FROM orders WHERE id=".$order_id);
	        foreach ($products as $key => $val) {
	            $cart['ids'][$key]['quantity'] = $val['quantity'];
	        }
	
	        $cart = $this->calculateCart($cart);
	        $this->SaveOrder($cart);
	            
	        $order = sql_getRow("SELECT *,FROM_UNIXTIME(order_date) as order_date FROM orders WHERE id=".$order_id);
	        $order['cart'] = $cart;
	        $order['payment_type_display'] = $this->str($order['payment_type']);
	        $order['status2'] = $this->str($order['status']);
	        $order['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$order['currency']."'");
	        global $directories;
	        foreach ($directories['shipping_type'] as $k=>$v){
	            $order['shipping_types'][$k] = $v;
	        }
	        $order['delivery_type'] = $directories['delivery_type'][$order['delivery_type']];
	        $this->getProductList($order);
			
			SendNotify('ORDER_CH_ORDER', $client_id, array('data'=>$order));
        }
        return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
    }
    
    function getProductList(&$order, $id = 0){
    	if (!$id){ $id = $order['id'];}
    	
    	$sql = "SELECT if (num != 0 ,CONCAT(p.id,'_',c.num), p.id) as nid,c.quantity,c.num, c.customer_price,c.discount,p.*, m.name as manufacturer 
		FROM cart AS c 
		LEFT JOIN products AS p ON p.id = c.product_id
		LEFT JOIN orders AS o ON o.id = c.order_id
		LEFT JOIN manufacturers as m ON p.manufacturer_id = m.id
		WHERE c.order_id = ".$id;
	
		$pr = sql_getRows($sql,true);
	
		//if (empty($pr)){redirect('/cabinet/registration');}
		
		$str = "";
		foreach ($pr as $k=>$v){
			if ($v['num'] != 0){
				//�������� ������ � �������������� ��������
				$str .= " OR ( order_id = '".$id."' AND product_id = '".$v['id']."' AND num = '".$v['num']."' )";
			}
		}
		$ism = array();
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
				$ism[$v['product_id']."_".$v['num']] = true;
			}		
		}
		
		$price = 0;
		$count = 0;
		foreach ($pr as $k=>$v){
			$price += $pr[$k]['customer_price'] * $pr[$k]['quantity'];
			$count += $pr[$k]['quantity'];
			if (in_array($k, array_keys($ism))){
				$pr[$k]['name'] = $pr[$k]['name'].")";
			}	
		}
	
    	$order['product_list'] = $pr;
    	$order['count'] = $count;
            
    }

    function GetDiscountPrice($product_id,$client_id){
        $product = sql_getRow("SELECT price,discount_group_id FROM products WHERE id=".$product_id);
        $user_discount_group_id = sql_getValue("SELECT group_id FROM auth_users_groups WHERE user_id=".$client_id);
        $product_groups = sql_getRows("SELECT id FROM product_discount_groups ORDER BY id");

        if (!empty($user_discount_group_id)) {
            $auth_group_type = sql_getValue("SELECT type FROM auth_groups WHERE id=".$user_discount_group_id);
            if ($auth_group_type == 'fixed') {
                foreach ($product_groups as $key=>$val) {
                    $d = sql_getValue("SELECT discount FROM discounts WHERE user_discount_group_id=".$user_discount_group_id." AND product_discount_group_id=".$val);
                    $discounts_array[$val] = $d ? $d : 0;
                }
            } elseif ($auth_group_type == 'volume') {
                $volume = sql_getValue("SELECT SUM(total) FROM orders WHERE (status='Delivered' OR status='ProcessingPaid' OR status='ReadyPaid' ) AND client_id=".$client_id);
                if (!$volume) $volume = 0;
                $discount = sql_getValue("SELECT discount FROM discounts_volume WHERE auth_group_id=".$user_discount_group_id." AND volume <= ".$volume." ORDER BY volume DESC LIMIT 1");
                if (!$discount) $discount = 0;
                foreach ($product_groups as $key=>$val) {
                    $discounts_array[$val] = $discount;
                }
            }
        } else {
            foreach ($product_groups as $key=>$val) {
                $discounts_array[$val] = 0;
            }
        }
        $str = $product['price']*(1-$discounts_array[$product['discount_group_id']]);
        return str_replace(",",".",$str);
    }
    ################################################
    ##########     ���������� �������   ############
    ################################################
	function EditProducts(){
		$order_id = $_POST['id'];
		$ids = $_POST['ids'];
        $cart = $this->getCart($order_id);
        $client_id = sql_getValue("SELECT client_id FROM orders WHERE id=".$order_id);

        foreach ($ids as $key=>$val) {
            $cart['ids'][$val] = sql_getRow("SELECT id,discount_group_id,price FROM products WHERE id=".$val);
            $cart['ids'][$val]['customer_price'] = $this->GetDiscountPrice($val,$client_id);
            $cart['ids'][$val]['quantity'] = 1;
            $cart['ids'][$val]['id'] = $val;
        }
		$cart = $this->calculateCart($cart);
        $this->SaveOrder($cart);
        
        $order = sql_getRow("SELECT *,FROM_UNIXTIME(order_date) as order_date FROM orders WHERE id=".$order_id);
        $order['cart'] = $cart;
        $order['payment_type_display'] = $this->str($order['payment_type']);
        $order['status2'] = $this->str($order['status']);
        $order['currency']=sql_getRow("SELECT * FROM currency WHERE name='".$order['currency']."'");
        global $directories;
        foreach ($directories['shipping_type'] as $k=>$v){
            $order['shipping_types'][$k] = $v;
        }
        $order['delivery_type'] = $directories['delivery_type'][$order['delivery_type']];
		$order['product_list']=sql_getRows("SELECT cart.*,p.*,m.name as manufacturer 
            	FROM cart  
            	LEFT JOIN products as p on cart.product_id=p.id 
            	LEFT JOIN manufacturers as m on m.id=p.manufacturer_id 
            	WHERE cart.order_id=".$order['id']);
            
        SendNotify('ORDER_CH_ORDER', $client_id, array('data'=>$order));
		return "<script>alert('".$this->str('saved')."');window.top.opener.location.reload();window.top.close();</script>";
    }

    ################################################
    ##########     ���������� � ��    ##############
    ################################################
	function SaveOrder($cart){
		foreach ($cart['ids'] as $key=>$val) {
			if (strpos($key, '_')) {
            		$t = explode('_', $key);
            		$product_id = $t[0];
            		$num = $t[1];
        	} else {
        		$product_id = $key;
        	}
            if ($val['quantity'] > 0){            	
            	if (sql_getValue("SELECT order_id FROM cart WHERE order_id=".$cart['order_id']." AND product_id=".$product_id.(isset($num)?" AND num=".$num:""))){
					$res = sql_query("UPDATE cart SET quantity=".$val['quantity'].", price=".$val['price'].", customer_price=".$val['customer_price']." WHERE order_id=".$cart['order_id']." AND product_id=".$product_id.(isset($num)?" AND num=".$num:""));            		
            	} else {
                	$res = sql_query("INSERT INTO cart (`order_id`, `product_id`, ".(isset($num)?" `num`, ":"")."`quantity`, `price`, `customer_price`) VALUES ('".$cart['order_id']."', '".$product_id."', ".(isset($num)?" '".$num."',":"")."'".$val['quantity']."', '".$val['price']."', '".$val['customer_price']."' )");
            	}
            }
            else { $res = sql_query("DELETE FROM cart WHERE order_id=".$cart['order_id']." AND product_id=".$product_id.(isset($num)?" AND num=".$num:""));}
            
            if (!$res) return "<script>alert('".$this->str('error').": ".sql_getError()."');</script>";
        }
        // ��������� ������� orders
        $nds = sql_getValue("SELECT value FROM strings WHERE name='NDS' LIMIT 1");
        $nds_type = sql_getValue("SELECT value FROM strings WHERE name='nds_type' LIMIT 1");
        if (intval($nds_type) === 0) {
            $sum = $cart['all_sum'];
            $total = (1+$nds)*$cart['all_sum'];
            $tax = $nds*$cart['all_sum'];
        } else {
            $total = $cart['all_sum'];
//            $sum = $total-$nds*$cart['all_sum'];
            $sum = $total/(1+$nds);
            $tax = $total-$sum;
        }
        $res = sql_query("UPDATE orders SET total=".str_replace(",",".",$total).",tax=".str_replace(",",".",$tax).",sum=".str_replace(",",".",$sum)." WHERE id=".$cart['order_id']);
        if (!$res) return "<script>alert('".$this->str('error').": ".sql_getError()."');</script>";
//        else "<script>window.location='cnt.php?page=orders&do=editform&id=".$cart['order_id']."';</script>";
    }

}

$GLOBALS['orders'] = & Registry::get('Torders');

?>