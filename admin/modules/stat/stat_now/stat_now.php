<?php

require_once (module('stat'));

class TNow extends TStat {

	var $name = 'stat/stat_now';

	########################

	function TNow() {
		global $str;

		TStat::TStat();

		$str[get_class_name($this)] = $str['tstat'] + array(
            'ban' => array('Действие',               'Action',),
			'ip'	=> array(
				'IP-адрес',
				'IP-address',
			),
			'pathes'	=> array(
				'Путь по сайту',
				'Site pathes',
			),
			'pages'	=> array(
				'Просмотрено страниц',
				'Pages viewed',
			),
			'time'	=> array(
				'Проведенное время',
				'Time spent',
			),
			'last'	=> array(
				'Последняя загрузка',
				'Last Access',
			),
            'confirm1' => array(
                'Вы уверены, что хотите поместить IP %s в черный список?',
                'Are you sure you want to ban IP %s?'
            ),
            'confirm2' => array(
                'Вы уверены, что хотите удалить IP %s из черного списка?',
                'Are you sure you want to unban IP %s?'
            ),
            'ban1' => array(
                'Забанить',
                'Ban',
            ),
            'ban2' => array(
                'Отбанить',
                'Unban',
            ),
		);
	}

	######################

	function Show() {
		global $limit;
		$this->Init();
		$this->AddStrings($ret);
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));

		$count = sql_getValue("SELECT COUNT(*) FROM ".STAT_SESSIONS_TABLE." WHERE robot=0 AND time_last>".(time() - STAT_SESS_TIME*60));
		$data = sql_getRows("SELECT ip, 'count_pages', path, time, time_last FROM ".STAT_SESSIONS_TABLE." WHERE robot=0 AND time_last>".(time() - STAT_SESS_TIME*60)." ORDER BY time_last DESC LIMIT ".$offset.", ".$limit);

		// Построить массив для всех страниц ($this->path)
		if ($data) {
			$pages_id = array();
			foreach ($data as $row) {
				$pages_id = array_merge($pages_id, explode(' ', trim($row['path'])));
			}
			
			$pages_id = array_unique($pages_id);
			$this->path_pages = sql_getRows("SELECT id, CONCAT(host, uri) AS page FROM ".STAT_PAGES_TABLE." WHERE id IN (".join(', ', $pages_id).")", true);
			$this->path_keys = array_flip(array_keys($this->path_pages));
			foreach ($this->path_pages as $page_id => $href) {
				$ret['pathes']['row'][] = array(
					'key' => $this->path_keys[$page_id] + 1,
					'href' => $href,
				);
			}
		}

		// Main Table
        $columns = array(
                array(
                    'header'    => 'ip',
                    'nowrap'    => '1',
                    'type'        => 'ip',
                ),
                array(
                    'header'    => 'pages',
                    'align'        => 'right',
                    'type'        => 'pages',
                ),
                array(
                    'header'    => 'pathes',
                    'type'        => 'path',
                ),
                array(
                    'header'     => 'time',
                    'align'        => 'right',
                    'type'         => 'time',
                ),
                array(
                    'header'     => 'last',
                    'align'        => 'right',
                    'type'         => 'last',
                ),
        );
        if (isset($GLOBALS['_stat']['stat/stat_banlist']))
            $columns[] = array(
                'header'     => 'ban',
                'type'       => 'ban',
                'align'      => 'center',
            );
		$ret['table'] = $this->stat_table(array(
			'columns'	=> $columns,
			'data' => $data,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));
		$ret['navig'] = $this->NavigForm(array(
			'hidden'	=> array('show' => $this->show),
		));
		return Parse($ret, 'stat/stat.no_navig.tmpl');
	}

	######################

	### Формирует пути сайта по данным из path (исплользуя переменную $this->path)
	function table_get_path($val) {
		$pages_id = explode(' ', trim($val));
		foreach ($pages_id as $page) {
			$ret[] = '<a href="http://'.$this->path_pages[$page].'" title="http://'.$this->path_pages[$page].'" target="_blank" class="Tpath">['.($this->path_keys[$page] + 1).']</a>';
		}
		return join('<span class="Tpath">&nbsp;&gt; </span>', $ret);
	}

	######################

	function table_get_ip($val, $row) {
//		$ced = "window.open('dialog.php?page=stat/stat_ip&do=showwhois&whois_ip=".$val."', 'whois', 'width=500, height=400, resizable=1, status=1, scrollbars=0')";
		$ced = "window.open('https://www.nic.ru/whois/?ip=".long2ip($val)."', 'whois', 'width=500, height=400, resizable=1, status=1, scrollbars=1')";
		return 
			$this->table_get_advanced('ip', long2ip($val)).
			'<a href="#" onclick="'.$ced.'; return false;">'.long2ip($val).'</a>';
	}

	######################

	function table_get_pages($val, $row) {
		return count(explode(' ', $row['path']));
	}

	######################

	function table_get_time($val, $row) {
		$time = time() - $val;
		return floor($time/60).':'.sprintf('%02d', $time%60);
	}

	######################

	function table_get_last($val, $row) {
		return date('H:i', $val);
	}

	######################

    function table_get_ban($val, $row) {
        // Узнаем забаннен этот ip или нет
        $res = sql_getValue("SELECT id FROM stat_banlist WHERE ip='".$row['ip']."'");
        if (!$res) {
            // Можно забаннить
            return "<input type='image' src='/admin/images/icons/icon.rcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban1')."' onclick='if (confirm(\"".sprintf($this->str('confirm1'), long2ip($row['ip']))."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editinsertip&ip=".$row['ip']."\"; return false;'>";
        } else {
            // Можно разбаннить
            return "<input type='image' src='/admin/images/icons/icon.gcard.gif' width='16' height='18' style='cursor: hand' title='".$this->str('ban2')."' onclick='if (confirm(\"".sprintf($this->str('confirm2'), long2ip($row['ip']))."\") == true) location.href=\"cnt.php?page=stat/stat_banlist&do=editdeleteip&id=".$res."\"; return false;'>";
        }
    }
}

$GLOBALS['stat__stat_now'] = & Registry::get('TNow');

?>