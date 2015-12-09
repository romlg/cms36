<?php
set_time_limit (0);
ini_set('memory_limit', '512M');

class TRnt_objects_xls extends TTable {

    // �������� ������
    var $name = 'rnt_objects_xls';
    // �������� �������
    var $table_flat = 'rnt_objects';
    //

    #public
    var $sRow = 2; // � ����� ������ ���������� �����(��������� ������, ����� ��������� �����)
    var $eRror; // ������ ���������� sql �������

    #private
    var $_Headers = array(); // ������ ���������� �������� ������� (� �������� ������-����� ��������)
    var $_cHeaders = array(); // count($this->_Headers);
    var $_rows; // ��������� �� ���������� � ����������� � ������ ����
    var $_numRows = 0; // ���������� ����� � �����
    var $_numCols = 0; // ���������� �������� � �����
    var $_cRow = 0; // ������� ������
    var $counts = array(0,0,0,0);
    var $priority = array();

    //-----------------------------------------

    function TObjects_xls() {
        global $actions, $str;

        // ����������� ��������
        TTable::TTable();

        // ��������� ��������� ������
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
        'title'	=> array(
			'������ ��������',
			'Object import',
        ),
        ));
    }

    //----------------------------------------------------
    function EditDownload(){
    	if (!isset($_POST['flat_type'])){
			return "<script>alert('�������� ��� ������������ ��������.');</script>";
		} else $flat_type = $_POST['flat_type'];
    	$clear = 0;
		if (isset($_POST['clear'])) $clear = $_POST['clear'];

        $file = $_POST['file'];
        if (strpos($file, '@temp') !== false) $file = substr($file, strlen('@temp'));
        $type = substr($file, strrpos($file,'.')+1);
        if ($type != 'xls'){
            return "<script>alert('���������� ����� �� ��������������');</script>";
        }
//        echo "<script>parent.stopLoad();parent.hideDownloadFrom();</script>";
        echo "<script>parent.stopLoad();</script>";
        echo $this->getFile($file, $flat_type, $clear);
    }

    //----------------------------------------------------
    function getFile($file, $flat_type, $clear){
    	global $settings;
        //������ ����
        $GLOBALS['gzip'] = false;
		require_once 'Excel/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1251');
        $data->read($file);

        $this->_numCols = $data->sheets[0]['numCols'];
        $this->_numRows = $data->sheets[0]['numRows'];
        $this->_rows = &$data->sheets[0]['cells'];
        if (empty($this->_rows)){
            $this->Error("���������� ���� ����.");
			return true;
        }

		$_house_type = sql_getRows('SELECT LEFT(name, 1) AS letter, id FROM obj_housetypes', true);
		// �������� ����� �������, �.�. ������������� �� ����������
		$_balcon_type = array(
        	'�' => '4',
	        '�' => '5',
    	    '2�' => '6',
        	'2�' => '7',
			'��' => '8',
        );

		ob_end_flush(); flush();

        //�������� ��������� �������
        $this->_Headers = $this->_rows[$this->sRow - 1];
        $this->_cHeaders = count($this->_Headers);

        //������� ��������� �������
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_objects_xls (
		`visible`			TINYINT(1),
		`lot_id`			INT(11),
		`market`			ENUM('first', 'second'),
		`room`				INT(2),
		`rent_room`			INT(2),
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
		`create_time`		DATETIME,
		`storey`			TINYINT(3),
		`storeys_number`	TINYINT(3),
		`total_area`		FLOAT(5,1),
		`living_area`		FLOAT(5,1),
		`kitchen_area`		FLOAT(5,1),
		`balcony`			INT(10),
		`phone`				INT(1),
		`lavatory`			INT(1),
		`furniture`			INT(1),
		`refrigerator`		INT(1),
		`washing_m`			INT(1),
		`tv`				INT(1),
		`agent_percent`		INT(10),
		`client_percent`	INT(10),
		`mobile_phone`		VARCHAR(255),
		`house_type`  		INT(10),
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

        	if ($this->_rows[$this->_cRow][1]!='' && $this->_rows[$this->_cRow][4]!='' && $this->_rows[$this->_cRow][22]!='') {

        		if ($this->_cRow<=$this->sRow)
        			$max_lot = (int)sql_getValue('SELECT MAX(lot_id) FROM '.$this->table_flat);
        		else
        			$max_lot = (int)sql_getValue('SELECT MAX(lot_id) FROM `tmp_objects_xls`');
				$max_lot = $max_lot + 1;

				$place = trim(substr($this->_rows[$this->_cRow][2], -2));
				switch ($place) {
				    case '�.':
						$metro = "�. ".substr($this->_rows[$this->_cRow][2], 0, -3);
						$metro_id = (int)sql_getValue ("SELECT id FROM `obj_locat_metrostations` WHERE name='$metro'");
						if (!$metro_id) $metro_id = 1;
						$district_id = 'NULL';
						$district_city_id = 'NULL';
						$moscow = 1;
						$address_city = "������ �.";
						break;
				    case '�.':
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

				$house = explode("/", substr($this->_rows[$this->_cRow][6], 0, -1));
				$area = explode("/", $this->_rows[$this->_cRow][7]);

				$d = explode(".", $this->_rows[$this->_cRow][20]);
				//��������, ���� ����������� ���� �� �����
				if (count($d)<=1) {$d = explode("/", $this->_rows[$this->_cRow][20]);}
				$data = date('Y-m-d H:i:s', mktime(10, 0, 0, $d[1], $d[0], $d[2]));

				//��������� ����� � ������� ������� � ���������
				$number_of_house = ($this->_rows[$this->_cRow][5]!='?') ? ", �.".$this->_rows[$this->_cRow][5] : '';
				$address = $address_city.", ".e(strip_tags($this->_rows[$this->_cRow][4])).e(strip_tags($number_of_house));
				$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
				if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));

				$rooms = explode("/", $this->_rows[$this->_cRow][1]);

        		$row = array (
					'visible'			=> 1,
					'lot_id'			=> $max_lot,
					'market'			=> $flat_type,
					'room'				=> (isset($rooms[1])) ? $rooms[1] : $rooms[0],
					'rent_room'			=> (isset($rooms[1])) ? $rooms[0] : 0,
					'district_id'		=> $district_id,
					'district_city_id'	=> $district_city_id,
					'metro_id'			=> $metro_id,
					'metro_dest_value'	=> (int)substr($this->_rows[$this->_cRow][3], 0, -1),
					'metro_dest_text'	=> $settings['metro_dest_xls'][substr($this->_rows[$this->_cRow][3], -1)],
					'obj_type_id'		=> 'room',
					'address'			=> $address_city.", ".$this->_rows[$this->_cRow][4].$number_of_house,
					'short_description' => str_replace ("'", "&#039;", $this->_rows[$this->_cRow][23]),
					'price_dollar'		=> str_replace (" ", "", $this->_rows[$this->_cRow][16]),
					'price_rub'			=> str_replace (" ", "", $this->_rows[$this->_cRow][15]),
					'price_euro'		=> str_replace (" ", "", $this->_rows[$this->_cRow][17]),
					'create_time'		=> $data,
					'storey'			=> $house[0],
					'storeys_number'	=> $house[1],
					'house_type'		=> $_house_type[substr($this->_rows[$this->_cRow][6], -1)],
					'total_area'		=> (isset($area[0]))?$area[0]:0,
					'living_area'		=> (isset($area[1]))?$area[1]:0,
					'kitchen_area'		=> (isset($area[2]))?$area[2]:0,
					'balcony'			=> $_balcon_type[$this->_rows[$this->_cRow][8]],
					'phone'				=> ($this->_rows[$this->_cRow][9]=='�') ? 1 : 0,
					'lavatory'			=> $settings['lavatory_types_xls'][$this->_rows[$this->_cRow][10]],
					'furniture'			=> ($this->_rows[$this->_cRow][11]=='+') ? 1 : 0,
					'refrigerator'		=> ($this->_rows[$this->_cRow][12]=='+') ? 1 : 0,
					'washing_m'			=> ($this->_rows[$this->_cRow][13]=='+') ? 1 : 0,
					'tv'				=> ($this->_rows[$this->_cRow][14]=='+') ? 1 : 0,
					'agent_percent'		=> $this->_rows[$this->_cRow][18],
					'client_percent'	=> $this->_rows[$this->_cRow][19],
					'mobile_phone'		=> $this->getMobileNumber($this->_rows[$this->_cRow][22]),
					'moscow'			=> $moscow,
					'contact_phone'		=> $this->_rows[$this->_cRow][22],
					'winner'			=> 1,
					'address_id'		=> $address_id,
					'status'			=> 2,
        		);

    	    	//�������� �� ��������� �������
				$id = sql_insert('tmp_objects_xls', $row);

				if (!is_int($id)) {
					$this->eRror("INSERT INTO tmp_objects_xls<br />".$id);
				} else $entries++;
        	}
        	else $empty++;
        }

		//���� ��� � ������� ���������� ������ � ������� �������
		if (empty($this->eRror)) {
			if (!empty($clear)) sql_query("DELETE FROM `".$this->table_flat."` WHERE winner='1'");
	        $sql = "INSERT INTO `".$this->table_flat."` (
	        	visible,lot_id,market,room,rent_room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,obj_type_id,address,short_description,price_dollar,price_rub,price_euro,create_time,storey,storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,furniture,refrigerator,washing_m,tv,agent_percent,client_percent,mobile_phone,moscow,contact_phone,winner,address_id,status
			) SELECT
				visible,lot_id,market,room,rent_room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,obj_type_id,address,short_description,price_dollar,price_rub,price_euro,create_time,storey,storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,furniture,refrigerator,washing_m,tv,agent_percent,client_percent,mobile_phone,moscow,contact_phone,winner,address_id,status
			FROM tmp_objects_xls";
			sql_query($sql);
		}
        if (empty($this->eRror)) $this->eRror = '������� ���������! ���������� '.$entries.' �������.';

        return "<script>
        	var err = parent.document.getElementById('error').innerHTML;
            parent.document.getElementById('error').innerHTML = '".e($this->eRror)."' + err;
        </script>";
    }

    //----------------------------------------------------
    function Show() {
        // ������������ �����
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
		// �������� ��� ������ �� ����
		$obj_stars = sql_getRows('SELECT * FROM `obj_stars` WHERE 1 ORDER BY stars ASC', true);
		if (!empty($obj_stars)) {
			foreach ($obj_stars AS $key => $val) {
				$obj_stars[$key]['storey']		= unserialize($obj_stars[$key]['storey']);
				$obj_stars[$key]['material']	= unserialize($obj_stars[$key]['material']);
				$obj_stars[$key]['area']		= unserialize($obj_stars[$key]['area']);
			}


	        //������� ��������� �������
			$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_obj_stars (
			`storey_from`		TINYINT(3),
			`storey_to`			TINYINT(3),
			`house_type`		INT(10),
			`room`				INT(2),
			`total_area_from`	FLOAT(5,1),
			`total_area_to`		FLOAT(5,1),
			`star`				INT(1))";
			sql_query($sql);

			//��������� ��������� �������
			foreach ($obj_stars AS $key => $val) {
				if (!empty($obj_stars[$key]['area'])) {
					foreach ($obj_stars[$key]['area'] AS $k => $v) {
						foreach ($obj_stars[$key]['material'] AS $km => $vm) {
							$sql = "INSERT INTO tmp_obj_stars (`storey_from`,`storey_to`,`house_type`,`room`,`total_area_from`,`total_area_to`,`star`)
							VALUES ('".$val['storey']['storey_from']."','".$val['storey']['storey_to']."','".$vm."','".$v['flat']."','".$v['from']."','".$v['to']."','".$val['stars']."')";
							sql_query($sql);
						}
					}
				}
			}

			//������� ������
			sql_query("UPDATE ".$this->table_flat.",tmp_obj_stars SET ".$this->table_flat.".stars=tmp_obj_stars.star WHERE tmp_obj_stars.room=".$this->table_flat.".room AND tmp_obj_stars.storey_from<".$this->table_flat.".storeys_number AND tmp_obj_stars.storey_to>=".$this->table_flat.".storeys_number AND tmp_obj_stars.house_type=".$this->table_flat.".house_type AND tmp_obj_stars.total_area_from<".$this->table_flat.".total_area AND tmp_obj_stars.total_area_to>=".$this->table_flat.".total_area");
			$info = mysql_info ();

			return "<script>alert('�������� ������� ���������. ���������� �������: ".e(str_replace(array("\r", "\n"), "", $info))."');</script>";
		} else {
			return "<script>alert('�� ������ ���������, ��� ����������� ���������.');</script>";
		}
    }

	//----------------------------------------------------
    function EditSetAddress () {
    	//�������� ��� �������� ������� � ������� �� ��������� ���� address_id
		$objects = sql_getRows('SELECT * FROM `'.$this->table_flat.'` WHERE visible>0 AND address_id=0', true);
		if (!empty($objects)) {
			foreach ($objects AS $key => $val) {

				//��������� ����� � ������� ������� � ���������
				$address = e(strip_tags($val['address']));
				$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
				if (!$address_id) {
					$data = array();
					$data['address'] = $address;
					if ($val['x']) $data['x'] = $val['x'];
					if ($val['y']) $data['y'] = $val['y'];
					$address_id = (int)sql_insert('obj_address', $data);
				}
				sql_query("UPDATE ".$this->table_flat." SET address_id='".$address_id."' WHERE id=".$val['id']);
			}

			return "<script>alert('�������� ������� ���������.');</script>";
		} else {
			return "<script>alert('��� �������� ��������� ���������.');</script>";
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

	//----------------------------------------------------
    function Error2($err) {
        echo "<script>alert('".e(str_replace(array("\r", "\n"), "", $err))."');</script>";
        ob_end_flush(); flush();
        die();
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
				$mobile = $clear_phone;
				break;
			}
		}
		return $mobile;
    }




	// ----------------------------------------------------
	function Editdownloadable (){
		$file = $_POST['file'];
		if (strpos($file, '@temp') !== false) $file = substr($file, strlen('@temp'));
    	$clear = 0;
		if (isset($_POST['clear'])) $clear = $_POST['clear'];

		$type = substr($file, strrpos($file,'.')+1);
		if ($type != 'gz'){
			return "<script>alert('���������� ����� �� ��������������');</script>";
		}
		echo "<script>parent.stopLoad();</script>";
		echo $this->getArchive($file, $clear);
	}

	function getArchive($file, $clear){
    	global $settings;
		require_once('Tar.php');
		$ldbl_files = array('dic_fltfnt.data', 'recs.data');
		$ldbl_fields = array(
			'dic_fltfnt.data'	=> array('id','name'),
			'recs.data'			=> array('id','dt','crdt','updt','act','prc','area','rgn_id','rgn_str','str_id','str_str','bldaddr','bldht','bldflr','farval','fltbl_str','fltbl_id','fartp_id','bldtp_id','bldtr_id','bldel_id','fltlv_id','fltfr_id','fltph_id','fltin_id','fltov_id','fltst_id','aptp','tlrmqt','slrmqt','sqtl','sqlf','sqkt','sqdl','cm','nova','mm_id','phones','photos','has_photos','price_rub','price_usd','price_eur','agency','indicator','ipoteka','tariff_mask'),
		);

		function esc_data($val){
			return "'".$val."'";
		}

		$srv_name = $_SERVER['DOCUMENT_ROOT'];
		$path = '/files/archives/';
		// ���������� ���� � �������� ������
		$file_path = $srv_name . $path;
		// ���� ��� ��������� ���������� �������� �
		if(!is_dir($srv_name . $path)) {
			if (!mkdir($file_path, 0777)) {
				return "<script>alert('������ ��� �������� ���������� ��� �������� ������.');</script>";
			}
		}

		// ��������� ������ ���� � ������
		$out_path = $file;
		// �������� ���� �� �����
		if(file_exists($out_path)) {
			$filesize = filesize($out_path);
			// �������� ��� ������
			if($filesize > 10000000) {
				return "<script>alert('����� ������� ����.');</script>";
			}
		}

		// ����� ����
		$in_path = $file_path . basename($out_path);

		// ����������� �����
		if(copy($out_path, $in_path)) {

			// ������� ��������� �����
			unlink($out_path);
			$tar_object = new Archive_Tar($in_path, 'gz');

			// ���� ������� �����
			if ($tar_object->extract($file_path."unpack")) {
				$files = scandir($file_path."unpack");

				// ����� �� ������
				if (count($files)) {

					// ������ loadable �������
					$sql = "SET NAMES cp1251;
						SET SESSION character_set_database=cp1251;
						TRUNCATE TABLE ldbl_recs_rent;
						TRUNCATE TABLE ldbl_dic_fltfnt;
						";
					sql_query ($sql);

					// ��������� ������� ��� ������ � ����
					$sql = "LOCK TABLES ldbl_dic_fltfnt WRITE, ldbl_recs_rent WRITE;";
					sql_query ($sql);
/*
					foreach ($files AS $file) {
						if (is_readable($file_path."unpack/".$file)) {
							if (in_array($file, $ldbl_files)){
								$table_name = substr($file, 0, -5);
								$result = sql_query ('LOAD DATA LOCAL INFILE "'.$file_path."unpack/".$file.'" INTO TABLE ldbl_'.$table_name.' FIELDS TERMINATED BY \'\t\' ENCLOSED BY \'"\' ESCAPED BY \'\\\' LINES TERMINATED BY "\r\n"');
								$this->eRror .= (!$result) ? ' �� ������� ��������� ���� '.$file.' �� ������'  : '';
							}
						} else {
							$this->eRror .= (!$result) ? ' �� ������� ��������� ���� '.$file.' �� ������'  : '';
						}
					}
*/

					foreach ($files AS $file) {
						if (is_readable($file_path."unpack/".$file)) {
							if (in_array($file, $ldbl_files)){
								$table_name = substr($file, 0, -5);
								if ($table_name=='recs') {$table_name = $table_name."_rent";}
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
											$this->eRror .= (!$result) ? ' ������ ��� �������� ����� '.$file.' ERROR:('.$result.')'  : '';
											$ii = 0;
										}
									}
									if (!$ii) {
										$result = sql_query ($sql_insert.$sql_values);
										$this->eRror .= (!$result) ? ' ������ ��� �������� ����� '.$file.' ERROR:('.$result.')'  : '';
									}
								}
							}
						} else {
							$this->eRror .= (!$result) ? ' �� ������� ��������� ���� '.$file.' �� ������'  : '';
						}
					}
					sql_query ("UNLOCK TABLES;");

					if (empty($this->eRror)) {
						if (!empty($clear)) sql_query("DELETE FROM `".$this->table_flat."` WHERE loadable='1'");

						$ldbl_regions = sql_getRows('SELECT ldbl_recs_rent.*, ldbl_dic_rgn.name FROM `ldbl_recs_rent` LEFT JOIN ldbl_dic_rgn ON ldbl_recs_rent.rgn_id=ldbl_dic_rgn.id  WHERE 1 GROUP BY ldbl_recs_rent.rgn_id', true);
						foreach ($ldbl_regions AS $key=>$value) {
							$place = substr(trim($value['name']), -2);
							switch ($place) {
							    case '�.':
									$metro = "�. ".substr(trim($value['name']), 0, -3);
									$metro_id = (int)sql_getValue ("SELECT id FROM `obj_locat_metrostations` WHERE name='$metro'");
									if (!$metro_id) $metro_id = 1;
									$district_id = 'NULL';
									$district_city_id = 'NULL';
									break;
							    case '�.':
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

							sql_query("UPDATE ldbl_recs_rent SET district_id='".$district_id."', district_city_id='".$district_city_id."', metro_id='".$metro_id."' WHERE rgn_id='".$value['rgn_id']."'");
						}

						$ldbl_addreses = sql_getRows("SELECT
							ldbl_recs_rent.*,
							CONCAT(
								IF(ldbl_recs_rent.str_id>0,
									ldbl_dic_str.name,
									ldbl_recs_rent.str_str
								),
								' ',
								bldaddr
							) AS address_name,
							ldbl_dic_rgn.name
						FROM `ldbl_recs_rent`
						LEFT JOIN ldbl_dic_str ON ldbl_recs_rent.str_id=ldbl_dic_str.id
						LEFT JOIN ldbl_dic_rgn ON ldbl_recs_rent.rgn_id=ldbl_dic_rgn.id
						WHERE 1 GROUP BY ldbl_recs_rent.str_id", true);

						foreach ($ldbl_addreses AS $key=>$value) {
							$place = trim(substr($value['name'], -2));
							switch ($place) {
							    case '�.':
									$address_city = "������ �.";
									break;
							    case '�.':
									$address_city = $value['name'];
									break;
								default:
									$address_city = $value['name'];
									break;
							}

							//��������� ����� � ������� ������� � ���������
							$address = $address_city.", ".e(strip_tags($value['address_name']));
							$address_id = (int)sql_getValue ("SELECT id FROM `obj_address` WHERE address='$address'");
							if (!$address_id) $address_id = (int)sql_insert('obj_address', array('address'=>$address));

							sql_query("UPDATE ldbl_recs_rent SET address_id='".$address_id."', address='".$address."' WHERE str_id='".$value['str_id']."'");
						}

						$ldbl_phones = sql_getRows("SELECT * FROM `ldbl_recs_rent` WHERE 1 GROUP BY phones", true);
						foreach ($ldbl_phones AS $key=>$value) {
							sql_query("UPDATE ldbl_recs_rent SET mobile_phone='".$this->getMobileNumber($value['phones'])."' WHERE phones='".$value['phones']."'");
						}

						// ��� ������ ����� �������� ������� ����������� ;)
						// sql ��� �������� �� loadable ������� � ���� �������
						$sql = "INSERT INTO objects (
							visible,lot_id,market,room,district_id,district_city_id,metro_id,metro_dest_value,metro_dest_text,
							obj_type_id,address,short_description,price_dollar,price_rub,create_time,storey,
							storeys_number,house_type,total_area,living_area,kitchen_area,balcony,phone,lavatory,
							moscow,contact_phone,loadable,address_id,status,agent_percent,client_percent,
							refrigerator,tv,washing_m,mobile_phone,photos
						)
						SELECT
						1,
						(SELECT MAX(lot_id) FROM objects)+1 AS lot_id,
						IF(ldbl_recs_rent.nova!=0,'first','second') AS market,
						ldbl_recs_rent.tlrmqt AS room,
						ldbl_recs_rent.district_id,
						ldbl_recs_rent.district_city_id,
						ldbl_recs_rent.metro_id,
						ldbl_recs_rent.farval AS metro_dest_value,
						IF(ldbl_recs_rent.fartp_id!=2,0,1) AS metro_dest_text,
						'room',
						ldbl_recs_rent.address,
						ldbl_recs_rent.cm AS short_description,
						ldbl_recs_rent.price_usd AS price_dollar,
						ldbl_recs_rent.price_rub AS price_rub,
						CONCAT(ldbl_recs_rent.crdt, ' 10:00:00') AS create_time,
						ldbl_recs_rent.bldht AS storey,
						ldbl_recs_rent.bldflr AS storeys_number,
						(SELECT obj_housetypes.id FROM obj_housetypes WHERE LEFT(obj_housetypes.name, 1)=ldbl_dic_bldtp.name) AS house_type,

						IF (ldbl_recs_rent.sqtl IS NULL,
							'0.0',
							IF (ldbl_recs_rent.sqtl mod 10 = 0,
								ldbl_recs_rent.sqtl div 10,
								CONCAT(ldbl_recs_rent.sqtl div 10, '.', ldbl_recs_rent.sqtl mod 10)
							)
						) AS total_area,

						IF (ldbl_recs_rent.sqlf IS NULL,
							'0.0',
							IF (ldbl_recs_rent.sqlf mod 10 = 0,
								ldbl_recs_rent.sqlf div 10,
								CONCAT(ldbl_recs_rent.sqlf div 10, '.', ldbl_recs_rent.sqlf mod 10)
							)
						) AS living_area,

						IF (ldbl_recs_rent.sqkt IS NULL,
							'0.0',
							IF (ldbl_recs_rent.sqkt mod 10 = 0,
								ldbl_recs_rent.sqkt div 10,
								CONCAT(ldbl_recs_rent.sqkt div 10, '.', ldbl_recs_rent.sqkt mod 10)
							)
						) AS kitchen_area,

						IF(ldbl_recs_rent.fltbl_id > 1,
							IF (ldbl_recs_rent.fltbl_id = 1,
								'1',
								IF (ldbl_recs_rent.fltbl_id = '�',
									'4',
									IF (ldbl_recs_rent.fltbl_id = '�',
										'5',
										IF (ldbl_recs_rent.fltbl_id = '2�',
											'6',
											IF (ldbl_recs_rent.fltbl_id = '2�',
												'7',
												IF (ldbl_recs_rent.fltbl_id = '��',
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

						IF(ldbl_recs_rent.fltph_id>1,
							'1',
							'0'
						) AS phone,

						IF(ldbl_recs_rent.fltlv_id > 3,
							'2',
							IF(ldbl_recs_rent.fltlv_id = 2,
								'1',
								'0'
							)
						) AS lavatory,

						IF (ldbl_recs_rent.area = 1,
							'1',
							'0'
						) AS moscow,

						phones AS contact_phone,
						'1',
						ldbl_recs_rent.address_id,
						'2',
						ldbl_recs_rent.commission_agency,
						ldbl_recs_rent.commission_client,
						ldbl_recs_rent.fltrf_id,
						ldbl_recs_rent.flttv_id,
						ldbl_recs_rent.fltwh_id,
						ldbl_recs_rent.mobile_phone,
						ldbl_recs_rent.photos

						FROM  `ldbl_recs_rent`
						Left join ldbl_dic_bldtp ON ldbl_recs_rent.bldtp_id = ldbl_dic_bldtp.id
						";
						$result = sql_query($sql);

						// � ��� ����������?
						$this->eRror = ($result) ? '' : ' �� ������� ��������� ������� ������ �� ��������� ������ � ��������.';
					}

					// ������ ������������� �����
					foreach ($files AS $file) {
						unlink ($file_path."unpack/".$file);
					}
				}

				// ������ �����
				unlink ($in_path);
			}

			if (empty($this->eRror)) {
				$this->eRror = '����� ������� ��������.';
			}

	        return "<script>
	        	var err = parent.document.getElementById('error').innerHTML;
	            parent.document.getElementById('error').innerHTML = '".e($this->eRror)."' + err;
	        </script>";
		} else {
	        return "<script>
	        	var err = parent.document.getElementById('error').innerHTML;
	            parent.document.getElementById('error').innerHTML = '�� ������� ��������� �����. ' + err;
	        </script>";
		}
	}



}

$GLOBALS['rnt_objects_xls'] = &Registry::get('TRnt_objects_xls');

?>