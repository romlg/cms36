<?php

class TRntObject {

    var $table = 'rnt_objects';
    var $elem_table = 'elem_type';
    var $sql = ' AND o.visible > 0 ';
    var $type;
    var $pathes = array();
    var $__pids = array();

    var $fields_align = array('ipoteka' => 'center', 'district_id' => 'center');

    // --- Поля на странице объекта
    var $inside_fields = array();

    var $filter = array();

    var $type_sql = array(
	    'room'		=> ' AND obj_type_id="room"',
	    'house'		=> ' AND obj_type_id="house"',
	    'commerce'	=> ' AND obj_type_id="commerce"',
	    'newbuild'	=> ' AND obj_type_id="newbuild"',
    );

    //-------------------------------------------------------------
    function getParams($params = array()){
        $page = & Registry::get('TPage');
        $page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'object');

        $param = array();
        $param['id'] 			=	 get('id',NULL,'pg');
        $param['offset'] 		=	 (int)get('offset',0,'pg');
        $param['limit'] 		=	 (int)get('limit',-1,'pg');
        if ($param['limit'] == -1) $param['limit'] = (int)$page->tpl->get_config_vars('object_limit');
        if (!$param['limit']) $param['limit'] = 10;
        $param['sort_by'] 		=	 get('sort_by',NULL,'pg');
        $param['sort_type'] 	=	 get('sort_type',NULL,'pg');
        $param['show'] 	        =	 get('show','table','pg');
        $param['moscow']        =	 get('moscow',NULL,'pg');

        if (isset($param['moscow'])) $this->sql .= ' AND o.moscow='.(int)$param['moscow'];

        $info = sql_getRow('SELECT * FROM '.$this->elem_table.' WHERE pid='.$page->content['id']);
        if ($info['type']) {
            switch ($info['type']) {
                case 'room'		:
                    if (isset($param['moscow']) && $param['moscow'] == '1') {
					   $this->fields = array(/*'district_id', 'metro_id', */'address', 'room_description', 'price_rub'/*, 'action'*/);
                    } elseif (isset($param['moscow']) && $param['moscow'] == '0') {
					   $this->fields = array(/*'city',*/'address', 'room_description', 'price_rub'/*, 'action'*/);
                    }
                    else {
                        $this->fields = array('address', 'room_description', 'price_rub'/*, 'action'*/);
                    }
					$this->type = 'room';
	                break;
                case 'newbuild'	:
                    if (isset($param['moscow']) && $param['moscow'] == '1') {
					   $this->fields = array(/*'district_id', */'address', /*'metro_id', */'srok', 'price_metr_rub', 'action');
                    } elseif (isset($param['moscow']) && $param['moscow'] == '0') {
					   $this->fields = array(/*'city', */'address', 'srok', 'price_metr_rub'/*, 'action'*/);
                    }
                    else {
                        $this->fields = array('address', 'srok', 'price_metr_rub'/*, 'action'*/);
                    }
					$this->type = 'newbuild';
	                break;
                case 'commerce'	:
					$this->fields = array('sell_type_id', 'location', 'purpose', 'total_area', 'action');
					$this->type = 'commerce';
                	break;
            }
            $this->sql .= $this->type_sql[$info['type']];
        } else {
            // Для раздела "Вся недвижимость"
            if (isset($param['moscow']) && $param['moscow'] == '1') {
                $this->fields = array('district_id', 'metro_id', 'address'/*, 'action'*/);
            } elseif (isset($param['moscow']) && $param['moscow'] == '0') {
                $this->fields = array('city', 'address'/*, 'action'*/);
            }
            else {
                $this->fields = array('address'/*, 'action'*/);
            }
        }

        if ($info['market']) {
            $this->sql .= ' AND o.market="'.$info['market'].'"';
            $param['market'] = $info['market'];
        }

        return $param;
    }

    //-------------------------------------------------------------
	function getPathToObject($id, $obj_type_id = '') {
		$sql = '';

		if (!$obj_type_id) {
			$info = sql_getRow('SELECT * FROM '.$this->table.' WHERE id='.$id);
		    $obj_type_id = $info['obj_type_id'];
		}
		$sql .= ' AND (type="'.$obj_type_id.'" OR type="")';

		if (isset($this->__pids[$sql])) $pid = $this->__pids[$sql];
		else {
		    $pid = sql_getValue("SELECT pid FROM ".$this->elem_table." WHERE 1 ".$sql." ORDER BY pid DESC");
		    $this->__pids[$sql] = $pid;
		}
		if (isset($this->pathes[$pid])) return $this->pathes[$pid];

		$tree_obj = & Registry::get('TTreeUtils');
		$path = $tree_obj->getPath($pid);
		$this->pathes[$pid] = $path;
		return $path;
	}

    //-------------------------------------------------------------
    function Show(&$params){
        $param = $this->getParams($params);
        $page = & Registry::get('TPage');

        // Разбираем путь на части
        $real_path = $_SERVER['REQUEST_URI'];
        if (substr($real_path, -1) != '/') $real_path .= '/'; // Текущий путь
        $query = explode('?', $real_path);
        $pids = explode('/', $query[0]);
        $pos = array_search('object_rnt', $pids); // Позиция слова object в пути страницы
        if ($pos !== false) $param['id'] = (int)$pids[$pos + 1]; // Определяем id объекта (должен находиться в пути после object)
        $param['path'] = '';
        for ($i=0; $i<=$pos-1; $i++)
        $param['path'] .= $pids[$i].'/';

        if (isset($param['id'])) return $this->getOneObject($param['id'], $pids, $pos, isset($pids[$pos+2]) ? $pids[$pos+2] : '');

        // Способ отображения
        $ret['show'] = $param['show'] ? $param['show'] : 'table';

        // Данные с формы поиска
        $ret['data'] = get('search', array(), 'g');

        // Сортировка
        $sort = $this->getSort($param);
        if (empty($param['sort_by'])) {
            switch ($this->type) {
                case 'room' :
                    $param['sort_by'] = "price_rub";
                    break;
                case 'newbuild':
                case 'commerce':
		    $param['sort_by'] = $param['show'] == 'table' ? "address_id" : "price_rub";
                    break;
                default: $param['sort_by'] = "price_rub"; break;
            }
        }
        $ret['sort_by'] = $param['sort_by'] ? $param['sort_by'] : 'price_rub';
        $ret['sort_type'] = $param['sort_type'] ? $param['sort_type'] : 'asc';
        $ret['sort'] = array();
        $ret['sort'][$ret['sort_by'] ] =  $ret['sort_type'] ;
        foreach ($this->fields as $key=>$val) {
            if ($val != $param['sort_by']) $ret['sort'][$val] = "";
        }

        // Список объектов
        $list = $this->getList($param, " AND o.winner='0'", $sort);
        $ret['objects'] = $list['list'];

        // Колонки
        $ret['fields'] = $this->fields;
        $ret['count_fields'] = count($this->fields);
        $ret['align'] = $this->fields_align;

        // Данные дляфильтра
        $ret['filter'] = $this->filter;

        // Навигация
        $ret['limit'] = $param['limit'];
        $ret['offset'] = $param['offset'];

        $ret['pages1'] = TContent::getNavigation($list['count'], $param['limit'], $param['offset'], $page->content['href']);
        $ret['pages2'] = TContent::getNavigation($list['count'], $param['limit'], $param['offset'], $page->content['href'], null, 'ids_pages.html');

        $ret['moscow'] = $param['moscow'];
        $ret['market'] = $param['market'];

        $url = $page->content['href'];

        // Формируем разные пути
        $path = array(
            'show_path'  => array('moscow', 'market'),
            'type_path'  => array('sort_by', 'sort_type', 'limit', 'show', 'market'),
            'sort_path'  => array('offset', 'limit', 'show', 'moscow', 'market'),
        );
        foreach ($path as $key=>$val) {
            $ret[$key] = $url;
            foreach ($val as $p) {
                if (isset($ret[$p])) $ret[$key] = add_query_arg($ret[$key], $p, $ret[$p]);
            }
            if (strpos($ret[$key], '?') === false) $ret[$key] .= '?'; else $ret[$key] .= '&';
        }

        $ret['type'] = $this->type;
        return $ret;
    }

    //-------------------------------------------------------------
    function getSort($param){
        $sort_state = " ORDER BY ";
        if ($this->type == 'room' && $param['show'] == 'table')  $sort_state = " ORDER BY o.room";

        if (!empty($param['sort_by']) && !empty($param['sort_type'])) {
            if ($this->type == 'room' && $param['show'] == 'table') $sort_state .= ', ';
            $sort_state .= $param['sort_by']." ".$param['sort_type'];
        }

        if ($sort_state == " ORDER BY ") {
            switch ($this->type) {
                case 'room' :
                    $sort_state = " ORDER BY price_rub";
                    break;
                case 'newbuild':
                case 'commerce':
		    $sort_state = $param['show'] == 'table' ? " ORDER BY address_id" : " ORDER BY price_rub";
                    break;
                default: $sort_state = " ORDER BY price_rub"; break;
            }
        }

        return $sort_state;
    }

    //-------------------------------------------------------------
    function getList($param, $sql, $sort){
        $page = & Registry::get('TPage');
        global $settings;

		$subQuery = 'SELECT SQL_CALC_FOUND_ROWS o.id
		FROM '.$this->table.' AS o
		WHERE 1 '.$this->sql.$sql.' '.$sort.'
		LIMIT '.$param['offset'].', '.$param['limit'];
		$list = sql_getRows($subQuery);
		$count = sql_getValue("SELECT FOUND_ROWS()");

//if ($_SERVER['REMOTE_ADDR'] == '80.243.13.242') {
//	pr ($subQuery);
//die;
//}

		if ($count) {
			$query = 'SELECT o.*, m.name AS metroname, oa.address AS address,
			oa.x AS x, oa.y AS y
			FROM '.$this->table.' AS o
			LEFT JOIN obj_address AS oa ON oa.id = o.address_id
			LEFT JOIN obj_locat_metrostations AS m ON m.id = o.metro_id
			WHERE o.id IN ('.implode(",",$list).') '.$sort;

//if ($_SERVER['REMOTE_ADDR'] == '80.243.13.242') {
//	pr ($query);
//die;
//}

			$list = sql_getRows($query);
		} else $list = array();

		$images = sql_getRows('SELECT pid, smallimagepath FROM rnt_obj_elem_images WHERE visible > 0 GROUP BY pid ORDER BY pid, priority', true);
		$metro = sql_getRows('SELECT id, name FROM obj_locat_metrostations', true);
		$direction = sql_getRows('SELECT id, name FROM obj_direction', true);
		$photo_counts = sql_getRows('SELECT pid, COUNT(*) FROM rnt_obj_elem_images WHERE type="photo" AND visible > 0 GROUP BY pid', true);
		$house_types = sql_getRows('SELECT id, name FROM obj_housetypes', true);
		$balcony = sql_getRows('SELECT id, name FROM obj_balcony', true);


		foreach ($list as $key=>$val) {

            switch ($val['obj_type_id']) {
                case 'room'		:
	                $list[$key]['storeys'] = $val['storey'].'/'.$val['storeys_number'];
	                $list[$key]['square'] = $val['total_area'].'/'.$val['living_area'].'/'.$val['kitchen_area'];
	                break;
                case 'house'	:
					$list[$key]['direction'] = $direction[$val['direction']];
	                break;
                case 'commerce'	:
                break;
            }
            if ($val['metro_id']) {
                $list[$key]['metro_id'] = $metro[$val['metro_id']];
            }

            if ($val['district_id']) {
            	if ($val['obj_type_id'] == 'room') {
            		$district = sql_getRow('SELECT * FROM obj_locat_districts WHERE id='.$val['district_id']);
            		while((int)$district['pid'] > 0) {
            			$district = sql_getRow('SELECT * FROM obj_locat_districts WHERE id='.$district['pid']);
            		}
            		$list[$key]['district_id'] = $district['name'];
            	}
                else {
                	$list[$key]['district_id'] = sql_getValue('SELECT name FROM obj_locat_districts WHERE id='.$val['district_id']);
                }
            }

            $price_rub = $val['price_rub'];
            $price_dollar = $val['price_dollar'];
            if ($price_dollar) $price_dollar = number_format($price_dollar, ' ', ' ', ' ');
            if ($price_rub) $price_rub = number_format($price_rub, ' ', ' ', ' ');
            $list[$key]['price'] = $price_dollar.'/'.$price_rub;
            $list[$key]['price_rub'] = $price_rub;
            $list[$key]['price_dollar'] = $price_dollar;
            /*  if ($val['credit'] && $param['show'] == 'table') {
                $prices = array('price', 'price_rub', 'price_dollar', 'price_rub_print', 'price_dollar_print', 'price_metr_rub');
                foreach ($prices as $num) {
                    $list[$key][$num] .= ' <span style="color:#ff0101;">&nbsp; рассрочка</span>';
                }
            }*/

            if (in_array('room_description', $this->fields)) {
                $description = array();
                $description2 = array();
                if ($val['storey']) $description[] = $val['storey'].'/'.$val['storeys_number'];
                if ($val['total_area']) $description[] = $val['total_area'].'/'.$val['living_area'].'/'.$val['kitchen_area'];
                if (isset($house_types[$val['house_type']])) $description[] = strtolower($house_types[$val['house_type']]);
                if ($val['lavatory'] != '2') $description[] = 'с/у '.($val['lavatory'] == '0' ? 'раздельный' : 'совмещенный');
                if (isset($balcony[$val['balcony']])) $description[] = strtolower($balcony[$val['balcony']]);
                if ($val['phone']) $description[] = 'телефон';
                if ($val['f_release']) $description2[] = $val['f_release'];
                if ($val['remont']) $description2[] = $val['remont'];
                if ($val['documents']) $description2[] = $val['documents'];
                if ($val['registration']) $description2[] = $val['registration'];

                $list[$key]['room_description'] = implode(', ', $description);
                if ($description2) $list[$key]['room_description'] .= '<br />'.implode('<br />', $description2);
            }

            if (in_array('action', $this->fields)) {
                if ($val['sell']) $list[$key]['action'] = '<span style="color:#ff0101;">продано</span>';
                else if ($val['avance']) $list[$key]['action'] = '<span style="color:#ff0101;">аванс</span>';
            }

            if (isset($param['show']) AND $param['show'] != 'table') {
                // Фотография
                if (empty($val['image_small']) || !is_file(substr($val['image_small'], 1))) {
                    $list[$key]['image_small'] = $page->tpl->get_config_vars("no_image_path");
                }
                $size = getimagesize(substr($list[$key]['image_small'], 1));
                $list[$key]['image_width'] = $size[0];
            }

            if ($val['set_color'] && !empty($val['color'])) {
                $list[$key]['address'] = '<span style="color: '.$val['color'].'">'.$val['address'].'</span>';
            }
			if (isset($param['moscow']) && $param['moscow'] == '1'){
				$list[$key]['address'] .= '<br /><span style="color:#6f6f6f; font-weight:normal">'.$list[$key]['district_id'].', '.$list[$key]['metro_id'].'</span>';
				}
				if (isset($param['moscow']) && $param['moscow'] == '0'){
				$list[$key]['address'] .= '<br /><span style="color:#6f6f6f; font-weight:normal">'.$list[$key]['district_id'].', '.$list[$key]['city'].'</span>';
				}
				if ($val['credit'] && $param['show'] == 'table') {
				$list[$key]['action'] .= '<span style="color:#ff0101;">&nbsp; рассрочка</span>';
				}
        }

        return array('list' => $list, 'count' => $count);
    }

    //-------------------------------------------------------------
    function getOneObject($id, $pids, $pos, $tab) {
		$page = & Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'object');
		$info = $this->getInfo($id, $tab, isset($pids[$pos+3]) ? $pids[$pos+3] : '');
		// "Хлебные крошки"
		$tree = & Registry::get('TTreeUtils');
		array_splice($pids, $pos);
		$url = implode('/', $pids);
		$page->tpl->_tpl_vars['pids_full']['pids'] = $tree->getPidsByUrl($url);
		$name = $page->tpl->get_config_vars('object_fld_lot_id');
		$page->tpl->_tpl_vars['pids_full']['pids'][] = array(
			'name'	=> $name.' '.$info['lot_id'],
		);
		$page->pids = $page->tpl->_tpl_vars['pids_full']['pids'];
		$page->content['name'] = $name.' '.$info['lot_id'].': '.$info['address'];
		$page->tpl->_tpl_vars['content']['name'] = $name.' '.$info['lot_id'].':&nbsp;'.$info['address'];
		$page->content['id'] = $page->tpl->_tpl_vars['pids_full']['pids'][count($page->tpl->_tpl_vars['pids_full']['pids'])-2]['id'];
		$page->tpl->assign(array('info' => $info, 'real_path' => $url, 'current_tab' => $tab, 'fields' => $this->inside_fields, 'type' => 'object_rnt'));
		return array('about' => $page->tpl->fetch('about_object.html'));
    }

    //-------------------------------------------------------------
    function getInfo($id, $tab, $_id) {
        global $settings;
        $page = &Registry::get('TPage');
		$info = sql_getRow('SELECT * FROM '.$this->table.' WHERE id='.$id);

		if ($info['address_id']){
			$obj_address = sql_getRow('SELECT x, y, address FROM `obj_address` WHERE id='.$info['address_id']);
			$info['x'] = $obj_address['x'];
			$info['y'] = $obj_address['y'];
			$info['address'] = $obj_address['address'];
		} else {
			$info['x'] = '';
			$info['y'] = '';
			$info['address'] = '';
		}

		$global_type = $info['obj_type_id'];
        switch ($global_type) {
            case 'room' :
	            $this->inside_fields = array('lot_id', 'address', 'market', 'metro_id', 'house_type', 'storeys', 'room', 'total_area', 'living_area', 'kitchen_area', 'lavatory', 'balcony', 'phone', 'windows', 'documents', 'registration', 'f_release', 'remont');
	            break;
            case 'commerce' :
	            $this->inside_fields = array('lot_id', 'transaction_type', 'location', 'purpose', 'square');
    	        break;
            case 'house' :
	            $this->inside_fields = array('lot_id', 'object_type', 'direction', 'distance', 'district', 'city_id', 'address', 'living_area', 'storeys_number', 'heating', 'decoration', 'land_area');
    	        break;
            case 'newbuild' :
	            $this->inside_fields = array('lot_id', 'address', 'srok', 'seria', 'storeys_number', 'decoration', 'square', 'transport', 'ready');
    	        break;
        }

		// Какие вкладки показывать
		$info['photos'] = sql_getRows('SELECT * FROM rnt_obj_elem_images WHERE pid='.$id.' AND type="photo" AND visible > 0');
		$info['count_flash'] = sql_getValue('SELECT COUNT(*) FROM rnt_obj_elem_images WHERE pid='.$id.' AND type="flash" AND visible > 0');

        // -------------------------------------------------------------------
        // ---- Информация по вкладкам ---------------------------------------
        // -------------------------------------------------------------------
        switch ($tab) {
        	// Свободные квартиры
        	case 'free'		: $info['free'] = $this->showFreeFlats($info); $info['form'] = $this->showOrder($info, true); break;
        	// Галерея
        	case 'photo'	: $info['gallery'] = $this->showPhoto($info); break;
        	// Виртуальные туры
        	case 'flash'	: $info = $this->showFlash($info, $_id); break;
        	// Заявка на просмотр
        	case 'order'	: $info['order'] = $this->showOrder($info); break;
        	// Планировки
        	case 'plans'	: $info['plans'] = $this->showPlans($info); break;
        	// Основная вкладка
        	default: $info = $this->showMainTab($info); /*if ($info['obj_type_id'] == 'room') $info['similar'] = $this->showSimilar($info);*/
        	break;
        }

        // Цены
        /**
         * todo надо цену высчитывать с учетом комисии
         *
         * $page = & Registry::get('TPage');
         * $numeric_r	= $page->tpl->get_config_vars('searchobject_markup_numeric_r');
         * $numeric_s	= $page->tpl->get_config_vars('searchobject_markup_numeric_s');
         * $percent	= $page->tpl->get_config_vars('searchobject_markup_percent');
         *
         */

        if ($info['obj_type_id'] == 'newbuild') {
        	$info['price'] = ($info['price_rub_print'] ? $info['price_rub_print'].' руб. ' : '').($info['price_dollar_print'] ? ' /'.$info['price_dollar_print'].' у.е.' : '');
        } else {
        	$info['price'] = number_format(doubleval(str_replace('', ' ', $info['price_rub'])), 0, ' ', ' ').' руб. /'.number_format(doubleval(str_replace(',', '.', $info['price_dollar'])), 0, ',', ' ').' у.е.';
        }

        // Фотография
        if (empty($info['image_small']) || !is_file(substr($info['image_small'], 1))) {
            $info['image_small'] = $page->tpl->get_config_vars("no_image_path");
        }
        $size = getimagesize(substr($info['image_small'], 1));
        $info['image_width'] = $size[0];

        return $info;
    }

    /**
     * Показ информации с основной вкладки
     *
     * @param array $info
     * @return array
     */
    function showMainTab(&$info) {
    	$info['lot_id'] = '№'.$info['lot_id'];
    	$info['storeys'] = $info['storey'].'/'.$info['storeys_number'];
    	if ($info['obj_type_id'] != 'newbuild') $info['square'] = $info['total_area'].'/'.$info['living_area'].'/'.$info['kitchen_area'];
    	$info['house_type'] = sql_getValue('SELECT name FROM obj_housetypes WHERE id='.$info['house_type']);
    	if ($info['market']) $info['market'] = $settings['market'][$info['market']];

    	if ($info['obj_type_id'] == 'commerce') {
    		$info['location'] = $info['address'];
    		$info['square'] = $info['total_area'];
    	}

    	if (in_array('direction', $this->inside_fields) && $info['direction']) {
    		$info['direction'] = sql_getValue('SELECT name FROM obj_direction WHERE id='.$info['direction']);
    	}
    	if (in_array('metro_id', $this->inside_fields) && $info['metro_id']) {
    		if ($info['metro_id'] == 1) {
    			$info['metro_id'] = sql_getValue('SELECT name FROM obj_locat_metrostations WHERE id='.$info['metro_id']);
    		} else {
    			$info['metro_id'] = sql_getValue('SELECT name FROM obj_locat_metrostations WHERE id='.$info['metro_id']);
    			if ($info['metro_dest_value']) {
    				$info['metro_dest_value'].= '&nbsp;мин&nbsp;' . ($info['metro_dest_text'] == 0 ? 'пешком' : 'транспортом');
    			}
    		}

    	}
    	if ($info['district_id']) {
    		$info['district_id'] = sql_getValue('SELECT name FROM obj_locat_districts WHERE id='.$info['district_id']);
    	}
    	$info['district'] = $info['district_id'];
    	if (in_array('city_id', $this->inside_fields) && $info['city_id']) {
    		$info['city_id'] = sql_getValue('SELECT name FROM obj_locat_districts WHERE id='.$info['city_id']);
    	}
    	if (in_array('lavatory', $this->inside_fields)) {
    		$info['lavatory'] = $info['lavatory'] == '0' ? 'раздельный' : ($info['lavatory'] == '1' ? 'совмещенный' : '?');
    	}
    	if (in_array('phone', $this->inside_fields)) {
    		$info['phone'] = $info['phone'] == '1' ? 'да' : ($info['phone'] == '0' ? 'нет' : '?');
    	}
    	if (in_array('balcony', $this->inside_fields)) {
    		$info['balcony'] = sql_getValue('SELECT name FROM obj_balcony WHERE id='.$info['balcony']);
    	}
    	if (in_array('balcony2', $this->inside_fields)) {
    		$info['balcony2'] = $info['balcony'];
    	}
    	$info['create_time'] = substr($info['create_time'], 6, 2).'.'.substr($info['create_time'], 4, 2).'.'.substr($info['create_time'], 0, 4);
    	return $info;
    }

    /**
     * Показ свободных квартир
     *
     * @param array $info
     * @return array
     */
    function showFreeFlats(&$info){
    	$rows = sql_getRows('SELECT * FROM obj_elem_free WHERE pid='.$info['id']);
		foreach ($rows AS $key=>$value){
			if ($value['image']){
				$size = getimagesize(substr($value['image'], 1));
				$rows[$key]['image_width'] = (isset($size[0])) ? $size[0] : 0;
			} else {
				$rows[$key]['image_width'] = 0;
			}
		}
    	return $rows;
    }

    /**
     * Галерея фотографий
     *
     * @param array $info
     * @return array
     */
    function showPhoto(&$info) {
    	$rows = sql_getRows('SELECT * FROM rnt_obj_elem_images WHERE type="photo" AND pid='.$info['id'].' AND visible > 0 ORDER BY priority');
    	foreach ($rows as $key=>$val) {
			$rows[$key]['size'] = getimagesize(substr($val['imagepath'], 1));
			$rows[$key]['size_small'] = getimagesize(substr($val['smallimagepath'], 1));
    	}
		return $rows;
    }

    /**
     * Виртуальные панорамы
     *
     * @param array $info
     * @param int $_id
     * @return array
     */
    function showFlash(&$info, $_id) {
        $info['gallery'] = sql_getRows('SELECT * FROM rnt_obj_elem_images WHERE type="flash" AND pid='.$info['id'].' AND visible > 0 ORDER BY priority');
       	if (!$_id) {
       		// Берем первое изображение
       		$_id = $info['gallery'][0]['id'];
       	}
       	$flash_file = sql_getValue('SELECT imagepath FROM rnt_obj_elem_images WHERE id='.$_id);
       	$size = getimagesize(substr($flash_file, 1));
       	$info['flash'] = array(
       		'id'	=> $_id,
       		'file'	=> $flash_file,
       		'width'	=> $size[0],
       		'height'=> $size[1],
       	);
       	return $info;
    }

    /**
     * Похожие объекты
     *
     * @param array $info
     * @return array
     */
    function showSimilar(&$info) {
    	$page = &Registry::get('TPage');
       	$diapozon = $page->tpl->get_config_vars('object_similar_diapozon');
       	if (empty($diapozon)) $diapozon = '-100000, 100000';
       	list($from, $to) = explode(',', $diapozon);
       	$list = sql_getRows('SELECT * FROM '.$this->table.' WHERE visible > 0 AND obj_type_id="'.$info['obj_type_id'].'" AND price_dollar BETWEEN "'.((double)$info['price_dollar'] + (double)trim($from)).'" AND "'.((double)$info['price_dollar'] + (double)trim($to)).'" ORDER BY priority');
       	foreach ($list as $key=>$val) {
       		$list[$key]['href'] = $this->getPathToObject($val['id'], $info['obj_type_id']);
       		$list[$key]['price_rub'] = number_format($val['price_rub'], ' ', ' ', ' ');
       		$list[$key]['price_dollar'] = number_format($val['price_dollar'], ' ', ' ', ' ');
       	}
       	return $list;
    }

    /**
     * Форма заявки
     *
     * @param array $info
     * @param bool $free
     * @return array
     */
	function showOrder(&$info, $free = false) {
		global $settings;
		if (isset($_GET['a'])) return $this->captcha();
		$ret = array();
		$page = & Registry::get('TPage');

        $form = new TForm(null, $this);
    	$form->form_name='content';
    	$form->elements=array(
    		'name'	=>	array(
    			'name'		=> 'name',
    			'type'		=> 'text',
    			'req'		=> 1,
			'atrib'		=> 'class="text"',
    		),
    		'contacts'=>	array(
    			'name'		=> 'contacts',
    			'type'		=> 'textarea',
    			'req'		=>	1,
			'atrib'   	=> 'style="height: 50px;" class="text"',
    		),
    		'message'=>	array(
    			'name'		=> 'message',
    			'type'		=> 'textarea',
    			'text'		=> 'Дополнительная информация',
    			'req'		=> 0,
			'atrib'    	=> 'style="height: 120px;" class="text"',
    		),
		'captcha' =>	array(
			'name'		=> 'captcha',
			'type'		=> 'html',
			'req'		=>	1,
	   		'value'		=> '
	   			<div class="floatLeft">
	   			<label>{#content_fld_captcha#}<span>*</span>:</label>
	   			<input type="text" name="fld[captcha]" class="text" /><br />
				<img src="'.$page->content['href'].'?a=captcha" align="middle" title="Щелкните на картинце, чтобы загрузить другой код" onclick="document.getElementById(\'captcha\').src=\''.$page->content['href'].'?a=captcha&\'+1000*Math.random()" id="captcha">
	   			</div>',
		),
		'flat_id' =>	array(
			'name'	=> 'flat_id',
			'type'	=> 'hidden',
		),
		array(
			'name'	=>	'button1',
			'type'	=>	'html',
			'value'	=>	'<a class="button big left" href="javascript:void(0);" onclick="document.getElementById(\'addOrg\').submit(); return false;"><span>'.$page->tpl->get_config_vars("send").'</span></a>',
			'group'	=>	'system',
		),
	);
		$fdata=$form->generate();

		$path = $this->getPathToObject($info['id']).'/object_rnt/'.$info['id'].($free ? '/free/' : '/order/');

		$fdata['form']['action'] = $path;
		$fdata['form']['width'] = '80%';
		$fdata['form']['id'] = "addOrg";

		if (empty($fdata['form']['errors']) && isset($_POST['fld'])) {

			$keystring = $_SESSION['captcha_keystring'];
			unset($_SESSION['captcha_keystring']);
			if (!empty($_POST['fld']['captcha']) && (empty($keystring) || $_POST['fld']['captcha'] !== $keystring)) {
				$fdata['form']['result'] = 'msg_captcha_error'; // Ошибка при вводе проверочной комбинации
			}
			else {

				$name = e(h($_POST['fld']['name']));
				$contacts = e(h($_POST['fld']['contacts']));
				$message = e(h($_POST['fld']['message']));
				$flat_id = isset($_POST['fld']['flat_id']) ? $_POST['fld']['flat_id'] : 0;

				// Сохраняем в БД
				$data = array(
					'object_id'	=> $info['id'],
					'flat_id'	=> $flat_id,
					'date'		=> date('Y-m-d H:i:s'),
					'name'		=> $name,
					'contacts'	=> $contacts,
					'info'		=> $message,
				);
				sql_insert('orders', $data);

				// Отправка письма
	        	$mail = &new PHPMailer();
	        	$admin_email = $page->tpl->get_config_vars('order_email');
	        	$emails = explode(',', $admin_email);
	        	foreach ($emails as $k=>$v) $emails[$k] = trim($v);

	        	$mail->From = $emails[0];
	        	$mail->Sender = $emails[0];
	        	$mail->Mailer = 'mail';
	        	$mail->Subject = 'Заявка на просмотр объекта аренды № '.$info['lot_id'];

	        	$body = $text_body = '';
	        	$body .= 'ФИО: '.$name.'<br>';
	        	$body .= 'Контакты: '.nl2br($contacts).'<br>';
	        	$body .= 'Дополнительная информация: '.nl2br($message).'<br>';
	        	$body .= '___________________________________________'.'<br>';

				// Здесь полная информация об объекте
				$body .= 'Дата размещения объекта в базе: '.$info['create_time'].'<br>';
				$body .= 'Регион: '.(($info['moscow'])?'Москва':'Московская область').'<br>';
				$body .= 'Адрес: '.$info['address'].'<br>';
				$body .= 'Квартира: '.$settings['market'][$info['market']].'<br>';
				$body .= 'Цена всего (руб.): '.$info['price_rub'].'<br>';
				$body .= 'Цена всего (у.е.): '.$info['price_dollar'].'<br>';
				$body .= 'Количество комнат: '.$info['room'].'<br>';
				$body .= 'Этаж: '.$info['storey'].'<br>';
				$body .= 'Всего этажей: '.$info['storeys_number'].'<br>';
				$body .= 'Тип здания: '.sql_getValue('SELECT name FROM obj_housetypes WHERE id='.$info['house_type']).'<br>';
				$body .= 'Общая площадь: '.$info['total_area'].'<br>';
				$body .= 'Жилая площадь: '.$info['living_area'].'<br>';
				$body .= 'Площадь кухни: '.$info['kitchen_area'].'<br>';
				$body .= 'Вид и качество ремонта: '.$info['remont'].'<br>';
				$body .= 'Телефон: '.(($info['phone'])?'есть':'нет').'<br>';
				$body .= 'Санузел: '.$settings['lavatory_types'][$info['lavatory']].'<br>';
				$body .= 'Балкон: '.sql_getValue('SELECT name FROM obj_balcony WHERE id='.$info['balcony']).'<br>';
				$body .= 'Окна: '.$info['windows'].'<br>';
				$body .= 'Кто зарегистрирован: '.$info['registration'].'<br>';
				$body .= 'Правоустанавливающие документы: '.$info['documents'].'<br>';
				$body .= 'Физическое освобождение: '.$info['f_release'].'<br>';
				$body .= 'Подробнее: '.$info['short_description'].'<br>';
				$body .= 'Контактные данные: '.$info['contact_phone'].'<br>';
				$body .= 'Рассрочка: '.(($info['credit'])?"да":"нет").'<br>';
				$body .= 'Аванс: '.(($info['avance'])?"да":"нет").'<br>';
				$body .= 'Ипотека: '.(($info['ipoteka'])?"да":"нет").'<br>';
				$body .= 'Продано: '.(($info['sell'])?"да":"нет").'<br>';

/*
	        	if ($flat_id) {
	        		$row = sql_getRow('SELECT * FROM obj_elem_free WHERE id='.$flat_id);
	        		$body .= 'Квартира: <br>';
	        		$body .= 'Кол-во комнат: '.$row['room'].'<br>';
	        		$body .= 'Площадь: '.$row['total_area'].'/'.$row['living_area'].'/'.$row['kitchen_area'].'<br>';
	        		$body .= 'Этаж: '.$row['storey'].'<br>';
	        		$body .= 'Секция: '.$row['section'].'<br>';
	        		$body .= 'Цена: '.$row['price'].' ('.$row['price_metr'].')<br>';
	        	}
*/
	        	$mail->Body = $body;
	        	$mail->AltBody = strip_tags(str_replace('<br>', "\r\n", $body));
	        	foreach ($emails as $k=>$v) $mail->AddAddress($v);

	        	$res = $mail->Send();
	        	redirect($path.'?msg='.($res ? 'order_message' : 'msg_not_send_email'));
			}
		}
		$page->tpl->assign(array('fdata'=> $fdata));
		return $page->tpl->fetch('form.html');
	}

	function showPlans(&$info) {
		$plans = array();
		$rows = sql_getRows('SELECT p.id as plan_id, p.name as plan_name, p.image as plan_image, i.*
		FROM obj_elem_plans AS p
		LEFT JOIN obj_elem_plans_items AS i ON i.pid=p.id
		WHERE p.pid='.$info['id']);
		foreach ($rows as $key=>$val) {
			if (!isset($plans[$val['plan_id']])) {
				$plans[$val['plan_id']] = array(
					'name'	=> $val['plan_name'],
					'image'	=> $val['plan_image'],
				);
			}
			$plans[$val['plan_id']]['items'][] = $val;
		}
		return $plans;
	}

    /**
	 * Функция генерирует уникальный ID кеша
	 *
	 */
    function _cache($block) {
        $page_obj = & Registry::get('TPage');

        $cache_id = lang().'_'.$page_obj->content['id'].'_'.$_SERVER['REQUEST_URI'];
        // из всего, что есть в get, формируем кеш
        if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $cache_id .= '_'.$_SERVER['REDIRECT_QUERY_STRING'];

        return $cache_id;
    }

    /**
     * Получение анонсов объектов
     */
    function showHotObjects(&$params) {
    	$ret = array();
		//Временно закоментируем
		/*
    	$page_obj = & Registry::get('TPage');
    	$page_obj->tpl->config_load($GLOBALS['domain'].'__'.lang().'.conf', 'object');
    	$limit = (int)$page_obj->tpl->get_config_vars('object_anonce_limit');
    	if (!$limit) $limit = 3;

    	$list = sql_getRows('SELECT * FROM '.$this->table.' AS o WHERE 1 '.$this->sql.' AND o.hot=1 ORDER BY RAND() LIMIT ' . $limit);
    	if (count($list)) {
    		$path = $this->getPathToObject($list[0]['id']);
    		foreach ($list as $key=>$val) {
    			$list[$key]['href'] = $path.'/object/'.$val['id'];
    			$list[$key]['price_rub'] = number_format($val['price_rub'], ' ', ' ', ' ');
    		}
    	}
    	$ret['anonce'] = $list;
    	*/
    	return $ret;
    }

	/**
	* Отправка уведомлений об истекающих объявлениях пользователям, у которых стоит соответствующий флажок
	*/
	function notify_expiring() {
		$users=sql_getRows("SELECT user_id FROM notify_user_settings WHERE type='announcement_ends' AND method='email'");
		$rows=sql_getRows("SELECT * FROM ".$this->table." WHERE status=2 AND UNIX_TIMESTAMP(expired_time)>".strval(time()-60*60*24));
		foreach($rows as $row) if(in_array($row['client_id'],$users)) SendNotify("ANNOUNCEMENT_ENDS", $row['client_id'], $row);
	}
}


?>