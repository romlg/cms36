<?php

include_once 'modules/cabinet/cabinet.class.php';

class TBills extends TCabinet {

    var $bill_methods = array(
        'Bank'      => 'Банковский перевод',
    );
    var $client_types = array(
        "fiz" => "Физического лица",
        "jur" => "Юридического лица"
    );
	var $statuses = array(
	   'new'   =>'Новый',
	   'paid'  =>'Оплаченный',
	   'canceled'=>'Отменен'
	);
	var $month = array('1'=>'января','2'=>'февраля','3'=>'марта','4'=>'апреля','5'=>'мая','6'=>'июня','7'=>'июля','8'=>'августа','9'=>'сентября','10'=>'октября','11'=>'ноября','12'=>'декабря');
    var $colors = array(
   		'new'						=>	'Green',
		'paid'	                    =>	'Black',
		'canceled'	                =>	'Gray',
    );
    var $tmpl = array(
        'Bank'  => array(
            'fiz'   => 'bill_fiz',
            'jur'   => 'bill_jur',
        ),
    );
    var $fields = array(
        'jur'   => array('fio2', 'comp_name','comp_fname','comp_inn','comp_kpp','comp_zip','comp_addr','comp_paddr','comp_phone','comp_fax','comp_email','comp_bank','comp_bik','comp_ks','comp_rs'),
        'fiz'   => array('fio','phone','email','addr'),
    );

    /**
     * Удаление счета
     *
     * @param integer $bill_id
     */
    function deleteBill($bill_id) {
        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

		// Проверяем принадлежность
		if ($client['id'] != sql_getValue('SELECT client_id FROM bills WHERE id='.$bill_id)) {
		    redirect("/cabinet/bills/");
		}

	    $page = & Registry::get('TPage');

        // Подготовка данных для отправки письма
        $bill = sql_getRow('SELECT * FROM bills WHERE id='.$bill_id);
        $bill['r_comp'] = array();
        foreach ($bill as $key=>$val) {
            if ($key != 'r_comp' && substr($key, 0, strlen('r_comp_')) == 'r_comp_') $bill['r_comp'][$key] = $val;
        }

        $tmpl = $this->getParseBill($this->tmpl[$bill['method']][$bill['client_type']], $bill);

        require_once('./modules/pclzip.lib.php');
        $dir = getcwd();
        chdir(dirname($tmpl));
        $zip = new PclZip('bill.zip');
        $zip->create(basename($tmpl));
        chdir($dir);

		$res = sql_delete('bills', $bill_id);
		if ($res) {
		    // Уведомление для администратора
            sendEmail(
                $page->tpl->get_config_vars('admin_email'),
                $page->tpl->get_config_vars('robot_email'),
                $page->tpl->get_config_vars('cabinet_delete_bill_subj'),
                $page->tpl->get_config_vars('cabinet_delete_bill_mail'),
                $bill,
                PATH_CACHE.'tmp/'.session_id().'/bill.zip'
            );
            redirect("/cabinet/bills/?msg=cabinet_msg_bill_delete_success");
        }
		else redirect("/cabinet/bills/?msg=msg_fail");
    }

    /**
     * Список счетов или счет подробно
     *
     * @param array $params
     * @return string
     */
    function showBills($params){
		$auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();
		if (isset($params['bill_id'])) {
            // Если счет принадлежит не этому клиенту - отправляем обратно
			$client_id = sql_getValue("SELECT client_id FROM bills WHERE id=".$params['bill_id']);
			if ($client_id != $client['id']) redirect("/cabinet/bills/?msg=cabinet_msg_bill_not_found");
			if (isset($params['act']) && $params['act'] == 'delete')  // Удаляем счет
                $this->deleteBill($params['bill_id']);
			else // Собираем информацию о выбранном для просмотра счете
				$list = $this->getInfo($params['bill_id']);
		} else { // Выводим список всех счетов
			$bill_list = sql_getRows("SELECT * FROM bills WHERE client_id=".$client['id']." ORDER BY date_issue DESC");
			if (count($bill_list)) foreach ($bill_list as $key=>$val) {
				$bill_list[$key]['status_display'] = $this->statuses[$val['status']];
                $bill_list[$key]['client_type_display'] = $this->client_types[$val['client_type']];

				// Проверяем, можно ли клиенту удалить счет
				$bill = sql_getValue("SELECT b.id FROM bills AS b
									LEFT JOIN billing AS o ON o.bill_id=b.id
									WHERE (b.status = 'canceled' AND o.id IS NULL)
									AND b.id=".$val['id']);
				if (!empty($bill)) $bill_list[$key]['act'] = 'delete';
			}
		}
		$tpl_obj = & Registry::get('TTemplate');
		$tpl_obj->assign(array('bill_list' 		=> isset($bill_list) ? $bill_list : NULL,
							   'product_list' 	=> isset($list['product_list']) ? $list['product_list'] : NULL,
							   'client_list' 	=> isset($list['client_list']) ? $list['client_list'] : NULL,
							   'count'	 		=> isset($list['count']) ? $list['count'] : NULL,
							   'bill'			=> isset($list['bill']) ? $list['bill'] : NULL,
                               'order'          => isset($list['order']) ? $list['order'] : NULL,
							   'currency'		=> isset($currency) ? $currency : NULL,
                               'colors'         => $this->colors,
							   ));
		if (isset($params['bill_id'])) {
            $ret = $tpl_obj->fetch('about_bill.html');
		} else {
            $ret = $tpl_obj->fetch('my_bills.html');
		}
        return $ret;
    }

    /**
     * Создание нового счета - маршрутизатор
     *
     * @param array $params
     * @return string
     */
    function newBill($params){
        if (empty($params['act'])) $params['act'] = 'step1';
        if (isset($params['act'])) {
            switch ($params['act']) {
                case 'step1': return $this->newBill_step1($params); break; // Спрашиваем способ пополнения счета и на какую сумму
                case 'step2': return $this->newBill_step2($params); break; // Запрашиваем данные пользователя
                case 'step3': return $this->newBill_step3($params); break; // Показываем информацию о счете и просим подтвердить
                case 'step4': return $this->newBill_step4($params); break; // Добавляем запись в БД
                case 'step5': return $this->newBill_step5($params); break; // Предлагаем печатать
            }
        }
	}

	/**
	 * Шаг 1 - Спрашиваем способ пополнения счета и на какую сумму
	 *
	 * @param array $params
	 * @return string
	 */
    function newBill_step1($params){
		$page = & Registry::get('TPage');
		$page->content['elem_text']['text'] = $page->tpl->get_config_vars('cabinet_createbill_step1');

        $form = new TForm(null, $this);
		$form->form_name='cabinet';

		$elements = array(
			'method'	=>	array(
				'name'		=> 	'method',
				'type'		=> 	'select',
				'options'	=>  $this->bill_methods,
				'value'		=>	'Bank',
				'group'		=>	'options'
			),
			'client_type'	=>	array(
				'name'		=> 	'client_type',
				'type'		=> 	'radio',
				'display'   =>  'inline',
				'options'	=>  $this->client_types,
				'value'		=>	'fiz',
				'group'		=>	'options',
				'atrib'     =>  'onclick="changeClientType(this.value)"',
			),
			'sum'	=>	array(
				'name'		=> 	'sum',
				'type'		=> 	'text',
				'group'		=>	'options',
				'req'       =>  1,
			),
		);

        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

		$client['email'] = $client['comp_email'] = $client['login'];

		if (!empty($client['bill_fio'])) {
		    // Специальное ФИО для счетов
		    $client['fio2'] = $client['fio'] = $client['bill_fio'];
		}
		else $client['fio2'] = $client['fio'] = '';

		if (!empty($client['bill_phone'])) {
		    // Специальный телефон для счетов
		    $client['phone'] = $client['bill_phone'];
		}

		if (!empty($client['bill_comp_name'])) {
		    // Специальная компания для счетов
		    $client['comp_name'] = $client['bill_comp_name'];
		}

		foreach ($this->fields as $client_type => $fields) {
		    foreach ($fields as $field) {
    		    $elements[$field] = array(
       				'name'		=> 	$field,
       				'type'		=> 	'text',
       				'value'		=>	isset($_POST['fld'][$field]) ? str_replace('"', '&quot;', $_POST['fld'][$field]) : str_replace('"', '&quot;', $client[$field]),
       				'group'		=>	'options_'.$client_type,
       				'req'       =>  $client_type == 'fiz' ? 1 : (!in_array($field, array('fio2', 'comp_name', 'comp_kpp', 'comp_paddr', 'comp_phone', 'comp_fax')) ? 1 : 0),
       				'atrib'     =>  'style="width: 300px"'
    			);
		    }
		}

		$elements['button'] = array(
				'name'		=> 	'button',
				'type'		=> 	'submit',
				'value'		=>	'Далее',
				'group'		=>	'system',
			);
		$elements['act'] = array(
				'name'	=>	'act',
				'type'	=>	'hidden',
				'group'	=>	'system',
				'value'	=>	'step1',
			);

		$form->elements = $elements;

		$form->action = '/cabinet/bills/new_bill';

		$client_type = isset($_POST['fld']['client_type']) ? (is_array($_POST['fld']['client_type']) ? current($_POST['fld']['client_type']) : $_POST['fld']['client_type']) : 'fiz';

		if (isset($_POST['fld'])) foreach ($this->fields[$client_type == 'fiz' ? 'jur' : 'fiz'] as $field) {
		    $form->elements[$field]['req'] = 0;
		}

		$fdata = $form->generate();

		$fdata['form']['width'] = '545';
		$fdata['form']['script'] = "
		{literal}
		<script type='text/javascript'>
		window.onload = function () {
    		var radios = document.forms['cabinet'].elements['fld[client_type][]'];
    		for (var i=0; i < radios.length; i++) {
                if (radios[i].checked) {
                    changeClientType(radios[i].value);
                    break;
                }
    		}
        }
		</script>
		{/literal}
		";

		if (isset($_POST['fld']) && empty($fdata['form']['errors'])) {
		    $params['act'] = 'step3';
		    return $this->newBill_step3($params);
		}

		$page->tpl->assign(array('fdata' 			=> $fdata,
						));
		$ret = $page->tpl->fetch('form.html');
        return $ret;
    }

    /*
    Закомментировала, т.к. решили совместить 1-й и 2-й шаг
    */

	/**
	 * Шаг 2 - Запрашиваем данные пользователя
	 *
	 * @param array $params
	 * @return string
	 */
    /*function newBill_step2($params){
        $_POST['fld']['sum'] = doubleval($_POST['fld']['sum']);
        if ($_POST['fld']['sum'] <= 0) redirect('/cabinet/bills/new_bill/');
        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

		$page       = & Registry::get('TPage');
		$page->content['elem_text']['text'] = $page->tpl->get_config_vars('cabinet_createbill_step2');

		foreach ($_POST['fld'] as $key=>$val) {
		    if (is_array($val)) $_POST['fld'][$key] = h(current($val));
		    else $_POST['fld'][$key] = h($val);
		}

		$elements = array();
		$client_type = is_array($_POST['fld']['client_type']) ? current($_POST['fld']['client_type']) : $_POST['fld']['client_type'];
		foreach ($this->fields[$client_type] as $field) {
		    $elements[$field] = array(
   				'name'		=> 	$field,
   				'type'		=> 	'text',
   				'value'		=>	isset($_POST['fld'][$field]) ? $_POST['fld'][$field] : $client[$field],
   				'group'		=>	'options',
   				'req'       =>  1,
   				'atrib'     =>  'style="width: 300px"'
			);
		}

		$elements['sum'] = array(
    		'name'		=> 	'sum',
    		'type'		=> 	'hidden',
    		'value'		=>	$_POST['fld']['sum'],
		);
		$elements['client_type'] = array(
    		'name'		=> 	'client_type',
    		'type'		=> 	'hidden',
    		'value'		=>	is_array($_POST['fld']['client_type']) ? current($_POST['fld']['client_type']) : $_POST['fld']['client_type']
		);
		$elements['method'] = array(
    		'name'		=> 	'method',
    		'type'		=> 	'hidden',
    		'value'		=>	is_array($_POST['fld']['method']) ? current($_POST['fld']['method']) : $_POST['fld']['method']
		);

        $form = new TForm(null, $this);
		$form->form_name = 'cabinet';
		$form->elements = $elements;
		$form->elements['button'] = array(
			'name'		=> 	'button',
			'type'		=> 	'submit',
			'value'		=>	'Далее',
			'group'		=>	'system',
        );
        $form->elements['act'] = array(
			'name'	=>	'act',
			'type'	=>	'hidden',
			'group'	=>	'system',
			'value'	=>	'step2',
		);
		$form->action = '/cabinet/bills/new_bill';
		$fdata = $form->generate();
		if (!isset($_POST['fld'][$this->fields[$client_type][0]])) $fdata['form']['errors'] = array();
		elseif (empty($fdata['form']['errors'])) {
		    $params['act'] = 'step3';
		    return $this->newBill_step3($params);
		}
		$page->tpl->assign(array('fdata' 			=> $fdata,
						));
		$ret = $page->tpl->fetch('form.html');
        return $ret;
    }*/

	/**
	 * Шаг 3 - Показываем информацию о счете и просим подтвердить
	 *
	 * @param array $params
	 * @return string
	 */
    function newBill_step3($params){
        $_POST['fld']['sum'] = doubleval($_POST['fld']['sum']);
        if ($_POST['fld']['sum'] <= 0) redirect('/cabinet/bills/new_bill/');

        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

        $page       = & Registry::get('TPage');
		$page->content['elem_text']['text'] = $page->tpl->get_config_vars('cabinet_createbill_step3');

		$nds 		= $page->tpl->get_config_vars("NDS");
		$nds_type	= (int)$page->tpl->get_config_vars("nds_type");
		$sum        = $_POST['fld']['sum'];
        $bill       = $this->calcBill($_POST['fld']['sum'], $nds, $nds_type);
		if ($nds_type == '0') $bill['sum_pr'] = $sum*(1+$nds);
		else $bill['sum_pr'] = $sum;
		$bill['sum_pr'] = $this->SumProp($bill['sum_pr']);

		foreach ($_POST['fld'] as $key=>$val) {
		    if (!is_array($val)) $_POST['fld'][$key] = $val = str_replace('"', '&quot;', $val);
		    if ($key != 'sum') $bill[$key] = is_array($val) ? current($val) : $val;
		}
		$bill['client_id'] = $client['id'];

        $date = getdate();
		$bill['d'] = $date['mday'];
		$bill['m'] = $this->month[$date['mon']];
		$bill['Y'] = $date['year'];

		$bill['r_comp'] = sql_getRow("SELECT * FROM bills_r_accounts");
		$bill['id'] = sql_getValue("SELECT MAX(id) FROM bills");

		if (isset($bill['fio2']) && !empty($bill['fio2'])) $bill['fio'] = $bill['fio2'];

		$tpl_obj = & Registry::get('TTemplate');

		$tmpl = $this->getParseBill($this->tmpl[$bill['method']][$bill['client_type']], $bill);
		$about_bill = file_get_contents($tmpl);

		$tpl_obj->assign(array( 'about_bill' 	=>	$about_bill,
								'act'			=>	$params['act'],
								'hidden'        =>  $_POST['fld'],
								'bill_type'     =>  $bill['client_type'],
						));
		$ret = $tpl_obj->fetch('about_new_bill.html');
        return $ret;
    }

	/**
	 * Шаг 4 - Добавляем запись в БД
	 *
	 * @param array $params
	 * @return string
	 */
	function newBill_step4($params){
        $_POST['sum'] = doubleval($_POST['sum']);
        if ($_POST['sum'] <= 0) redirect('/cabinet/bills/new_bill/');

        $auth_obj = & Registry::get('TUserAuth');
        $client = $auth_obj->getCurrentUserData();

        $bill_id = $_POST['bill_id'];

        if (!$bill_id) {

            $page       = & Registry::get('TPage');
            $nds 		= $page->tpl->get_config_vars("NDS");
    		$nds_type	= (int)$page->tpl->get_config_vars("nds_type");

    		$bill = $this->calcBill($_POST['sum'], $nds, $nds_type);

            $fields = array(
                'client_id'     => $client['id'],
                'status'        => 'new',
                'date_issue'    => date('Y-m-d H:i:s'),
                'history'       => date("Y-m-d H:i:s")." клиентом создан счет на сумму ".$bill['total'],
                'client_type'   => $_POST['client_type'],
                'method'        => $_POST['method'],
            );
    		$r = sql_getRow("SELECT * FROM bills_r_accounts");
    		foreach ($r as $key=>$val) {
                if ($key != 'id' && $key != 'type' && $key != 'purpose' && $key != 'updated' && $key != 'r_comp_logo' && $key != 'r_comp_sign' && $key != 'kind_of_payment')
                    $fields[$key] = $val;
            }

            $temp = $this->fields[$_POST['client_type']];
            foreach ($temp as $key=>$val) {
                $fields[$val] = $_POST[$val];
            }

            $fields['total'] = str_replace(',','.',$bill['total']);
            $fields['sum'] = str_replace(',','.',$bill['sum']);
            $fields['tax'] = str_replace(',','.',$bill['tax']);

            if (isset($fields['fio2'])) {
                $fields['fio'] = $fields['fio2']; unset($fields['fio2']);
            }

            $bill_id = sql_insert('bills', $fields);
            if (!is_numeric($bill_id)) redirect('/cabinet/bills/new_bill/?msg=msg_fail');

            // Обновляем данные в карточке клиента
            $sql = array();
            foreach ($this->fields[$fields['client_type']] as $field) {
                if ($field == 'email') continue;
                if ($field == 'fio2') $field = 'fio';
                if ($field == 'fio') $field2 = 'bill_fio';
                elseif ($field == 'phone') $field2 = 'bill_phone';
                elseif ($field == 'comp_name') $field2 = 'bill_comp_name';
                else $field2 = $field;
                $sql[] = '`'.$field2.'`="'.mysql_escape_string(str_replace('"', '&quot;', $fields[$field])).'"';
            }
            if ($sql) {
                $sql = 'UPDATE auth_users SET '.implode(', ', $sql).' WHERE id='.$client['id'];
                sql_query($sql);
            }

            $fields['id'] = $bill_id;
            $page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'cabinet');

            // Подготовка данных для отправки письма
            $fields['r_comp'] = array();
            foreach ($fields as $key=>$val) {
                if ($key != 'r_comp' && substr($key, 0, strlen('r_comp_')) == 'r_comp_') $fields['r_comp'][$key] = $val;
            }
            $fields['r_comp']['kind_of_payment'] = $r['kind_of_payment'];

    		$filename = $this->getParseBill($this->tmpl[$fields['method']][$fields['client_type']], $fields);

            require_once('./modules/pclzip.lib.php');
            $dir = getcwd();
            chdir(dirname($filename));
            $zip = new PclZip('bill.zip');
            $zip->create(basename($filename));
            chdir($dir);

            // Отправляем письмо клиенту с квитанцией или со счетом
            $r = sendEmail(
                $fields['email'] ? $fields['email'] : ($fields['comp_email'] ? $fields['comp_email'] : $client['login']),
                $page->tpl->get_config_vars('robot_email'),
                $page->tpl->get_config_vars('cabinet_new_bill_'.$fields['client_type'].'_subj'),
                $page->tpl->get_config_vars('cabinet_new_bill_'.$fields['client_type'].'_mail'),
                $fields,
                PATH_CACHE.'tmp/'.session_id().'/bill.zip'
            );

            @unlink($filename);
        }

        $row = sql_getRow('SELECT * FROM bills WHERE id='.$bill_id);
        if ($client['id'] != $row['client_id']) {
            die();
        }

        //redirect('/cabinet/bills/new_bill?act=step5&bill_id='.$bill_id);
        echo "
        <script>
        window.parent.document.forms['form1'].elements['bill_id'].value = '".$bill_id."';
        window.open('/cabinet/bills/print_kvit?bill_id=".$bill_id."', 'popup', 'width=670, height=650').focus();
        </script>
        ";
        exit;
	}

	/**
	 * Шаг 5 - Предлагаем печатать
	 *
	 * @param array $params
	 * @return string
	 */
	function newBill_step5($params){
        $bill_id = get('bill_id',NULL,'g');
        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();
        if (sql_getValue("SELECT client_id FROM bills WHERE id=".$bill_id)!=$client['id'])
            redirect ('/cabinet/bills/');

        $tpl_obj = & Registry::get('TTemplate');
        $page = & Registry::get('TPage');
		$page->content['elem_text']['text'] = $tpl_obj->get_config_vars('cabinet_createbill_step5');

		$tpl_obj->assign(array( 'act'			=>	$params['act'],
								'bill_id'		=>	$bill_id,
								'bill_type'		=>	sql_getValue("SELECT client_type FROM bills WHERE id=".$bill_id),
						));
		$ret = $tpl_obj->fetch('about_new_bill.html');
        return $ret;
    }

    /**
     * Подсчет суммы с НДС, без НДС и т.д.
     *
     * @param double $sum
     * @param double $nds
     * @param integer $nds_type
     * @return array
     */
    function calcBill($sum, $nds, $nds_type) {
        if ($nds_type == '0') {
            $bill['sum'] = $sum;
            $bill['tax'] = sprintf('%.2f', $sum*$nds);
            $bill['total'] = sprintf('%.2f', $sum*(1+$nds));
            $bill['purpose'] = "Счет на сумму ".sprintf('%.2f', $sum*(1+$nds));
            $bill['all_sum'] = sprintf('%.2f', $sum*(1+$nds));
        } else {
            $bill['sum'] = sprintf('%.2f', $sum/(1+$nds));
            $bill['tax'] = sprintf('%.2f', $sum-$sum/(1+$nds));
            $bill['total'] = sprintf('%.2f', $sum);
            $bill['purpose'] = "Счет на сумму ".sprintf('%.2f', $sum);
            $bill['all_sum'] = sprintf('%.2f', $sum);
        }
        return $bill;
    }

    /**
     * Распечатка счета
     *
     */
    function printVersion() {
		$bill_id = get('bill_id','','pg');
		$client_id = sql_getValue("SELECT client_id FROM bills WHERE id=".$bill_id);
        $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();
		if ($client_id != $client['id']) redirect("/cabinet/bills/");

		$info = $this->getInfo($bill_id);

		$page = & Registry::get('TPage');

		$tmpl = $this->getParseBill($this->tmpl[$info['bill']['method']][$info['bill']['client_type']], $info['bill']);
		$about_bill = file_get_contents($tmpl);

		$page->template = 'print_bill';
		$page->tpl->assign(array(
		  'about_bill' => $about_bill,
		));
	}

	/**
	 * Информация о счете
	 *
	 * @param integer $bill_id
	 * @return array
	 */
	function getInfo($bill_id) {
	    $auth_obj = & Registry::get('TUserAuth');
		$client = $auth_obj->getCurrentUserData();

		$bill = sql_getRow("SELECT * FROM bills WHERE id=".$bill_id);
		if ($bill['client_id'] != $client['id']) redirect("/cabinet/bills/");

        $date = getdate(strtotime($bill['date_issue']));
		$bill['d'] = $date['mday'];
		$bill['m'] = $this->month[$date['mon']];
		$bill['Y'] = $date['year'];

		$bill['status_display'] = $this->statuses[$bill['status']];
        $bill['client_type_display'] = $this->client_types[$bill['client_type']];
		$bill['r_comp'] = sql_getRow("SELECT * FROM bills_r_accounts");
		$client_list = sql_getRow("SELECT ".implode(",", $this->fields[$bill['client_type']])." FROM bills WHERE id=".$bill_id);

        $bill['sum_pr'] = $this->SumProp($bill['total']);

        $bill['payments'] = sql_getRows('SELECT * FROM billing WHERE bill_id='.$bill_id.' ORDER BY date');
        if ($bill['payments']) {
            foreach ($bill['payments'] as $key=>$val) {
   	           if (substr($val['comment'], 0, 3) == 'By ') $bill['payments'][$key]['comment'] = ($val['sum'] > 0 ? 'Зачислено' : 'Списано').' администратором';
            }
        }

		return array(
		  'bill'          => $bill,
		  'client_list'   => $client_list
		);
	}

	function SumProp($sum)
	{
		// Проверка ввода
        $sum = str_replace(' ','',$sum);
        $sum = trim($sum);
        if ((!(@eregi('^[0-9]*'.'[,\.]'.'[0-9]*$', $sum)||@eregi('^[0-9]+$', $sum)))||($sum=='.')||($sum==',')) return "Это не деньги: $sum";
		// Меняем запятую, если она есть, на точку
		$sum = str_replace(',','.',$sum);
		if($sum >= 1000000000)	return "Максимальная сумма &#151 один миллиард рублей минус одна копейка";

		// Обработка копеек
		$rub = floor($sum);
		$kop = 100*round($sum-$rub,2);
		$kop .= " коп.";
		if (strlen($kop) == 6)	$kop="0".$kop;

		// Выясняем написание слова 'рубль'
		$one = substr($rub, -1);
		$two = substr($rub, -2);
		if ($two > 9 && $two < 21) $namerub = "рублей";
		elseif ($one == 1) $namerub = "рубль";
		elseif ($one > 1 && $one < 5)	$namerub = " рубля";
		else $namerub = "рублей";
		if($rub == "0") return "Ноль рублей $kop";

		//----------Сотни
		$sotni = strlen($rub) >= 3 ? substr($rub, -3) : $rub;
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

	/**
	 * Возвращает имя файла с отпарсенным шаблоном счета
	 *
	 * @param string $name
	 * @return string
	 */
	function getParseBill($name, $bill) {
	    $page = & Registry::get('TPage');
	    $page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'calculation');
        $tmpl = $page->tpl->get_config_vars('calculation_'.$name);

        $filename = PATH_CACHE.'tmp/'.session_id().'/bill.html';
        $fp = fopen($filename, 'w');
        if (!$fp) return false;

        fwrite($fp, $tmpl);
        fclose($fp);

        $page->tpl->assign(array('bill' => $bill));
        $kvit = $page->tpl->fetch('../'.$filename);

        $fp = fopen($filename, 'w');
        if (!$fp) return false;

        fwrite($fp, $kvit);
        fclose($fp);

        return $filename;
	}
}
?>