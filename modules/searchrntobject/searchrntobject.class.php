<?php
define ('MO_DISTRICS', 166); //Районы МО
require_once 'modules/object_rnt/rnt_object.class.php';

class TSearchRntObject extends TRntObject {

	var $table = 'rnt_objects';
	var $sql = ' AND o.visible > 0 ';
	var $prefix = '----';
//	var $prices = array('не важно', '100 000 у.е.', '150 000 у.е.', '200 000 у.е.', '250 000 у.е.', '300 000 у.е.', '400 000 у.е.', '500 000 у.е.', '750 000 у.е.', '1 000 000 у.е.', '1 500 000 у.е.');

	function getParams(){
		$page = & Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'objectsearch');

		$params = parent::getParams();

		$fld = get('fld', array(), 'gp');

		$param['offset'] 		=	 (int)get('offset',0,'pg');
		$param['limit'] 		=	 (int)get('limit',-1,'pg');
		if ($param['limit'] == -1) {
			$param['limit'] = (int)$page->tpl->get_config_vars('object_limit');
		}
		if (!$param['limit']) {
			$param['limit'] = 10;
		}

		$params['market']			=	isset($fld['market']) ? $fld['market'] : ''; //  рынок - первичный или вторичный
		$params['price_dollar']		=	isset($fld['price_dollar']) ? $fld['price_dollar'] : '';//  ценовой диапозон
		$params['room']				=	isset($fld['room']) ? (int)$fld['room'] : -1;//  кол-во комнат
		$params['district_id']		=	isset($fld['district_id']) ? (int)$fld['district_id'] : 0;// округ
		$params['lot_id']			=	isset($fld['lot_id']) ? $fld['lot_id'] : '';//  лот
		$params['price1']			=	isset($fld['price1']) ? $fld['price1'] : '';//  цена от
		$params['price2']			=	isset($fld['price2']) ? $fld['price2'] : '';//  цена до
		$params['room1']			=	isset($fld['room1']) ? $fld['room1'] : -1;//  комнат от
		$params['room2']			=	isset($fld['room2']) ? $fld['room2'] : -1;//  комнат до
		$params['raion']			=	isset($fld['raion']) ? $fld['raion'] : array();// районы
		$params['metro']			=	isset($fld['metro']) ? $fld['metro'] : array();// метро
		$params['total_area1']		=	isset($fld['total_area1']) ? $fld['total_area1'] : 0;
		$params['total_area2']		=	isset($fld['total_area2']) ? $fld['total_area2'] : 0;
		$params['living_area1']		=	isset($fld['living_area1']) ? $fld['living_area1'] : 0;
		$params['living_area2']		=	isset($fld['living_area2']) ? $fld['living_area2'] : 0;
		$params['kitchen_area1']	=	isset($fld['kitchen_area1']) ? $fld['kitchen_area1'] : 0;
		$params['kitchen_area2']	=	isset($fld['kitchen_area2']) ? $fld['kitchen_area2'] : 0;
		$params['storey1']			=	isset($fld['storey1']) ? $fld['storey1'] : 0;
		$params['storey2']			=	isset($fld['storey2']) ? $fld['storey2'] : 0;
		$params['storeys_number1']	=	isset($fld['storeys_number1']) ? $fld['storeys_number1'] : 0;
		$params['storeys_number2']	=	isset($fld['storeys_number2']) ? $fld['storeys_number2'] : 0;
		$params['address']			=	isset($fld['address']) ? $fld['address'] : '';

        return $params;
	}

	function show(&$params) {
		global $settings;
	    $this->type = 'room';
		if (isset($_GET['adv']) && !isset($_POST['fld'])) {
			$params['_block']['tmpls'][0] = 'adv_search';
			return $this->advsearch($res);
		}

		$param = $this->getParams();

		$res['metrostations'] = sql_getRows('SELECT id, name FROM `obj_locat_metrostations` WHERE 1 ORDER BY name', true);
		$res['districs_mo'] = sql_getRows("SELECT id, name, coordinat, style_for_map FROM `obj_locat_districts` WHERE pid='".MO_DISTRICS."' AND coordinat<>'' ORDER BY name", true);

		//Получим все города МО
		$ids_mo = "";
		foreach ($res['districs_mo'] AS $key=>$value){
			$ids_mo .= $key.",";
		}
		$ids_mo = substr($ids_mo, 0, -1);
		if ($ids_mo){
			$cities_mo = sql_getRows("SELECT id, name, pid FROM `obj_locat_districts` WHERE pid in (".$ids_mo.") ORDER BY pid, name", true);
			foreach ($cities_mo AS $key=>$value){
				$cities_arr[$value['pid']][$value['id']] = array(
					'name' => $value['name']
				);
			}
			$res['cities_mo'] = $cities_arr;
		}

		// Новый код
		if (isset($_POST['fld'])) {
			foreach ($_POST['fld'] as $key=>$val) {
				$data[$key] = !is_array($val) ? mysql_real_escape_string($val) : $val;
			}
			if (isset($data['room'])) {
				$rooms = explode(',', $data['room']);
				foreach ($rooms as $key=>$val) {
					if (!$val) unset($rooms[$key]);
				}
				$data['rooms'] = array_keys($rooms);
			} else {$data['rooms'] = array();}
			if (isset($data['rate'])) {
				$data['rates'] = array_values($data['rate']);
				if ($data['rates']) {
					$data['my_script'] = 'var stars_selected = new Array();';
					foreach ($data['rates'] as $k=>$rate) {
						$data['my_script'] .= 'stars_selected['.$k.'] = "'.$rate.'";';
					}

					$data['my_script'] .= "
					var id;
					for (i=0; i<stars_selected.length; i++) {
						id = stars_selected[i];
						document.getElementById('rate_'+id).checked = true;
						rateArr[id]=[document.getElementById('rate_'+id).title];
					}
					q2000_rnt.search.rateGetList();";
				}
			}
			if (isset($data['raion'])) {
				foreach ($data['raion'] as $k=>$v) {
					$data['raions'][$v] = $res['districs_mo'][$v]['name'];
				}
			}
			if (isset($data['city_mo'])) {
				foreach ($data['city_mo'] as $k=>$v) {
					$data['city_mo_name'][$v] = $cities_mo[$v]['name'];
				}
			}
			if (isset($data['metro'])) {
				foreach ($data['metro'] as $k=>$v) {
					$data['metros'][$v] = $res['metrostations'][$v];
				}
			}

			if (isset($data['price_from'])) {$data['price_from'] = (int)str_replace(" ", "", $data['price_from']);}
			if (isset($data['price_to'])) {$data['price_to'] = (int)str_replace(" ", "", $data['price_to']);}
			if (!isset($data['storey_from'])){$data['storey_from'] = 3;}
			if (!isset($data['storey_to'])){$data['storey_to'] = 8;}
			if (!isset($data['storeys_number_from'])){$data['storeys_number_from'] = 9;}
			if (!isset($data['storeys_number_to'])){$data['storeys_number_to'] = 20;}
			if (!isset($data['live_area_from'])){$data['live_area_from'] = 30;}
			if (!isset($data['live_area_to'])){$data['live_area_to'] = 100;}
			if (!isset($data['kitchen_area_from'])){$data['kitchen_area_from'] = 5;}
			if (!isset($data['kitchen_area_to'])){$data['kitchen_area_to'] = 30;}
		} else {
			$data = array(); //$settings['default_filter_data'];
			$data['price_from'] = 2000;
			$data['price_to'] = 300000;
			$data['total_area_from'] = 20;
			$data['total_area_to'] = 200;
		}

		$data['orderBoxDisplay'] = "none";
		if (isset($_COOKIE['obj_favorite'])){
			$obj_favorite = unserialize($_COOKIE['obj_favorite']);
		   if (!empty($obj_favorite))$data['favorite'] = "Избранное (".count($obj_favorite).")";
			$data['orderBox'] = "<script>showOrderBox = true;</script>";
			$data['orderBoxDisplay'] = "inline";
		}
		if(isset($_GET['market'])) $data['market'] = $_GET['market'];

		$data['metrostations'] = $res['metrostations'];
		$data['districs_mo'] = $res['districs_mo'];
		$data['cities_mo'] = $res['cities_mo'];
        $res = $data;
		$house_type = sql_getRows('SELECT id, name FROM `obj_housetypes` WHERE 1 ORDER BY id', true);
		$i=$j=1;
		foreach ($house_type AS $key=>$value){
			$res['house_type'][$i][$key] = $value;
			if ($j>count($house_type)/2) {$i++;$j=0;}
			$j++;
		}
		$tpl = & Registry::get('TTemplate');
		$tpl->assign($res);
		$ret['search_menu'] = $tpl->fetch("search_rnt_menu.html");
		$this->search_by_metro();
		$data['city'] = (isset($_POST['fld']['city']))?$_POST['fld']['city']:'1';
		$data['price_type'] = 1;

		$sql = $this->generateQuery($data);
		$order_by = $this->getSortTable (array('sort'=>'price','sort_d'=>'asc'));
		if ($adv_search){
			$list = $this->getList(array('offset'=>0, 'limit'=>20), $sql, $order_by, 20);
			$obj_arr = $this->formatObjList($list['list'], $data['price_type'], $list['count'], 0, 20, $sql);
		}else{

			$list = $this->getList(array('offset'=>0, 'limit'=>12), $sql, $order_by, 12);
			$obj_arr = $this->formatObjList($list['list'], $data['price_type'], $list['count'], 0, 12, $sql);

		}

		$ret['obj_on_page'] = $settings['obj_on_page'];
		$ret['list'] = $obj_arr['data'];
		$ret['pages'] = $obj_arr['navig'];
		$ret['searchResultText'] = $obj_arr['searchResultText'];
		$ret['xml_objects'] = $this->getXmlObj($ret['list']);
		$ret['tableClass'] = $obj_arr['tableClass'];
		$ret['price_type'] = 1;
		return $ret;
	}

	/**
	 * Отрисовка верхней формы поиска
	 *
	 * @param array $params
	 * @return array
	 */
	function showTopSearchForm(&$params) {
	    global $settings;

	    $page = & Registry::get('TPage');
	    $content = $page->show_content();
		if (!$content['visible_top_search']) return array();

	    $res = $settings['default_filter_data'];
		$res['metrostations'] = sql_getRows('SELECT id, name FROM `obj_locat_metrostations` WHERE 1 ORDER BY name', true);
		$res['districs_mo'] = sql_getRows("SELECT id, name, coordinat, style_for_map FROM `obj_locat_districts` WHERE pid='".MO_DISTRICS."' AND coordinat<>'' ORDER BY name", true);

		//Получим все города МО
		$ids_mo = "";
		foreach ($res['districs_mo'] AS $key=>$value){
			$ids_mo .= $key.",";
		}
		$ids_mo = substr($ids_mo, 0, -1);
		if ($ids_mo){
			$cities_mo = sql_getRows("SELECT id, name, pid FROM `obj_locat_districts` WHERE pid in (".$ids_mo.") ORDER BY pid, name", true);
			foreach ($cities_mo AS $key=>$value){
				$cities_arr[$value['pid']][$value['id']] = array(
					'name' => $value['name']
				);
			}
			$res['cities_mo'] = $cities_arr;
		}
	    return array('top_search_data' => $res);
	}

	/**
	 * Форма быстрого поиска
	 *
	 * @param array $params
	 * @return array
	 */
	function showQuickSearchForm(&$params) {
		global $settings;
		$ret = $settings;
		$ret['districts'] = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid IS NULL ORDER BY priority', true);
		return $ret;
	}

	/**
	 * Функция формирует выражение WHERE в зависимости от переданных ей параметров
	 *
	 * @param array $res - все параметры из адресной строки
	 */
	function generateQuery($res) {
		global $settings;
		$sql = "";
		$sql .= " AND o.obj_type_id='room'";

		if (isset($res['advanced_search']) && (int)$res['advanced_search']==1) $adv_search = true;
		else $adv_search = false;

		// Тип (новостройки, вторичка)
		//---------------------------------------------------------------
		if (isset($res['market']) && $res['market'] > -1){
			if ($res['market'] == '1') $sql .= " AND o.market='first'";
			else $sql .= " AND o.market='second'";
		}

		// Количество комнат
		//---------------------------------------------------------------
		if (isset($res['room']) && !empty($res['room'])){
			$rooms="";
			foreach($res['room'] as $key=>$val) {
			  if(!empty($res['room'][$key]) && (int)$res['room'][$key]) $rooms.=$val.",";
			}
			if(!empty($rooms)) {
				$rooms=substr($rooms,0,-1);
				$sql.=" AND o.room IN (".$rooms.") ";
			}
		}
		// Элитность
		//---------------------------------------------------------------
		if (isset($res['rate']) && !empty($res['rate'])){
		    if (is_numeric($res['rate'])) {
                $sql .= " AND o.stars='".(int)$res['rate']."'";
		    } else {
		        $sql .= " AND o.stars IN ('".implode("','", $res['rate'])."')";
		    }
		}

		// Площадь
		//---------------------------------------------------------------
		if (isset($res['total_area_from']) && $res['total_area_from'] > 0) {
			$sql .= " AND (o.total_area>=".$res['total_area_from'].")";
		}
		if (isset($res['total_area_to']) && $res['total_area_to'] > 0) {
			$sql .= " AND (o.total_area<=".$res['total_area_to'].")";
		}

		//------------------------------------------------------------------------
		//москва или область
		if (isset($res['city']) && in_array($res['city'], array('1', '2'))) {
			$sql .= " AND o.moscow=".($res['city'] == '1' ? 1 : 0);

			if ($res['city'] == '1'){
				// Метро
				//---------------------------------------------------------------
				if (isset($res['metro']) && !empty($res['metro'])){
					$sql .= " AND o.metro_id IN(".implode(", ", $res['metro']).") ";
				}

				// Время от метро
				//---------------------------------------------------------------
				if (isset($res['metro_time_from']) && $res['metro_time_from'] > 0) {
					$sql .= " AND (o.metro_dest_value>=".$res['metro_time_from'].")";
				}
				if (isset($res['metro_time_to']) && $res['metro_time_to'] > 0 && $res['metro_time_to']>$res['metro_time_from']) {
					$sql .= " AND (o.metro_dest_value<".$res['metro_time_to'].")";
				}
				if (isset($res['metro_time_type']) && (int)$res['metro_time_type'] > 0) {
					if ((int)$res['metro_time_type']==1) $sql .= " AND (o.metro_dest_text='1')";
					if ((int)$res['metro_time_type']==2) $sql .= " AND (o.metro_dest_text='0')";
				}
			} else {
				//------------------------------------------------------------------------
				// города
				if (isset($res['city_mo']) && is_array($res['city_mo']) && !empty($res['city_mo'])) {
					$sql .= " AND o.district_city_id IN (".implode(", ", $res['city_mo']).") ";
					$districs_mo = sql_getRows("SELECT pid FROM `obj_locat_districts` WHERE id  IN (".implode(", ", $res['city_mo']).")");
					$res['raion'] = array_merge($res['raion'], $districs_mo);
				}
				// округ
				if (isset($res['raion']) && is_array($res['raion']) && !empty($res['raion'])) {
					$sql .= " AND o.district_id IN(".implode(", ", $res['raion']).") ";
				}
			}
		}

		// Цена
		//---------------------------------------------------------------
		if (isset($res['price_type']) && $res['price_type'] > 0) {

			$page = & Registry::get('TPage');

			if ($res['price_type']==1){
				if (isset($res['price_from']) && (int)$res['price_from'] > 0) {
					$price_from = (int)str_replace(" ", "", $res['price_from']);
					$sql .= " AND (o.price_rub>=".$price_from.")";
				}
				if (isset($res['price_to']) && (int)$res['price_to'] > 0) {
					$price_to = (int)str_replace(" ", "", $res['price_to']);
					$sql .= " AND (o.price_rub<".$price_to.")";
				}
			}
			if ($res['price_type']==2){
				if (isset($res['price_from']) && (int)$res['price_from'] > 0) {
					$price_from = (int)$res['price_from'];
					$sql .= " AND (o.price_dollar>=".$price_from.")";
				}
				if (isset($res['price_to']) && (int)$res['price_to'] > 0) {
					$price_to = (int)$res['price_to'];
					$sql .= " AND (o.price_dollar<".$price_to.")";
				}
			}
		}

		// Адрес
		//---------------------------------------------------------------
		if (!empty($res['addres'])) {
			$addres = iconv('utf-8', 'windows-1251', $res['addres']);
			if (!$addres) $addres = $res['addres'];
			$sql .= ($addres!='Введите ключевое слово')?" AND o.address LIKE '%".e(strip_tags($addres))."%'":"";
		}

		//Расширенный поиск
		if ($adv_search){
			// Остальная площадь
			if (isset($res['live_area_from']) && $res['live_area_from'] > 0) {
				$sql .= " AND (o.living_area>=".$res['live_area_from'].")";
			}
			if (isset($res['live_area_to']) && $res['live_area_to'] > 0) {
				$sql .= " AND (o.living_area<=".$res['live_area_to'].")";
			}
			if (isset($res['kitchen_area_from']) && $res['kitchen_area_from'] > 0) {
				$sql .= " AND (o.kitchen_area>=".$res['kitchen_area_from'].")";
			}
			if (isset($res['kitchen_area_to']) && $res['kitchen_area_to'] > 0) {
				$sql .= " AND (o.kitchen_area<=".$res['kitchen_area_to'].")";
			}

			// Этаж
			//---------------------------------------------------------------
			if (isset($res['storey_from']) && $res['storey_from'] > 0) {
				$sql .= " AND (o.storey>=".$res['storey_from'].")";
			}
			if (isset($res['storey_to']) && $res['storey_to'] > 0) {
				$sql .= " AND (o.storey<=".$res['storey_to'].")";
			}
			if (isset($res['storey_first']) && $res['storey_first'] > 0) {
				$sql .= " AND (o.storey>1)";
			}
			if (isset($res['storey_last']) && $res['storey_last'] > 0) {
				$sql .= " AND (o.storey<o.storeys_number)";
			}

			// Этажность
			//---------------------------------------------------------------
			if (isset($res['storeys_number_from']) && $res['storeys_number_from'] > 0) {
				$sql .= " AND (o.storeys_number>=".$res['storeys_number_from'].")";
			}
			if (isset($res['storeys_number_to']) && $res['storeys_number_to'] > 0) {
				$sql .= " AND (o.storeys_number<=".$res['storeys_number_to'].")";
			}

			// Тип дома
			//---------------------------------------------------------------
			if (isset($res['house_type']) && !empty($res['house_type'])){
				$sql .= " AND o.house_type IN(".implode(", ", $res['house_type']).") ";
			}

			// Специальные условия
			//---------------------------------------------------------------
			if (isset($res['avance']) && $res['avance'] > 0) {
			    $sql .= " AND o.avance=1";
			}
			if (isset($res['credit']) && $res['credit'] > 0) {
			    $sql .= " AND o.credit=1";
			}
			if (isset($res['ipoteka']) && $res['ipoteka'] > 0) {
			    $sql .= " AND o.ipoteka=1";
			}


			// Наличие в квартире
			if (isset($res['furniture']) && $res['furniture'] > 0) {
				$sql .= " AND o.furniture=1";
			}
			if (isset($res['refrigerator']) && $res['refrigerator'] > 0) {
				$sql .= " AND o.refrigerator=1";
			}
			if (isset($res['washing_m']) && $res['washing_m'] > 0) {
				$sql .= " AND o.washing_m=1";
			}
			if (isset($res['phone']) && $res['phone'] > 0) {
				$sql .= " AND o.phone=1";
			}
			if (isset($res['tv']) && $res['tv'] > 0) {
				$sql .= " AND o.tv=1";
			}
			if (isset($res['lavatory']) && $res['lavatory'] > 0) {
				$sql .= " AND o.lavatory=0"; //0 это раздельный сан.узел
			}
			if (isset($res['balcony']) && $res['balcony'] > 0) {
				$sql .= " AND o.balcony>0";
			}
		}

		// Проверим статус объявления
		$sql .= " AND o.status = '2' ";
/*
		//------------------------------------------------------------------------
		// направление
		if (isset($res['direction']) && $res['direction'] != 0){
			$sql .= " AND o.direction = '".$res['direction']."' ";
		}
*/

		return $sql;
	}

	//---------------------------------------------------------------------------------
	function getChilds($table, $orderby, $id=null, $where=null, $level=0){
		$childs = array();
    	if (!isset($id))
			$childs = sql_getRows("SELECT * FROM ".$table." WHERE ".(isset($where) ? $where.' AND ' : '')." pid IS NULL ".$orderby, true);
		else
			$childs = sql_getRows("SELECT * FROM ".$table." WHERE ".(isset($where) ? $where.' AND ' : '')." pid=".$id." ".$orderby, true);

		if (!empty($childs)) {
    		foreach ($childs as $key=>$val) {
    			$childs[$key]['name'] = str_pad($val['name'], strlen($val['name']) + $level*strlen($this->prefix), $this->prefix, STR_PAD_LEFT);
    			$childs[$key]['items'] = $this->getChilds($table, $orderby, $val['id'], $where, $level+1);
    		}
    	}
    	return $childs;
	}

    function getChildsList($pids, &$res, $full = 0) {
    	foreach ($pids as $key=>$val) {
			if ($full) $res[$val['id']] = $val; else $res[$val['id']] = $val['name'];
			if (isset($val['items'])) {
				$this->getChildsList($pids[$key]['items'], $res, $full);
			}
    	}
    }

    /**
     * Форма расширенного поиска
     *
     * @param array $param
     * @return string
     */
	function advsearch($param) {

		$ret = "";
		$tpl = & Registry::get('TTemplate');

		global $settings;
		$res['prices'] = $this->prices;
		$res['rooms'] = $settings['room_count'];
		$res['type'] = get('type', '', 'g');

		switch ($res['type']) {
			case 'district' : {
				$rows = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid IS NULL ORDER BY priority', true); // Районы Москвы
				$districts2 = sql_getRows('SELECT id, pid, name FROM obj_locat_districts WHERE pid IN ('.implode(',', array_keys($rows)).') ORDER BY priority');
				$districts = array();
				foreach ($districts2 as $k=>$v) {
					if (!isset($districts[$v['pid']])) {
						$districts[$v['pid']] = array(
							'name'	=> $rows[$v['pid']],
						);
					}
					$districts[$v['pid']]['items'][] = $v;
				}
				$res['districts'] = $districts;
				break;
			}
			case 'metro' : {
				$this->search_by_metro();
				break;
			}
			case 'params' : {
				$res['markets'] = $settings['market'];
				$res['districts'] = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid IS NULL ORDER BY priority', true);
				break;
			}
		}

		$tpl->assign($res);
		$ret = $tpl->fetch("adv_search.html");

		return $ret;
	}

	/**
	 * Функция поиска по параметрам
	 *
	 */
	function search(&$params){
		$page_obj = & Registry::get('TPage');
		$res = $params;

		$sql = $this->generateQuery($res);

		//------------------------------------------------------------------------
		// Сортировка
		$order_by = $this->getSort($res);

		$list = $this->getList($res, $sql, $order_by);
		$res['objects'] = $list['list'];

		$this->getFlatStars($list['list']);

        $search_query = '?'.$_SERVER['QUERY_STRING'];
        $search_query = remove_query_arg($search_query, 'offset');
        $search_query = remove_query_arg($search_query, 'limit');
        $search_query = remove_query_arg($search_query, 'sort_by');
        $search_query = remove_query_arg($search_query, 'sort_type');
        $res['search_query'] = '&'.substr($search_query, 1);

        $res['pages1'] = TContent::getNavigation($list['count'], $res['limit'], $res['offset'], $page_obj->content['href']);
        $res['pages2'] = TContent::getNavigation($list['count'], $res['limit'], $res['offset'], $page_obj->content['href'], null, 'ids_pages.html');

        $res['sort_by'] = $res['sort_by'] ? $res['sort_by'] : 'price_rub';
        $res['sort_type'] = $res['sort_type'] ? $res['sort_type'] : 'asc';
        $res['sort'] = array();
        $res['sort'][$res['sort_by']] =  $res['sort_type'];

        return $res;
	}

	/**
	 * Функция поиска по схеме метро
	 * Если в GET пришли данные о выбранных станциях метро, то передаем их в функцию search, иначе - формируем схему метро
	 */
	function search_by_metro() {
		$metrostations = sql_getRows('SELECT * FROM obj_locat_metrostations WHERE id<>1 AND x<>0 AND y<>0');

		// Расставляем точки метро на карте
		$cache_name = 'metro_pp.cache';
		$filename = 'javascripts/pp.js';
		if (!is_file($filename) || !cache_table_test($cache_name, array('obj_locat_metrostations'), true)) {
			$rows = $metrostations;
			$script = "document.write('";
			foreach ($rows as $key=>$val) {
				$script .= "<img src=\"/images/pp.gif\" id=\"pp".$val['id']."\" style=\"position: absolute; top:".($val['y']-4)."px; left: ".($val['x']-4)."px; border: 0; display: none; cursor: pointer\" onclick=\"checkMetro(".$val['id']."); return false;\" title=\"".$val['name']."\" alt=\"".$val['name']."\">";
			}
			$script .= "');";
			$fp = fopen($filename, 'w');
			fwrite($fp, $script);
			fclose($fp);
			cache_save($cache_name, '', true);
		}
		// Рисуем area для map
		$cache_name = 'metro_area.cache';
		$filename = 'javascripts/area.js';
		if (!is_file($filename) || !cache_table_test($cache_name, array('obj_locat_metrolines', 'obj_locat_metrostations'), true)) {
			$rows = $metrostations;
			$script = "document.write('";
			foreach ($rows as $key=>$val) {
				$script .= "<area shape=\"rect\" coords=\"".($val['x']-4).", ".($val['y']-4).", ".($val['x']+4).", ".($val['y']+4)."\" onclick=\"checkMetro(".$val['id']."); return false;\" title=\"".$val['name']."\" href=\"javascript: void(0);\">";
			}
			$script .= "');";
			$fp = fopen($filename, 'w');
			fwrite($fp, $script);
			fclose($fp);
			cache_save($cache_name, '', true);
		}
		//Генерируем привязки к округам
		$cache_name = 'metro_okrug_binds.cache';
		$filename = 'javascripts/okrug_binds.js';
		if (!is_file($filename) || !cache_table_test($cache_name, array('obj_locat_metrostations'), true)) {
			$okrug_binds=array();
			foreach($metrostations as $station) {
				$okruga=explode(',',$station['okrug']);
				foreach($okruga as $okrug) {
					$okrug=intval($okrug);
					if(!$okrug) continue;
					$okrug_binds[$okrug].=$station['id'].',';
				}
			}
			$script="var okrug_binds={\n";
			foreach($okrug_binds as $okr=>$okrug_bind) $script.="\t".$okr.": [".$okrug_bind."],\n";
			$script.="};\n";
			$fp = fopen($filename, 'w');
			fwrite($fp, $script);
			fclose($fp);
			cache_save($cache_name, '', true);
		}
		return true;
	}

	/**
	 * Вычисляет звездность квартиры
	 */
	function getFlatStars ($flatsArr){
		// Получаем все звезды из базы
		$obj_stars = sql_getRows('SELECT * FROM `obj_stars` WHERE 1 ORDER BY stars ASC', true);
		if (!empty($obj_stars)) {
			foreach ($obj_stars AS $key => $val) {
				$obj_stars[$key]['storey']		= unserialize($obj_stars[$key]['storey']);
				$obj_stars[$key]['material']	= unserialize($obj_stars[$key]['material']);
				$obj_stars[$key]['area']		= unserialize($obj_stars[$key]['area']);
			}

	        //создаем временную таблицу
			$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_obj_stars (
			`storey_from`		INT(10),
			`storey_to`			INT(11),
			`house_type`		VARCHAR(127),
			`room`				INT(2),
			`total_area_from`	FLOAT(5,1),
			`total_area_to`		FLOAT(5,1),
			`star`				INT(2))";
			sql_query($sql);

			//заполняем временную таблицу
			foreach ($obj_stars AS $key => $val) {
				if (!empty($obj_stars[$key]['area'])){

					$material = "";
					foreach ($val['material'] AS $vm) $material .= $vm.", ";
					$material = substr($material, 0, -2);

					foreach ($obj_stars[$key]['area'] AS $k => $v) {
						$sql = "INSERT INTO tmp_obj_stars (`storey_from`,`storey_to`,`house_type`,`room`,`total_area_from`,`total_area_to`,`star`)
						VALUES ('".$val['storey']['storey_from']."','".$val['storey']['storey_to']."','".$material."','".$v['flat']."','".$v['from']."','".$v['to']."','".$val['stars']."')";
						sql_query($sql);
					}
				}
			}

			//раздаем звезды
			foreach ($flatsArr AS $key => $val){
				$where = ($val['room'])?" AND room='".$val['room']."'":"";
				$where .= ($val['storeys_number'])?" AND storey_from<".$val['storeys_number']:"";
				$where .= ($val['storeys_number'])?" AND storey_to>=".$val['storeys_number']:"";
				$where .= ($val['house_type'])?" AND house_type='".$val['house_type']."'":"";
				$where .= ($val['total_area'])?" AND total_area_from<".$val['total_area']:"";
				$where .= ($val['total_area'])?" AND total_area_to>=".$val['total_area']:"";
				$flatsArr[$key]['star'] = sql_getValue("SELECT star FROM tmp_obj_stars WHERE 1 ".$where);
			}
		}

		return ;
	}

	function formatObjList ($data, $price_type = '1', $count, $page_id, $limit, $sql, $favoriteFlag = false) {
		$loadFlag = ($favoriteFlag) ? 'true' : '';

		if (empty($data))
			return array ('data'=>$data,'navig'=>'','searchResultText'=>"Найдено ".$count." объектов");

		$page = & Registry::get('TPage');

		$obj_favorite = unserialize($_COOKIE['obj_favorite']);
		$class = true;
		$i = 0;
		$tableClass = "";
		foreach ($data AS $key=>$value) {
			if ($value['image100']){
				$image100 = $value['image100'];
				$image100 = (substr($image100, 0, 1)=="/") ? substr($image100, 1) : $image100;
				if (is_file($image100)) $image100 = "/".$image100;
				else $image100 = "/images/pic_tabl_nophoto.gif";
			} else $image100 = "/images/pic_tabl_nophoto.gif";

			if ($price_type==1){
				$price = $value['price_rub'];
				$price = (int) str_replace(" ", "", $price);
				$price = number_format($price, ' ', ' ', ' ');
				$price_field = 'price_rub';
			} else {
				$price = $value['price_dollar'];
				$price = (int) str_replace(" ", "", $price);
				$price = number_format($price, ' ', ' ', ' ');
				$price_field = 'price_dollar';
			}

//			$event = "onmouseout=\"GEvent.trigger(marker[".$i."],'mouseout')\"
//			onmouseover=\"GEvent.trigger(marker[".$i."],'mouseover'); q2000_rnt.search.showMap(".$i."); return false; \"";
			$event = "";
			$data[$key]['tr_id']		= "list_tr_".$i."";
			$data[$key]['event']		= $event;
			$data[$key]['image100']		= $image100;
			$data[$key]['class']		= ($class)?"class='color'":"";
			$data[$key]['total_area']	= number_format($value['total_area'], 0, '', '');
			$data[$key]['living_area']	= number_format($value['living_area'], 0, '', '');
			$data[$key]['kitchen_area']	= number_format($value['kitchen_area'], 0, '', '');
			$data[$key]['price']		= $price;
			$data[$key]['favorile']		= (in_array ($value['id'], $obj_favorite)) ? '<img src="/images/ico_added_favorite.png" onClick="q2000_rnt.search.addToFavoriteObject(\'\','.$value['id'].'); return false;" alt="" />' : '<img src="/images/ico_add_cart.png" onClick="q2000_rnt.search.addToFavoriteObject(\'add\','.$value['id'].'); return false;" alt="" />';
			$data[$key]['contact_phone']= str_replace (",", ",<br />", $value['contact_phone']);

			$tableClass .= "tableClass[".$i."] = ".(($class)?"'color'":"''").";";
			$class = ($class) ? false : true;
			$i++;

			$data[$key]['href'] = $this->getPathToObject($value['id'],$value['obj_type_id']);
		}
		$tableClass = "<script language='JavaScript'>".$tableClass."</script>";
		$variable = sql_getRows('SELECT MIN('.$price_field.') AS minimum, MAX('.$price_field.') AS maximum FROM `rnt_objects` AS o WHERE 1 AND o.visible > 0 '.$sql);
		$min = (!empty($variable)) ? number_format($variable[0]['minimum'], 0, ' ', ' ') : "";
		$max = (!empty($variable)) ? number_format($variable[0]['maximum'], 0, ' ', ' ') : "";

		// Строим навигацию
		$cnt = floor ($count/$limit);
		$nav = "";
		if ($cnt) {
			$navArr = array();
			if($page_id != 0) $navArr[] = " <a class=\"prev\" href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id - 1)."'; q2000_rnt.search.load(".$loadFlag.");\">Предыдущая</a>\n";
			else		  $navArr[] = " <a class=\"prevDisabled\" href='javascript:void(0);'\" title=\"предыдущая\">Предыдущая</a>\n";
			if($page_id-6>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '0'; q2000_rnt.search.load(".$loadFlag.");\">1</a>\n";

			// Находим две ближайшие станицы с обоих краев, если они есть
			if($page_id-6>0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-6)."'; q2000_rnt.search.load(".$loadFlag.");\">...</a>\n";
			if($page_id-5>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-5)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id-4)."</a>\n";
			if($page_id-4>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-4)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id-3)."</a>\n";
			if($page_id-3>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-3)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id-2)."</a>\n";
			if($page_id-2>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-2)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id-1)."</a>\n";
			if($page_id-1>=0) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id-1)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id-0)."</a>\n";

			$navArr[] = "<span>".($page_id+1)."</span>\n";

			if($page_id+1<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+1)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id+2)."</a>\n";
			if($page_id+2<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+2)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id+3)."</a>\n";
			if($page_id+3<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+3)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id+4)."</a>\n";
			if($page_id+4<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+4)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id+5)."</a>\n";
			if($page_id+5<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+5)."'; q2000_rnt.search.load(".$loadFlag.");\">".($page_id+6)."</a>\n";
			if($page_id+6<$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id+6)."'; q2000_rnt.search.load(".$loadFlag.");\">...</a>\n";

			if($page_id+6<=$cnt) $navArr[] = " <a href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($cnt)."'; q2000_rnt.search.load(".$loadFlag.");\">".($cnt+1)."</a>\n";
			if($page_id != $cnt) $navArr[] = " <a class=\"next\" href='javascript:void(0);' onClick=\"document.getElementById('page_obj').value = '".($page_id + 1)."'; q2000_rnt.search.load(".$loadFlag.");\">Следующая</a>\n";
			else		     $navArr[] = " <a class=\"nextDisabled\" href='javascript:void(0);'\" title=\"следующая\">Следующая</a>\n";

			$nav = implode("", $navArr);
		}
		$nav = ($nav)?"<div class=\"pagingBox\">\n<div class=\"center\">\n<div class=\"paging\">\n".$nav."</div>\n</div>\n</div>\n":"";

		return array (
			'data'				=> $data,
			'navig'				=> $nav,
			'searchResultText'	=> ($count)?"Найдено ".number_format($count,0,' ',' ')." объектов от ".$min." до ".$max.(($price_type==1)?" p.":" $"):"Найдено ".number_format($count,0,' ',' ')." объектов",
			'tableClass'		=> $tableClass,
		);
	}


	/**
	 * Функция обрабатывает ajax запрос
	 */

	function searchResult(){
		$favoriteFlag = false;
		$loadFlag = 'false';
		$fld = $_REQUEST['fld'];
		$limit = (int)$_REQUEST['limit'];
		$page = (int)$_REQUEST['page'];
		$sort = $_REQUEST['sort'];
		$sort_d = $_REQUEST['sort_d'];

		$sql = "";
		if (isset ($_REQUEST['favorite']) && $_REQUEST['favorite']) {
			if (isset ($_SESSION['new_objects'])) {
				$_COOKIE['obj_favorite'] = $_SESSION['new_objects'];
				unset ($_SESSION['new_objects']);
			}

			if (isset($_COOKIE['obj_favorite'])){
				$obj_favorite = unserialize($_COOKIE['obj_favorite']);
				if (count ($obj_favorite)) {
					$sql = " AND o.id IN ('".implode("','", $obj_favorite)."')";
					$favoriteFlag = true;
					$loadFlag = 'true';
				}
			}
		}

		$sql = ($sql) ? $sql : $this->generateQuery($fld);
		$res = array('offset'=>($page*$limit), 'limit'=>$limit);
		$order_by = $this->getSortTable (array('sort'=>$sort,'sort_d'=>$sort_d));

		$list = $this->getList($res, $sql, $order_by);

		$obj_arr = $this->formatObjList($list['list'], $fld['price_type'], $list['count'], $page, $limit, $sql, $favoriteFlag);
		$list['list'] = $obj_arr['data'];

		$table = "<table class='objects' id='report'>";
		$table .= "<colgroup>
				<col class=\"rooms\" />
				<col class=\"icons\" />
				<col />
				<col />
				<col />
				<col />
				<col />
				<col />
				<col />
				<col class=\"select\" />
			</colgroup>";
		$table .= "<tr>\n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('img'); return false;\">Комн.</th> \n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('room'); return false;\">Карта</a></th>\n
				<th><a class=\"sort top\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('metro'); return false;\">Метро</a></th>\n
				<th><a class=\"sort bottom\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('address'); return false;\">Адрес</a></th>\n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('storey'); return false;\">Этаж</a></th>\n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('area'); return false;\">Площадь</a></th>\n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('price'); return false;\">Цена ".(($fld['price_type']==1)?" руб.":" $")."</a></th>\n
				<th><a class=\"sort\" href='javascript:void(0);' onClick=\"q2000_rnt.search.tableSort('stars'); return false;\">Комфортность дома</a> </th>\n
				<th>Контакты</th>\n
				<th></th>\n
			</tr>\n";

		if ($list){
			$i = 0;
			foreach ($list['list'] AS $key=>$value) {
				$table .= "<tr ".$value['class']." ".$value['event']." id='".$value['tr_id']."'>\n
				<td>".$value['room']."</td>
				<td>\n
				<a href=\"javascript:void(0);\"><img src=\"/images/ico_photo.gif\" alt=\"\" /></a>\n
				<img id=\"".$i."\" src=\"/images/map_ico.png\" onmouseover=\"q2000_rnt.search.showMapGoogle(".$i."); return false;\"  onclick=\"this.onmouseout=null; q2000_rnt.search.showMapGoogle(".$i."); return false;\" onmouseout=\"q2000_rnt.search.closeDiv('object_mapmouse'); return false;\" style=\"cursor:pointer\"  title=\"Нажмите чтобы закрепить карту\"  alt=\"\" />\n
				</td>\n
				<td>".$value['metroname']."</td>
				<td><a href=\"".$value['href']."/object_rnt/".$value['id']." \"  target=\"_blank\">".$value['address']."</a></td>
				<td>".$value['storey']."/".$value['storeys_number']."</td>
				<td>".$value['total_area']."/".$value['living_area']."/".$value['kitchen_area']."</td>
				<td style=\"width:80px; \">".$value['price']."</td>
				<td><img src='/images/star_".$value['stars'].".png' alt='' /></td>
				<td>".$value['contact_phone']."</td>
				<td><span id='favorite_".$value['id']."'>".$value['favorile']."</span></td>
				";
				$i++;
			}
		}
		$table .= "</table>";
		$table .= $obj_arr['navig'];

		$data = array(
			'table'=> iconv('windows-1251', 'utf-8', $table),
			'searchResultText'=>iconv('windows-1251', 'utf-8', $obj_arr['searchResultText']),
			'xml_objects' => iconv('windows-1251', 'utf-8', $this->getXmlObj($list['list'])),
			'tableClass' => iconv('windows-1251', 'utf-8', $obj_arr['tableClass']),
		);
		if ($favoriteFlag) $data['favoriteLink'] = iconv('windows-1251', 'utf-8', "<a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.addToFavorite('delete');\">Удалить выделенные объекты из избранного</a>");

		if ($favoriteFlag) {
			$data['show_f'] = iconv('windows-1251', 'utf-8', "<a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.load(); return false;\"> <img src=\"/images/back.png\"> Вернуться к результатам поиска</a>");
		} else {
			if (isset($_COOKIE['obj_favorite'])){
				$obj_favorite = unserialize($_COOKIE['obj_favorite']);
				if (!empty($obj_favorite)) {
					$data['show_f'] = iconv('windows-1251', 'utf-8', " <a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.load(true); return false;\"> <img src=\"/images/favorite.png\"> Избранное(".count($obj_favorite).")</a>");
				}
			} else {
				$data['show_f'] = iconv('windows-1251', 'utf-8', "<a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.load(true); return false;\"></a>");
			}
		}

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	/**
	 * Функция сохраняет координаты адреса
	 *
	 */
	function saveCoord(){
		$coordinat = json_decode($_REQUEST['marker'], true);
		$new_lat = $coordinat['new_lat'];
		$new_lng = $coordinat['new_lng'];
		$address_id = (int)$coordinat['address_id'];

		if ($address_id && $new_lat && $new_lng) {
			sql_query("UPDATE `obj_address` SET x=".$new_lat.", y=".$new_lng." WHERE id=".$address_id);
		}

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode("");
		exit;
	}

	/**
	 * Функция создает xml структуры для использования в google-map
	 *
	 * @param array $data - массив объектов недвижимости
	 * @return xml структура
	 */
	function getXmlObj ($data){
		$host = 'http://'.$_SERVER['HTTP_HOST'].'/';
		$output = '<?xml version="1.0" encoding="Windows-1251" ?><markers>';

		foreach ($data AS $key=>$row) {
			$obj_id		= 'obj_id="'.$this->replace_symbols($row['id']).'"';
			$name		= 'name="'.$this->replace_symbols($row['room']."-комн.кв.").'"';
			$address	= 'address="'.$this->replace_symbols($row['address']).'"';
			$lat		= 'lat="'.($row['x']?$row['x']:'0.000000').'"';
			$lng		= 'lng="'.($row['y']?$row['y']:'0.000000').'"';
			$stars		= 'stars="'.$this->replace_symbols($row['stars']).'"';
			$total_area	= 'total_area="'.$this->replace_symbols($row['total_area']).'"';
			$living_area= 'living_area="'.$this->replace_symbols($row['living_area']).'"';
			$kitchen_area= 'kitchen_area="'.$this->replace_symbols($row['kitchen_area']).'"';
			$price_rub	= 'price_rub="'.$this->replace_symbols($row['price_rub']).'"';
			$type		= 'type="'.$this->replace_symbols($row['market']).'"';
			$address_id	= 'address_id="'.$row['address_id'].'"';
			$output .= '<marker '.$obj_id.' '.$name.' '.$address.' '.$lat.' '.$lng.' '.$stars.' '.$total_area.' '.$living_area.' '.$kitchen_area.' '.$price_rub.' '.$type.' '.$address_id.' />';
		}

		$output .= "</markers>";
		return $output;
	}

	function replace_symbols($value){
		return str_replace(array('&', "\r\n", "<", ">", "'", "\""), array('&amp;', "", "&lt;", "&gt;", "&apos;", "&quot;"), $value);
	}

	//-------------------------------------------------------------
	function getSortTable($param){
		switch ($param['sort']) {
			case 'img' :
				$sort_state = ' ORDER BY o.image100 '.$param['sort_d'];
				break;
			case 'room' :
				$sort_state = ' ORDER BY o.room '.$param['sort_d'];
				break;
			case 'metro' :
				$sort_state = ' ORDER BY o.metro_id '.$param['sort_d'];
				break;
			case 'address' :
				$sort_state = ' ORDER BY o.address '.$param['sort_d'];
				break;
			case 'storey' :
				$sort_state = ' ORDER BY o.storey '.$param['sort_d'].", o.storeys_number ".$param['sort_d'];
				break;
			case 'area' :
				$sort_state = ' ORDER BY o.total_area '.$param['sort_d'].", o.living_area ".$param['sort_d'].", o.kitchen_area ".$param['sort_d'];
				break;
			case 'price' :
				$sort_state = ' ORDER BY o.price_rub '.$param['sort_d'];
				break;
			case 'stars' :
				$sort_state = ' ORDER BY o.stars '.$param['sort_d'];
				break;
			default:
				$sort_state = ' ORDER BY o.price_rub ASC';
				break;
        }
		return $sort_state;
	}

	function searchAddFavorite(){
		$id = $_REQUEST['id'];
		$doit = $_REQUEST['doit'];
		if ($id) {
			if ($doit=="add") {
				if (isset($_COOKIE['obj_favorite'])){
					$obj_favorite = unserialize($_COOKIE['obj_favorite']);
					$obj_favorite = $obj_favorite ? $obj_favorite : array();
					$fld = array_unique (array_merge (array($id), $obj_favorite));
				} else {
					$fld = array($id);
				}
				setcookie('obj_favorite', serialize($fld), time()+604800, '/');
				$favorite_img = '<img src="/images/ico_added_favorite.png" onClick="q2000_rnt.search.addToFavoriteObject(\'\','.$id.'); return false;" alt="" />';
			} else {
				$arr = "";
				if (isset($_COOKIE['obj_favorite'])){
					$obj_favorite = unserialize($_COOKIE['obj_favorite']);
					$arr = array_diff ($obj_favorite, array($id));
					if (count($arr)) {
						$fld = $arr;
						$arr = serialize($arr);
						$_COOKIE['obj_favorite'] = $arr;
					} else {
						$fld = array();
						$arr = "";
						$_COOKIE['obj_favorite'] = $arr;
					}
					$_SESSION['new_objects'] = $arr;
					setcookie('obj_favorite', $arr, time()+604800, '/');
				}
				$favorite_img = '<img src="/images/ico_add_cart.png" onClick="q2000_rnt.search.addToFavoriteObject(\'add\','.$id.'); return false;" alt="" />';
			}

			$favorite_count = (count($fld)) ? "  <a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.load(true); return false;\"> <img src=\"/images/favorite.png\"> Избранное (".count($fld).")</a>" : "";
		} else {
			$favorite_count	= "";
		}

		$data = array(
			'favorite_count' => iconv('windows-1251', 'utf-8', $favorite_count),
			'favorite_img'	 => iconv('windows-1251', 'utf-8', $favorite_img),
		);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	/**
	 * Функция отправляет заявку на выбранные объекты
	 *
	 */
	function sendOrder(){
		global $settings;
		require_once ('smstraff.php');
		require_once ("phpmailer/class.phpmailer.php");

		$error = "";
		$order = $_REQUEST['fld']['order'];

		$keystring = $_SESSION['captcha_keystring'];
		unset($_SESSION['captcha_keystring']);

		if (empty($keystring) || $order['captcha'] !== $keystring) {
			$error = 'Не правельно введен проверочный код.';
		}
		if (empty($order['name'])||empty($order['email'])||empty($order['phone'])) {
			$error = 'Не заполнены обязательные поля.';
		}
		if (!empty($order['email']) && !CheckMailAddress($order['email'])) {
			$error = 'Не корректно введен E-mail адрес.';
		}

		if ($error){
			$data = array(
				'error_form'=>iconv('windows-1251', 'utf-8', $error),
			);
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($data);
			exit;
		}
		$ids = array();

		// Проверяем уже сохраненные в куках объекты
		if (isset($_COOKIE['obj_favorite'])){
			$obj_favorite = unserialize($_COOKIE['obj_favorite']);
			if (count ($obj_favorite)) {
				$ids = array_merge($ids, $obj_favorite);
			}
		}
		$ids = array_unique($ids);

		$count_sms = (isset($_COOKIE['count_sms'])) ? $_COOKIE['count_sms'] : 0;
		$query = "SELECT o.*, oa.address
		FROM rnt_objects AS o
		LEFT JOIN obj_address AS oa ON oa.id=o.address_id
		WHERE o.id IN ('".implode("','", $ids)."')";
		$list = sql_getRows($query);

		//Готовим отправку уведомлений
		//Разложим все объекты по пользователям
		foreach ($list AS $key => $value) {
			//Узнаем какие типы уведомлений выбраны пользователем
			$user_notify = sql_getRows("SELECT method FROM notify_user_settings WHERE type='view_order' AND user_id=".$value['client_id']);
			if (!empty($user_notify)){
				foreach ($user_notify AS $k=>$v){
					$value['notify_'.$v] = $v;
				}
			}

			//Если пользователя нет, то установим все возможное
			if (!$value['client_id']) {
				$value['notify_email'] = 'email';
				$value['notify_sms'] = 'sms';
			}
			$c_objects[$value['client_id']][] = $value;
			$e_objects[$value['email']][] = $value;
		}

		$sent_mobiles = array(); // массив мобильных телефонов на которые уже отправили смс
		foreach ($c_objects AS $key=>$object) {
			foreach ($object AS $k=>$value) {
				//проверяем, хочет ли пользователь получать смс
				//и не закончился ли лимит отправок
				if ($value['notify_sms'] == 'sms' && $count_sms<=20) {
					$sms_text = "";
					//проверим поле сотовый телефон,  и что на него еще не отсылали смс
					if ($value['mobile_phone'] && !in_array($value['mobile_phone'], $sent_mobiles)){
						$locmankvartir = $this->Translit(" locmankvartir.ru");
						$sms_text = substr($this->Translit(iconv('utf-8', 'windows-1251', $order['name'])), 0, 12).", ";
						$sms_text .= substr($this->Translit(iconv('utf-8', 'windows-1251', $order['phone'])), 0, 12).", ";

						$remain = 160 - (int)strlen($sms_text) - (int)strlen($locmankvartir);
						$sms_text .= substr($this->Translit($value['address']), 0, $remain).",";
						$sms_text .= $locmankvartir;

						$sent_mobiles[] = $value['mobile_phone'];

						//Сама отправка
						$mobile_phone = (substr($value['mobile_phone'], 0, 1) == "8") ? "+7".substr($value['mobile_phone'], 1, 10) : $value['mobile_phone'];
						$response = Sms::send($value['mobile_phone'], $sms_text);
						$count_sms++;
					}
				}
			}
		}
		//Запишем количество отправленных смс
		setcookie('count_sms', $count_sms, time()+604800, '/');

		//Отправляем уведомления на email
		$page = &Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'searchobject');
		$count_objects = $page->tpl->get_config_vars('searchobject_count_objects_in_email');

		// Подготовка письма
		$mail = &new PHPMailer();
		$mail->From = $mail->Sender = $page->tpl->get_config_vars('admin_email');
		$mail->Mailer = 'mail';

		$tableHead = "<tr>
		<td>Адрес</td>
		<td>Квартира</td>
		<td>Цена</td>
		<td>Кол-во комнат</td>
		<td>Этаж/этажность</td>
		<td>Тип здания</td>
		<td>Площадь</td>
		<td>Балкон</td>
		<td>Контактные данные</td>
		</tr>";

		foreach ($e_objects AS $key=>$object) {
			if ($key) {
				$i = $j = 0;
				$tableBody = "";
				foreach ($object AS $k=>$value) {
					if ($value['notify_email'] == 'email' && $value['email']) {
						if ($i==0) {
							$j++;
							$mail->Subject = 'Заявка на просмотр объектов от '.iconv('utf-8', 'windows-1251', $order['name']).' часть '.$j.' из '.(int)ceil(count ($object)/$count_objects);
							$body = '';
							$body .= 'ФИО: '.iconv('utf-8', 'windows-1251', $order['name']).'<br>';
							$body .= 'E-mail: '.nl2br($order['email']).'<br>';
							$body .= 'Телефон: '.nl2br(iconv('utf-8', 'windows-1251', $order['phone'])).'<br>';
							$body .= 'Дополнительно: '.nl2br(iconv('utf-8', 'windows-1251', $order['other'])).'<br>';
							$body .= '<hr>';
						}

						// Здесь полная информация об объектах
						$tableBody .= "<tr width='5000'>
						<td>".$value['address']."</td>
						<td>".$settings['market'][$value['market']]."</td>
						<td>".$value['price_rub']."</td>
						<td>".$value['room']."</td>
						<td>".$value['storey']."/".$value['storeys_number']."</td>
						<td>".sql_getValue('SELECT name FROM obj_housetypes WHERE id='.$value['house_type'])."</td>
						<td>".$value['total_area']."/" .$value['living_area']."/".$value['kitchen_area']."</td>
						<td>".sql_getValue('SELECT name FROM obj_balcony WHERE id='.$value['balcony'])."</td>
						<td>".$value['contact_phone']."</td>
						</tr>";

						$i++;
						if ($i==$count_objects) {
							$body .= "<table cellpadding='5' cellspacing='5' border='2'>".$tableHead.$tableBody."</table>";
							$mail->Body = $body;
							$mail->ClearAddresses();
							$mail->AddAddress($key);
							$mail->IsHTML(true);
							$mail->Send();
							$i = 0;
						}
					}
				}

				if ($i != $count_objects) {
					$body .= "<table cellpadding='5' cellspacing='5' border='2'>".$tableHead.$tableBody."</table>";
					$mail->Body = $body;
					$mail->ClearAddresses();
					$mail->AddAddress($key);
					$mail->IsHTML(true);
					$mail->Send();
				}
			}
		}

		$data = array(
			'msg_form'=>iconv('windows-1251', 'utf-8', "Ваша заявка была отправлена на доступные электронные ящики и мобильные телефоны."),
		);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	function Translit($name) {
		$transl = array(
			'а' => 'a',  'б' => 'b',   'в' => 'v',  'г' => 'g',  'д' => 'd',
			'е' => 'e',  'ё' => 'e',   'ж' => 'zh', 'з' => 'z',  'и' => 'i',
			'й' => 'y',  'к' => 'k',   'л' => 'l',  'м' => 'm',  'н' => 'n',
			'о' => 'o',  'п' => 'p',   'р' => 'r',  'с' => 's',  'т' => 't',
			'у' => 'u',  'ф' => 'f',   'х' => 'h',  'ц' => 'ts', 'ч' => 'ch',
			'ш' => 'sh', 'щ' => 'sch', 'ъ' => '\'', 'ы' => 'y',  'ь' => '\'',
			'э' => 'e',  'ю' => 'u',   'я' => 'ya',
			'А' => 'A',  'Б' => 'B',   'В' => 'V',  'Г' => 'G',  'Д' => 'D',
			'Е' => 'E',  'Ё' => 'E',   'Ж' => 'Zh', 'З' => 'Z',  'И' => 'I',
			'Е' => 'I',  'К' => 'K',   'Л' => 'L',  'М' => 'M',  'Н' => 'N',
			'О' => 'O',  'П' => 'P',   'Р' => 'R',  'С' => 'S',  'Т' => 'T',
			'У' => 'U',  'Ф' => 'F',   'Х' => 'H',  'Ц' => 'Ts', 'Ч' => 'Ch',
			'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '\'', 'ы' => 'y',  'ь' => '\'',
			'Э' => 'E',  'Ю' => 'U',   'Я' => 'Ya',
		);
		return strtr($name, $transl);
	}
}