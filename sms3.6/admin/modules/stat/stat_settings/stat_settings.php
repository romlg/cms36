<?php

require_once (module('stat'));

class TSettings extends TStat {

	var $name = 'stat/stat_settings';
	
	########################

	function TSettings() {
		global $str, $actions;

		TStat::TStat();

		$actions[str_replace('/', '__', $this->name)] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.MySubmit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
			),
			'default' => array(
				'Восстановить по умолчанию',
				'Reset to Default',
				'link'	=> 'cnt.SaveDefault()',
				'img' 	=> 'icon.close.gif',
				'display'	=> 'block',
			),
		);

		$str[get_class_name($this)] = $str['tstat'] + array(
            // Фильтрация отчетов статистики
            'filters'	=> array(
				'Фильтрация отчетов статистики',
				'Filtration of statistics reports',
			),
			'filter'	=> array(
				'Включить фильтр',
				'Filter on',
			),
			'filter_hosts'	=> array(
				'Не показывать в отчетах статистику следующих хостов',
				'Do not show in reports statistic of the following hosts'
			),
			'filter_hosts_note'	=> array(
				'(хосты указываются через запятую и без <b>http://</b> и <b>www</b>)',
				'(Hosts are separated by comma and do not have <b>http://</b> and <b>www</b>)'
			),
			'filter_ips'	=> array(
				'Не показывать в отчетах запросы сделанные со следующих ip-адресов или подсети',
				'Do not show in reports requests from the following ip-addresses or subnet'
			),
			'filter_ips_note'	=> array(
				'(ip или подсети указываются через запятую; для указания подсети используйте <b>*</b>, например, 192.168.1.*)',
				'(ip-addresses or subnets are separated by comma; use <b>*</b> for subnet, for example, 192.168.1.*)'
			),
			'filter_pages'	=> array(
				'Не показывать в отчетах статистику следующий станиц',
				'Do not show in reports statistic of the following pages'
			),
			'filter_pages_note'	=> array(
				'(указывать страницы относительно хоста сайта, например, <b>/search/</b> - не считаться будут все страницы начинающиеся с данной строки (<b>/search/advanced/</b>); несколько страниц указываются через запятую)',
				'(enter relative URL of the page, for example, <b>/search/</b> - all the pages started with this URL will be ignored (<b>/search/advanced/</b>); (you can enter several URLs separated by comma)'
			),
            // Описание IP адресов
			'ip_alias' => array(
				'Описание ip-адресов',
				'Ip address describe',
			),
			'ip_alias_note'	=> array(
				'Для вашего удобства, вы можете присвоить определённым ip-адресам имена. Результат будет заметен при просмотре отчёта ip-адреса. Если ip-адресу присвоено имя, оно отобразится в скобках',
				'(add an alias for ip address)'
			),
            // События
            'events' => array('События', 'Events'),
            'event_alias' => array(
                'Список событий',
                'Event List',
            ),
            'event_alias_note'    => array(
                '(Укажите название события и соотвествующий ему URL)',
                '(set event name and corresponding url to it)'
            ),
            //
			'favorites' => array(
				'Избраное',
				'Favorites',
			),
			'favorites_note'	=> array(
				'(управление избранными отчетами)',
				'(enter relative URL of the page, for example, <b>/search/</b> - all the pages started with this URL will be ignored (<b>/search/advanced/</b>); (you can enter several URLs separated by comma)'
			),
			'add' => array(
				'Добавить',
				'Add',
			),
			'stat_popular' => array(
				'Популярные страницы',
				'Popular Pages',
			),
			'stat_search_ph' => array(
				'Поисковые фразы',
				'Search phrases',
			),
			'stat_ip' => array(
				'Ip-адреса',
				'Ip-addresses',
			),
            'saved' => array(
                'Данные успешно сохранены',
                'The information has been saved successfully',
            ),
		);
	}
	######################

	function Show() {
		$row = sql_getRows('SELECT * FROM '.STAT_SETTINGS_TABLE, true);
		$ip_alias = '<div style="overflow-y : scroll; height: 100"><table width=100% cellpadding="2" cellspacing="0" id="table_ip">';
		$ip_alias .= '<tr bgcolor="#EEEEEE"><td>Ип адрес</td><td>Имя</td><td></td></tr>';
		$ip_alias .= '<tr><td><input type=text name=ip size=12></td><td><input type=text name=ip_value></td><td><a href="#" title="'.$this->str('add').'" onclick="add(\'ip\'); return false;">[+]</td></tr>';
		$ip = unserialize(isset($row['ip'])?$row['ip']:'');
		if(!is_array($ip)) $ip = array();
		foreach($ip as $key=>$value) {
			$ip_alias .= '';
			$ip_alias .= '<tr><td><input type=hidden name="fld[ip]['.$key.']" value="'.$value.'">'.$key.'</td><td>'.$value.'</td><td><a href="#" onclick=\'if (confirm("Удалить "+this.parentNode.parentNode.cells[0].innerText+"?")) this.parentNode.parentNode.removeNode(true)\' title=\'Удалить\'>[-]</td></tr>';
		}
		$ip_alias .= '</table></div>';
		$row['ip_alias'] = $ip_alias;

        #events
        if (defined('STAT_EVENT_REPORT') && STAT_EVENT_REPORT) {
            $row2 = sql_getRows('SELECT * FROM '.STAT_SETTINGS_TABLE, true);
            $event_alias = '<div style="overflow-y : scroll; height: 100"><table width=100% cellpadding="2" cellspacing="0" id="table_events">';
            $event_alias .= '<tr bgcolor="#EEEEEE"><td>'.$this->str('name').'</td><td>'.$this->str('url').'</td><td></td></tr>';
            $event_alias .= '<tr><td><input type=text name=events size=20></td><td><input type=text name=events_value></td><td><a href="#" title="'.$this->str('add').'" onclick="add(\'events\'); return false;">[+]</td></tr>';
            $events = unserialize(isset($row['events'])?$row['events']:'');
            if(!is_array($events)) $events = array();
            foreach($events as $key=>$value) {
                $event_alias .= '';
                $event_alias .= '<tr><td><input type=hidden name="fld[events]['.$key.']" value="'.$value.'">'.$key.'</td><td>'.$value.'</td><td><a href="#" onclick=\'if (confirm("Удалить "+this.parentNode.parentNode.cells[0].innerText+"?")) this.parentNode.parentNode.removeNode(true)\' title=\'Удалить\'>[-]</td></tr>';
            }
            $event_alias .= '</table></div>';
            $row['events_alias'] = $event_alias;
        }

        # popular
		$row['popular'] = (isset($row['popular'])?unserialize($row['popular']):array());
		if(!is_array($row['popular'])) $row['popular'] = array();
		$popular = '<div style="overflow-y : scroll; height: 100; width:100%"><table width=100% cellpadding="2" cellspacing="0" id="table_popular">';
		$popular .= '<tr><td><input type=text name=popular></td><td><a href="#" title="'.$this->str('add').'" onclick="add(\'popular\'); return false;">[+]</td></tr>';
		foreach($row['popular'] as $key=>$value) {
			$popular .= '<tr><td><input type=hidden name="fld[popular][]" value="'.$value.'">'.$value.'</td><td><a href="#" onclick=\'if (confirm("Удалить "+this.parentNode.parentNode.cells[0].innerText+"?")) this.parentNode.parentNode.removeNode(true)\' title=\'Удалить\'>[-]</td></tr>';
		}
		$popular .= '</table></div>';
		$row['popular'] = $popular;		

		# search_ph
		$row['search_ph'] = (isset($row['search_ph'])?unserialize($row['search_ph']):array());
		if(!is_array($row['search_ph'])) $row['search_ph'] = array();
		$search_ph = '<div style="overflow-y : scroll; height: 100; width:100%"><table width=100% cellpadding="2" cellspacing="0" id="table_search_ph">';
		$search_ph .= '<tr><td><input type=text name=search_ph></td><td><a href="#" title="'.$this->str('add').'" onclick="add(\'search_ph\'); return false;">[+]</td></tr>';
		foreach($row['search_ph'] as $key=>$value) {
			$ip_alias .= '';
			$search_ph .= '<tr><td><input type=hidden name="fld[search_ph][]" value="'.$value.'">'.$value.'</td><td><a href="#" onclick=\'if (confirm("Удалить "+this.parentNode.parentNode.cells[0].innerText+"?")) this.parentNode.parentNode.removeNode(true)\' title=\'Удалить\'>[-]</td></tr>';
		}
		$search_ph .= '</table></div>';
		$row['search_ph'] = $search_ph;		

		# favorites_ip
		$row['favorites_ip'] = (isset($row['favorites_ip'])?unserialize($row['favorites_ip']):array());
		if(!is_array($row['favorites_ip'])) $row['favorites_ip'] = array();
		$favorites_ip = '<div style="overflow-y : scroll; height: 100; width:100%"><table width=100% cellpadding="2" cellspacing="0" id="table_favorites_ip">';
		$favorites_ip .= '<tr><td><input type=text name=favorites_ip></td><td><a href="#" title="'.$this->str('add').'" onclick="add(\'favorites_ip\'); return false;">[+]</td></tr>';
		foreach($row['favorites_ip'] as $key=>$value) {
			$favorites_ip .= '<tr><td><input type=hidden name="fld[favorites_ip][]" value="'.$value.'">'.$value.'</td><td><a href="#" onclick=\'if (confirm("Удалить "+this.parentNode.parentNode.cells[0].innerText+"?")) this.parentNode.parentNode.removeNode(true)\' title=\'Удалить\'>[-]</td></tr>';
		}
		$favorites_ip .= '</table></div>';
		$row['favorites_ip'] = $favorites_ip;

		$this->AddStrings($row);
		return Parse($row, 'stat/stat_settings/stat.settings.tmpl');
	}

	######################

	function Edit() {
		$rows = get('fld', array(), 'p');
		$default = (int)get('default', 0, 'p');
		if ($default) {
			$rows = array(
				'filter_ips' => '',
				'ip' => serialize(array()),
                'events' => serialize(array()),
				'favorites' => serialize(array()),
			);
		}

		$rows['ip'] = serialize($rows['ip']);
		$rows['popular'] = serialize($rows['popular']);
		$rows['search_ph'] = serialize($rows['search_ph']);
		$rows['favorites_ip'] = serialize($rows['favorites_ip']);
        if (isset($rows['events'])) $rows['events'] = serialize($rows['events']);

        foreach ($rows as $key=>$value) {
			if($this->getvalue('SELECT name FROM '.STAT_SETTINGS_TABLE.' where name="'.$key.'"')!=$key) {
				mysql_unbuffered_query("INSERT INTO ".STAT_SETTINGS_TABLE." (name, value) VALUES ('".$key."', '".$value."')");
			}
			else {
				mysql_unbuffered_query("REPLACE INTO ".STAT_SETTINGS_TABLE." (name, value) VALUES ('".$key."', '".$value."')");
			}
			if (sql_getError()) 
				return "<script>alert('".$this->str('error').": ".addslashes(sql_getError())."');</script>";
		}
		
		if ($default)
			echo "<script>window.parent.location.reload();</script>";
		return "<script>alert('".$this->str('saved')."');</script>";
	}
	
	######################
}

$GLOBALS['stat__stat_settings'] = & Registry::get('TSettings');

?>