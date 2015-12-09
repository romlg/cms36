<?php

function cmp($banner1, $banner2) {
	if ($banner1['level'] == $banner2['level']) {
		if ($banner1['priority'] == $banner2['priority']) return 0;
		return ($banner1['priority'] < $banner2['priority']) ? -1 : +1;
	}
	return ($banner1['level'] > $banner2['level']) ? -1 : +1;
}


class TBanners {

	function shuffle_me($shuffle_me) {
		$randomized_keys = array_rand($shuffle_me, count($shuffle_me));
		foreach($randomized_keys as $current_key) {
			$shuffled_me[$current_key] = $shuffle_me[$current_key];
		}
		return $shuffled_me;
	}

	function goto() {
		$id = (int)get('id', 0, 'g');
		if (!$id) {
			return;
		}
		if ($link = $this->ClickBanner($id)) {
			redirect($link);
		}
	}

	function ClickBanner($id) {
		$table = 'banners';
		sql_query('UPDATE '.$table.' set clicks=clicks+1 WHERE id='.$id);
		return sql_getValue('SELECT link FROM '.$table.' WHERE id='.$id);
	}

	function GetBanners($id, $table, $position = '', $limit = '', $random = true, $root_id = '') {
		$id = (int)$id;
		if (empty($id)) {
			return;
		}
		if ($position) {
			$position = " AND  position='".$position."' ";
		}
		$columns = sql_getRows('SHOW COLUMNS FROM banners', 'Field');
        if ($root_id) {
        	if (isset($columns['show_at_sites'])) {
        	    $page_obj = & Registry::get('TPage');
            	$root_state = " AND (
            	   root_id='".$root_id."' OR (
            	       FIND_IN_SET(".$root_id.", show_at_sites) AND
            	       (FIND_IN_SET(".$page_obj->content['id'].", pages) OR pages = '') AND
            	       (NOT FIND_IN_SET(".$page_obj->content['id'].", except) OR except = '')
            	   )
            	)";
        	} else $root_state = " AND (root_id='".$root_id."')";
        	if (defined('LANG_SELECT') && LANG_SELECT === true) $root_state .= " AND lang='".lang()."'";
        } else {
            $root_state = " AND lang='".lang()."'";
        }
		$order = ' ORDER BY priority';
		$tree_obj = & Registry::get('TTreeUtils');
		$pids = $tree_obj->getPids($id);
		foreach ($pids as $k => $v) {
			$sql_parts_except[] = "(FIND_IN_SET('".$v['id']."', except))";
			$sql_parts_pages[] = "(FIND_IN_SET('".$v['id']."', pages))";
		}

		$sql = "SELECT * FROM ".$table." WHERE visible>0
		AND (	".implode(' or ', $sql_parts_pages)."	or (pages = '')	)
		AND NOT (	".implode(' or ', $sql_parts_except)." )	".$position.$root_state.$order;

		$res = sql_getRows($sql, true);

		if (($limit > 0) && (count($res) > $limit)) {
			//здесь надо срезать те баннеры которые были выставлены в более корневых уровн€х меню
			foreach ($res as $k => $v) {
				$level = -1;
				$pages = explode(',', $v['pages']);
				foreach ($pids as $k2 => $v2) {
					if (!in_array($v2['id'], $pages)) {
						continue;
					}
					$level = $v2['level'];
				}
				$res[$k]['level'] = $level;
			}
			uksort($res, cmp);
		}

		if (count($res) > 1) {
			if ($random) {
				srand((float) microtime() * 10000000);
				//$res = $this->shuffle_me($res);
				shuffle($res);
			}
			if ($limit > 0) {
				$i = 0;
				$new_res = array();
				foreach ($res as $key=>$val) {
					if ($i == $limit) break;
					$new_res[$key] = $res[$key];
					$i++;
				}
				$res = $new_res;
			}
		}

		// вставим размеры дл€ баннеров, если это картинка //Timon
		foreach($res as $key => $val) {
			if ($val['image']) {
				if (is_file(substr($val['image'], 1))) {
					$size = getimagesize(substr($val['image'], 1));
					$res[$key]['width'] = $size[0];
					$res[$key]['height'] = $size[1];
					$res[$key]['size'] = $size[3];
					if ($size['mime'] == 'application/x-shockwave-flash') {
						// ¬ерсию минимально необходимого флеш плеера определ€ем приближенно: 
						// - если первые три символа файла это FWS, то компресси€ не используетс€ и можно 
						// испльзовать 5-ю версию плеера
						// - если это символы CWS, то была использована компресси€ и нужен плеер 6-й версии
						// TODO: по возможности сделать более точное определение минимально необхлдимой версии флеш плеера
						$fp = fopen(substr($val['image'], 1), 'r');
						$text = '';
						if ($fp) {
							$text = fread($fp, 3);
							fclose($fp);
						}
						if ($text == 'FWS') $version = 5;
						else if ($text == 'CWS') $version = 6;
						else $version = 7; // Ќа вс€кий случай
						$link = '?module=banners&method=goto&id='.$val['id'];
						$res[$key]['swf'] = '
						<script>
						try {
							insertFlash('.$version.', "'.$val['image'].'", "'.(isset($val['alt_image']) ? $val['alt_image'] : '').'", "'.$link.'", "'.$res[$key]['width'].'", "'.$res[$key]['height'].'", "'.$val['id'].'");
						} catch(e) {} finally {}
						</script>';
					}
					else {
						$res[$key]['swf'] = 0;
					}
				}
			}
			// —тавим target
			if ($val['target'] == 1) {
				$res[$key]['target'] = '_blank';
			}
			else {
				$res[$key]['target'] = '_self';
			}
		}

		if (!empty($res)){
			//добавл€ем баннерам просмотр
			sql_query('UPDATE '.$table.' SET views=views+1 WHERE id IN ('.implode(',', array_keys($res)).')');
		}


		return $res;
	}

	function show_banners($cfg) {
		// limit
		if (!isset($cfg['limit'])) {
			$cfg['limit'] = 1;
		}
		// table
		if (!isset($cfg['table'])) {
			$cfg['table'] = 'banners';
		}

		$page_obj = & Registry::get('TPage');

		$ret['banners'] = $this->GetBanners($page_obj->content['id'], $cfg['table'], $cfg['position'], $cfg['limit'], $cfg['random'], ROOT_ID);
		return $ret;
	}

}// end of class TBanner

?>