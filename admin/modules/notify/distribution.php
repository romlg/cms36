<?php

/* $Id: distribution.php,v 1.7 2007/08/17 13:13:25 vetal Exp $
 */
require_once (elem_inc('notify'));

class TDistribution extends TNotify {

	// �������� ������
	var $name = 'distribution';
	// ���������� �� �������� �����?
	var $selector = false;

	//-------------------------------------------------------------------------------
	
	function TDistribution(){
		global $actions, $str;

		// ����������� ��������
		TNotify::TNotify();
		TTable::TTable();
		$str[get_class_name($this)] = array(
			'title' => array('���������', 'Send'),
			'plugins' => array('��� ���������', 'Plugin'),
			'basic_caption' => array('���������', 'Properties'),
			'sms' => array('SMS ���������', 'SMS message'),
			'email' => array('����������� �����', 'E-mail'),
			'news' => array('�������� ��������', 'News'),
			'objects' => array('�������� ��������', 'Objects'),
			'templates' => array('�������', 'Templates'),
			'saved' => array('������ ������� ���������','Data has been saved successfully'),
		);
		//������������ ������
		$actions[$this->name] = array(
			'send' => array(
				'���������',
				'Send',
				'link' => 'cnt.Send();',
				'img' => 'icon.countries.gif',
				'display' => 'block',
				'show_title' => true,
			),
			'preview' => array(
				'��������������� ��������',
				'Preview',
				'link' => 'cnt.sendForm.Preview();',
				'img' => 'icon.view.gif',
				'display' => 'block',
				'show_title' => true,
			),
			'save' => array(
				'���������',
				'Send',
				'link' => 'cnt.sendForm.Save();',
				'img' => 'icon.save.gif',
				'display' => 'block',
				'show_title' => true,
			),
		);
		
		global $notify_subscribe;
		foreach ($notify_subscribe as $key=>$val) {
		    $notify_subscribe[$key] = str_replace('__ROOT_ID__', domainRootId(), $val);
		}
	}
	
	//-------------------------------------------------------------------------------
	
	function Show() {
		// ������������ �����
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		
		//�������� ������������ �������
		$types = array(); 
		foreach ($GLOBALS['notify_subscribe'] as $k=>$v){ $types[$k] = $this->str($k);}
		
		$names = array(
			'types'  => $types,
			'tabs'	 => "'".implode('\',\'',array_keys($types))."'",
		);
		$data = array(
			'basic' => $names,
			'query' => $GLOBALS['_SERVER']['QUERY_STRING'],
			'type' => $types['0'],
		);
		$data['thisname'] = 'send';
		if (empty($types)){
			return '������� ������ $GLOBALS["notify_subscrib"]';
		}
		return Parse($data, 'notify/tmpls/distribution.act.tmpl');		
	}
	
	//-------------------------------------------------------------------------------
	
	function showType(){
		global $notify_subscribe;
		$data['type'] = get('type', '', 'pg');

		$res = mysql_query($notify_subscribe[$data['type']]);
		for($i=0;$i<mysql_num_fields($res);$i++){
		    $field = mysql_fetch_field($res);
		    $comment = sql_getValue("
					SELECT 
					comment
					FROM phpmyadmin.pma_column_info 
					WHERE 
					table_name='".$field->table."'
					AND column_name='".$field->name."'
					AND db_name='".MYSQL_DB."' 
					");
		    $data['fields'][$field->table.'.'.$field->name] = $comment;
		}

		$data['fld'] = sql_getRow('SELECT * FROM notify_subscribe_tpls WHERE id="'.$data['type'].'"');
		
		include_fckeditor();
		$oFCKeditor = &Registry::get('FCKeditor');
		$oFCKeditor->ToolbarSet = 'Small';
		$oFCKeditor->Value = $data['fld']['header'];
		$data['fld']['header'] = $oFCKeditor->ReturnFCKeditor('fld[header]', '100%', '100px');

		$oFCKeditor = &Registry::get('FCKeditor');
		$oFCKeditor->ToolbarSet = 'Source';
		$oFCKeditor->Value = $data['fld']['template'];
		$data['fld']['template'] = $oFCKeditor->ReturnFCKeditor('fld[template]', '100%', '200px');
		
		$oFCKeditor = &Registry::get('FCKeditor');
		$oFCKeditor->ToolbarSet = 'Small';
		$oFCKeditor->Value = $data['fld']['footer'];
		$data['fld']['footer'] = $oFCKeditor->ReturnFCKeditor('fld[footer]', '100%', '100px');
		
		$this->AddStrings($data);
		$tpl = NOTIFY_DIR.'tmpls/'.$this->name.'.editform.tmpl';
		if (is_file($tpl)){
			return Parse($data, $tpl);
		}
		
		return $content;
	}
	
	//-------------------------------------------------------------------------------
	
	function getSqlData($query){
		$res = mysql_query($query);	
		if ($res) {
			$rows = array();
			$metas = array();
			for ($i = 0; $i < mysql_num_fields($res); $i++) {
				$metas[$i] = mysql_fetch_field($res, $i);
			}
			while($values = mysql_fetch_row($res)) {
	         $row = array();
				foreach ($metas as $i => $meta) {
					$row[$meta->table][$meta->name] = $values[$i];
				}
				$rows[] = $row;
			}
			mysql_free_result($res);
			return $rows;
		}
	}
	
	//-------------------------------------------------------------------------------
	
	function EditSaveTmpl(){
		$fld = get('fld',array(),'gp');
		$type = get('type','','gp');
		$users = get('users','','gp');
		$sql = "REPLACE INTO notify_subscribe_tpls(`id`,`subject`,`header`,`template`,`footer`) VALUES(
			'".mysql_escape_string($type)."',
			'".mysql_escape_string($fld['subject'])."',
			'".mysql_escape_string($fld['header'])."',
			'".mysql_escape_string($fld['template'])."',
			'".mysql_escape_string($fld['footer'])."'
		)";
		mysql_query($sql);
		return "<script>alert('".$this->str('saved')."');</script>";
	}
	
	//-------------------------------------------------------------------------------
	
	// �������� ��� ���� ��������
	function showStun(){
		// ��������� ������ ��� ����� � ���� � ������ ����
		$plg = $this->getPluginClass();
		$plg->LoadStrings();
		$data = array();
		$plg->AddStrings($data);
		//pr($data);
		return Parse($data, NOTIFY_DIR.'tmpls/sending.tmpl');
	}
	
	//-------------------------------------------------------------------------------	
		
	function getPluginClass(){
		// ���������� ������
		$plugin = get('plugin', 'email', 'pg');
		include_once('plugins/'.$plugin.'/'.$plugin.'.php');
		$class = 'T'.$plugin;
		$plg = &Registry::get($class);
		return $plg;		
	}
	
	//-------------------------------------------------------------------------------		
	
	function EditSend(){
		global $notify_subscribe;
		$data['type'] = get('type', '', 'gp'); //��� ����������
		$data['fld'] = get('fld',array(),'gp'); // ������ ��� ��������
		$data['users'] = get('users','','gp'); // ���� ����������
		$data['plugin'] = get('plugin','','gp'); // ����� ������ ����������

		//������� ������ ������ ��� ��������
		//$data['obj'] = sql_getRows($notify_subscribe[$data['type']]);
		$data['obj'] = $this->getSqlData($notify_subscribe[$data['type']]);

		//������� ������ -  ��������� �������� � $data['text']
		$this->createMail($data);
		
		$_return = true;  
		
		$plg = $this->getPluginClass();
		$plg->init($this->options[$data['plugin']]);
		$plg->LoadStrings();

		$plg->SendSubscribe($data);
	}
	
	//-------------------------------------------------------------------------------
	//��������������� ��������
	function showTmpl(){	
		global $notify_subscribe;
		$data['type'] = get('type', '', 'gp'); //��� ����������
		$data['fld'] = get('fld',array(),'gp'); // ������ ��� ��������
		$data['users'] = get('users','','gp'); // ���� ����������
		$data['plugin'] = get('plugin','','gp'); // ����� ������ ����������

		//������� ������ ������ ��� ��������
		//����������: ������ ��� �������� �������
		$data['obj'] = $this->getSqlData($notify_subscribe[$data['type']]);
		//������� ������ -  ��������� �������� � $data['text']
		$this->createMail($data);	
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		</head>
		<body>
		';
		echo $data['text'];
		echo '</body></html>';
	}
	
	//-------------------------------------------------------------------------------
	// ��������� ������ ��� ��������
	function createMail(&$data){
		$data['subj'] = $data['fld']['subject'];
		$GLOBALS['smarty_text'] = $data['fld'];
		$tmpl = &Registry::get('TTemplate');
		//����������� ������ ������
		$data['text'] = $tmpl->fetch("text:header");
		foreach ($data['obj'] as $k=>$value){
			$tmpl->assign($value);
			$data['text'] .= $tmpl->fetch("text:template");
		}
		$data['text'] .= $tmpl->fetch("text:footer");
		
	}
	//-------------------------------------------------------------------------------		

}

$GLOBALS['distribution'] = &Registry::get('TDistribution');
?>
