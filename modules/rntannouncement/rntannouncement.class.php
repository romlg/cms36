<?php
class TRntAnnouncement {

	var $data; //данные, отправляемые в форму
	var $table = 'rnt_objects'; //данные из временной таблицы

	var $req_fields = array( //требуемые поля
		'district_id'		=> 'Нас.пункт',
		'metro_id'			=> 'Метро',
		'metro_time'		=> 'Время от метро',
		'metro_time_type'	=> 'Время от метро',
		'rooms'				=> 'Комнат',
		'addres'			=> 'Улица, проспект (адрес)',
		'storey'			=> 'Этаж',
		'storey_number'		=> 'Этажность',
		'house_type_id'		=> 'Тип дома',
		'total_area'		=> 'Общая площадь',
		'price_rub'			=> 'Стоимость',
		'fio'				=> 'Ваше имя',
		'phone'				=> 'Телефон',
		'state'				=> 'Состояние квартиры',
		'rnt_time'			=> 'Срок аренды',
	);

	//----------------------------------------------------------------------------
	function show() {
		$auth_obj = & Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		$page = & Registry::get('TPage');

        switch ($page->content['page']) {
        	case 'add_rnt':
				return $this->step1();
        		break;
        	case 'step2':
        		return $this->step2();
        		break;
        	case 'step3':
        		return $this->step3();
        		break;
        	case '7days':
			case '14days':
			case '30days':
			case '90days':
        		return $this->step4();
        		break;
			case 'prolongation':
				return $this->pstep1();
				break;
			case 'pstep2':
				return $this->pstep2();
				break;
			case 'pstep3':
				return $this->pstep3();
				break;
			default:
        		return $this->step1();
        }
	}

	//----------------------------------------------------------------------------
	function step1() {
		global $settings;
		$page = & Registry::get('TPage');
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		$ret = array();

		$ret['metrostations'] = array('' => '- не выбрано -') + sql_getRows('SELECT id, name FROM `obj_locat_metrostations` WHERE 1 ORDER BY name', true);
		$ret['districs_mo'] = array('' => '- не выбрано -') + $this->getDistricsAndCities();
		$ret['house_type'] = array('' => '- не выбрано -') + sql_getRows('SELECT id, name FROM `obj_housetypes` WHERE 1 ORDER BY id', true);
		$ret['states'] = array('' => '- не выбрано -') + $settings['annoucement_states'];
		$ret['rnt_times'] = array('' => '- не выбрано -') + $settings['annoucement_rnt_times'];
		$ret['prepays'] = array('' => '- не выбрано -') + $settings['annoucement_prepays'];

		if ($profile) {
			$ret['fio'] = $profile['fio'];
			$ret['email'] = $profile['login'];
			$ret['phone'] = $profile['phones'];
		}

		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];
			if ($post['city']==1) {
				unset($this->req_fields['district_id']);
			} else {
				unset($this->req_fields['metro_id']);
				unset($this->req_fields['metro_time']);
				unset($this->req_fields['metro_time_type']);
			}

			if ($post['phone2']) {
				$mobile = $this->getMobileNumber($post['phone2']);
				if (!$mobile) {
					$errors[] = 'Не правельный формат мобильного телефона';
				} else {
					$post['phone2'] = $mobile;
				}
			}

			foreach($this->req_fields as $key=>$val){
				if (empty($post[$key])) {
					$errors[] = $this->req_fields[$key];
				}
			}

			if (isset($_FILES)) {
				$photo = $plan = array();
				foreach ($_FILES['fld'] AS $key=>$value){
					if (isset($value['photo'])){
						$photo[$key] = $value['photo'];
					}
					if (isset($value['plan'])) {
						$plan[$key] = $value['plan'];
					}
				}

				include_once ('functions.php');
				if ($photo){
					$sizes = array(
						'im_small'=>array('50', '36'),
						'image_small'=>array('250', '250'),
						'image_large'=>array('640', '480')
					);
					$images_photo = downloadFiles($_FILES, count($photo['name']), 'photo', "files/tmp", $sizes, 85, true);
					if (!empty($images_photo)) {
						$GLOBALS['_SESSION']['new_announce']['files_photo'] = $images_photo;
					}
				}

				if ($plan){
					$sizes = array(
						'image_small'=>array('50', '36'),
						'image_large'=>array('640', '480')
					);
					$images_plan = downloadFiles($_FILES, count($plan['name']), 'plan', "files/tmp", $sizes, 85, true);
					if (!empty($images_plan)) {
						$GLOBALS['_SESSION']['new_announce']['files_plan'] = $images_plan;
					}
				}
			}

			$ret['select_metro_time_type'] = $post['metro_time_type'];
			$ret['select_metro_time'] = $post['metro_time'];
			foreach ($post AS $key => $val) {
				$ret[$key] = $post[$key];
			}

			// Ошибки
			if ($errors){
				array_unshift ($errors, '<b>Не заполнены следующие поля</b>');
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				// Если ошибок нет, то сохраняем все в сессию и переходим к следующему шагу
				// Пробегаемся по каждому полю конкретно, чтобы не затереть данные с предыдущих шагов
				foreach ($ret AS $key => $val) {$GLOBALS['_SESSION']['new_announce']['fields'][$key] = $ret[$key];}
				redirect('/add_rnt/step2');
			}
		} else {
			if (isset($GLOBALS['_SESSION']['new_announce']['fields'])) {
				$session = $GLOBALS['_SESSION']['new_announce']['fields'];
				foreach ($session AS $key => $val) {$ret[$key] = $session[$key];}
			} else {
				$ret['city'] = 1;
			}
		}

		$ret['step'] = '1';
		$ret['url'] = $page->content['href'];
		return $ret;
	}

	//----------------------------------------------------------------------------
	function step2() {
		global $settings;
		//Проверим заполнен ли предыдущий шаг, если нет отправляем заполнять
		if (!isset($GLOBALS['_SESSION']['new_announce']['fields']) || !count($GLOBALS['_SESSION']['new_announce']['fields'])) redirect('/add_rnt');

		$page = & Registry::get('TPage');
		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];

			// Ошибки
			if (empty($post['days'])){
				$page->tpl->assign(array('errors' => array('Укажите срок подачи объявления')));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				// Если ошибок нет, то сохраняем все в сессию и переходим к следующему шагу
				$GLOBALS['_SESSION']['new_announce']['fields']['days'] = $post['days'];
				redirect('/add_rnt/step3');
			}
		} else {
			foreach($settings['annoucement_prices'] as $key=>$val) $ret['variants'][]=array('days'=>$key,'price'=>$val);
			if (isset($GLOBALS['_SESSION']['new_announce']['fields']['days'])) {
				$ret['days'] = $GLOBALS['_SESSION']['new_announce']['fields']['days'];
			}
		}

		$ret['step'] = '2';
		$ret['url'] = $page->content['href'];

		return $ret;
	}

	//----------------------------------------------------------------------------
	function step3() {
		global $settings;
		//Проверим заполнен ли предыдущий шаг, если нет отправляем заполнять
		if (!isset($GLOBALS['_SESSION']['new_announce']['fields']) || !count($GLOBALS['_SESSION']['new_announce']['fields'])) redirect('/add_rnt');

		$page = & Registry::get('TPage');
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];
			$this->req_fields = array(
				'paytype' => 'Выберите способ оплаты',
			);

			foreach($this->req_fields as $key=>$val){
				if (empty($post[$key])) {
					$errors[] = $this->req_fields[$key];
				}
			}

			foreach ($post AS $key => $val) {
				$ret[$key] = $post[$key];
			}

			// Ошибки
			if ($errors){
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				// Если нет ошибок
				if ($post['paytype']=="free"){
					$this->saveObject();
					redirect('/add_rnt/ok');
				} elseif ($post['paytype']=="sms") {
					// переходим на нужную страницу
					switch ($GLOBALS['_SESSION']['new_announce']['fields']['days']) {
					    case 7:
					        redirect('/add_rnt/7days');
					    case 14:
							redirect('/add_rnt/14days');
					    case 30:
							redirect('/add_rnt/30days');
					    case 90:
							redirect('/add_rnt/90days');
						default:
							redirect('/add_rnt/step2');
					}
				} elseif ($post['paytype']=="balance") {
					$err=array();
					$price=floatval($settings['annoucement_prices'][$GLOBALS['_SESSION']['new_announce']['fields']['days']]);
					if(!$price) $err=array('Нет такого варианта оплаты!');
					else if(floatval($profile['balance'])<$price) $err=array('Не хватает средств на балансе!');
					if(!empty($err)) {
						$page->tpl->assign(array('errors' => $err));
						$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
						$page->tpl->clear_assign('errors_box');
					}
					else {
						$balance=floatval($profile['balance'])-$price;
						sql_query('UPDATE auth_users SET balance="'.$balance.'" WHERE id='.$profile['id']);
						sql_query('INSERT INTO billing (client_id,sum,date,balance) VALUES("'.$profile['id'].'","'.-$price.'",CURRENT_TIMESTAMP,"'.$balance.'")');
						$this->saveObject();
						redirect('/add_rnt/ok');
					}
				} else {
					// не понятно как сюда попапли.. ошибка или хак
					$page->tpl->assign(array('errors' => array('Не определенное дейсвие. <br /> Мы не знаем что делать.')));
					$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
					$page->tpl->clear_assign('errors_box');
				}
			}
		} else {
			if (isset($GLOBALS['_SESSION']['new_announce']['fields'])) {
				$session = $GLOBALS['_SESSION']['new_announce']['fields'];
				foreach ($session AS $key => $val) {$ret[$key] = $session[$key];}
			}
		}

		$ret['step'] = '3';
		$ret['url'] = $page->content['href'];
		return $ret;
	}

	/**
	 * Функция подтверждения оплаты и добавления в БД, в случае успеха.
	 */
	function step4() {
		//Проверим заполнен ли предыдущий шаг, если нет отправляем заполнять
		if (!isset($GLOBALS['_SESSION']['new_announce']['fields']) || !count($GLOBALS['_SESSION']['new_announce']['fields'])) redirect('/add_rnt');

		$page = & Registry::get('TPage');
		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];

			// Ошибки
			if (empty($post['code'])){
				$page->tpl->assign(array('errors' => array('Укажите полученный код')));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				//код получен будем проверять..
				$record_id = (int)sql_getValue ("SELECT id FROM `sms_payment` WHERE tousercode='".$post['code']."' AND is_available>0 AND is_used=0");
				if ($record_id) {
					sql_query("UPDATE `sms_payment` SET is_used='1' WHERE id='".$record_id."'");
					$this->saveObject();
					redirect('/add_rnt/ok');
				} else {
					$page->tpl->assign(array('errors' => array('Введенный код не верен. Убедитесь в правельности кода и повторите попытку.')));
					$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
					$page->tpl->clear_assign('errors_box');
				}
			}
		}

		$ret['step'] = '4';
		$ret['url'] = $page->content['href'];
		return $ret;
	}

	//----------------------------------------------------------------------------
	function showLeftMenu() {
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		$cabinet_id = (int)sql_getValue ("SELECT id FROM `tree` WHERE page='cabinet'");

		$menu = new TMenu();
		$left_menu = $menu->menu($cabinet_id, 0, 2, 1, array('text', 'module'));

		$ret['profile'] = $profile;
		$ret['menu'] = $left_menu;
		return array('menu'=>$ret);
	}

	//----------------------------------------------------------------------------
	function getDistricsAndCities () {

		// используем кеширование для более быстрой генерации списка
		$my_cache_name = "districs_and_cities_list";
		if (!cache_table_test($my_cache_name, array('obj_locat_districts'), true)) {
			//Получим все МО
			$districs_mo = sql_getRows("SELECT id, name FROM `obj_locat_districts` WHERE pid='".MO_DISTRICS."' AND coordinat<>'' ORDER BY name", true);

			//Получим все города МО
			$ids_mo = "";
			foreach ($districs_mo AS $key=>$value){
				$ids_mo .= $key.",";
			}
			$ids_mo = substr($ids_mo, 0, -1);
			if ($ids_mo){
				$ret['cities_mo'] = sql_getRows("SELECT id, name, pid FROM `obj_locat_districts` WHERE pid in (".$ids_mo.") ORDER BY pid, name", true);
			}

			$new_cities = array();
			foreach ($ret['cities_mo'] AS $key=>$value) {
				foreach ($ret['cities_mo'] AS $k=>$v) {
					if ($value['pid']==$v['pid'] && !in_array($k, $new_cities[$value['pid']])) {
						$new_cities[$value['pid']][$k] = $v;
					}
				}
			}

			$all_districs = array();
			foreach ($districs_mo AS $key=>$value) {
				$all_districs[$key] = $value;
				if (isset($new_cities[$key])){
					foreach ($new_cities[$key] AS $val){
						$all_districs[$val['id']] = "--".$val['name'];
					}
				}
			}

			cache_save($my_cache_name, serialize($all_districs), true);
		} else {
			$all_districs = unserialize(cache_get($my_cache_name, true));
		}

		return $all_districs;
	}

	/**
	 * Функция для генерации уникального ID кеша
	 *
	 */
	function _cache($block) {
		$page_obj = & Registry::get('TPage');
		$cache_id = lang().'_'.$page_obj->content['id'];
		// из всего, что есть в get, формируем кеш
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $cache_id .= '_'.$_SERVER['REDIRECT_QUERY_STRING'];
		return $cache_id;
	}

	/**
	 * Показываем список пользовательских объявлений
	 */
	function show_user_anno() {
		$mounthArr = array(
			'01' => 'янв.',
			'02' => 'фев.',
			'03' => 'мар.',
			'04' => 'апр.',
			'05' => 'май.',
			'06' => 'июн.',
			'07' => 'июл.',
			'08' => 'авг.',
			'09' => 'сен.',
			'10' => 'окт.',
			'11' => 'ноя.',
			'12' => 'дек.',
		);
		$search_obj = & Registry::get('TSearchRntObject'); // Подключили клас, из которого будем брать похожие методы

		$auth_obj = &Registry::get('TUserAuth');        // Клас пользователя
		$profile = $auth_obj->getCurrentUserData();     // Профиль пользователя, для того, чтобы получить его идентификатор

		if(!$profile) {                                 // Если не авторизовались то идем в регистрацию
			redirect('/registruser');
		}
		$sql = " AND client_id=".$profile['id'];

		$list = $search_obj->getList(array('offset'=>0, 'limit'=>20), $sql, $order_by, 20);
		$obj_arr = $search_obj->formatObjList($list['list'], $data['price_type'], $list['count'], 0, 20, $sql);
		$count_public = (int)sql_getValue("SELECT COUNT(*) FROM `".$this->table."` WHERE client_id='".$profile['id']."' AND status='2'");

		foreach ($obj_arr['data'] AS $key=>$value){
			if ($value['status']==3){
				$obj_arr['data'][$key]['data_status'] = "не активно";
			} else {
				$update_time = explode(" ", $value['update_time']);
				$update_time = $update_time[0];
				$update_time = explode("-", $update_time);

				$expired_time = explode(" ", $value['expired_time']);
				$expired_time = $expired_time[0];
				$expired_time = explode("-", $expired_time);
				$obj_arr['data'][$key]['data_status'] = $update_time[2]." ".$mounthArr[$update_time[1]]." ".$update_time[2]."<br />".$expired_time[2]." ".$mounthArr[$expired_time[1]]." ".$expired_time[2];
			}
		}

		$ret['list'] = $obj_arr['data'];
		$ret['pages'] = $obj_arr['navig'];
		$ret['price_type'] = 1;
		$ret['cnt'] = count($obj_arr['data']);
		$ret['count_public'] = $count_public;
		$ret['add_object_link'] = '/add_rnt';
		$ret['edit_object_link'] = 'announcement_rnt';
		$ret['balance'] = $profile['balance'];

		return $ret;
	}

	/**
	* Готовим данные для записи в базу
	*/
	function MakeSqlObject($object) {
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();
		$max_lot = (int)sql_getValue('SELECT MAX(lot_id) FROM `'.$this->table.'`');
		$kurs_dol = (int)sql_getValue("SELECT value FROM `strings` WHERE name='kurs_dol'");
		$kurs_eur = (int)sql_getValue("SELECT value FROM `strings` WHERE name='kurs_eur'");
		switch ($object['city']) {
		    case 1:
				$metro_id = $object['metro_id'];
				if (!$metro_id) $metro_id = 1;
				$district_id = 'NULL';
				$district_city_id = 'NULL';
				$moscow = 1;
				$address_city = "Москва г.,";
				break;
		    case 2:
				$district = sql_getRow("SELECT id, pid FROM `obj_locat_districts` WHERE id='".$object['district_id']."'");
				$district_id = 'NULL';
				$district_city_id = 'NULL';
				if (!empty($district)){
					if ($district['pid']==MO_DISTRICS){
						$district_id = $district['id'];
					} else {
						$district_id = $district['pid'];
						$district_city_id = $district['id'];
					}
				}
				$metro_id = 1;
				$moscow = 0;
				$address_city = "";
				break;
		}
		//Проверяем адрес в таблице адресов и координат
		$address = e(strip_tags($object['addres']));
		$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
		if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));
		$metro_dest_text = "";
		if($object['metro_time_type']==1) {
			$metro_dest_text = 0;
		} elseif($object['metro_time_type']==2) {
			$metro_dest_text = 1;
		}

		return array (
			'visible'			=> 1,
			'lot_id'			=> $max_lot+1,
			'market'			=> (isset($object['new_house']))?"first":"second",
			'room'				=> $object['rooms'],
			'district_id'		=> $district_id,
			'district_city_id'	=> $district_city_id,
			'metro_id'			=> $metro_id?$metro_id:1,
			'metro_dest_value'	=> $object['metro_time'],
			'metro_dest_text'	=> $metro_dest_text,
			'obj_type_id'		=> 'room',
			'address'			=> $address_city." ".$object['addres'],
			'short_description' => str_replace ("'", "&#039;", $object['description']),
			'price_dollar'		=> str_replace (" ", "", $object['price_rub'])/$kurs_dol,
			'price_rub'			=> str_replace (" ", "", $object['price_rub']),
			'storey'			=> $object['storey'],
			'storeys_number'	=> $object['storey_number'],
			'house_type'		=> $object['house_type_id'],
			'total_area'		=> (isset($object['total_area']))?$object['total_area']:0,
			'living_area'		=> (isset($object['live_area']))?$object['live_area']:0,
			'kitchen_area'		=> (isset($object['kitchen_area']))?$object['kitchen_area']:0,
			'balcony'			=> (isset($object['balcony']))?$object['balcony']:0,
			'phone'				=> (isset($object['phones']))?$object['phones']:0,
			'lavatory'			=> (isset($object['lavatory']))?$object['lavatory']:0,

			'state'				=> (isset($object['state']))?$object['state']:0,
			'furniture'			=> (isset($object['furniture']))?$object['furniture']:0,
			'refrigerator'		=> (isset($object['refrigerator']))?$object['refrigerator']:0,
			'washing_m'			=> (isset($object['washing_m']))?$object['washing_m']:0,
			'phones'			=> (isset($object['phones']))?$object['phones']:0,
			'tv'				=> (isset($object['tv']))?$object['tv']:0,
			'internet'			=> (isset($object['internet']))?$object['internet']:0,
			'chute'				=> (isset($object['chute']))?$object['chute']:0,
			'lift'				=> (isset($object['lift']))?$object['lift']:0,
			'children'			=> (isset($object['children']))?$object['children']:0,
			'animal'			=> (isset($object['animal']))?$object['animal']:0,

			'rnt_time'			=> $object['rnt_time'],
			'prepay'			=> $object['prepay'],
			'deposit'			=> str_replace (" ", "", $object['deposit']),
			'agent_percent'		=> $object['percent'],
			'mobile_phone'		=> $object['phone2'],
			'email'				=> $object['email'],

			'moscow'			=> $moscow,
			'contact_phone'		=> $object['phone'].((isset($object['phone2'])&&$object['phone2'])?", ".$object['phone2']:""),
			'winner'			=> 0,
			'address_id'		=> $address_id,
			'client_id'			=> (isset($profile['id']))?$profile['id']:"",
			'create_time'		=> date("Y-m-d H:i:s"),
			'update_time'		=> date("Y-m-d H:i:s"),
			'expired_time'		=> date("Y-m-d H:i:s", time()+(isset($object['days'])?$object['days']*24*60*60:0)),
			'status'			=> 1,
		);
	}

	/**
	* Обновление информации о фотках в базе
	*/
	function updatePhotos($object_id,$photoobject,$tabl) {
		$dir = 'files/objects/'.$object_id;
		if (!is_dir($dir)) return false;
		$imgmain = $photoobject[0];
		$image_large = $dir."/".basename($imgmain['image_large']);
		$image_small = $dir."/".basename($imgmain['image_small']);
		$sql = "UPDATE `objects` SET image='/".$image_large."', image_small='/".$image_small."' WHERE id='".$object_id."'";
		sql_query($sql);
		foreach ($photoobject AS $key=>$value){
			$image_large = $dir."/".basename($value['image_large']);
			switch($tabl) {
				case 'obj_elem_plans' :
					$sql= "INSERT INTO `obj_elem_plans` (pid, name, visible, image) VALUES ('$object_id', '".basename($image_small)."', 'photo', '/".$image_large."')";
				break;
				case 'obj_elem_images' :
					if (isset($value['image_small'])){
						$image_small = $dir."/".basename($value['image_small']);
						$sql= "INSERT INTO `obj_elem_images` (pid, name, type, visible, smallimagepath, imagepath) VALUES ('$object_id', '".basename($image_small)."', 'photo', 1, '/".$image_small."', '/".$image_large."')";
					} else {
						$sql= "INSERT INTO `obj_elem_images` (pid, name, type, visible, imagepath) VALUES ('$object_id', '".basename($image_large)."', 'photo', 1, '/".$image_large."')";
					}
				break;
			}
			sql_query($sql);
			if (!sql_getError()) return true;
		}
		return false;
	}

	/**
	 * Функция сохранения объявления в базу
	 *
	 */
	function saveObject($id){
		sql_query('BEGIN');
		$new_announce = $GLOBALS['_SESSION']['new_announce']['fields'];
		unset($new_announce['metrostations']);
		unset($new_announce['districs_mo']);
		unset($new_announce['house_type']);

		if ($new_announce['addres_house']) {$new_announce['addres'] .= " д.".$new_announce['addres_house'];}
		if ($new_announce['addres_corp']) {$new_announce['addres'] .= " корп.".$new_announce['addres_corp'];}
		if ($new_announce['addres_str']) {$new_announce['addres'] .= " стр.".$new_announce['addres_str'];}
		if ($new_announce['addres_vlad']) {$new_announce['addres'] .= " влад.".$new_announce['addres_vlad'];}

		$row = $this->makeSqlObject($new_announce);

		$values = $fields = "";
		foreach ($row as $k=>$v){
			$fields .= "`".$k."`,";
			$values .= "'".mysql_escape_string($v)."',";
		}
		$sql= "INSERT INTO `".$this->table."` (".substr($fields,0,-1).") VALUES (".substr($values,0,-1).")";

		sql_query($sql);
		$object_id = sql_getLastId();
		if (sql_getError()) {
			sql_query('ROLLBACK');
		}

		$dir = 'files/rnt_objects/'.$object_id;
		if (!is_dir($dir)) {
			mkdir($dir);
			chmod($dir, 0775);
		}

		if (isset($GLOBALS['_SESSION']['new_announce']['files_photo'])){
			$f = false;
			if($this->updatePhotos($object_id,$GLOBALS['_SESSION']['new_announce']['files_photo'],'object_elem_images')) $f = true;
			if($this->updatePhotos($object_id,$GLOBALS['_SESSION']['new_announce']['files_plans'],'object_elem_plans')) $f = true;
			if($f) {
				if (isset($value['image_small'])) {rename("./".$value['image_small'], "./".$image_small);}
				rename("./".$value['image_large'], "./".$image_large);
				$im_small = $dir."/".basename($value['im_small']);
				rename("./".$value['im_small'], "./".$im_small);
			}
		}

		sql_query('COMMIT');
		unset($GLOBALS['_SESSION']['new_announce']);

		return true;
	}

	//----------------------------------------------------------------------------
	/**
	 * Редактирование объявления
	 */
	function edit() {
		global $settings;

		$page = & Registry::get('TPage');
		$id = 0;
		$real_path = $_SERVER['REQUEST_URI'];
		if (substr($real_path, -1) != '/') $real_path .= '/';
		$query = explode('?', $real_path);
		$pids = explode('/', $query[0]);
		$pos = array_search('edit', $pids);
		if ($pos!==false) $id=(int)$pids[$pos + 1];
		if (!$id) {
			$page->tpl->assign(array('errors' => array('Объявление не указано!')));
			$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
			$page->tpl->clear_assign('errors_box');
			return $ret;
		}
		$auth = &Registry::get('TUserAuth');
		$userid = (int) $auth->getCurrentUserId();
		if (!$userid) {
			$page->tpl->assign(array('errors' => array('Вы не авторизованы!')));
			$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
			$page->tpl->clear_assign('errors_box');
			return $ret;
		}
		$row = sql_getRow("SELECT * FROM `".$this->table."` WHERE id='$id' AND client_id='$userid'");
		if (!$row) {
			$page->tpl->assign(array('errors' => array('Нет такого объявления или объявление принадлежит не Вам!')));
			$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
			$page->tpl->clear_assign('errors_box');
			return $ret;
		}

		if(isset($_POST['fld'])) {
			$post = $_POST['fld'];
			if ($post['city'] == 1) {
				unset($this->req_fields['district_id']);
			} else {
				unset($this->req_fields['metro_id']);
				unset($this->req_fields['metro_time']);
				unset($this->req_fields['metro_time_type']);
			}

			if ($post['phone2']) {
				$mobile = $this->getMobileNumber($post['phone2']);
				if (!$mobile) {
					$errors[] = 'Не правельный формат мобильного телефона';
				} else {
					$post['phone2'] = $mobile;
				}
			}

			foreach($this->req_fields as $key=>$val){
				if (empty($post[$key])) {
					$errors[] = $this->req_fields[$key];
				}
			}

			foreach($post['delphoto'] as $delphoto=>$smth) $this->deletePhoto($delphoto);
			foreach($post['delplan'] as $delplan=>$smth) $this->deletePlan($delplan);

			if (isset($_FILES)) {
				$photo = $plan = array();
				foreach ($_FILES['fld'] AS $key=>$value){
					if (isset($value['photo'])){
						$photo[$key] = $value['photo'];
					}
					if (isset($value['plan'])) {
						$plan[$key] = $value['plan'];
					}
				}
				include_once ('functions.php');
				$dir = 'files/rnt_objects/'.$id;
				if (!is_dir($dir)) {
					mkdir($dir);
					chmod($dir, 0775);
				}
				if ($photo){
					$sizes = array(
						'im_small'=>array('50', '36'),
						'image_small'=>array('250', '250'),
						'image_large'=>array('640', '480')
					);
					$images_photo = downloadFiles($_FILES, count($photo['name']), 'photo', "files/objects/$id", $sizes, 85, true);
					if (!empty($images_photo)) {
						$ret['files_photo'] = $images_photo;
					}
				}
				if ($plan){
					$sizes = array(
						'image_small'=>array('50', '36'),
						'image_large'=>array('640', '480')
					);
					$images_plan = downloadFiles($_FILES, count($plan['name']), 'plan', "files/rnt_objects/$id", $sizes, 85, true);
					if (!empty($images_plan)) {
						$ret['files_plan'] = $images_plan;
					}
				}
			}

			$ret['select_metro_time_type'] = $post['metro_time_type'];
			$ret['select_metro_time'] = $post['metro_time'];

			$ret['metro_dest'] = intval($post['select_metro_time']);
			$ret['metro_dest_text'] = intval($post['select_metro_time_type']);
			foreach ($post AS $key => $val) {
				$ret[$key] = $post[$key];
			}
			if ($errors) {
				array_unshift ($errors, '<b>Не заполнены следующие поля</b>');
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			}
			else {
				$this->updateObject($id,$ret);
				$row=sql_getRow("SELECT * FROM `".$this->table."` WHERE `id`='$id' AND `client_id`='$userid'");
			}
		}

		$ret = array();
		$ret['id'] = $id;
		$ret['metrostations'] = array('' => '- не выбрано -') + sql_getRows('SELECT id, name FROM `obj_locat_metrostations` WHERE 1 ORDER BY name', true);
		$ret['districs_mo'] = array('' => '- не выбрано -') + $this->getDistricsAndCities();
		$ret['house_type'] = array('' => '- не выбрано -') + sql_getRows('SELECT id, name FROM `obj_housetypes` WHERE 1 ORDER BY id', true);
		$ret['city_id'] = $row['city_id'];
		$ret['district_id'] = $row['distrcit_id'];
		$ret['metro_id'] = $row['metro_id'];
		$ret['select_metro_time'] = $row['metro_dest_value'];
		$ret['select_metro_time_type'] = intval($row['metro_dest_text'])+1;
		$ret['rooms'] = $row['room'];
		$ret['addres'] = sql_getValue("SELECT `address` FROM `obj_address` WHERE `id`='".$row['address_id']."'");
		$ret['new_house'] = ($row['market']=="first")?1:0;
		$ret['storey'] = $row['storey'];
		$ret['storey_number'] = $row['storeys_number'];
		$ret['house_type_id'] = $row['house_type'];
		$ret['total_area'] = $row['total_area'];
		$ret['live_area'] = $row['living_area'];
		$ret['kitchen_area'] = $row['kitchen_area'];
		$ret['lavatory'] = $row['lavatory'];
		$ret['balcony'] = $row['balcony'];
		$ret['phones'] = $row['phone'];
		$ret['description'] = $row['short_description'];
		$ret['price_rub'] = $row['price_rub'];

		$ret['states'] = array('' => '- не выбрано -') + $settings['annoucement_states'];
		$ret['rnt_times'] = array('' => '- не выбрано -') + $settings['annoucement_rnt_times'];
		$ret['prepays'] = array('' => '- не выбрано -') + $settings['annoucement_prepays'];
		$ret['city'] = $row['city'];
		$ret['state'] = $row['state'];



		$profile = $auth->getCurrentUserData();
		$ret['fio'] = $profile['fio'];
		$ret['email'] = $profile['login'];
		$contact_phones = explode(",",$row['contact_phone']);
		$ret['phone'] = trim($contact_phones[0]);
		$ret['phone2'] = trim($contact_phones[1]);
		$fotki = sql_getRows("SELECT * FROM `obj_elem_images` WHERE `type`='photo' AND `pid`='$id'");

		foreach($fotki as $fotka) {
			$ret['photos'][]=array(
				'id'		=>	$fotka['id'],
				'link'		=>	$fotka['imagepath'],
				'filename'	=>	substr($fotka['imagepath'],strrpos($fotka['imagepath'],'/')+1),
			);
		}
		$plans=sql_getRows("SELECT * FROM `obj_elem_plans` WHERE `pid`='$id'");
		foreach($plans as $plan) {
			$ret['plans'][]=array(
				'id'		=>	$plan['id'],
				'link'		=>	$plan['image'],
				'filename'	=>	substr($plan['image'],strrpos($plan['image'],'/')+1),
			);
		}
		return $ret;
	}

	/**
	* Удаление фотки
	*/
	function deletePhoto($id) {
		$path = sql_getValue("SELECT `imagepath` FROM `obj_elem_images` WHERE `id`='$id'");
		unlink($path);
		sql_delete('obj_elem_images',$id);
	}

	/**
	* Удаление планировки
	*/
	function deletePlan($id) {
		$path=sql_getValue("SELECT `image` FROM `obj_elem_plans` WHERE `id`='$id'");
		unlink($path);
		sql_delete('obj_elem_plans',$id);
	}

	/**
	* Обновление объекта в базе
	*/
	function updateObject($id,$postobject) {
		$row=$this->makeSqlObject($postobject);
		$set="";
		foreach ($row as $k=>$v){
			if(in_array($k,array('lot_id','create_time'))) continue;
			$set .= "`".$k."`='".mysql_escape_string($v)."',\n";
		}
		$sql="UPDATE `objects` SET ".substr($set,0,-2)." WHERE id='$id'";
		sql_query($sql);
		if (sql_getError()) {
			sql_query('ROLLBACK');
		}
		if(isset($postobject['files_photo'])) $this->updatePhotos($id,$postobject['files_photo'],'obj_elem_images');
		if(isset($postobject['files_plan'])) $this->updatePhotos($id,$postobject['files_plan'],'obj_elem_plans');
	}

	//----------------------------------------------------------------------------
	/**
	 * Продление объявлений.
	 */

	function pstep1() {
		if(isset($GLOBALS['_SESSION']['prol'])) {
			unset($GLOBALS['_SESSION']['prol']);
		}
		$mounthArr = array(
			'01' => 'янв.',
			'02' => 'фев.',
			'03' => 'мар.',
			'04' => 'апр.',
			'05' => 'май.',
			'06' => 'июн.',
			'07' => 'июл.',
			'08' => 'авг.',
			'09' => 'сен.',
			'10' => 'окт.',
			'11' => 'ноя.',
			'12' => 'дек.',
		);

		$page = & Registry::get('TPage');

		$search_obj = & Registry::get('TSearchObject'); // Подключили клас, из которого будем брать похожие методы

		$auth_obj = &Registry::get('TUserAuth');        // Клас пользователя
		$profile = $auth_obj->getCurrentUserData();     // Профиль пользователя, для того, чтобы получить его идентификатор

		if(!$profile) {                                 // Если не авторизовались то идем в регистрацию
			redirect('/registruser');
		}
		$sql = " AND client_id=".$profile['id'];

		$list = $search_obj->getList(array('offset'=>0, 'limit'=>20), $sql, $order_by, 20);
		$obj_arr = $search_obj->formatObjList($list['list'], $data['price_type'], $list['count'], 0, 20, $sql);
		$count_public = (int)sql_getValue("SELECT COUNT(*) FROM `objects` WHERE client_id='".$profile['id']."' AND status='2'");

		foreach ($obj_arr['data'] AS $key=>$value){
			if ($value['status']==3){
				$obj_arr['data'][$key]['data_status'] = "не активно";
			} else {
				$update_time = explode(" ", $value['update_time']);
				$update_time = $update_time[0];
				$update_time = explode("-", $update_time);

				$expired_time = explode(" ", $value['expired_time']);
				$expired_time = $expired_time[0];
				$expired_time = explode("-", $expired_time);
				$obj_arr['data'][$key]['data_status'] = $update_time[2]." ".$mounthArr[$update_time[1]]." ".$update_time[2]."<br />".$expired_time[2]." ".$mounthArr[$expired_time[1]]." ".$expired_time[2];
			}
		}

		$ret['list'] = $obj_arr['data'];
		$ret['pages'] = $obj_arr['navig'];
		$ret['price_type'] = 1;
		$ret['cnt'] = count($obj_arr['data']);
		$ret['count_public'] = $count_public;
		$ret['add_object_link'] = '/add_rnt';
		$ret['edit_object_link'] = 'announcement_rnt';
		$ret['balance'] = $profile['balance'];
		$ret['step'] = '1';
		$ret['url'] = $page->content['href'];

		if(isset($_POST['fld'])) {
			if(!isset($_POST['fld']['id'])) {
				$errors = 'Не выбрано ни одного объявления для продления!';
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				unset($GLOBALS['_SESSION']['prol']['id']);
				foreach ($_POST['fld']['id'] as $key => $val) {
					$GLOBALS['_SESSION']['prol']['fields']['id'][] = $val;
				}
				redirect('/cabinet/prolongation/pstep2');
			}
		}

		return $ret;
	}

	function pstep2() {
		global $settings;
		//Проверим заполнен ли предыдущий шаг, если нет отправляем заполнять
		if(!isset($GLOBALS['_SESSION']['prol']['fields']) || !count($GLOBALS['_SESSION']['prol']['fields'])) {
			redirect('/cabinet/prolongation');
		}

		$page = & Registry::get('TPage');
		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];

			// Ошибки
			if (empty($post['days'])){
				$page->tpl->assign(array('errors' => array('Укажите срок подачи объявления')));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
				foreach($settings['annoucement_prices'] as $key=>$val) $ret['variants'][]=array('days'=>$key,'price'=>$val);
				if (isset($GLOBALS['_SESSION']['prol']['fields']['days'])) {
					$ret['days'] = $GLOBALS['_SESSION']['prol']['fields']['days'];
				}
			} else {
				// Если ошибок нет, то сохраняем все в сессию и переходим к следующему шагу
				$GLOBALS['_SESSION']['prol']['fields']['days'] = $post['days'];
				redirect('/cabinet/prolongation/pstep3');
			}
		} else {
			foreach($settings['annoucement_prices'] as $key=>$val) $ret['variants'][]=array('days'=>$key,'price'=>$val);
			if (isset($GLOBALS['_SESSION']['prol']['fields']['days'])) {
				$ret['days'] = $GLOBALS['_SESSION']['prol']['fields']['days'];
			}
		}


		$ret['url'] = $page->content['href'];
		$ret['step'] = '2';
		return $ret;
	}

	function pstep3() {
		global $settings;
		//Проверим заполнен ли предыдущий шаг, если нет отправляем заполнять
		if(!isset($GLOBALS['_SESSION']['prol']['fields']) || !count($GLOBALS['_SESSION']['prol']['fields'])) {
			redirect('/cabinet/prolongation/pstep2');
		}

		$page = & Registry::get('TPage');
		$auth_obj = &Registry::get('TUserAuth');
		$profile = $auth_obj->getCurrentUserData();

		if (isset($_POST['fld'])) {
			$post = $_POST['fld'];
			$this->req_fields = array(
				'paytype' => 'Выберите способ оплаты',
			);

			foreach($this->req_fields as $key=>$val){
				if (empty($post[$key])) {
					$errors[] = $this->req_fields[$key];
				}
			}

			foreach ($post AS $key => $val) {
				$ret[$key] = $post[$key];
			}

			// Ошибки
			if ($errors){
				$page->tpl->assign(array('errors' => $errors));
				$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
				$page->tpl->clear_assign('errors_box');
			} else {
				// Если нет ошибок
				if ($post['paytype']=="free"){
					pr('Бесплатно.');
//					$this->saveObject();
//					redirect('/add/ok');
				} elseif ($post['paytype']=="sms") {
					pr('Выбор оплаты через смс!');
					// переходим на нужную страницу
//					switch ($GLOBALS['_SESSION']['new_announce']['fields']['days']) {
//					    case 7:
//					        redirect('/add/7days');
//					    case 14:
//							redirect('/add/14days');
//					    case 30:
//							redirect('/add/30days');
//					    case 90:
//							redirect('/add/90days');
//						default:
//							redirect('/add/step2');
//					}
				} elseif ($post['paytype']=="balance"){
					$err = array();
					$price = floatval($settings['annoucement_prices'][$GLOBALS['_SESSION']['prol']['fields']['days']]);
					if(!$price) {
						$err=array('Нет такого варианта оплаты!');
					} else if(floatval($profile['balance']) < $price) {
						$err=array('Не хватает средств на балансе!');
					}
					if(!empty($err)) {
						$page->tpl->assign(array('errors' => $err));
						$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
						$page->tpl->clear_assign('errors_box');
					} else {
						$price = $price * count($GLOBALS['_SESSION']['prol']['fields']['id']);
						$balance = floatval($profile['balance']) - $price;
						sql_query('UPDATE auth_users SET balance="'.$balance.'" WHERE id='.$profile['id']);
						sql_query('INSERT INTO billing (client_id,sum,date,balance) VALUES("'.$profile['id'].'","'.-$price.'",CURRENT_TIMESTAMP,"'.$balance.'")');
						foreach ($GLOBALS['_SESSION']['prol']['fields']['id'] as $key => $val) {
							$date[$key] = sql_getValue ('SELECT UNIX_TIMESTAMP(expired_time) FROM objects WHERE id='.$val);
						}
						$days = $GLOBALS['_SESSION']['prol']['fields']['days'] * 24 * 60 * 60; // секунды от выбранного количества дней
						foreach ($date as $key => $val) {
							$new_date = $date[$key] + $days;
							sql_query("UPDATE objects SET expired_time='".date('Y-m-d H:i:s', $new_date)."' WHERE id='".$GLOBALS['_SESSION']['prol']['fields']['id'][$key]."';");
						}
						unset($GLOBALS['_SESSION']['prol']);
						redirect('/cabinet/prolongation/pok');
					}
				}
				else {
					// не понятно как сюда попапли.. ошибка или хак
					$page->tpl->assign(array('errors' => array('Не определенное дейсвие. <br /> Мы не знаем что делать.')));
					$ret['errors_box'] = $page->tpl->fetch('errors_box.html');
					$page->tpl->clear_assign('errors_box');
				}
			}
		} else {
			if (isset($GLOBALS['_SESSION']['prol']['fields'])) {
				$session = $GLOBALS['_SESSION']['prol']['fields'];
				foreach ($session AS $key => $val) {$ret[$key] = $session[$key];}
			}
		}

		$ret['step'] = '3';
		$ret['url'] = $page->content['href'];
		return $ret;
	}

    function getMobileNumber($phones_str) {
		$prefix = sql_getValue ("SELECT value FROM `strings` WHERE module='searchrntobject' AND name='mobile_prefix'");
		$prefix = explode(",", $prefix);

		$mobile = "";
		$phones = explode(",", $phones_str);
		foreach ($phones AS $phone) {
			$clear_phone = str_replace (array("-"," "), array("",""), $phone);
			$phone_prefix = substr($clear_phone, -10, 3);

			if (in_array($phone_prefix, $prefix)) {
				$mobile = $phone;
				break;
			}
		}
		return $mobile;
    }
}

?>