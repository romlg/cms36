<?php
set_time_limit (0);
ini_set('memory_limit', '512M');

class TObjects_xls extends TTable {

    // название модуля
    var $name = 'objects_xls';
    // название таблицы
    var $table_flat = 'objects';
    //

    #public
    var $sRow = 2; // с какой строки начинается прайс(следующая строка, после заголовка полей)
    var $eRror; // ошибка выполнения sql запроса

    #private
    var $_Headers = array(); // массив заголовков описания товаров (в качестве ключей-номер столбцов)
    var $_cHeaders = array(); // count($this->_Headers);
    var $_rows; // указатель на полученный и разобранный в массив файл
    var $_numRows = 0; // количество строк в файле
    var $_numCols = 0; // количество столбцов в файле
    var $_cRow = 0; // текущая строка
    var $counts = array(0,0,0,0);
    var $priority = array();

    //-----------------------------------------

    function TObjects_xls() {
        global $actions, $str;

        // обязательно вызывать
        TTable::TTable();

        // строковые константы модуля
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
        'title'	=> array(
			'Импорт объектов',
			'Object import',
        ),
        ));
    }

    //----------------------------------------------------
    function EditDownload(){
    	if (!isset($_POST['flat_type'])){
			return "<script>alert('Выберите тип закачиваемых объектов.');</script>";
		} else $flat_type = $_POST['flat_type'];
    	$clear = 0;
		if (isset($_POST['clear'])) $clear = $_POST['clear'];

        $file = $_POST['file'];
        if (strpos($file, '@temp') !== false) $file = substr($file, strlen('@temp'));
        $type = substr($file, strrpos($file,'.')+1);
        if ($type != 'xls'){
            return "<script>alert('Расширение файла не поддерживается');</script>";
        }
//        echo "<script>parent.stopLoad();parent.hideDownloadFrom();</script>";
        echo "<script>parent.stopLoad();</script>";
        echo $this->getFile($file, $flat_type, $clear);
    }

    //----------------------------------------------------
    function getFile($file, $flat_type, $clear){
    	global $settings;
        //читаем файл
        $GLOBALS['gzip'] = false;
		require_once 'Excel/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1251');
        $data->read($file);

        $this->_numCols = $data->sheets[0]['numCols'];
        $this->_numRows = $data->sheets[0]['numRows'];
        $this->_rows = &$data->sheets[0]['cells'];
        if (empty($this->_rows)){
            $this->Error("Полученный файл пуст.");
			return true;
        }

		$_house_type = sql_getRows('SELECT LEFT(name, 1) AS letter, id FROM obj_housetypes', true);
		// значения задаю вручную, т.к. автоматически не определить
		$_balcon_type = array(
        	'Б' => '4',
	        'Л' => '5',
    	    '2Б' => '6',
        	'2Л' => '7',
			'БЛ' => '8',
        );

		ob_end_flush(); flush();

        //получаем заголовки колонок
        $this->_Headers = $this->_rows[$this->sRow - 1];
        $this->_cHeaders = count($this->_Headers);

        //создаем временную таблицу
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_objects_xls (
		`visible`			TINYINT(1),
		`lot_id`			VARCHAR(20),
		`market`			ENUM('first', 'second'),
		`room`				INT(2),
		`district_id`		INT(11),
		`district_city_id`	INT(11),
		`metro_id`			INT(11),
		`metro_dest_value`	VARCHAR(100),
		`metro_dest_text`	INT(1),
		`obj_type_id`		ENUM('room', 'house', 'commerce', 'newbuild'),
		`address`			VARCHAR(255),
		`short_description`	TEXT,
		`price_dollar`		DOUBLE(15,2),
		`price_rub`			DOUBLE(15,2),
		`price_euro`		DOUBLE(15,2),
		`price_metr_doll`	DOUBLE(15,2),
		`create_time`		DATETIME,
		`storey`			TINYINT(3),
		`storeys_number`	TINYINT(3),
		`total_area`		FLOAT(5,1),
		`living_area`		FLOAT(5,1),
		`kitchen_area`		FLOAT(5,1),
		`balcony`			INT(10),
		`phone`				INT(1),
		`lavatory`			INT(1),
		`house_type`  		INT(10),
		`ipoteka`			INT(1),
		`moscow`			INT(1),
		`contact_phone`		VARCHAR(255),
		`winner`			INT(1),
		`address_id`		INT(10),
		`status`		INT(1)
		)";
		sql_query($sql);

		$empty = $entries = 0;
        for ($this->_cRow = $this->sRow; $this->_cRow <= $this->_numRows; $this->_cRow++){
        	unset($row);

        	if ($this->_rows[$this->_cRow][1]!='' && $this->_rows[$this->_cRow][5]!='' && $this->_rows[$this->_cRow][12]!='') {

        		if ($this->_cRow<=$this->sRow)
        			$max_lot = (int)sql_getValue('SELECT MAX(CAST(lot_id AS UNSIGNED)) FROM `objects`');
        		else
        			$max_lot = (int)sql_getValue('SELECT MAX(CAST(lot_id AS UNSIGNED)) FROM `tmp_objects_xls`');
				$max_lot = $max_lot + 1;

				$place = trim(substr($this->_rows[$this->_cRow][2], -2));
				switch ($place) {
				    case 'м.':
						$metro = "м. ".substr($this->_rows[$this->_cRow][2], 0, -3);
						$metro_id = (int)sql_getValue ("SELECT id FROM `obj_locat_metrostations` WHERE name='$metro'");
						if (!$metro_id) $metro_id = 1;
						$district_id = 'NULL';
						$district_city_id = 'NULL';
						$moscow = 1;
						$address_city = "Москва г.";
						break;
				    case 'г.':
						$district = substr($this->_rows[$this->_cRow][2], 0, -3);
						$district = sql_getRow("SELECT id, pid FROM `obj_locat_districts` WHERE name='$district' AND coordinat=''");
						$district_id = 'NULL';
						$district_city_id = 'NULL';
						if (!empty($district)){
							$district_id = $district['pid'];
							$district_city_id = $district['id'];
						}
						$metro_id = 1;
						$moscow = 0;
						$address_city = $this->_rows[$this->_cRow][2];
						break;
					default:
						$district = $this->_rows[$this->_cRow][2];
						$district = sql_getRow("SELECT id, pid FROM `obj_locat_districts` WHERE name LIKE '%$district%'");
						$district_id = 'NULL';
						$district_city_id = 'NULL';
						if (!empty($district)){
							$district_id = $district['pid'];
							$district_city_id = $district['id'];
						}
						$metro_id = 1;
						$moscow = 0;
						$address_city = $this->_rows[$this->_cRow][2];
						break;
				}

				$house = explode("/", substr($this->_rows[$this->_cRow][5], 0, -1));
				$area = explode("/", $this->_rows[$this->_cRow][6]);

				$d = explode("/", $this->_rows[$this->_cRow][14]);
				$data = date('Y-m-d H:i:s', mktime(10, 0, 0, $d[1], $d[0], $d[2]));

				//Проверяем адрес в таблице адресов и координат
				$address = $address_city.", ".e(strip_tags($this->_rows[$this->_cRow][4]));
				$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
				if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));

        		$row = array (
					'visible'			=> 1,
					'lot_id'			=> $max_lot,
					'market'			=> $flat_type,
					'room'				=> $this->_rows[$this->_cRow][1],
					'district_id'		=> $district_id,
					'district_city_id'	=> $district_city_id,
					'metro_id'			=> $metro_id,
					'metro_dest_value'	=> (int)substr($this->_rows[$this->_cRow][3], 0, -1),
					'metro_dest_text'	=> $settings['metro_dest_xls'][substr($this->_rows[$this->_cRow][3], -1)],
					'obj_type_id'		=> 'room',
					'address'			=> $address_city.", ".$this->_rows[$this->_cRow][4],
					'short_description' => str_replace ("'", "&#039;", $this->_rows[$this->_cRow][17]),
					'price_dollar'		=> str_replace (" ", "", substr($this->_rows[$this->_cRow+1][12], 0,-1)),
					'price_rub'			=> str_replace (" ", "", substr($this->_rows[$this->_cRow][12], 0,-1)),
					'price_euro'		=> str_replace (" ", "", $this->_rows[$this->_cRow+2][12]),
					'price_metr_doll'	=> str_replace (" ", "", substr($this->_rows[$this->_cRow][13], 1)),
					'create_time'		=> $data,
					'storey'			=> $house[0],
					'storeys_number'	=> $house[1],
					'house_type'		=> $_house_type[substr($this->_rows[$this->_cRow][5], -1)],
					'total_area'		=> (isset($area[0]))?$area[0]:0,
					'living_area'		=> (isset($area[1]))?$area[1]:0,
					'kitchen_area'		=> (isset($area[2]))?$area[2]:0,
					'balcony'			=> $_balcon_type[$this->_rows[$this->_cRow][7]],
					'phone'				=> ($this->_rows[$this->_cRow][7]=='Т') ? 1 : 0,
					'lavatory'			=> $settings['lavatory_types_xls'][$this->_rows[$this->_cRow][9]],
					'ipoteka'			=> ($this->_rows[$this->_cRow][11]=='?') ? 0 : 1,
					'moscow'			=> $moscow,
					'contact_phone'		=> $this->_rows[$this->_cRow][16],
					'winner'			=> 1,
					'address_id'		=> $address_id,
					'status'			=> 2,
        		);
	        	$this->_cRow = $this->_cRow+2;

    	    	//Инсертим во временную таблицу
				$id = sql_insert('tmp_objects_xls', $row);

				if (!is_int($id)) {
					$this->eRror("INSERT INTO tmp_objects_xls<br />".$id);
				} else $entries++;
        	}
        	else $empty++;
        }

		//Если все в порядке перемещаем данные в рабочую таблицу
		if (empty($this->eRror)) {
			if (!empty($clear)) sql_query("DELETE FROM `objects` WHERE winner='1'");
	        $sql = "INSERT INTO objects (
	        	visible,lot_id,market,room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,obj_type_id,address,short_description,price_dollar,price_rub,create_time,storey,storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,moscow,contact_phone,winner,ipoteka,address_id
			) SELECT
				visible,lot_id,market,room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,obj_type_id,address,short_description,price_dollar,price_rub,create_time,storey,storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,moscow,contact_phone,winner,ipoteka,address_id
			FROM tmp_objects_xls";
			sql_query($sql);
		}
        if (empty($this->eRror)) $this->eRror = 'Успешно завершено! Загруженно '.$entries.' записей.';

        return "<script>
        	var err = parent.document.getElementById('error').innerHTML;
            parent.document.getElementById('error').innerHTML = '".e($this->eRror)."' + err;
        </script>";
    }

    //----------------------------------------------------
    function Show() {
        // обязательная фигня
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }
        $ret['thisname'] = $this->name;

        $this->AddStrings($ret);
        return $this->Parse($ret, $this->name.'.tmpl');
    }

    //----------------------------------------------------
    function EditSetStars($flatsArr){
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
			`storey_from`		TINYINT(3),
			`storey_to`			TINYINT(3),
			`house_type`		INT(10),
			`room`				INT(2),
			`total_area_from`	FLOAT(5,1),
			`total_area_to`		FLOAT(5,1),
			`star`				INT(1))";
			sql_query($sql);

			//заполняем временную таблицу
			foreach ($obj_stars AS $key => $val) {
				if (!empty($obj_stars[$key]['area'])){
					foreach ($obj_stars[$key]['area'] AS $k => $v) {
						foreach ($obj_stars[$key]['material'] AS $km => $vm) {
							$sql = "INSERT INTO tmp_obj_stars (`storey_from`,`storey_to`,`house_type`,`room`,`total_area_from`,`total_area_to`,`star`)
							VALUES ('".$val['storey']['storey_from']."','".$val['storey']['storey_to']."','".$vm."','".$v['flat']."','".$v['from']."','".$v['to']."','".$val['stars']."')";
							sql_query($sql);
						}
					}
				}
			}

			//раздаем звезды
			sql_query("UPDATE objects,tmp_obj_stars SET objects.stars=tmp_obj_stars.star WHERE tmp_obj_stars.room=objects.room AND tmp_obj_stars.storey_from<objects.storeys_number AND tmp_obj_stars.storey_to>=objects.storeys_number AND tmp_obj_stars.house_type=objects.house_type AND tmp_obj_stars.total_area_from<objects.total_area AND tmp_obj_stars.total_area_to>=objects.total_area");
			$info = mysql_info ();

			return "<script>alert('Операция успешно выполнена. Результаты запроса: ".e(str_replace(array("\r", "\n"), "", $info))."');</script>";
		} else {
			return "<script>alert('Не заданы параметры, для определения элитности.');</script>";
		}
    }

    function EditSetAddress () {
    	//Выбираем все активные объекты у которых не заполнено поле address_id
		$objects = sql_getRows('SELECT * FROM `objects` WHERE visible>0 AND address_id=0', true);
		if (!empty($objects)) {
			foreach ($objects AS $key => $val) {

				//Проверяем адрес в таблице адресов и координат
				$address = e(strip_tags($val['address']));
				$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
				if (!$address_id) {
					$data = array();
					$data['address'] = $address;
					if ($val['x']) $data['x'] = $val['x'];
					if ($val['y']) $data['y'] = $val['y'];
					$address_id = (int)sql_insert('obj_address', $data);
				}
				sql_query("UPDATE objects SET address_id='".$address_id."' WHERE id=".$val['id']);
			}

			return "<script>alert('Операция успешно выполнена.');</script>";
		} else {
			return "<script>alert('Нет объектов требующих обработки.');</script>";
		}
	}

    //----------------------------------------------------
    function Error($err) {
        echo "<script>
        var err = parent.document.getElementById('error').innerHTML;
        parent.document.getElementById('error').innerHTML = err + '".e(str_replace(array("\r", "\n"), "", $err))."';
        </script>";
        ob_end_flush(); flush();
    }
    function Error2($err) {
        echo "<script>alert('".e(str_replace(array("\r", "\n"), "", $err))."');</script>";
        ob_end_flush(); flush();
        die();
    }

	// ----------------------------------------------------
	function Editdownloadable (){
		$file = $_POST['file'];
		if (strpos($file, '@temp') !== false) $file = substr($file, strlen('@temp'));
    	$clear = 0;
		if (isset($_POST['clear'])) $clear = $_POST['clear'];

		$type = substr($file, strrpos($file,'.')+1);
		if ($type != 'gz'){
			return "<script>alert('Расширение файла не поддерживается');</script>";
		}
		echo "<script>parent.stopLoad();</script>";
		echo $this->getArchive($file, $clear);
	}

	function getArchive($file, $clear){
    	global $settings;
		require_once('Tar.php');
		$ldbl_files = array('dic_rgn.data', 'dic_str.data', 'dic_mm.data', 'dic_bldtp.data', 'dic_fartp.data', 'dic_fltbl.data', 'dic_fltph.data', 'dic_fltlv.data', 'dic_fltfr.data', 'dic_fltov.data', 'dic_fltin.data', 'dic_bldtr.data', 'dic_bldel.data', 'dic_fltst.data', 'dic_currency.data', 'recs.data', 'history.data', 'phones.data');
		$ldbl_fields = array(
			'dic_rgn.data'		=> array('id','name'),
			'dic_str.data'		=> array('id','name'),
			'dic_mm.data'		=> array('id','name'),
			'dic_bldtp.data'	=> array('id','name'),
			'dic_fartp.data'	=> array('id','name'),
			'dic_fltbl.data'	=> array('id','name'),
			'dic_fltph.data'	=> array('id','name'),
			'dic_fltlv.data'	=> array('id','name'),
			'dic_fltfr.data'	=> array('id','name'),
			'dic_fltov.data'	=> array('id','name'),
			'dic_fltin.data'	=> array('id','name'),
			'dic_bldtr.data'	=> array('id','name'),
			'dic_bldel.data'	=> array('id','name'),
			'dic_fltst.data'	=> array('id','name'),
			'dic_currency.data'	=> array('id','name'),
			'history.data'		=> array('rec_id','dt','mm_id','prc','prc_unit_id'),
			'phones.data'		=> array('rec_id','phone'),
			'recs.data'			=> array('id','dt','crdt','updt','act','prc','area','rgn_id','rgn_str','str_id','str_str','bldaddr','bldht','bldflr','farval','fltbl_str','fltbl_id','fartp_id','bldtp_id','bldtr_id','bldel_id','fltlv_id','fltfr_id','fltph_id','fltin_id','fltov_id','fltst_id','aptp','tlrmqt','slrmqt','sqtl','sqlf','sqkt','sqdl','cm','nova','mm_id','phones','photos','has_photos','price_rub','price_usd','price_eur','agency','indicator','ipoteka','tariff_mask'),
		);

		function esc_data($val){
			return "'".$val."'";
		}

		$srv_name = $_SERVER['DOCUMENT_ROOT'];
		$path = '/files/archives/';
		// Подготовим путь к хранению файлов
		$file_path = $srv_name . $path;
		// Если нет указанной директории создадим её
		if(!is_dir($srv_name . $path)) {
			if (!mkdir($file_path, 0777)) {
				return "<script>alert('Ошибка при проверки директории для загрузки архива.');</script>";
			}
		}

		// Временный старый путь к архиву
		$out_path = $file;
		// Проверим есть ли архив
		if(file_exists($out_path)) {
			$filesize = filesize($out_path);
			// Проверим его размер
			if($filesize > 10000000) {
				return "<script>alert('Очень большой файл.');</script>";
			}
		}

		// Новый путь
		$in_path = $file_path . basename($out_path);

		// Переместили архив
		if(copy($out_path, $in_path)) {

			// Удалили временный архив
			unlink($out_path);
			$tar_object = new Archive_Tar($in_path, 'gz');

			// Если открыли архив
			if ($tar_object->extract($file_path."unpack")) {
				$files = scandir($file_path."unpack");

				// Архив не пустой
				if (count($files)) {

					// Чистим loadable таблицы
					$sql = "SET NAMES cp1251;
						SET SESSION character_set_database=cp1251;
						TRUNCATE TABLE ldbl_recs;
						TRUNCATE TABLE ldbl_phones;
						TRUNCATE TABLE ldbl_history;
						TRUNCATE TABLE ldbl_dic_rgn;
						TRUNCATE TABLE ldbl_dic_str;
						TRUNCATE TABLE ldbl_dic_mm;
						TRUNCATE TABLE ldbl_dic_bldtp;
						TRUNCATE TABLE ldbl_dic_fartp;
						TRUNCATE TABLE ldbl_dic_fltbl;
						TRUNCATE TABLE ldbl_dic_fltph;
						TRUNCATE TABLE ldbl_dic_fltlv;
						TRUNCATE TABLE ldbl_dic_fltfr;
						TRUNCATE TABLE ldbl_dic_fltov;
						TRUNCATE TABLE ldbl_dic_fltin;
						TRUNCATE TABLE ldbl_dic_bldtr;
						TRUNCATE TABLE ldbl_dic_bldel;
						TRUNCATE TABLE ldbl_dic_fltst;
						TRUNCATE TABLE ldbl_dic_currency;";
					sql_query ($sql);

					// Блокируем таблицы для работы с ними
					$sql = "LOCK TABLES ldbl_dic_rgn WRITE, ldbl_dic_str WRITE, ldbl_dic_mm WRITE, ldbl_dic_bldtp WRITE,
						ldbl_dic_fartp WRITE, ldbl_dic_fltbl WRITE, ldbl_dic_fltph WRITE, ldbl_dic_fltlv WRITE,
						ldbl_dic_fltfr WRITE, ldbl_dic_fltov WRITE, ldbl_dic_fltin WRITE, ldbl_dic_bldtr WRITE,
						ldbl_dic_bldel WRITE, ldbl_dic_fltst WRITE, ldbl_dic_currency WRITE,
						ldbl_recs WRITE, ldbl_phones WRITE, ldbl_history WRITE, ldbl_config WRITE;";
					sql_query ($sql);

					foreach ($files AS $file) {
						if (is_readable($file_path."unpack/".$file)) {
							if (in_array($file, $ldbl_files)){
								$table_name = substr($file, 0, -5);
								$stings = file ($file_path."unpack/".$file);
								if (count($stings)) {
									$ii = 0;
									foreach ($stings AS $num => $line) {
										if ($ii == 0) {
											$sql_insert = "INSERT INTO ldbl_".$table_name." (".implode(",", $ldbl_fields[$file]).") ";
											$sql_values = " VALUES (".implode(",", array_map("esc_data", explode("\t", $line)))."), ";
										} else {
											$sql_values .= " VALUES (".implode(",", array_map("esc_data", explode("\t", $line)))."), ";
										}

										$ii++;
										if ($ii == 1000) {
											$sql_values = substr($sql_values, 0, -2).";";
											$result = sql_query ($sql_insert.$sql_values);
											$this->eRror .= (!$result) ? ' Ошибки при загрузке файла '.$file.' ERROR:('.$result.')'  : '';
											$ii = 0;
										}
									}
									if (!$ii) {
										$result = sql_query ($sql_insert.$sql_values);
										$this->eRror .= (!$result) ? ' Ошибки при загрузке файла '.$file.' ERROR:('.$result.')'  : '';
									}
								}
							}
						} else {
							$this->eRror .= (!$result) ? ' Не удалось прочитать файл '.$file.' из архива'  : '';
						}
					}
					sql_query ("UNLOCK TABLES;");

					if (empty($this->eRror)) {
						if (!empty($clear)) sql_query("DELETE FROM `".$this->table_flat."` WHERE loadable='1'");

						$ldbl_regions = sql_getRows('SELECT ldbl_winner_view.*, ldbl_dic_rgn.name FROM `ldbl_winner_view` LEFT JOIN ldbl_dic_rgn ON ldbl_winner_view.rgn_id=ldbl_dic_rgn.id  WHERE 1 GROUP BY ldbl_winner_view.rgn_id', true);
						foreach ($ldbl_regions AS $key=>$value) {
							$place = substr(trim($value['name']), -2);
							switch ($place) {
							    case 'м.':
									$metro = "м. ".substr(trim($value['name']), 0, -3);
									$metro_id = (int)sql_getValue ("SELECT id FROM `obj_locat_metrostations` WHERE name='$metro'");
									if (!$metro_id) $metro_id = 1;
									$district_id = 'NULL';
									$district_city_id = 'NULL';
									break;
							    case 'г.':
									$district = substr(trim($value['name']), 0, -3);
									$district = sql_getRow("SELECT id, pid FROM `obj_locat_districts` WHERE name='$district' AND coordinat=''");
									$district_id = 'NULL';
									$district_city_id = 'NULL';
									if (!empty($district)){
										$district_id = $district['pid'];
										$district_city_id = $district['id'];
									}
									$metro_id = 1;
									break;
								default:
									$district = trim($value['name']);
									$district = sql_getRow("SELECT id, pid FROM `obj_locat_districts` WHERE name LIKE '%$district%'");
									$district_id = 'NULL';
									$district_city_id = 'NULL';
									if (!empty($district)){
										$district_id = $district['pid'];
										$district_city_id = $district['id'];
									}
									$metro_id = 1;
									break;
							}

							sql_query("UPDATE ldbl_recs SET district_id='".$district_id."', district_city_id='".$district_city_id."', metro_id='".$metro_id."' WHERE rgn_id='".$value['rgn_id']."'");
						}

						$ldbl_addreses = sql_getRows("SELECT
							ldbl_winner_view.*,
							CONCAT(
								IF(ldbl_winner_view.str_id>0,
									ldbl_dic_str.name,
									ldbl_winner_view.str_str
								),
								' ',
								bldaddr
							) AS address_name,
							ldbl_dic_rgn.name
						FROM `ldbl_winner_view`
						LEFT JOIN ldbl_dic_str ON ldbl_winner_view.str_id=ldbl_dic_str.id
						LEFT JOIN ldbl_dic_rgn ON ldbl_winner_view.rgn_id=ldbl_dic_rgn.id
						WHERE 1 GROUP BY ldbl_winner_view.str_id", true);

						foreach ($ldbl_addreses AS $key=>$value) {
							$place = trim(substr($value['name'], -2));
							switch ($place) {
							    case 'м.':
									$address_city = "Москва г.";
									break;
							    case 'г.':
									$address_city = $value['name'];
									break;
								default:
									$address_city = $value['name'];
									break;
							}

							//Проверяем адрес в таблице адресов и координат
							$address = $address_city.", ".e(strip_tags($value['address_name']));
							$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
							if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));

							sql_query("UPDATE ldbl_recs SET address_id='".$address_id."', address='".$address."' WHERE str_id='".$value['str_id']."'");
						}

						// Все готово можно начинать великое переселение ;)
						// sql для переноса из loadable таблицы в нашу рабочую
						$sql = "INSERT INTO objects (
							visible,lot_id,market,room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,
							obj_type_id,address,short_description,price_dollar,price_rub,create_time,storey,
							storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,
							moscow,contact_phone,loadable,address_id,status,photos
						)
						SELECT
						1,
						(SELECT MAX(lot_id) FROM objects)+1 AS lot_id,
						IF(ldbl_recs.nova!=0,'first','second') AS market,
						ldbl_recs.tlrmqt AS room,
						ldbl_recs.district_id,
						ldbl_recs.district_city_id,
						ldbl_recs.metro_id,
						ldbl_recs.farval AS metro_dest_value,
						IF(ldbl_recs.fartp_id!=2,0,1) AS metro_dest_text,
						'room',
						ldbl_recs.address,
						ldbl_recs.cm AS short_description,
						ldbl_recs.price_usd AS price_dollar,
						ldbl_recs.price_rub AS price_rub,
						CONCAT(ldbl_recs.crdt, ' 10:00:00') AS create_time,
						ldbl_recs.bldht AS storey,
						ldbl_recs.bldflr AS storeys_number,
						(SELECT obj_housetypes.id FROM obj_housetypes WHERE LEFT(obj_housetypes.name, 1)=ldbl_dic_bldtp.name) AS house_type,

						IF (ldbl_recs.sqtl IS NULL,
							'0.0',
							IF (ldbl_recs.sqtl mod 10 = 0,
								ldbl_recs.sqtl div 10,
								CONCAT(ldbl_recs.sqtl div 10, '.', ldbl_recs.sqtl mod 10)
							)
						) AS total_area,

						IF (ldbl_recs.sqlf IS NULL,
							'0.0',
							IF (ldbl_recs.sqlf mod 10 = 0,
								ldbl_recs.sqlf div 10,
								CONCAT(ldbl_recs.sqlf div 10, '.', ldbl_recs.sqlf mod 10)
							)
						) AS living_area,

						IF (ldbl_recs.sqkt IS NULL,
							'0.0',
							IF (ldbl_recs.sqkt mod 10 = 0,
								ldbl_recs.sqkt div 10,
								CONCAT(ldbl_recs.sqkt div 10, '.', ldbl_recs.sqkt mod 10)
							)
						) AS kitchen_area,

						IF(ldbl_recs.fltbl_id > 1,
							IF (ldbl_recs.fltbl_id = 1,
								'1',
								IF (ldbl_recs.fltbl_id = 'Б',
									'4',
									IF (ldbl_recs.fltbl_id = 'Л',
										'5',
										IF (ldbl_recs.fltbl_id = '2Б',
											'6',
											IF (ldbl_recs.fltbl_id = '2Л',
												'7',
												IF (ldbl_recs.fltbl_id = 'БЛ',
													'8',
													'0'
												)
											)
										)
									)
								)
							),
							'0'
						) AS balcony,

						IF(ldbl_recs.fltph_id>1,
							'1',
							'0'
						) AS phone,

						IF(ldbl_recs.fltlv_id > 3,
							'2',
							IF(ldbl_recs.fltlv_id = 2,
								'1',
								'0'
							)
						) AS lavatory,

						IF (ldbl_recs.area = 1,
							'1',
							'0'
						) AS moscow,

						phones AS contact_phone,
						'1',
						ldbl_recs.address_id,
						'2',
						ldbl_recs.photos

						FROM  `ldbl_recs`
						Left join ldbl_dic_bldtp ON ldbl_recs.bldtp_id = ldbl_dic_bldtp.id
						";
						$result = sql_query($sql);

						// У нас получилось?
						$this->eRror = ($result) ? '' : ' Не удалось выполнить перенос данных из временных таблиц в основную.';
					}

					// Удалим распакованные файлы
					foreach ($files AS $file) {
						unlink ($file_path."unpack/".$file);
					}
				}

				// Удалим архив
				unlink ($in_path);
			}

			if (empty($this->eRror)) {
				$this->eRror = 'Архив успешно загружен.';
			}

	        return "<script>
	        	var err = parent.document.getElementById('error').innerHTML;
	            parent.document.getElementById('error').innerHTML = '".e($this->eRror)."' + err;
	        </script>";
		} else {
	        return "<script>
	        	var err = parent.document.getElementById('error').innerHTML;
	            parent.document.getElementById('error').innerHTML = 'Не удалось загрузить архив. ' + err;
	        </script>";
		}
	}
}

$GLOBALS['objects_xls'] = &Registry::get('TObjects_xls');

?>