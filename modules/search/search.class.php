<?php

include_once('modules/news/news.class.php');
include_once('modules/object/object.class.php');

class TSearch {

	var $search;

	function search() {

		$this->find();

		$tpl = & Registry::get('TTemplate');
		$tpl->config_load($GLOBALS['domain'].'__'.lang().'.conf', 'search');

        $ret['pages'] = TContent::getNavigation($this->search['total'],$this->search['limit'],$this->search['offset'],"search");

        $ret['search'] = $this->search;
        
        // Сохраняем в статистике
        if (!$ret['search']['type']) {
	        $client = isset($GLOBALS['client_id']) ? $GLOBALS['client_id'] : 0;
	        if (!empty($ret['search']['query'])) 
	        	sql_query('INSERT INTO stat_search (root_id, client_id, keyword, date) VALUES ('.ROOT_ID.', '.$client.', "'.$ret['search']['query'].'", '.time().')');
        }

        if (!$ret['search'] ||$ret['search']['total'] > 0) unset($tpl->_tpl_vars['sitemap']);
		return $ret;
	}

	function show_search_form() {
        $page = & Registry::get('TPage');
		$page->tpl->config_load($GLOBALS['domain'].'__'.lang().'.conf', 'search');
        $fld = get('fld',NULL,'pg');
        $limit = !empty($_GET['limit']) ? $_GET['limit'] : ($page->tpl->get_config_vars("search_limit") ? $page->tpl->get_config_vars("search_limit") : 10);
		return array('query' => $fld['query'], 'limit'=>$limit);
	}

	function find() {
        $page = & Registry::get('TPage');
		$page->tpl->config_load($GLOBALS['domain'].'__'.lang().'.conf', 'search');		
		if (isset($_GET['fld']['query'])) {
            $page->tpl->_tpl_vars['text']['text'] = "";
			$query = h($_GET['fld']['query']);
			$limit = !empty($_GET['limit']) ? $_GET['limit'] : ($page->tpl->get_config_vars("search_limit") ? $page->tpl->get_config_vars("search_limit") : 10);
			$offset = !empty($_GET['offset']) ? $_GET['offset'] : 0;
			$type = !empty($_GET['type']) ? $_GET['type'] : ''; // Параметр, определяющий, в каком разделе искать 
			
			if (!empty($query)){
				if ($type) {
					// Нужна только одна таблица
					$sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_".$type."
	                        (`id` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `description` TEXT, `text` TEXT)";
				} else {
					// Временные таблицы для результатов поиска
					$sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_object
	                        (`id` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `description` TEXT, `text` TEXT)";
					$sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_text
	                        (`id` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `description` TEXT, `text` TEXT)";
					$sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_news
	                        (`id` INT(11), `name` VARCHAR(255), `type` VARCHAR(16), `description` TEXT, `text` TEXT)";
				}
				foreach ($sql as $_sql)	sql_query($_sql);
				
				if ($type) {
					if (substr($type, 0, 5) == 'news_') $func = 'findInNews';
					else $func = 'findIn'.$type;
					if (method_exists($this, $func)) $this->$func($query);
				} else {
					$this->findInText($query);
					$this->findInObject($query);
					$this->findInNews($query);
				} 

				$limit_str = "";
	            if ($limit != -1)
	            $limit_str = " LIMIT ".$offset.", ".$limit;

	            if ($type) $res = $this->getTypeResults($query, $type, $limit_str);
	            else $res = $this->getAllResults($limit_str);
	
	            $page_obj = & Registry::get('TPage');

	            $url = $page_obj->url.'/?fld[query]='.$query;
	            $this->search = array(
	            	'type'		=> $type,
	            	'html'		=> isset($res['html'])? $res['html'] : null,
		            'query'		=> $query,
		            'total'		=> $res['total'],
		            'limit'		=> $limit,
		            'offset'	=> $offset,
		            'selected'	=> isset($res['html'])? $res['selected'] : count($res['list']),
		            'results'	=> $res['list'],
	            );
			}
		}
	}
	
	function getTypeResults($query, $type, $limit_str){
		$res = sql_getRows("SELECT * FROM tmp_".$type." ".$limit_str, true);
		$total = sql_getValue("SELECT COUNT(*) FROM tmp_".$type);
		
		$words = explode(' ', $query);
		foreach ($res as $i => $val) {
			if ($type == 'text') {
				unset($res[$i]['description']);
			}
			$res[$i]['href'] = $this->getHref($val['id'],$val['type']);
			$res[$i]['path'] = $this->getPath($val['id'],$val['type']);
	
			//@todo правильная обрезка текста, анонсирующего найденный раздел
			$res[$i]['description'] = $this->convertText($res[$i]['description'],$words);
			$res[$i]['text'] = $this->convertText($res[$i]['text'],$words);
			$res[$i]['name'] = $this->convertText($res[$i]['name'],$words,0);
		}
		return array('list' => $res, 'total' => $total);
	}

	function getAllResults($limit_str){
		$res[0]['title'] = 'ОБЪЕКТЫ НЕДВИЖИМОСТИ';
		$res[0]['name'] = 'object';
		$res[0]['list'] = sql_getRows("SELECT SQL_CALC_FOUND_ROWS * FROM tmp_object ".$limit_str, true);
		$res[0]['total'] = (int)sql_getValue("SELECT FOUND_ROWS()");
		
		$res[1]['title'] = 'НОВОСТИ';
		$res[1]['name'] = 'news';
		$res[1]['list'] = sql_getRows("SELECT SQL_CALC_FOUND_ROWS * FROM tmp_news ".$limit_str, true);
		$res[1]['total'] = (int)sql_getValue("SELECT FOUND_ROWS()");
		
		$res[2]['title'] = 'ДРУГИЕ РАЗДЕЛЫ';
		$res[2]['name'] = 'text';
		$res[2]['list'] = sql_getRows("SELECT SQL_CALC_FOUND_ROWS * FROM tmp_text ".$limit_str, true);
		$res[2]['total'] = (int)sql_getValue("SELECT FOUND_ROWS()");

		$_total = 0;
		foreach ($res as $k=>$v) $_total += $v['total'];
		
		return array('list' => $res, 'total' => $_total);
	}

	function findInObject($query){
		//Поиск по таблице объектов
		$this->searchInTables('_object', $query, 'objects', array('id', 'address', 'short_description', 'description'), array('address', 'description', 'short_description'), 'visible>0', 'object');
	}
	
	function findInNews($query){
		//Поиск по таблице новостей
		$this->searchInTables('_news', $query, 'elem_news', array('id', 'name', 'description', 'text'), array('name', 'description', 'text'), 'visible>0', 'one_news');
	}

	function findInText($query){
		// Поиск по таблице tree
		$this->searchInTree($query, array(
			array(
				'name'          => 'tree',
				'search_fields' => 'name',
			),
			array(
				'name'			=> 'elem_text',
				'select_fields'	=> 'uptime, text',
				'search_fields'	=> 'text',
			),
		),'tree');
	}
	
	function getPath($id, $type){
    	$path = "";
    	$tree_obj = & Registry::get('TTreeUtils');
        switch ($type) {
            case 'one_news' : {
            	$page_id = sql_getValue("SELECT pid FROM elem_news WHERE id=".$id);
                $pids = $tree_obj->getPids($page_id);
                break;
            }
            case 'object' : {
            	$obj = & Registry::get('TObject');
            	$obj_path = $obj->getPathToObject($id);
            	$pids = $tree_obj->getPids($obj_path);
                break;
            }
            default: {
		    	$pids = $tree_obj->getPids($id);
            }
        }
		if (isset($pids)) foreach ($pids as $key=>$val)
			$path .= "<a href='".$val['href']."'>".$val['name']."</a>".($key < count($pids)-1 ? "&gt;&nbsp;" : "");
		return $path;
    }
	
	function getHref($id,$type){
    	$page_obj = & Registry::get('TPage');
		$tree_obj = & Registry::get('TTreeUtils');
        $href = "";
        switch ($type) {
            case 'one_news' : {
				$page_id = sql_getValue("SELECT pid FROM elem_news WHERE id=".$id);
				$href = $tree_obj->getPath($page_id);
				if (!empty($href)) $href .= '/news/'.$id;
                break;
            }
            case 'object' : {
            	$obj = & Registry::get('TObject');
				$href =  $obj->getPathToObject($id).'/object/'.$id.'/';
                break;
            }
            default: {
				$href = $page_obj->dirs_lang.$tree_obj->getPath($id);
            }
        }
        return $href;
    }

    function convertText($text,$query,$cut=1){
        $text = strip_tags($text);
	$i = 0;
        foreach ($query as $k=>$v) {
        	if (!empty($v)) {
        		$offset = 0;
        		do {
        			$pos[$i] = strpos(strtolower($text),strtolower($v), $offset);
        			if ($pos[$i]) $offset = $pos[$i] + 1;
        			$i++;
        		} while ($pos[$i-1] !== false && $i<5);
        		if ($pos[$i-1] === false) unset($pos[$i-1]);
        	}
        }
		if (count($pos) > 0){
            if ($cut) {
            	$temp = '';
            	foreach ($pos as $key=>$val) {
                	$minus = $val-100;
                	if ($minus < 0) $minus = 0;
                	$temp_text = substr($text, $minus, 200);
                	if ($temp != '...'.$temp_text.'...<br>') $temp .= '...'.$temp_text.'...<br>';
            	}
            	$text = $temp;
            }
            foreach ($query as $k=>$v) {
            	if (empty($v)) continue;
            	$text = preg_replace("`(" . preg_quote($v) . ")`i", "<span class='searchfind'>\\1</span>", $text);
            }
		} elseif ($cut) $text="";
        return $text;
    }

    function searchInTables($tmp, $query, $table, $fields_select, $fields_search, $where='', $type='', $limit = 0, $offset = 0) {

		$page = & Registry::get('TPage');
		$select_fields = array();
		$search_fields = array();
        $match_fields = array();

		$search_str = $query;
		$words = explode(' ', $search_str);
		if (count($words)<10){
			$rel = $rel2 = $words2 = array();
			foreach ($words as $k=>$v){
			    if (e($v)) {
			    	$words2[] = $v;
					$rel[] = $table.".".$fields_search[0]." LIKE '%".$v."%'";
					
					$rel2[] = $table.".".$fields_search[0]." LIKE '".$v."%'";
					$rel2[] = $table.".".$fields_search[0]." LIKE '% ".$v." %'";
			    }	
			}
		}

		$rel = "
		IF(".implode(" AND ", $rel).", 3,
	      IF(".implode(" OR ", $rel2).", 2, 
	          IF(".implode(" OR ", $rel).", 1, 0)
	      )
		)"; // релевантность		


        foreach ($fields_select as $key=>$val) {
            if ($val != 'null') $select_fields[] = "IFNULL(".$table.".".$val.", '') AS ".$val;
            else $select_fields[] = $val;
    		$match_fields[] = $table.".".$val;
        }
        foreach ($fields_search as $key=>$val) {
            $search_fields[] = "IFNULL(".$table.".".$val.", '')";
    		$match_fields[] = $table.".".$val;
        }

		// where
		$ss = array();
		foreach ($words2 AS $key => $value){
			$ss[] = "LCASE(CONCAT(".implode(', ', $search_fields).") LIKE '%".e(strtolower($value))."%')";	
		}
		

        $search_where = array();
		$search_where[] = $where;
		$search_where[] = '('.implode(' OR ', $ss).')';

		$columns = sql_getRows("SHOW columns FROM ".$table);
		foreach ($columns as $key=>$val) {
			if ($val['Field'] == 'root_id') {
				$search_where[] = 'root_id='.ROOT_ID;
				break;
			}
		}
		
		$sql = "INSERT INTO tmp".$tmp." (id,name,description,text) SELECT ".implode(', ', $select_fields)." FROM ".$table." WHERE ".implode(' AND ', $search_where)." ORDER BY ".$rel." DESC";
        $res = sql_query($sql);
        $sql = "UPDATE tmp".$tmp." SET type='".$type."' WHERE type IS NULL";
        $res = sql_query($sql);

		return $res;

	}

    function searchInTree($query, $tables, $type, $limit = 0, $offset = 0) {

		$page = & Registry::get('TPage');
        if ($type == 'tree') {
    		$tree_obj = & Registry::get('TTreeUtils');
    		$select_fields = array($tree_obj->table.'.id',$tree_obj->table.'.name',$tree_obj->table.'.type');
        }
        else
    		$select_fields = array($type.'.id',$type.'.name');
		$search_fields = array();
		$match_fields = array();
		$joins = array();

		// form select and search fields, join tables
		foreach ($tables as $k=>$table) {
			if (!empty($table['name']) && !empty($table['select_fields']))
                $joins[] = " LEFT JOIN ".$table['name']." AS ".$table['name']." ON ".$table['name'].".pid=".$type.".id";

			// select fields
            if (!empty($table['select_fields'])) {
    			$sel_fields = explode(', ', $table['select_fields']);
                foreach($sel_fields as $key=>$val) {
			        $select_fields[] = "IFNULL(".$table['name'].".".$val.", '') AS fld".$k;
			    }
            }

			// search fields
            if (!empty($table['search_fields'])) {
    			$ser_fields = explode(', ', $table['search_fields']);
    			foreach ($ser_fields as $search_field) {
    				$match_fields[] = $table['name'].".".$search_field;
    				$search_fields[] = "IFNULL(".$table['name'].".".$search_field.", '')";
    			}
            }
		}

		$search_str = $query;
		$words = explode(' ', $search_str);
		if (count($words)<10){
			$rel = $rel2 = $words2 = array();
			foreach ($words as $k=>$v){
			    if (e($v)) {
					$words2[] = $v;
			    	foreach ($match_fields AS $k2 => $v2){
						$rel[] = $v2." LIKE '%".$v."%'";
					
						$rel2[] = $v2." LIKE '".$v."%'";
						$rel2[] = $v2." LIKE '% ".$v." %'";
			    	}
			    }	
			}
		}

		$rel = "
		IF(".implode(" AND ", $rel).", 3,
	      IF(".implode(" OR ", $rel2).", 2, 
	          IF(".implode(" OR ", $rel).", 1, 0)
	      )
		)"; // релевантность			
		
		// where
		$ss = array();
		
		foreach ($words2 AS $key => $value){
			$ss[] = "LCASE(CONCAT(".implode(', ', $search_fields).") LIKE '%".e(strtolower($value))."%')";
		}

		$search_where = array();
		$search_where[] = $type.'.visible>0';
		$search_where[] = 'root_id='.ROOT_ID;
		$search_where[] = '('.implode(' OR ', $ss).')';

        if ($type == 'tree')
            $sql = "INSERT INTO tmp_text SELECT ".implode(', ', $select_fields)." FROM ".$type." AS ".$type." ".implode(' ', $joins)." WHERE ".implode(' AND ', $search_where)." ORDER BY ".$rel." DESC";
        else
            $sql = "INSERT INTO tmp_text (id,name,description,text) SELECT ".implode(', ', $select_fields)." FROM ".$type." AS ".$type." ".implode(' ', $joins)." WHERE ".implode(' AND ', $search_where)." GROUP BY id"." ORDER BY ".$rel." DESC";

        $res = sql_query($sql);
        $sql = "UPDATE tmp_text SET type='".$type."' WHERE type IS NULL";
        $res = sql_query($sql);

        return $res;

	}
}

// class TSearch