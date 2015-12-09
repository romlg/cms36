<?

require_once elem(OBJECT_EDITOR_MODULE.'/elems');
define ('USE_ED_VERSION', '1.0.2');

class TCommonObjectElement extends TElems {

	var $elem_table = "objects";
	var $elem_str = array(
		'address'		=> array('Адрес',							'Address',),
		'archive'		=> array('Перемещен в архив',				'Replaced into archive',),
		'balcony'		=> array('Балкон',							'Balcony'),
		'city'			=> array('Город',							'City',),
		'country'		=> array('Страна',							'Country',),
		'create_time'	=> array('Дата  размещения объекта в базе',	'Create date',),
		'direction'		=> array('Направление',						'Direction',),
		'district'		=> array('Район',							'District',),
		'district_id'	=> array('Район города',					'City District',),
		'garbage'		=> array('Мусоропровод',					'Garbage'),
		'hot'			=> array('Спецпредложение',					'Special Offer',),
		'house'			=> array('Дом',								'House Type'),
		'visible'		=> array('Показывать',						'Visible',),
		'image_large'	=> array('Большое фото',					'Image large',),
		'image_small'	=> array('Маленькое фото',					'Image small',),
		'kitchen_area'	=> array('Площадь кухни',					'Kitchen Area'),
		'lavatory'		=> array('Санузел',							'Toilet'),
		'legal_status'	=> array('Приватизированная',				'Legal Status'),
		'lift'			=> array('Лифт',							'Lift'),
		'living_area'	=> array('Жилая площадь',					'Living Area'),
		'loggia'		=> array('Лоджия',							'Loggia'),
		'lot_id'		=> array('Лот (если оставите пустым - сгенерируется автоматически)','lot',),
		'metro_dest'	=> array('Расстояние до метро',				'Metro Distance',),
		'metro_id'		=> array('Станция метро',					'Metro station'),
		'manager_id'	=> array('Менеджер',						'Manager',),
		'obj_class_id'	=> array('Класс',							'Class',),
		'obj_type_id'	=> array('Тип',								'Type',),
		'phone'			=> array('Телефон',							'Phone'),
		'price'			=> array('Цена',							'Price',),
		'price_all'		=> array('Цена всего (дол.)',				'Total price ($)',),
		'price_dollar'	=> array('Цена всего (дол.)',				'Total price ($)',),
		'price_metr'	=> array('Цена за кв.м. (дол.)',			'Price per metr ($)',),
		'price_rub'		=> array('Цена всего (руб.)',				'Total price (RUB)',),
		'region'		=> array('Регион',							'Region',),
		'ring'			=> array('Расстояние',						'Distance',),
		'room'			=> array('Количество комнат',				'Room count',),
		'sell_type_id'	=> array('Назначение',						'Sell_type',),
		'square'		=> array('Площадь',							'Square',),
		'status'		=> array('Статус',							'Status',),
		'storey'		=> array('Этаж',							'Storey'),
		'storeys_number'=> array('Всего этажей',					'Storeys Number'),
		'tolet'			=> array('Свободные площади',				'Free square',),
		'total_area'	=> array('Общая площадь',					'Total Area'),
		'ultra'			=> array('Ультрапредложение',				'Ultra Offer',),
	);
	var $prefix = '----';
	
	//---------------------------------------------------------------------------------

	function get_sell_type(){
		return sql_getRows('SELECT id, name FROM obj_transaction', true);
	}

	//---------------------------------------------------------------------------------

	function getRing(){
		global $settings, $str;
		foreach ($settings['ring'] as $k => $v) {
			$rings[$k] = $this->str($v);
		}
		return $rings;
	}

	//---------------------------------------------------------------------------------

	function get_Date($v) {
		if (isset($v['value']) && $v['value'] != '00000000000000') return substr($v['value'], 0, 4).'-'.substr($v['value'], 4, 2).'-'.substr($v['value'], 6, 2).' '.substr($v['value'], 8, 2).':'.substr($v['value'], 10, 2);
		else return date("Y-m-d H:i");
	}    

	//---------------------------------------------------------------------------------

	function getHouse(){global $settings; return $settings['house_types'];}
	function getExist(){global $settings; return $settings['exist_types_full'];}
	function getLavatory(){global $settings; return $settings['lavatory_types'];}

	//---------------------------------------------------------------------------------

	function getStatus(){
		global $settings;
		return $settings['status'];
	}

	//---------------------------------------------------------------------------------

	function get_managers(){
		$sql = 'SELECT id, name	FROM obj_managers ORDER BY name';
		return sql_GetRows($sql, true);
	}

	//---------------------------------------------------------------------------------

	function get_metro(){
		$sql = 'SELECT id, name FROM obj_locat_metrostations ORDER BY name';
		return sql_GetRows($sql, true);
	}

	//---------------------------------------------------------------------------------

	function get_direction(){
		global $settings, $intlang;
		$rows = array();
		foreach ($settings['direction'] as $k=>$v)
			$rows[$k] = $v[$intlang];
		return $rows;
	}

	//---------------------------------------------------------------------------------

	function get_district(){
		$childs = $this->getChilds('obj_locat_districts', 'ORDER BY name');
		$this->getList($childs, $res);
		return $res;
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

    function getList($pids, &$res, $full = 0) {
    	foreach ($pids as $key=>$val) {
			if ($full) $res[$val['id']] = $val; else $res[$val['id']] = $val['name'];
			if (isset($val['items'])) {
				$this->getList($pids[$key]['items'], $res, $full);
			}
    	}
    }
    
	//---------------------------------------------------------------------------------

	function getCountry(){
		// Предполагаем, что страны - это верхний уровень районов
		$rows = sql_getRows('SELECT id, name FROM obj_locat_districts WHERE pid IS NULL ORDER BY name', true);
		return $rows;
	}

	//---------------------------------------------------------------------------------

	var $script="
		{literal}
		var cur_id = '';
		var time = 0;
		window.onload = function() {
			cur_id = document.forms['editform'].elements['id'].value;
			if (document.getElementById('fld[sell_type_id]')) {
				window.setTimeout(\"ChangeBelong(document.getElementById('fld[sell_type_id]').value)\", this.time);
				this.time = this.time + 500;				
			} 
			if (document.getElementById('fld[belong]')) {
				window.setTimeout(\"ChangeBelong(document.getElementById('fld[belong]').value)\", this.time);
				this.time = this.time + 500;				
			} 
			if (document.getElementById('fld[country]')) {
				window.setTimeout(\"ChangeCountry(document.getElementById('fld[country]').value)\", this.time);
				this.time = this.time + 500;				
			} else if (document.getElementById('fld[region]')) {
				window.setTimeout(\"ChangeRegion(document.getElementById('fld[region]').value)\", this.time);
				this.time = this.time + 500;				
			} else if (document.getElementById('fld[city]')) {
				window.setTimeout(\"ChangeCity(document.getElementById('fld[city]').value)\", this.time);
				this.time = this.time + 500;				
			}  else if (document.getElementById('fld[district_id]')) {
				window.setTimeout(\"ChangeCity()\", this.time);
				this.time = this.time + 500;				
			}
		}

		function cleanNode(dest) {
  			while (dest.firstChild)
    			dest.removeChild(dest.firstChild);
		}

		/* Функция для выбора регионов, в зависимости от выбранной страны */
		function ChangeCountry(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetRegion&country='+num+'&id='+cur_id;
			loadXMLDoc(url, 'country');
		}

		/* Функция для выбора городов, в зависимости от выбранного региона */
		function ChangeRegion(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetCity&region='+num+'&id='+cur_id;
			loadXMLDoc(url, 'region');
		}

		/* Функция для выбора районов, в зависимости от выбранного города */
		function ChangeCity(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetDistrict&city='+num+'&id='+cur_id;
			loadXMLDoc(url, 'city');
		}

		function ChangeDistrict(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetMetro&district='+num+'&id='+cur_id;
			loadXMLDoc(url, 'district');
		}

		function ChangeType(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetClass&table='+thisname+'&belong='+num+'&id='+cur_id;
			loadXMLDoc(url, 'type');
		}

		function ChangeBelong(num){
			url = '/admin/page.php?page='+thisname+'&do=editgetTypes&table='+thisname+'&belong='+num+'&id='+cur_id;
			loadXMLDoc(url, 'belong');
		}

		function loadXMLDoc(url, type) {
			if (window.XMLHttpRequest) {
				req = new XMLHttpRequest();
				if (type == 'belong'){ req.onreadystatechange = processReqChangeBelong;}
				if (type == 'type'){ req.onreadystatechange = processReqChangeType;}
				if (type == 'district'){ req.onreadystatechange = processReqChangeDistrict;}
				if (type == 'country'){ req.onreadystatechange = processReqChangeCountry;}
				if (type == 'region'){ req.onreadystatechange = processReqChangeRegion;}
				if (type == 'city'){ req.onreadystatechange = processReqChangeCity;}
				req.open(\"GET\", url, true);
				req.send(null);
			} else if (window.ActiveXObject) {
				req = new ActiveXObject(\"Microsoft.XMLHTTP\");
				if (req) {
					if (type == 'belong'){ req.onreadystatechange = processReqChangeBelong;}
					if (type == 'type'){ req.onreadystatechange = processReqChangeType;}
					if (type == 'district'){ req.onreadystatechange = processReqChangeDistrict;}
					if (type == 'country'){ req.onreadystatechange = processReqChangeCountry;}
					if (type == 'region'){ req.onreadystatechange = processReqChangeRegion;}
					if (type == 'city'){ req.onreadystatechange = processReqChangeCity;}
					req.open(\"GET\", url, true);
					req.send();
				}
			}
		}
		
		function processReqChange(id, func1, func2){
			if (req.readyState == 4) {
				if (req.status == 200) {
					response = req.responseXML.documentElement;
					if (response){
						items = response.getElementsByTagName('item');
						obj = document.getElementById(id);
						if (obj) {
                            str = '';
						    if (items.length == 1) {
                                id = items[0].childNodes[0].childNodes[0].data;
                                if (id != 1) {
    								str = '<select id='+id+' name='+id+' onchange=\"'+func1+'\">';
   									name = items[0].childNodes[1].childNodes[0].data;
   									selected = items[0].childNodes[2].childNodes[0].data;
   									if (selected == '1'){ sel = 'selected';} else { sel = '';}
   									str = str + '<OPTION VALUE=\"'+id+'\" '+sel+'>'+name+'</OPTION> ';
    								str = str + '</select>';
                                }
						    }
							else if (items.length > 0) {
								str = '<select id='+id+' name='+id+' onchange=\"'+func1+'\">';
								for (i=0; i<items.length; i++){
									id = items[i].childNodes[0].childNodes[0].data;
									name = items[i].childNodes[1].childNodes[0].data;
									selected = items[i].childNodes[2].childNodes[0].data;
									if (selected == '1'){ sel = 'selected';} else { sel = '';}
									str = str + '<OPTION VALUE=\"'+id+'\" '+sel+'>'+name+'</OPTION> ';
								}
								str = str + '</select>';
							} else {
								str = '<input type=text id='+id+' name='+id+' size=40>';
							}
		
							if (func2) {
								window.setTimeout(func2, this.time);
								this.time = this.time + 500;
							}
							if (str) {
							 obj = obj.parentNode;
							 obj.innerHTML = str;
							}
						}
					}
				} else {
					alert(\"There was a problem retrieving the XML data:\" + req.statusText);
				}
			}
		}
		

		function processReqChangeCountry(){
			if (document.getElementById('fld[region]')) processReqChange('fld[region]', 'ChangeRegion(this.value)', \"ChangeRegion(document.getElementById('fld[region]').value)\");
		}

		function processReqChangeRegion(){
			if (document.getElementById('fld[city]')) processReqChange('fld[city]', 'ChangeCity(this.value)', \"ChangeCity(document.getElementById('fld[city]').value)\");
		}

		function processReqChangeCity(){
			if (document.getElementById('fld[district_id]')) processReqChange('fld[district_id]', 'ChangeDistrict(this.value)', \"ChangeDistrict(document.getElementById('fld[district_id]').value)\");
		}

		function processReqChangeDistrict(){
			if (document.getElementById('fld[metro_id]')) processReqChange('fld[metro_id]');
		}

		function processReqChangeBelong(){
			if (document.getElementById('fld[obj_type_id]'))
				processReqChange('fld[obj_type_id]', 'ChangeType(this.value)', \"ChangeType(document.getElementById('fld[sell_type_id]').value)\");
			else if (document.getElementById('fld[pid]'))
				processReqChange('fld[pid]');
		}

		function processReqChangeType(){
			if (document.getElementById('fld[obj_class_id]')) processReqChange('fld[obj_class_id]');
		}
		{/literal}
	";
    
	function ElemRedactB($fld){
		foreach ($fld as $key=>$val) {
			switch ($key) {
				case 'lot_id' : 
					if (empty($val)) {
						$max_lot = (int)sql_getValue('SELECT MAX(CAST(lot_id AS UNSIGNED)) FROM '.$this->elem_table);
						if ($max_lot) $fld[$key] = $max_lot + 1;
					}
					break;
				case 'create_time':
					$fld[$key] = date('YmdHis', strtotime($val));
					break;
				case 'obj_type_id':
				case 'obj_class_id':
				case 'district_id':
				case 'metro_id':
					if (empty($val)) $fld[$key] = 'NULL';
					break;
			}
		}
		return $fld;
	}
}

?>