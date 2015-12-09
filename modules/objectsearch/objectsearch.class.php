<?php

require_once 'modules/object/object.class.php';

class TObjectSearch extends TObject {

	var $prefix = '----';
	var $prices = array('�� �����', '100 000 �.�.', '150 000 �.�.', '200 000 �.�.', '250 000 �.�.', '300 000 �.�.', '400 000 �.�.', '500 000 �.�.', '750 000 �.�.', '1 000 000 �.�.', '1 500 000 �.�.');
	
	function getParams(){
		$page = & Registry::get('TPage');
		$page->tpl->config_load($page->content['domain'].'__'.lang().'.conf', 'objectsearch');
		
		$params = parent::getParams();
		
		if (!$params['limit']) $params['limit'] = (int)$page->tpl->get_config_vars('objectsearch_limit');
		if (!$params['limit']) $params['limit'] = 20;
		
		$fld = get('fld', array(), 'g');		
		$params['market']			=	isset($fld['market']) ? $fld['market'] : ''; //  ����� - ��������� ��� ���������
		$params['price_dollar']		=	isset($fld['price_dollar']) ? $fld['price_dollar'] : '';//  ������� ��������
		$params['room']				=	isset($fld['room']) ? (int)$fld['room'] : -1;//  ���-�� ������
		$params['district_id']		=	isset($fld['district_id']) ? (int)$fld['district_id'] : 0;// �����
		$params['lot_id']			=	isset($fld['lot_id']) ? $fld['lot_id'] : '';//  ���
		$params['price1']			=	isset($fld['price1']) ? $fld['price1'] : '';//  ���� ��
		$params['price2']			=	isset($fld['price2']) ? $fld['price2'] : '';//  ���� ��
		$params['room1']			=	isset($fld['room1']) ? $fld['room1'] : -1;//  ������ ��
		$params['room2']			=	isset($fld['room2']) ? $fld['room2'] : -1;//  ������ ��
		$params['raion']			=	isset($fld['raion']) ? $fld['raion'] : array();// ������
		$params['metro']			=	isset($fld['metro']) ? $fld['metro'] : array();// �����
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
	    $this->type = 'room';
		if (isset($_GET['adv']) && !isset($_POST['fld'])) {
			$params['_block']['tmpls'][0] = 'adv_search';
			return $this->advsearch($res);
		}
		
		$param = $this->getParams();

		if (isset($param['moscow']) && $param['moscow'] == '1') {
		    $this->fields = array('district_id', 'metro_id', 'address', 'room_description', 'price_rub'/*, 'action'*/);
		} elseif (isset($param['moscow']) && $param['moscow'] == '0') {
		    $this->fields = array('city', 'address', 'room_description', 'price_rub'/*, 'action'*/);
		}
		else {
		    $this->fields = array('address', 'room_description', 'price_rub'/*, 'action'*/);
		}

        $ret = $this->search($param);
		
        // ��������� ������ ����

        $page = & Registry::get('TPage');
        $url = $page->content['href'];
        
        $fld = get('fld', array(), 'g');
        if ($fld) {
            foreach ($fld as $key=>$val) {
                if (!is_array($val)) {
                    $url = add_query_arg($url, 'fld['.$key.']', $val);
                } else {
                    foreach ($val as $k=>$v) {
                        $url = add_query_arg($url, 'fld['.$key.']['.$k.']', $v);
                    }
                }
            }
        }
        
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

        $ret['type'] = 'room';
        $ret['fields'] = $this->fields;

		return $ret;
	}
	
	/**
	 * ����� �������� ������
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
	/*	������� ���������� ������ �� ������� �������� � �� �����������
	/*	$where - ������� ��� �������
	/*	$orderby - ���� ��� ����������	
	/*  
	/**/
	/*function getList($where, $orderby, $offset=0, $limit=10) {
		$res['objects'] = sql_getRows('SELECT SQL_CALC_FOUND_ROWS o.*, old.name AS district, olm.name AS metro
		FROM '.$this->table.' AS o 
		LEFT JOIN obj_locat_districts AS old ON old.id = o.district_id
		LEFT JOIN obj_locat_metrostations AS olm ON olm.id = o.metro_id
		WHERE '.$where.' AND o.visible > 0 '.(!empty($orderby) ? $orderby : ' ORDER BY o.priority').' LIMIT '.$offset.','.$limit);
		$res['count'] = sql_getValue("SELECT FOUND_ROWS()");
		return $res;
	}*/

	/**
	 * ������� ��������� ��������� WHERE � ����������� �� ���������� �� ����������
	 *
	 * @param array $res - ��� ��������� �� �������� ������
	 */
	function generateQuery($res, $obj_types) {
		global $settings;
		$sql = "";
		
		$sql .= " AND obj_type_id='room'";

		//------------------------------------------------------------------------
		// ������ � ������� ������� ���������
		if (isset($res['price_dollar']) && $res['price_dollar'] > 0) {
			$price = $settings['prices'][$res['price_dollar']];
			list($pricedown, $priceup) = explode(' - ', $price);
			$pricedown = str_replace(array('$', ' '), '', $pricedown);
			$pricedown = doubleval($pricedown);
			$priceup = str_replace(array('$', ' '), '', $priceup);
			$priceup = doubleval($priceup);
			$sql .= " AND (o.price_dollar>=".$pricedown." AND o.price_dollar<=".$priceup.")";
		}
		if (isset($res['price1']) && $res['price1'] > 0) {
			$price = str_replace(array('$', ' ', '�.�.'), '', $this->prices[$res['price1']]);
			$sql .= " AND (o.price_dollar>=".$price.")";
		}
		if (isset($res['price2']) && $res['price2'] > 0) {
			$price = str_replace(array('$', ' ', '�.�.'), '', $this->prices[$res['price2']]);
			$sql .= " AND (o.price_dollar<=".$price.")";
		}

		//------------------------------------------------------------------------
		// ���-�� ������
		if (isset($res['room']) && $res['room'] > -1){
			if ($res['room'] == '6') $sql .= " AND o.room >=5";
			else $sql .= " AND o.room = ".$res['room'];
		}
		if (isset($res['room1']) && $res['room1'] > -1) {
			if ($res['room1'] == '6') $sql .= " AND (o.room>=5)";
			else $sql .= " AND (o.room>=".$res['room1'].")";
		}
		if (isset($res['room2']) && $res['room2'] > -1) {
			if ($res['room2'] == '6' && (!isset($res['room1']) || $res['room1'] == -1)) $sql .= " AND (o.room>=5)";
			elseif ($res['room2'] != '6') $sql .= " AND (o.room<=".$res['room2'].")";
		}

		//------------------------------------------------------------------------
		// �����
		if (isset($res['district_id']) && !empty($res['district_id'])){
			$childs = $this->getChilds('obj_locat_districts', 'ORDER BY name', $res['district_id']);
			$districts = array();
			$this->getChildsList($childs, $districts);
			$districts[$res['district_id']] = 1;
			$sql .= " AND o.district_id IN (".implode(',', array_keys($districts)).")";
		}
		if (isset($res['raion']) && is_array($res['raion']) && !empty($res['raion'])) {
			$parents = sql_getColumn('SELECT DISTINCT pid FROM obj_locat_districts WHERE id IN ('.implode(',', $res['raion']).')');
			if ($parents) $res['raion'] = array_merge($res['raion'], $parents);
			$sql .= " AND o.district_id IN (".implode(',', $res['raion']).")";
		}

		//------------------------------------------------------------------------
		// �����
		if (isset($res['market']) && !empty($res['market']) && $res['market'] != '-1'){
			$sql .= " AND o.market = '".$res['market']."' ";
		}

		//------------------------------------------------------------------------
		// ���
		if (isset($res['lot_id']) && !empty($res['lot_id']) && $res['lot_id'] != '�� �����'){
			$sql .= " AND o.lot_id = '".$res['lot_id']."' ";
		}

		//------------------------------------------------------------------------
		// �����
		if (isset($res['metro']) && !empty($res['metro'])){
			$sql .= " AND o.metro_id IN(".implode(", ", $res['metro']).") ";
		}

		//------------------------------------------------------------------------
		// �����������
		if (isset($res['direction']) && $res['direction'] != 0){
			$sql .= " AND o.direction = '".$res['direction']."' ";
		}

		//------------------------------------------------------------------------
		// ��� �������
		if (isset($res['type']) && $res['type'] != 0)	{
			if (!empty($obj_types[$res['type']])) {
				$sql .= " AND o.obj_type_id IN (".implode(", ", $obj_types[$res['type']]).") ";				
			}
		}		

		//------------------------------------------------------------------------
		//�������
		if (isset($res['total_area1']) && $res['total_area1'] > 0) {
			$sql .= " AND (o.total_area>=".$res['total_area1'].")";
		}
		if (isset($res['total_area2']) && $res['total_area2'] > 0) {
			$sql .= " AND (o.total_area<=".$res['total_area2'].")";
		}
		if (isset($res['living_area1']) && $res['living_area1'] > 0) {
			$sql .= " AND (o.living_area>=".$res['living_area1'].")";
		}
		if (isset($res['living_area2']) && $res['living_area2'] > 0) {
			$sql .= " AND (o.living_area<=".$res['living_area2'].")";
		}
		if (isset($res['kitchen_area1']) && $res['kitchen_area1'] > 0) {
			$sql .= " AND (o.kitchen_area>=".$res['kitchen_area1'].")";
		}
		if (isset($res['kitchen_area2']) && $res['kitchen_area2'] > 0) {
			$sql .= " AND (o.kitchen_area<=".$res['kitchen_area2'].")";
		}
		
		//------------------------------------------------------------------------
		//����
		if (isset($res['storey1']) && $res['storey1'] > 0) {
			$sql .= " AND (o.storey>=".$res['storey1'].")";
		}
		if (isset($res['storey2']) && $res['storey2'] > 0) {
			$sql .= " AND (o.storey<=".$res['storey2'].")";
		}
		if (isset($res['storeys_number1']) && $res['storeys_number1'] > 0) {
			$sql .= " AND (o.storeys_number>=".$res['storeys_number1'].")";
		}
		if (isset($res['storeys_number2']) && $res['storeys_number2'] > 0) {
			$sql .= " AND (o.storeys_number<=".$res['storeys_number2'].")";
		}
		
		//------------------------------------------------------------------------
		//�����
		if (!empty($res['address'])) {
			$sql .= " AND o.address LIKE '%".e(strip_tags($res['address']))."%'";
		}

		//------------------------------------------------------------------------
		//������ ��� �������
		if (isset($res['moscow']) && in_array($res['moscow'], array('0', '1'))) {
			$sql .= " AND o.moscow=".(int)$res['moscow'];
		}

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
     * ����� ������������ ������
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
				$rows = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid IS NULL ORDER BY priority', true); // ������ ������
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
				$this->search_by_metro($param); 
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
	 * ������� ������ �� ����������
	 *
	 */
	function search(&$params){		
		$page_obj = & Registry::get('TPage');
		$res = $params;
		
		$sql = $this->generateQuery($res);

		//------------------------------------------------------------------------
		// ����������
		$order_by = $this->getSort($res);

		$list = $this->getList($res, " AND o.winner='0'", $order_by);
		$res['objects'] = $list['list'];
		
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
	 * ������� ������ �� ����� �����
	 * ���� � GET ������ ������ � ��������� �������� �����, �� �������� �� � ������� search, ����� - ��������� ����� �����
	 *
	 * @param array $params - ��� ��������� �� �������� ������
	 */
	function search_by_metro($params) {
		$metrostations = sql_getRows('SELECT * FROM obj_locat_metrostations WHERE id<>1 AND x<>0 AND y<>0');

		// ����������� ����� ����� �� �����
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
		// ������ area ��� map
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
		return true;
	}

} // class TAdvSearch