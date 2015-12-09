<?
//��������� ���������� ����
define ('NOTIFY_DIR', substr(realpath(__FILE__), 0, ((strrpos(realpath(__FILE__), "\\"))?strrpos(realpath(__FILE__),"\\"): strrpos(realpath(__FILE__), "/"))+1));

class TNotify extends TTable {
	
	// ������ ������ ����������
	var $err; 
	var $options;
	
	//-------------------------------------------------------------------------------
	
	function TNotify(){
		// �������� ������ ����������� ��������
		global $notify_plugins, $str;
		// ���������� ��������� ���� ��������
		$cfg = ReadIni(NOTIFY_DIR.'config.cfg');
		$own = ReadIni(NOTIFY_DIR.'config.ini');
		// ��������� ��������� ������ ��� ����������� �������

		foreach ($notify_plugins as $k=>$plugin){
			$config = array(
				'cfg' => array(),
				'own' => array(),
			);
			if (isset($cfg[$plugin])){
				$config['cfg'] = $cfg[$plugin];
			}
			if (isset($own[$plugin])){
				$config['own'] = $own[$plugin];
			} elseif (!isset($cfg[$plugin])){
				trigger_error('��� �������� ��� �������: <b>'.$plugin.'</b>', E_USER_WARNING);
			}
			
			$this->options[$plugin] = array_merge($config['cfg'], $config['own']);
		}

		$tmpl = &Registry::get('TTemplate');
		// ������������ ������ ��� ������ "notify"(��� ����� ������, � �� �����)
		$tmpl->register_resource("notify", array("notify_get_template",
	                                         "notify_get_timestamp",
	                                         "notify_get_secure",
	                                         "notify_get_trusted"));
		$tmpl->register_resource("text", array("text_get_template",
	                                         "text_get_timestamp",
	                                         "text_get_secure",
	                                         "text_get_trusted"));	                                         
		
		require_once(NOTIFY_DIR.'/plugins/sender.php');
	}
	

	//-------------------------------------------------------------------------------	
	
	function Send($event, $user_id, $data = array()){
		// ���������� ���� �� ���������� � �������
		$file_dir = $this->getFileDir();	
		// �������� ����������� ����� �����������
		$plugins = $this->verify_event($event ,$user_id);
		// �������� ������� ��� ������� �������� � ������ � ��� ������
		$tpls = $this->getTmpls($plugins, $event, $data);
		$tmpl = &Registry::get('TTemplate');
		$_return = true;  
		if ($tpls) {
		foreach ($tpls as $plugin=>$value){
			// �������� �� ���������� ������� �������
			$sender = &Registry::get('TSender');
			if (isset($this->options[$plugin])){
				//������ � ������ ������ ��� ������
				$tmpl->assign($data);
				// ���������� ������ �� PHP �������
				$text = $tmpl->fetch('notify:'.$value['id'].',template,notify_templates');
				//������ subject
				$value['subject'] = $tmpl->fetch('notify:'.$value['id'].',subject,notify_templates');
				
				// ����������
				// ���������� ��������� ��� ������� �������
				$sender->params = $this->options[$plugin]; 
				require_once(NOTIFY_DIR.'/plugins/'.$plugin.'/'.$plugin.'.php');
				$class = 'T'.$plugin;
				$plg = &Registry::get($class);
				$params = $plg->genParams($event, $user_id, $text, $plugin, $value, $file_dir);
				if (!isset($params) || (isset($params) & !call_user_func(array(&$sender, $plugin.'Send'), $params))){ 	
						return false;		
				} else {
				  	$plg->AddToSent($params);
					  	return true;
				}
				} else return false;
			}
		} else return false;
	}
	
	//-------------------------------------------------------------------------------

	function verify_event(&$event, $user_id){
		// �������� ����������� ����� �����������
		//-----------------------------------------------
		// �������� ������������� �������
		$event = sql_getRow("SELECT id, recipient FROM notify_events WHERE name='".$event."'");
		// �������� ��������� ������� ��� �������
		$plugins = sql_getRows("SELECT plugin FROM notify_compare WHERE event=".$event['id'], true);
		
		// �������� ������� ��� ������� ��� ��� ������.
		if ($event['recipient'] == 'client'){		
			//�������� ������������� ������ ������������
			$group_id =sql_getValue("SELECT group_id FROM auth_users_groups WHERE user_id=".$user_id);
			
			//��������, ����������� ������� ��� ������
			$group_plugins = sql_getRows("SELECT nt.name FROM notify_groups AS ng
											LEFT JOIN notify_types AS nt ON nt.id=ng.notif_id
											WHERE ng.group_id=".$group_id, true);
			
			if (!sql_getErrNo()){
				$plugins = array_intersect($plugins, $group_plugins);
			} 
			/*
			//�������� ���� �����������, ��������� �������������
			$user_plugins = sql_getRows("
			SELECT 
				nt.name
			FROM notify_user AS nu
			LEFT JOIN notify_types AS nt ON nu.notify_id=nt.id
			WHERE nu.user_id=".$user_id." AND nu.event_id=".$event['id']
			, true);
			
			if (!sql_getErrNo()){
				$plugins = array_intersect($plugins,$user_plugins);
			}
			*/
		}
		return $plugins;
	}
	
	//-------------------------------------------------------------------------------
	// �������� ������� ��� ������� ��������
	function getTmpls($plugins, $event){
		$str = '';
		foreach ($plugins as $name=>$name2){
			$str .= ",'".$name."'";
		}
		$str = substr($str,1);
		return sql_getRows("SELECT plugin, id , attachment, subject FROM notify_templates WHERE event=".$event['id']." AND plugin IN (".$str.")", true);
	}
	//-------------------------------------------------------------------------------	
	function getFileDir(){
		if (defined('FILES_PATH') & is_dir(FILES_PATH)){ 
			return FILES_PATH;
		} else {
			return FILES_DIR;
		}
	}
	//-------------------------------------------------------------------------------
	
	//-------------------------------------------------------------------------------
	
	//-------------------------------------------------------------------------------
}
// ���������� �������
//-----------------------------------------------------------------------------------------------------------
// ��������������� ��������� file, ��� ����, ����� ����� ���� ������� �� ������ ���� �� � ������ �� ��
// ��� � ����� �������
function notify_get_template ($tpl_name, &$tpl_source, &$smarty_obj){
	//$tpl_name: id,field,table
	$tpl_name = explode(',', $tpl_name);
	$tpl_source = sql_getValue("SELECT ".$tpl_name[1]." FROM ".$tpl_name[2]." WHERE id='".$tpl_name[0]."'");
	return true;
}
function notify_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj){
    $tpl_timestamp = time();
    return true;
}
function notify_get_secure($tpl_name, &$smarty_obj){
    // ������������, ��� ���� ������� ���������� ���������
    return true;
}
function notify_get_trusted($tpl_name, &$smarty_obj){
    // �� ������������ ��� ��������
}
//-----------------------------------------------------------------------------------------------------------
// ��������������� ��������� file, ��� ����, ����� ����� ���� ������� �� ������ ���� �� � ������ ����� �� ���������� ����������
// ��� � ����� �������
function text_get_template ($tpl_name, &$tpl_source, &$smarty_obj){
	$tpl_source = $GLOBALS['smarty_text'][$tpl_name];
	return true;
}
function text_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj){
    $tpl_timestamp = time();
    return true;
}
function text_get_secure($tpl_name, &$smarty_obj){
    // ������������, ��� ���� ������� ���������� ���������
    return true;
}
function text_get_trusted($tpl_name, &$smarty_obj){
    // �� ������������ ��� ��������
}
//-----------------------------------------------------------------------------------------------------------
// ������ � ��������

//ini_set('display_errors', 0);
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//set_error_handler("ErrorHandler");

//-----------------------------------------------------------------------------------------------------------

function ErrorHandler($errno, $errstr, $errfile, $errline){ 
	
	if ($errno != E_NOTICE){ // ��������� ������
		global $notify_errors;
		$notify_errors[] = array(
			'errno' => $errno,
			'errstr' => $errstr,
			'errfile' => $errfile,
			'errline' => $errline,
		);
		$tclass = &Registry::get('TTable');
		$GLOBALS['str']['ttable']['function'] = array('�������: ','Function: ');
		$GLOBALS['str']['ttable']['file'] = array('����: ','File: ');
		$GLOBALS['str']['ttable']['call'] = array('�������: ','Call: ');
		$debug = debug_backtrace();
		$error = $tclass->str('error')." :\n".$tclass->str('file').$errfile." : ".$errline."\n\n".$errstr."\n\n";
		if (isset($debug['1'])){
			//��������� �������
			$error .= $tclass->str('call')."\n	".$tclass->str('function').$debug['1']['function']."()\n";
			//��� ���������
			$error .= "	".$tclass->str('file').$debug['1']['file']." : ".$debug['1']['line']."\n";
		}
		if (isset($debug['2'])){
			//������ ������� 
			$error .= "	".$tclass->str('function').((isset($debug['2']['class'])) ? strtoupper($debug['2']['class']).$debug['2']['type'] : '').$debug['2']['function']."()\n";
			//��� ���������
			$error .= "	".$tclass->str('file').$debug['2']['file']." : ".$debug['2']['line']."\n";
		}
		
		ob_clean();
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		</head>
		<body>
		';
		echo str_replace("\n",'<br>',$error);
		echo "<script>if (parent.loading) {parent.disable_loading();} alert(\"".e($error)."\");</script>";
		echo '</body></html>';
		exit;
	}
}


//-----------------------------------------------------------------------------------------------------------
// ���� �������� � ������� ��������
// ������ c ini
//-----------------------------------------------------------------------------------------------------------
// ������� ������ ini �����
// ���������� ������������� ������
function ReadIni($filename){

   if (!is_file($filename)){
        trigger_error(' ���� �� ������: <b>'.$filename.'</b>', E_USER_ERROR);
        return false;
   }

   $ini_array = parse_ini_file($filename, true);

return $ini_array;
}

//-----------------------------------------------------------------------------------------------------------

//������ ������ � ini ����
function WriteIni($filename, $data){

   if (!is_file($filename)){
        trigger_error(' ���� �� ������: <b>'.$filename.'</b>', E_USER_ERROR);
        return false;
   }

   $file=fopen($filename, "w");
   $delimiter = "\n";

   //������� ����� ��� ������ �� ������������� �������(���������� ����, ���� �� ������� ������ ����� ������, ��� ������������� ������)
   foreach($data as $key => $value) {
        if (!is_array($value)){
            fwrite($file, $key.' = '.$value.$delimiter);
        }
   }
   fwrite($file,$delimiter);
   // ������� ������
   foreach($data as $key => $value) {
        if (is_array($value)){
            fwrite($file, '['.$key.']'.$delimiter);
            foreach ($value as $k => $v){
                 fwrite($file, $k.' = '.$v.$delimiter);
            }
            fwrite($file, $delimiter);
        }
   }

   fclose($file);

return true;
}

//-----------------------------------------------------------------------------------------------------------

//������ ��������� ������ � ini �����
function ChangeIni($filename, $data){

     $ini = ReadIni($filename);
     return WriteIni($filename, array_merge($ini,$data));

}

//-----------------------------------------------------------------------------------------------------------


?>
