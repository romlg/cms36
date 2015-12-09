<?php

/* $Id: support.php,v 1.1 2009-02-18 13:09:12 konovalova Exp $
*/

class TSupport extends TTable {

	var $name = 'support';
	var $table = 'support';
	var $dialog_table = 'support_dialog';

	########################

	function TSupport() {
		global $str, $actions, $do;

		TTable::TTable();

		$GLOBALS['_elems'] = $do == 'showdetails' ? true : NULL; # trigger ced left menu

		$actions[$this->name] = array(
			'details' => array(
				'���������',
				'Details',
				'link'	=> 'cnt.showDetails()',
				'img' 	=> 'icon.preview.gif',
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
		);

		$actions[$this->name.'.showdetails'] = array(
			'create' => array(
				'��������',
				'Add Response',
				'link'	=> 'cnt.editItem(0)',
				'img' 	=> 'icon.create.gif',
				'display'	=> 'block',
			),
			'edit' => &$actions['table']['edit'],
			'delete' => array(
				'�������',
				'Delete',
				'link'	=> 'cnt.deleteDetails(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			'report' => array(
				'�����',
				'Report',
				'link'	=> 'cnt.showReport()',
				'img' 	=> 'icon.kb.gif',
				'display'	=> 'none',
				'hint' 	=> 'Create/Edit knowledge base report',
			),
            'progress' => array(
                '�������',
                'Accept',
                'link'    => 'cnt.Progress()',
                'img'     => 'icon.desc.gif',
                'display'    => 'none',
            ),
            'closed' => array(
                '������� ����',
                'Close',
                'link'    => 'cnt.Closed()',
                'img'     => 'icon.desc.gif',
                'display'    => 'none',
            ),
            'close' => &$actions['table']['close'],
		);

		$actions[$this->name.'.showclientselector'] = array(
			'null' => array(
				'��������',
				'Set Null',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'block',
			),
			'add' => array(
				'OK',
				'OK',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);
		$actions[$this->name.'.showcomponentselector'] = array(
			'null' => array(
				'��������',
				'Set Null',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'block',
			),
			'add' => array(
				'OK',
				'OK',
				'link'	=> 'cnt.addToList()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'close' => &$actions['table']['close'],
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('����� ���������',		'Support centre'),
			'selectclient'		=> array('����� �������',		'Select Client'),
			'id'			=> array('�',				'Ref#'),
            'client'        => array('������',            'Client'),
			'customer'		=> array('������',			'Customer'),
			'category'		=> array('��������� ��������',		'Problem Category',),
//			'group'			=> array('������ ���������',		'Product Group',),
//			'part'			=> array('���������',			'Part',),
			'summary' 		=> array('����',			'Summary'),
			'status' 		=> array('������',			'Status'),
			'new' 			=> array('�����',			'New',),
			'rma' 			=> array('RMA',				'RMA'),
			'closed' 		=> array('�a�����',			'Closed'),
			'visible' 		=> array('����������',			'Visible'),
			'progress' 		=> array('� ��������',			'Progress'),
			'_closed' 		=> array('�a�����',			'Closed'),
			'created' 		=> array('�������',			'Created'),
			'updated' 		=> array('���������',			'Updated'),
			'manager' 		=> array('��������',			'Manager'),
			'info' 			=> array('����������',			'Information'),
			'dialog' 		=> array('������',			'Dialog'),
			'serial' 		=> array('�������� �����',		'Serial No.'),
			
			####### RESPONSE ########
			
			'response'		=> array('��������',			'Response'),
			'text' 			=> array('������',			'Dialog'),
			'cancel' 		=> array('������',			'Cancel'),
			'edit' 			=> array('�������������',		'Edit'),
			'alert_no_text'		=> array('���������, ����������, �����','Please, fill an answer'),
			'record'		=> array('������',			'Record',),
			'file'			=> array('������������ ����',		'Attach file',),
			'attached'		=> array('�������������� ����',		'Attached file',),
			'change_status' 	=> array('�������� ������',		'Change status'),
			'edit' 			=> array('��������',			'Edit'),
			'component'		=> array('���������',			'Component'),
			'hardware' 		=> array('������������ ������������',	'Hardware configuration'),
			'software' 		=> array('������������ ��',		'Software configuration'),
			'goto_kb' 		=> array('������� � ���� ������',	'Go to Knowledge Base'),
			'editform' 		=> array('��������/�������������� ������','Create/Edit record'),
			'email' 		=> array('E-mail',			'E-mail'),
			'kb_id' 		=> array('���� ������',			'Knowledge Base'),
			'report' 		=> array('�����',			'View Report'),
			'saved' 		=> array('������ ������� ���������',	'Data has been saved successfully'),
			'c_report' 		=> array('������� ����� � ���� ������?','Report will be created in the knowledge base. Continue?'),
			'read_adm' 		=> array('������������� ��������� �� ������������',		'Unread messages from client'),
            'read_usr'      => array('��������� �������������',        'Read by user'),
		));
	}
	
	########################

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		$ret['wide'] = (int)get('wide');
		$ret['client_id'] = get('client_id', '');
		$ret['show'] = get('show', '');
		
		$pid = (int)get('pid');
		require_once(core('ajax_table'));
		$ret['thisname'] = $this->name;
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 's.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 's.id',
					'display'	=> 'id',
					'flags'		=> FLAG_SORT | FLAG_FILTER,
					'filter_size'	=> 2,
					'filter_maxlength'	=> 6,
				),
				array(
					'select'	=> 's.read',
					'display'	=> 'read_adm',
					'flags'		=> FLAG_SORT,
					'align'		=> 'center',
					'type'		=> 'read_adm',
				),
                array(
                    'select'    => 's.read',
                    'display'    => 'read_usr',
                    'flags'        => FLAG_SORT,
                    'align'        => 'center',
                    'type'        => 'read_usr',
                ),
				array(
					'select'	=> 's.status',
					'display'	=> 'status',
					'align'		=> 'center',
					'type'		=> 'str',
					'flags'		=> FLAG_SORT,
				),
                array(
                    'select'    => 'UNIX_TIMESTAMP(s.updated)',
                    'display'    => 'updated',
                    'type'        => 'date',
                    'flags'        => FLAG_SORT,
                    'nowrap'    => 1,
                ),
				array(
					'select'	=> 'UNIX_TIMESTAMP(s.created)',
					'display'	=> 'created',
					'type'		=> 'date',
					'flags'		=> FLAG_SORT,
					'nowrap'	=> 1,
				),
				array(
					'select'	=> 'CONCAT(c.name,CHAR(32),c.lname,CHAR(32),"(",c.login,")")',
					'as'		=> 'fio',
					'display'	=> 'customer',
					'flags'		=> FLAG_SEARCH,
				),
				array(
					'select'	=> 's.summary',
					'display'	=> 'summary',
					'flags'		=> FLAG_SEARCH,
				),
                array(
                    'select'    => 's_c.name',
                    'flags'        => FLAG_FILTER | FLAG_SORT,
                    'filter_type'    => 'array',
                    'filter_value'    => array('')+sql_getRows('SELECT name, name FROM support_categories order by name', true),
                    'filter_str'    => false,
                    'filter_display'    => 'category',
                    'display' => 'category',
                ),
				array(
					'select'	=> 'UNIX_TIMESTAMP(s.closed)',
					'display'	=> $ret['wide'] ? '_closed' : NULL,
					'type'		=> 'date',
					'flags'		=> FLAG_SORT,
					'nowrap'	=> 1,
				),
			),
			'from'	 	=> '
				support AS s 
				LEFT JOIN products AS p ON p.id=s.product_id
				LEFT JOIN auth_users AS c ON c.id=s.client_id 
				LEFT JOIN admins as u ON u.id=s.manager_id 
				LEFT JOIN support_categories AS s_c ON s_c.id = s.category_id',
			'where' 	=> 
				'1=1'.
				($ret['client_id'] ? ' AND c.id='.$ret['client_id'] : '').
				($ret['show']=='new' ? ' AND s.status="new"':'').
				($ret['show']=='progress' ? ' AND s.status="new"':'').
				($ret['show']=='closed' ? ' AND s.status="closed"':''),
			'params'			=> array(
				'page' => $this->name, 
				'do' => 'show',
				'wide' => $ret['wide'],
				'client_id' => $ret['client_id'],
				'show' => $ret['show']
			),
			'click'				=> 'ID=cb.value;',
			'orderby'			=> 's.updated DESC, s.read, s.status',
			'dblclick'			=> 'showDetails(id);',
			//'_sql'=>true
		), $this);
		$this->AddStrings($ret);
		$ret['wide'] = (int)!$ret['wide'];
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################

	function table_get_read_adm(&$value, &$column, &$row) {
		return ($value == 'adm') ? "&radic;" : "";
	}

    function table_get_read_usr(&$value, &$column, &$row) {
        return ($value != 'usr') ? "&radic;" : "";
    }
	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$row = $this->GetRow($id);
		} else {
			$row['client_id'] = (int)get('client_id');
			$this->SetDefaultValues($row);
		}
		if ($row['client_id']) $row['client'] = sql_getValue("SELECT CONCAT(name, ' ', lname) FROM auth_users WHERE id=".$row['client_id']);

		$this->AddStrings($row);
		$row['client'] = sql_getValue("SELECT CONCAT(name, ' ', lname) FROM auth_users WHERE id=".$row['client_id']);
		$row['target'] = get('target', $this->name);
		
		$row['categories'] = $this->GetArrayOptions(sql_getRows("SELECT id, name FROM support_categories ORDER BY priority, name", true), $row['category_id'], true);
		$row['status'] = $this->GetSetOptions('status', $row['status']);
//		$row['product_types'] = $this->GetArrayOptions($this->GetRows("SELECT id, name FROM product_types ORDER BY priority, name", true), $row['product_type_id'], true);
//		$row['parts'] = sql_getRows("SELECT id , name FROM products WHERE id=".$row['product_id'], true);
		$row['managers'] = $this->GetArrayOptions(sql_getRows("SELECT id, CONCAT(login,' (',fullname,')') FROM admins WHERE ".$this->WhereSubst()." ORDER BY login", true), $row['manager_id'], true);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = (int)get('id', 0, 'p');
		$row = &$GLOBALS['_POST']['fld'];
		if (!$id) {
            $row['created'] = 'NULL';
            $row['`read`'] = 'usr';
        }
		else {
			$old_status = sql_getValue("SELECT status FROM $this->table WHERE id=$id");
		}

//		if ($row['product_type_id']){$row['product_type_id'] = sql_getValue("SELECT product_type_id FROM products WHERE id=".$row['product_id']);}
		$res = $this->Commit(array('summary'));

		$reload = (!sql_getErrNo())? "if (window.top.name != '') window.top.location.reload(); else window.parent.location.reload()" : "";
		$rma = $old_status != $row['status'] && $row['status'] == 'rma' ? "window.showModalDialog('dialog.php?page=rma&do=editform&support_id=$id', '', 'dialogWidth: 500px; dialogHeight: 350px')" : "";
		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); $reload; $rma</script>";
		return $this->Error($res);
	}

    function EditStatus(){
        $id = (int)get('id',0,'g');
        if (!$id) return $this->Error();
        $status = get('to','','g');
        if (!$status) return $this->Error();

        $res = sql_query("UPDATE $this->table SET `status`='$status' WHERE id=$id");
        if ($res) return "<script>alert('".$this->str('saved')."'); window.top.location.reload(); </script>";
        return $this->Error($res);
    }

    ###################### ced: left menu

	function GetBasicElement() {
		return array(
			'basic_caption'	=> $this->str('record'),
			'basic_icon'	=> 'box.support.gif',
			'src'		=> $GLOBALS['_SERVER']['QUERY_STRING'],
			'tree'		=> $this->Summary(),
			'row'	=>  "<a HIDEFOCUS href='#' onclick='act.cnt.editSummary();return false;' title=''><img align=absmiddle src='images/icons/icon.edit.gif' width=16 height=16 border=0 hspace=4><b>".$this->str('edit')."</b></a>",
		);
	}

	######################

	function ShowDetails() {
		global $user;
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		$GLOBALS['limit'] = -1;
		$ret['id'] = (int)get('id');

		$ret+= sql_getRow("SELECT * FROM $this->table WHERE id=".$ret['id']);

		# ���� ����������
//		if ($ret['manager_id'] == $user['id']){sql_query("UPDATE support SET `read` = 'none' WHERE id=".$ret['id']);}
        if ($ret['read'] == 'adm') sql_query("UPDATE support SET `read` = 'none' WHERE id=".$ret['id']);

		# ��������, ����� ������ "�����" ��� ���
		$ret['need_report'] = sql_getValue("
			SELECT COUNT(*)
			FROM support_kb
			WHERE group_id".($ret['product_type_id'] ? "=".$ret['product_type_id'] : " IS NULL")."
			AND product_id".($ret['product_id'] ? "=".$ret['product_id'] : " IS NULL")."
			AND category_id".($ret['category_id'] ? "=".$ret['category_id'] : " IS NULL"));
		if (strlen($ret['need_report'] > 10)) pre($ret['need_report']); # ����� ������ ������
		$ret['need_report'] = (int)!$ret['need_report'];

        $ret['progress'] = $ret['status'] == 'new' ? 1 : 0;
        $ret['closed'] = $ret['status'] == 'closed' ? 0 : 1;

        $this->AddStrings($ret);

		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'd.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'd.text',
					'display'	=> 'text',
					'type'		=> 'text',
				),
				array(
					'select'	=> 'IF(LENGTH(u.fullname)>0,u.fullname,u.login)',
					'as'		=> 'manager',
				),
				array(
					'select'	=> 'd.file',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(d.updated)',
					'as'		=> 'updated',
				),
				array(
					'select'	=> 'UNIX_TIMESTAMP(d.created)',
					'as'		=> 'created',
				),
				array(
					'select'	=> 'd.updated',
					'as'		=> '_updated',
				),
				array(
					'select'	=> 'd.created',
					'as'		=> '_created',
				),
			),
            'from'      => "support_dialog as d LEFT JOIN admins as u ON u.id=d.manager_id",
			'where'		=> 'support_id='.$ret['id'],
			'orderby'	=> 'd.created',
			'params'	=> array('page' => $this->name, 'do' => 'showdetails'),
			'click'		=> 'ID=cb.value',
			//'dblclick'	=> 'editItem(id)',
            //'_sql'      => true,
			'target'	=> 'tmp'.$this->name.'details',
			'roll'		=> 0,
		), $this);
		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.details.tmpl');
	}

	######################

	function table_get_text(&$value, &$column, &$row) {
		$file = $row['file'] ? $file = "<div align=right><i>".$this->str('attached').": <a href='".$row['file']."'><b>".basename($row['file'])."</b></a></div></i>" : "";
		$created = "<div align=right><i>".$this->str('created').": ".date(FORMAT_DATETIME, $row['created'])."</i></div>";
		$updated = $row['_created'] != $row['_updated'] ? "<i><div align=right>".$this->str('updated').": ".date(FORMAT_DATETIME, $row['updated'])."</div></i>" : "";

		if (!$row['manager']) $row['manager'] = "<font color=red>".$this->str('client')."</font>";

		return "<div><b>".$row['manager']."</b></div>
		<div style='padding: 8px'>$value</div>
		$created
		$updated
		$file
		";
	}

	########################

	function table_get_checkbox(&$value, &$column, &$row) {
		if ($GLOBALS['do'] == 'showdetails') return isset($row['manager']) && $row['manager'] ? "<input type=checkbox name='{$column['display']}[$value]' value='$value'".(!empty($column['checked']) ? ' CHECKED' : '')." onclick=''>" : "";
		else {
			$at = & Registry::get('Ajax_Table');
			return $at->table_get_checkbox($value, $column, $row);
		}
	}

	######################

	function Summary() {
		$id = (int)get('id');
/*		$sql="
			SELECT
				s.id,
				s.status,
				IF(LENGTH(u.fullname)>0,u.fullname,u.login) as manager,
				CONCAT(c.name,CHAR(32),c.lname) as client,
				UNIX_TIMESTAMP(s.created) as created,
				UNIX_TIMESTAMP(s.updated) as updated,
				p.name as part,
				cat.name as category,
				kb.id as kb_id,
				s.category_id as _category_id
			FROM $this->table as s
				LEFT JOIN products as p ON p.id=s.product_id
				LEFT JOIN support_categories as cat ON cat.id=s.category_id
				LEFT JOIN users as u ON u.id=s.manager_id
				LEFT JOIN auth_users as c ON c.id=s.client_id
				LEFT JOIN product_types as t ON t.id=s.product_type_id
				LEFT JOIN support_kb as kb ON kb.product_id=s.product_id AND kb.category_id=s.category_id  AND kb.group_id=s.product_type_id
			WHERE s.id=$id";   */
        $sql="
            SELECT
                s.id,
                s.status,
                CONCAT(c.name,CHAR(32),c.lname) as client,
                UNIX_TIMESTAMP(s.created) as created,
                UNIX_TIMESTAMP(s.updated) as updated,
                cat.name as category,
                kb.id as kb_id,
                s.category_id as _category_id
            FROM $this->table as s
                LEFT JOIN support_categories as cat ON cat.id=s.category_id
                LEFT JOIN auth_users as c ON c.id=s.client_id
                LEFT JOIN product_types as t ON t.id=s.product_type_id
                LEFT JOIN support_kb as kb ON kb.product_id=s.product_id AND kb.category_id=s.category_id  AND kb.group_id=s.product_type_id
            WHERE s.id=$id";
		$row = sql_getRow($sql);

		# ��������� �����
		$row['status'] = "<font color=red>".$this->str($row['status'])."</font>";
		$row['updated'] = date(FORMAT_DATETIME, $row['updated']);
		$row['created'] = date(FORMAT_DATETIME, $row['created']);
		if ($row['category']) $row['category'] = "<a href='javascript:window.open(\"ced.php?page=support_kb&filter[k.category_id]={$row['_category_id']}\", \"support_kb\", \"width=800, height=600, resize=1, status=1\").focus()' title='".$this->str('goto_kb')."'>".$row['category']."<img align=absmiddle src='images/icons/icon.kb.gif' width=16 height=16 border=0 hspace=4></a>";
		if ($row['kb_id']) $row['kb_id'] = "<a href='javascript:window.open(\"ced.php?page=support_kb&do=showdetails&id={$row['kb_id']}\", \"kbdetails\", \"width=800, height=600, resize=1, status=1\").focus()' title='".$this->str('goto_kb')."'>".$this->str('report')."<img align=absmiddle src='images/icons/icon.kb.gif' width=16 height=16 border=0 hspace=4></a>";

		foreach ($row as $key=>$val) {
			if ($key{0} != '_') $ret[] = array(
				'name'	=> $key == 'client' ? "<font color=red>".$this->str($key)."</font>" : $this->str($key),
				'value'	=> $val ? $val : "<span class=loading>n/a</span>",
			);
		}

		return $this->Parse(array('rows' => $ret), $this->name.'.summary.tmpl');
	}

	########################

	function EditDetailForm() {
		$id = (int)get('id');
		$row['support_id'] = (int)get('support_id');
		$row['visible'] = 1;
		$this->table = $this->dialog_table;
		if ($id) {
			$row = $this->GetRow($id);
		} else {
		}
		$this->AddStrings($row);

		include_fckeditor();

		$oFCKeditor = &new FCKeditor;
		$oFCKeditor->ToolbarSet = 'Common';
		$oFCKeditor->CanUpload = false;
		$oFCKeditor->Value = $id ? $row['text'] : '';
		$row['text'] = $oFCKeditor->ReturnFCKeditor('fld[text]', '100%', '100%');

		$row['thisname'] = $this->name;
		$row['save'] = $this->str('save');
		$row['close'] = $this->str('cancel');
		$GLOBALS['title'] = $this->str('title');
		return $this->Parse($row, $this->name.'.editdetailform.tmpl');
	}

	########################

	function EditDetail() {
		global $user;

		$fld = &$GLOBALS['_POST']['fld'];

		# set created
		$id = (int)get('id', 0, 'p');
		if (!$id) $fld['created'] = 'NULL';

		$table = $this->table;
		$this->table = $this->dialog_table;
		$fld['manager_id'] = $user['id'];
		$res = $this->Commit(array('text'));
		$reload = (!sql_getErrNo()) ? "window.parent.location.reload(); window.top.opener.location.reload();" : "";
		if (is_int($res)) {
			# update parent
			$status = sql_getValue("SELECT status FROM $table WHERE id=".$fld['support_id']);
			if ($status == 'new') $status = 'progress';
			sql_query("UPDATE support SET `updated` = NULL, `status` = '$status', `read` = 'usr' WHERE id=".$fld['support_id']);
			# notification
			require_once(module('email_notify'));
			$client_id = sql_getValue("SELECT client_id FROM support WHERE id=".$fld['support_id']);
			$client = sql_getRow("SELECT * FROM auth_users WHERE id=".$client_id);

			$GLOBALS['email_notify']->Notify('support_client', $client['name'].' '.$client['lname'], $client['login'], $fld['support_id']);
			
			statuslog("<script>alert('".$this->str('saved')."'); $reload</script>");
			$this->emailNotify($client, 'support_client');
			sql_query('INSERT INTO `history`(`client_id`,`pid`,`type`,`text`,`date`) VALUES ('.$client_id.',\'0\',\'support\',\'�������� ������� ��������� � ������ ��������\',\''.time().'\')');
			return "<script>alert('".$this->str('saved')."'); $reload</script>";
		}
		return $this->Error($res);
	}

################################################
//---------------- Notify ---------------------
	function emailNotify($temp,$template){
		$email_notify = new TEmailNotify;
		 $mail_to = '�������:'.$temp['lname']." ".$temp['name']." ".$temp['tname'];
			/*
			if (empty($temp['comp_email']) & !empty($temp['email'])){
			$email_notify->Notify($template,$mail_to,$temp['email']);
			}
			if (!empty($temp['comp_email']) & empty($temp['email'])){
			$email_notify->Notify($template,$mail_to,$temp['comp_email']);
			}
			if (!empty($temp['comp_email']) & !empty($temp['email'])){
			$email_notify->Notify($template,$mail_to,$temp['email']);
			}
			*/
			if (empty($temp['comp_email']) & !empty($temp['login'])){
			$email_notify->Notify($template,$mail_to,$temp['login']);
			}
			if (!empty($temp['comp_email']) & empty($temp['login'])){
			$email_notify->Notify($template,$mail_to,$temp['comp_email']);
			}
			if (!empty($temp['comp_email']) & !empty($temp['login'])){
			$email_notify->Notify($template,$mail_to,$temp['login']);
			}
	}
################################################

	function DeleteDetails() {
		$this->table = $this->dialog_table;
		$res = $this->DeleteItems();
        if ($res) return "<script>alert('".$this->str('saved')."'); window.top.location.reload(); </script>";
        return $this->Error($res);
	}

	######################

	function ShowClientSelector() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'from'		=> "auth_users",
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'CONCAT(name,CHAR(32),lname)',
					'display'	=> 'customer',
					'flags'		=> FLAG_SEARCH,
				),
				/*array(
					'select'	=> 'email',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),*/
				array(
					'select'	=> 'login',
					'display'	=> 'email',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'where'		=> 'reg_date>0 AND visible>0',
			'orderby'	=> 'id',
			'params'	=> array('page' => $this->name, 'do' => 'showclientselector'),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'addToList()',
			'target'	=> 'tmp'.$this->name.'select',
//			'_sql'=>true,
		), $this);
		$this->AddStrings($ret);
		return $this->Parse($ret, 'selectwindow.tmpl');
	}

	########################
	function ShowComponentSelector() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$prod_id = get('prod_id', '', 'g');
		$temp=array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'component',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'from'		=> "products",
			'where'     => "product_type_id=".$prod_id,
			'orderby'	=> 'id',
			'params'	=> array('page' => $this->name, 'do' => 'showcomponentselector'),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'addToList2()',
			'target'	=> 'tmp'.$this->name.'select',
//			'_sql'=>true,
		);
		if (empty($prod_id) || $prod_id==0){$temp['where']="";}

		$ret['table'] = ajax_table($temp, $this);
		$this->AddStrings($ret);
		return $this->Parse($ret, 'selectwindow.tmpl');
	}

	########################

	function PostSelect() {
		$id = get('id', array(), 'p');
		$id = array_shift($id);
		$name = sql_getValue("SELECT CONCAT(name, ' ', lname) FROM auth_users WHERE id=".$id);
		if (!empty($name)) {
			return "<script>
			window.top.opener.document.forms.editform.elements['fld[client_id]'].value=$id;
			window.top.opener.document.forms.editform.client.value='".addslashes($name)."';
			window.top.close();
			</script>";
		}
	}
	function PostSelect2() {
		$id = get('id', array(), 'p');
		$id = array_shift($id);
		$name = sql_getValue("SELECT name FROM products WHERE id=".$id);
		if (!empty($name)) {
			return "<script>
			window.top.opener.document.forms.editform.elements['fld[product_id]'].value=$id;
			window.top.opener.document.forms.editform.component.value='".addslashes($name)."';
			window.top.close();
			</script>";
		}
	}
	########################

	function ShowReportConfirm() {
		$row['id'] = (int)get('id');
		$this->AddStrings($row);
		$row['STR_YES'] = $this->str('yes');
		$row['STR_NO'] = $this->str('no');
		return $this->Parse($row, $this->name.'.report.tmpl');
	}

	########################

	function ShowReport() {
		global $user;

		$id = (int)get('id');
		$row = $this->GetRow($id);

		if (!$row['product_id']) $row['product_id'] = 'NULL';
		if (!$row['category_id']) $row['category_id'] = 'NULL';
		if (!$row['group_id'] || $row['group_id']==0) $row['group_id'] = sql_getValue("SELECT product_type_id FROM products WHERE id=".$row['product_id']);

		# ������� ����� ������ � KB
		$data = array(
			'group_id'		=> $row['group_id'],
			'product_id'	=> $row['product_id'],
			'category_id'	=> $row['category_id'],
			'description'	=> $row['summary'],
			'created'		=> 'NULL',
		);
		$this->table = 'support_kb';
		$kb_id = sql_insert($this->table, $data);
		if ($id) {
			# ������� ������ � ������
			$rows = sql_getRows("SELECT * FROM $this->dialog_table WHERE support_id=$id ORDER BY created");
			$text = '';
			foreach ($rows as $val) {
				$text.= sprintf("<div><b>%s</b></div>\n%s<br><br>\n\n", ($val['manager_id'] ? 'Answer' : 'Question'), $val['text']);
			}

			$data = array(
				'kb_id'			=> $kb_id,
				'manager_id'	=> $user['id'],
				'text'			=> $text,
				'created'		=> 'NULL',
			);
			$this->table = 'support_kb_dialog';
			$id = sql_insert($this->table, $data);
		}
		return "<script>
		window.open('ced.php?page=support_kb&do=showdetails&id=$kb_id', 'kbdetails', 'width=800, height=600, resizable=1, status=1').focus();
		//window.top.location.reload();
		</script>";
	}
	########################
	#
	# ������� ���������� ������ ��� ������� � sql ������ where
	# � ����������� �� is_root � $user['subst']
	# 1) where 1 AND fullname!=''
	# 2) where id IN (1,2,3) AND fullname!=''
	#

	function WhereSubst($fieldName='id') {
		return 1;
/*		$ret = '';
		if (is_root()) $ret = 1; # WHERE 1 AND ...
		$ret = "$fieldName IN (".join(',',$this->user['subst']).")";
		return "$ret AND fullname!=''";*/
		// subst ���� ����������
	}

	######################
}

$GLOBALS['support'] = & Registry::get('TSupport');
?>