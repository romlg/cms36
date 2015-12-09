<?php

/* $Id: events.php,v 1.7 2007/08/17 13:13:25 vetal Exp $
 */
require_once (elem_inc('notify'));

class TEvents extends TNotify {

	// название модуля
	var $name = 'events';
	var $table = 'notify_events';
	// отображать ли селектор языка?
	var $selector = false;

	//-------------------------------------------------------------------------------
	
	function TEvents(){
		global $actions, $str;

		// обязательно вызывать
		TNotify::TNotify();
		TTable::TTable();

		// экшены для метода Show (когда отображаем табличку)
		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
		);

		if (is_devel()){
			$actions[$this->name]['create'] = &$actions['table']['create'];
			$actions[$this->name]['delete'] = array(
				'Удалить',
				'Delete',
				'link' => 'cnt.deleteItem();',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			);
		}
		
		$actions[$this->name.'.editform'] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link' => 'cnt.document.forms.editform.submit();',
				'img' => 'icon.edit.gif',
				'display' => 'none',
			),
			'tpl' => array(
				'Шаблон',
				'Template',
				'link' => 'cnt.showTemplate();',
				'img' => '../third/template.png',
				'display' => 'none',
			),
			'close' => &$actions['table']['close'],
		);

		// строковые константы модуля
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'		  => array( 'События', 'Events',),
			'description' => array( 'Описание для клиента', 'Description for client',),
			'name' => array( 'Событие', 'Event',),
			'method' => array( 'Тип уведомления', 'Notify type',),
			'email' => array( 'Отправка по e-mail', 'Send by e-mail',),
			'sms' => array( 'Отправка по sms', 'Send by sms',),
			'saved' => array( 'Сохранение прошло успешно', 'Retention passed successfully',),
			'error' => array( 'Ошибка', 'Error',),
			'recipient' => array( 'Получатель', 'Recipient',),
			'client' => array( 'Клиент', 'Client',),
			'admin' => array( 'Администратор', 'Admin',),
			'header' => array( 'получатели', 'recivers',),
			'delete' => array( 'удалить', 'delete',),
			'comments' => array( 'Комментарии', 'Comments',),
			'fullname' => array( 'имя', 'fullname',),
			'where' => array( 'куда', 'where',),
			
			'error_name' => array( 'Событие с таким именем уже существует', 'Event with this name already exists.',),
		));
		
		$id = get('id', 0, 'pg');
		if ($id){
			$id = sql_getValue("SELECT name FROM ".$this->table." WHERE id=".$id);
			$edit =  array('Событие: '.$id, 'Event: ',);
		} else { 
			$edit =  array('Настройка события', 'Events properties',); 
		}

		$str[get_class_name($this)]['editform'] = $edit;
	}
	
	//-------------------------------------------------------------------------------
	
	function Show() {
		
		// обязательная фигня
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		// подключение библиотеки для построения таблиц
		require_once (core('ajax_table'));
		
		// строим таблицу
		$columns = array(
			array(
				'select'	=> 'id',
				'display'	=> 'id',
				'type'		=> 'checkbox',
				'width'		=> '1px',
			),
		);
		if (is_devel()){
			$columns[] = array(
				'select'	=> 'name',
				'display' 	=> 'name',
				'width'		=> '1px',
				'flags' 	=> FLAG_SEARCH | FLAG_SORT,
			);
		}
		$columns[] =array(
				'select'	=> 'recipient',
				'display' 	=> 'recipient',
				'width'		=> '1px',
				'flags' 	=> FLAG_SORT,
				'type'      => 'recipient',
			);
		$columns[] =array(
				'select'	=> 'description',
				'display' 	=> 'description',
			);
		$columns[] =array(
				'select'	=> 'comments',
				'display' 	=> 'comments',
			);
			
		$data['table'] = ajax_table(array(
			'columns'	=> $columns,	
			'from'		=> $this->table,
			'params'	=> array('page' => 'notify/'.$this->name, 'do' => 'show'),
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID=cb.value',
			//'_sql' => true,
		), $this);
		
		$this->AddStrings($data);
		$data['thisname'] = 'events';
		return Parse($data, "notify/tmpls/".$this->name.'.tmpl');
	}

	//-------------------------------------------------------------------------------
	
	function table_get_recipient(&$value, &$column, &$row) {
		return $this->str($value);
	}
	
	//-------------------------------------------------------------------------------
	
	function EditForm(){
		$id = get('id', 0,'g');
		// обязательная фигня
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}		
		
		$data['is_devel'] = is_devel();
		foreach ($this->options as $k=>$v){
			$data['types'][$k] = $this->str($k);
		} 

		if ($id){
			$data['row'] = $this->getRow($id);
		}
		$admins = sql_getRows("SELECT admin_id, type FROM notify_admins WHERE event=".$id." AND root_id=".domainRootId());
		foreach ($admins as $k=>$v){
			$data['admins'][$v['type']][$v['admin_id']] = $this->getRow("SELECT fullname,".$v['type']." FROM admins WHERE id=".$v['admin_id']);
		}

		$plugins = sql_getRows("SELECT plugin FROM notify_compare WHERE event=".$id);
		foreach ($plugins as $k=>$v){
			$data['checked'][$v] = $this->str($v);
		} 
		//pr($data);
		$this->AddStrings($data);
		return Parse($data, "notify/tmpls/".$this->name.'.editform.tmpl');
	}
	
	//-------------------------------------------------------------------------------
	
	function Edit(){
		global $notify_errors;
		$id = get('id', '','p');

		$id = $this->Save(array('name'));
		if (!(int)$id){	
			return $id;
		}
		if (!empty($notify_errors)){	
			foreach ($notify_errors as $k=>$v){
				if ($v['errno'] == E_USER_ERROR){
					return "<script>alert('".$this->str('error')." : ".$v['errstr']."');</script>";
				}
			}
		}
		$script="window.top.location = window.top.location + '&id=".$id."';";
		return "<script>alert('".$this->str('saved')."'); window.top.opener.location.reload(); ".$script."</script>";
	}
	
	//-------------------------------------------------------------------------------

	function deleteItems(){
		$id = get('id', '','p');
		
		if (is_devel()){
			foreach ($id as $k=>$v){
				$ids .= $v.",";
			}
			$ids = substr($ids,0,-1);
			$sql = sql_query("DELETE FROM ".$this->table." WHERE id IN(".$ids.")");
			if (!$sql){
				$err = sql_getError();
				trigger_error($err, E_USER_ERROR);
				return "<script>alert('".$this->str('error')." : ".$err."');</script>";
			}
			else {
				return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
			}
		}
	}
	
	//-------------------------------------------------------------------------------
	
	function Save($unique){
		
		$fld = get('fld', array(), 'p');
		$id = get('id', '','p');
	
/*
    [recipient] => admin
    [types] => Array
        (
            [email] => on
            [sms] => on
        )

    [admins] => Array
        (
            [email] => Array
                (
                    [0] => 1
                    [1] => 3
                )

            [sms] => Array
                (
                    [0] => 2
                    [1] => 1
                    [2] => 3
                )

        )*/
		//Проверяем уникальные поля
		$query = '';
		foreach ($unique as $k=>$field){
			if (!empty($fld[$field])){
				$query .= " `".$field."`='".$fld[$field]."' OR";
			}
		}
		if (!empty($query)){
			//обрезаем последний OR
			$query = substr($query,0,-2);
			//запрашиваем id
			$uid = sql_getValue("SELECT id FROM ".$this->table." WHERE ".$query);
			if ($uid && $id!=$uid){
				return "<script>alert('".$this->str('error_name')."');</script>";
			}
		}
		
		// добавляем новую запись	
		if (!$id){
			$sql = sql_query("INSERT INTO ".$this->table." (`name`,`description`,`comments`,`recipient`) VALUES('".htmlspecialchars($fld['name'])."', '".htmlspecialchars($fld['description'])."', '".htmlspecialchars($fld['comments'])."', '".$fld['recipient']."')");	
			if (!$sql){
				trigger_error(sql_getError(), E_USER_ERROR);
			} else {
				$id = sql_getLastId();
			}
		} else {
			if (is_devel()){
				$sql = sql_query("UPDATE ".$this->table." SET name='".htmlspecialchars($fld['name'])."', description='".htmlspecialchars($fld['description'])."', comments='".htmlspecialchars($fld['comments'])."',recipient='".$fld['recipient']."' WHERE id=".$id);	
				if (!$sql){
					trigger_error(sql_getError(), E_USER_ERROR);
				}
			} elseif(isset($fld['description']) & !empty($fld['description'])) {
				$sql = sql_query("UPDATE ".$this->table." SET description='".htmlspecialchars($fld['description'])."' WHERE id=".$id);	
				if (!$sql){
					trigger_error(sql_getError(), E_USER_ERROR);
				}			
			}
		}
		//удаляем всех админов для данного события
		$root = domainRootId();
		sql_query("DELETE FROM notify_admins WHERE event=".$id." AND root_id=".$root);
		$types = $fld['types'];
		if ($fld['recipient'] == 'admin'){
			unset($fld['types']);
			if (isset($fld['admins'])){
				foreach ($fld['admins'] as $plugin=>$it){
					if (isset($types[$plugin])){
						foreach ($it as $k=>$admin_id){
							sql_query("INSERT INTO notify_admins(`event`,`admin_id`,`type`,`root_id`) VALUES(".$id.",".$admin_id.",'".$plugin."',".$root.")");
						}
						$fld['types'][$plugin] = 'on';
					}
				}
			}
		}
		
		$sql = sql_query("DELETE FROM notify_compare WHERE event=".$id);
		if (!$sql){
			trigger_error(sql_getError(), E_USER_ERROR);
		}								
		
		if (isset($fld['types']) & !empty($fld['types'])){	
			foreach ($fld['types'] as $k=>$v){
				$sql = sql_query("INSERT INTO notify_compare(`event`,`plugin`) VALUES ('".$id."', '".$k."')");								
				if (!$sql){
					trigger_error(sql_getError(), E_USER_ERROR);
				}
			}
		}
		return $id;
	}
	
	//-------------------------------------------------------------------------------
}

$GLOBALS['events'] = &Registry::get('TEvents');
?>
