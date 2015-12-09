<?php

/* $Id: distribution.php,v 1.7 2007/08/17 13:13:25 vetal Exp $
 */
require_once (elem_inc('notify'));

class TDistribution extends TNotify {

	// название модуля
	var $name = 'distribution';
	// отображать ли селектор языка?
	var $selector = false;

	//-------------------------------------------------------------------------------
	
	function TDistribution(){
		global $actions, $str;

		// обязательно вызывать
		TNotify::TNotify();
		TTable::TTable();
		$str[get_class_name($this)] = array(
			'title' => array('Отправить', 'Send'),
			'plugins' => array('Вид сообщения', 'Plugin'),
			'basic_caption' => array('Параметры', 'Properties'),
			'sms' => array('SMS сообщение', 'SMS message'),
			'email' => array('Электронная почта', 'E-mail'),
			'news' => array('Рассылка новостей', 'News'),
			'objects' => array('Рассылка объектов', 'Objects'),
			'templates' => array('Шаблоны', 'Templates'),
			'saved' => array('Данные успешно сохранены','Data has been saved successfully'),
		);
		//собственныйе экшены
		$actions[$this->name] = array(
			'send' => array(
				'Отправить',
				'Send',
				'link' => 'cnt.Send();',
				'img' => 'icon.countries.gif',
				'display' => 'block',
				'show_title' => true,
			),
			'preview' => array(
				'Предварительный просмотр',
				'Preview',
				'link' => 'cnt.sendForm.Preview();',
				'img' => 'icon.view.gif',
				'display' => 'block',
				'show_title' => true,
			),
			'save' => array(
				'Сохранить',
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
		// обязательная фигня
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		
		//собираем подключенные плугины
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
			return 'Укажите массив $GLOBALS["notify_subscrib"]';
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
	
	// заглушка для окна отправки
	function showStun(){
		// скидываем ифрэйм для поста в него и строим окно
		$plg = $this->getPluginClass();
		$plg->LoadStrings();
		$data = array();
		$plg->AddStrings($data);
		//pr($data);
		return Parse($data, NOTIFY_DIR.'tmpls/sending.tmpl');
	}
	
	//-------------------------------------------------------------------------------	
		
	function getPluginClass(){
		// подгружаем плугин
		$plugin = get('plugin', 'email', 'pg');
		include_once('plugins/'.$plugin.'/'.$plugin.'.php');
		$class = 'T'.$plugin;
		$plg = &Registry::get($class);
		return $plg;		
	}
	
	//-------------------------------------------------------------------------------		
	
	function EditSend(){
		global $notify_subscribe;
		$data['type'] = get('type', '', 'gp'); //что отправляем
		$data['fld'] = get('fld',array(),'gp'); // шаблон для отправки
		$data['users'] = get('users','','gp'); // кому отправляем
		$data['plugin'] = get('plugin','','gp'); // какой плугин используем

		//генерим массив данных для отправки
		//$data['obj'] = sql_getRows($notify_subscribe[$data['type']]);
		$data['obj'] = $this->getSqlData($notify_subscribe[$data['type']]);

		//генерим письмо -  результат хранится в $data['text']
		$this->createMail($data);
		
		$_return = true;  
		
		$plg = $this->getPluginClass();
		$plg->init($this->options[$data['plugin']]);
		$plg->LoadStrings();

		$plg->SendSubscribe($data);
	}
	
	//-------------------------------------------------------------------------------
	//предварительный просмотр
	function showTmpl(){	
		global $notify_subscribe;
		$data['type'] = get('type', '', 'gp'); //что отправляем
		$data['fld'] = get('fld',array(),'gp'); // шаблон для отправки
		$data['users'] = get('users','','gp'); // кому отправляем
		$data['plugin'] = get('plugin','','gp'); // какой плугин используем

		//генерим массив данных для отправки
		//определяем: запрос или название таблицы
		$data['obj'] = $this->getSqlData($notify_subscribe[$data['type']]);
		//генерим письмо -  результат хранится в $data['text']
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
	// генерится письмо для отправки
	function createMail(&$data){
		$data['subj'] = $data['fld']['subject'];
		$GLOBALS['smarty_text'] = $data['fld'];
		$tmpl = &Registry::get('TTemplate');
		//отпарсиваем каждый шаблон
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
