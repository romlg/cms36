<?php

class TSiteGroups extends TTable {

	var $name = 'site_groups';
	var $table = 'auth_groups';

	########################

	function TSiteGroups() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Группы пользователей сайта',
				'Site User Groups',
			),
			'name'	=> array(
				'Название группы',
				'Group Title',
			),
			'user_name' => array(
				'Имя пользователя',
				'User Name',
			),
			'user_login' => array(
				'Логин',
				'Login',
			),
			'discount_type' => array(
				'Тип скидочной группы',
				'Discount type',
			),
			'saved' => array(
				'Данные успешно сохранены',
				'Data has been saved successfully'
			),
			'privs_added' => array(
				'Группе добавлены %d привилегий',
				'Added %d privileges to group',
			),
			'privs_deleted' => array(
				'У группы удалено %d привилегий',
				'Deleted %d privileges from group',
			),
		));

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'moveup' => &$actions['table']['moveup'],
			'movedown' => &$actions['table']['movedown'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
			'users' => array(
				'Пользователи группы',
				'Group Users',
				'link'	=> 'cnt.editGroupUsers()',
				'img' 	=> 'icon.user_groups.gif',
				'display'	=> 'none',
			),
			'rights' => array(
				'Права группы',
				'Group Rights',
				'link'	=> 'cnt.editGroupPrivs()',
				'img' 	=> 'icon.users.gif',
				'display'	=> 'none',
			),
		);
	}

	########################

	function Show() {
		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				if($this->Allow($action)) {
					return $this->$action();
				}
				else {
					return $this->alert_method_not_allowed();
				}
			}
		}
		$_GET['limit'] = -1;
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
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SEARCH,
				),
			),
			'from'		=> $this->table,
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'orderby'	=> 'priority',
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
		), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################

	function editGroupUsers() {
		$group_id = (int)get('id', 0, 'gp');
		if (!$group_id) {
			return;
		}

		$ret = array();

		$_GET['limit'] = -1;
		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'auth_users.name',
					'display'	=> 'user_name',
					'flags'		=> FLAG_SORT,					
				),
				array(
					'select'	=> 'auth_users.login',
					'display'	=> 'user_login',
					'flags'		=> FLAG_SORT,
				),
			),
			'from' => 'auth_users LEFT JOIN auth_users_groups ON (auth_users_groups.user_id = auth_users.id)',
			'params' => array('page' => $this->name, 'do' => 'editgroupusers', 'id' => $group_id),
			'orderby' => 'auth_users.name',
			'where' => 'auth_users_groups.group_id='.$group_id,
			'roll' => 0,
		), $this);

		$this->AddStrings($ret);

		return $this->Parse($ret, $this->name.'.groupusers.tmpl');
	}


	########################

	function editGroupPrivs() {
		$group_id = (int)get('id');

		$ret = array();
		$this->AddStrings($ret);

		$ret['id'] = $group_id;
		$ret['modules'] = $this->getPrivs($group_id);

		return $this->Parse($ret, $this->name.'.groupprivs.tmpl');
	}

	########################

	function savePrivs() {
		$group_id = (int)get('id', 0, 'p');
		if (!$group_id) {
			// ошибка
			return;
			//return $this->Error($id);
		}

		$post_privs = &$_POST['privs'];
		$db_privs = $this->getPrivs($group_id);

		$to_delete = array();
		$to_add = array();

		foreach ($db_privs as $module_name => $module) {
			foreach ($module['privs'] as $priv) {
				$priv_code = $priv['priv_code'];
				$new_state = isset($post_privs[$priv_code]) ? 1 : 0;
				$old_state = $priv['checked'] ? 1 : 0;

				if ($new_state == 0 && $old_state == 1) {
					//delete
					$to_delete[] = $priv_code;
				}
				if ($new_state == 1 &&  $old_state == 0) {
					//insert
					$to_add[] = $priv_code;
				}
			}
		}

		$report_str = '';
		if (count($to_delete)) {
			$sql = "
				DELETE FROM
					auth_groups_privs
				WHERE
					group_id = ".$group_id." AND
					priv_code IN ('".implode('\', \'', $to_delete)."')
			";
			sql_query($sql);

			$report_str .= sprintf($this->str('privs_deleted').'\n', count($to_delete));
		}

		if (count($to_add)) {
			foreach ($to_add as $k => $v) {
				$to_add[$k] = '('.$group_id.', \''.$v.'\')';
			}
			$sql = "
				INSERT INTO
					auth_groups_privs
				(group_id, priv_code)
				VALUES
			".implode(', ', $to_add);
			sql_query($sql);

			$report_str .= sprintf($this->str('privs_added').'\n', count($to_add));
		}

		return '<script>alert(\''.$this->str('saved').'\n'.$report_str.'\');</script>';
	}

	########################

	function getModulesInfo() {
		static $modules_info = array();
		if (!empty($modules_info)) {
			return $modules_info;
		}
		// @todo переделать получение конфига модулей
		$modules = $GLOBALS['cfg']['modules'];
		foreach ($modules as $module_name => $config) {
			$auth_file = $module_name.'/module.info';
			$auth_pathname = _name('modules/', $auth_file, SITE_CORE);
			if (!is_file($auth_pathname)) {
				continue;
			}
			include_once ($auth_pathname);
		}
		return $module_info;
	}

	########################

	function getPrivs($group_id) {
		$group_id = (int)$group_id;

		$sql = "
			SELECT
				priv_code
			FROM
				auth_groups_privs
			WHERE
				group_id = ".$group_id;

		$user_privs = sql_getRows($sql);

		$module_info = $this->getModulesInfo();

		$modules = array();
		foreach ($module_info as $module_name => $module) {
			if (!is_array($module['privs']) || empty($module['privs'])) {
				continue;
			}
			$modules[$module_name] = array();
			$modules[$module_name]['module_name'] = $module_name;
			// @todo поправить определение названия модуля
			$modules[$module_name]['module_title'] = $GLOBALS['cfg']['modules'][$module_name]['display'][int_langId()];
			foreach ($module['privs'] as $priv_code => $display) {
				$modules[$module_name]['privs'][] = array(
					'priv_title' => $display[int_langId()],
					'priv_code' => $priv_code,
					'checked' => in_array($priv_code, $user_privs),
				);
			}
		}

		return $modules;
	}

	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$row = $this->getRow($id);
		}
		else {
			$row = array();
		}
		$this->SetDefaultValues($row);
		$this->AddStrings($row);
		
		$discount_types = sql_getRows("SELECT id, name FROM discount_types ORDER BY priority", true);
		$row['discount_types'] = $this->GetArrayOptions($discount_types, $row['discount_type'], true);
		
		$row['table'] = sql_getRows("
		SELECT nt.id, nt.descr,
		IF (
		ng.group_id =".$id.", ng.group_id, ''
		) AS group_id
		FROM notify_types AS nt
		LEFT JOIN notify_groups AS ng ON ng.notif_id = nt.id
		AND ng.group_id =".$id);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = $this->Commit(array('name'));
		$reload = !sql_getErrNo() ? 'window.parent.location.reload()' : '';
		if (!is_int($id)) {
			return $this->Error($id);
		}
		//Обновляем права на уведомления
		$ntid = get('ntid','','p');
		if ($ntid) {
			sql_query("DELETE FROM notify_groups WHERE group_id = ".$id);
			foreach ($ntid as $k=>$v){
				$sql = "REPLACE INTO notify_groups SET group_id = ".$id.",notif_id=".$v;
				sql_query($sql);
			}
		}
		return '<script>alert(\''.$this->str('saved').'\'); '.$reload.'</script>';
	}

	######################

	function Info() {
		return array(
			'version'	=> get_revision('$Revision: 1.1 $'),
			'checked'	=> 1,
			'disabled'	=> 1,
			'type'		=> 'checkbox',
		);
	}

	########################
}

$GLOBALS['site_groups'] = & Registry::get('TSiteGroups');

?>