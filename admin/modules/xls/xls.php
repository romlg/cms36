<?php
set_time_limit (0);
ini_set('memory_limit', '256M');

class TXls extends TTable {

    // название модуля
    var $name = 'xls';
    // название таблицы
    var $table_flat = 'flat_csv';
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
    
	var $_house_type = array(
			'?' => 0,
			'Б' => 1,
			'П' => 2,
			'К' => 3,
			'М' => 4,
			'С' => 5,
			'Э' => 6,
		);
	
    //-----------------------------------------

    function TXls() {
        global $actions, $str;

        // обязательно вызывать
        TTable::TTable();

        // строковые константы модуля
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
        'title'	=> array(
        'Загрузка данных',
        'Import',
        ),
        ));
    }

    //----------------------------------------------------
    function EditDownload(){
        $file = $_POST['file'];
        if (strpos($file, '@temp') !== false) $file = substr($file, strlen('@temp'));
        $type = substr($file, strrpos($file,'.')+1);
        if ($type != 'xls'){
            return "<script>alert('Расширение файла не поддерживается');</script>";
        }
        echo "<script>parent.stopLoad();parent.hideDownloadFrom();</script>";
        echo $this->getFile($file);
    }
    
    //----------------------------------------------------
    function EditSettings(){
		//Сохраняем массив обязательных полей
		$required = serialize ($_POST['required']);
		$percent = serialize ($_POST['percent']);

		//Будем записывать настройки в файл
		chdir ("../configs");		
		$handle = fopen("settings.txt", 'w');
		fwrite($handle, $required);
		fwrite($handle, "##");
		fwrite($handle, $percent);
		fclose($handle);		
		chdir ("../admin");
		
		return "<script>alert('Настройки сохранены');</script>";
    }
    
    //----------------------------------------------------
    function getFile($file){
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
        }
        
        echo "<script>var msg_div = parent.document.getElementById('msg_div');</script>"; ob_end_flush(); flush();

        //получаем заголовки колонок
        $this->_Headers = $this->_rows[$this->sRow - 1];
        $this->_cHeaders = count($this->_Headers);

        //создаем временную таблицу
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_flat (
  		`rooms` INT(11),
  		`metro_id` INT(11),
  		`distance` INT(11),
  		`distance_type` ENUM('foot','transport'),
  		`street` VARCHAR(255),
  		`storey` TINYINT(3),
  		`storeys_number` TINYINT(3),
  		`house_type` INT(11),
  		`total_area` FLOAT(5,1),
  		`living_area` FLOAT(5,1),
  		`kitchen_area` FLOAT(5,1),
  		`balcony` VARCHAR(16),
  		`price_rub` DOUBLE(15,2),
  		`price_dollar` DOUBLE(15,2),
  		`price_euro` DOUBLE(15,2))";
		sql_query($sql);
        
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_metrostations (
  		`id` INT(11),
  		`name` VARCHAR(255));";
		sql_query($sql);
		
		$sql = "INSERT INTO tmp_metrostations (name) SELECT name FROM flat_csv_metrostations";
        sql_query($sql);		
		sql_query("TRUNCATE TABLE `flat_csv_metrostations`");
		
		$empty = 0;
        for ($this->_cRow = $this->sRow; $this->_cRow <= $this->_numRows; $this->_cRow++){
        	unset($row);
        	
        	if ($this->_rows[$this->_cRow][1]!='' && $this->_rows[$this->_cRow][5]!='' && $this->_rows[$this->_cRow][7]!='') {
	        	$metro = (substr($this->_rows[$this->_cRow][2], -2)=='м.')?substr($this->_rows[$this->_cRow][2], 0, -3):$this->_rows[$this->_cRow][2];
				$metro_id = (int)sql_getValue ("SELECT id FROM `flat_csv_metrostations` WHERE name='$metro'");
				if (!$metro_id) $metro_id = sql_insert('flat_csv_metrostations', array('name'=>$metro));

				$house = explode("/", substr($this->_rows[$this->_cRow][5], 0, -1));
				$area = explode("/", $this->_rows[$this->_cRow][6]);
        		$row = array (
  					'rooms' => $this->_rows[$this->_cRow][1],
  					'metro_id' => $metro_id,
			  		'distance' => (int)substr($this->_rows[$this->_cRow][3], 0, -1),
  					'distance_type' => (substr($this->_rows[$this->_cRow][3], -1)=='п')?'foot':'transport',
			  		'street' => $this->_rows[$this->_cRow][4],
		  			'storey' => $house[0],
		  			'storeys_number' => $house[1],
	  				'house_type' => $this->_house_type[substr($this->_rows[$this->_cRow][5], -1)],
			  		'total_area' => (isset($area[0]))?$area[0]:0,
  					'living_area' => (isset($area[1]))?$area[1]:0,
		  			'kitchen_area' => (isset($area[2]))?$area[2]:0,
					'price_rub' => str_replace (" ", "", substr($this->_rows[$this->_cRow][7], 0,-1)),
		  			'price_dollar' => str_replace (" ", "", substr($this->_rows[$this->_cRow+1][7], 0,-1)),
	  				'price_euro' => str_replace (" ", "", $this->_rows[$this->_cRow+2][7]),
        		);
	        	$this->_cRow = $this->_cRow+2;

    	    	//Инсертим во временную таблицу
				$id = sql_insert('tmp_flat', $row);
				if (!is_int($id)) {
					$this->Error("insert into tmp_flat<br />".$id);
				}
        	}
        	else $empty++;
        }
		
		//Если все в порядке перемещаем данные в рабочую таблицу
		if (empty($this->eRror)) {
			sql_query("TRUNCATE TABLE `flat_csv`");
	        $sql = "INSERT INTO flat_csv (
				rooms,metro_id,distance,distance_type,street,storey,storeys_number,house_type,total_area,living_area,kitchen_area,price_rub,price_dollar,price_euro
			) 
			SELECT 
				rooms,metro_id,distance,distance_type,street,storey,storeys_number,house_type,total_area,living_area,kitchen_area,price_rub,price_dollar,price_euro
			FROM tmp_flat";
        	sql_query($sql);
		}
		else {
			sql_query("TRUNCATE TABLE `flat_csv_metrostations`");
	        $sql = "INSERT INTO flat_csv_metrostations (name) SELECT name FROM tmp_metrostations";
        	sql_query($sql);			
		}
        touch_cache('flat_csv');

//        if (empty($this->eRror)) $this->eRror = 'Успешно завершено!'.' пустых записей:'.$empty;
        if (empty($this->eRror)) $this->eRror = 'Успешно завершено!';
        
        $table_status = sql_getRow("SHOW TABLE STATUS LIKE 'flat_csv'");
		
        return "<script>
            parent.document.getElementById('msg_div').innerHTML = '';
            parent.document.getElementById('a1').innerHTML = '".$table_status['Update_time']."';
            parent.document.getElementById('a2').innerHTML = '".$table_status['Rows']."';
            parent.document.getElementById('error').innerHTML = '".e($this->eRror)."';
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

        $table_status = sql_getRow("SHOW TABLE STATUS LIKE 'flat_csv'");        
        $ret['update_time'] = $table_status['Update_time'];
        $ret['rows'] = $table_status['Rows'];
        
		//Читаем файл настроек
		chdir ("../configs");
		$filename = "settings.txt";
		$handle = fopen($filename, 'r');
		$contents = fread($handle, filesize($filename));
		fclose($handle);		
		chdir ("../admin");        
		$settings = explode("##", $contents);

		$ret['required'] = unserialize($settings[0]);
		$ret['percent'] = unserialize($settings[1]);        

        $this->AddStrings($ret);
        return $this->Parse($ret, $this->name.'.tmpl');
    }

    //----------------------------------------------------
    function Error($err) {
        echo "<script>parent.document.getElementById('error').innerHTML = '".e(str_replace(array("\r", "\n"), "", $err))."';</script>";
        ob_end_flush(); flush();
    }
    function Error2($err) {
        echo "<script>alert('".e(str_replace(array("\r", "\n"), "", $err))."');</script>";
        ob_end_flush(); flush();
        die();
    }
}

$GLOBALS['xls'] = &Registry::get('TXls');

?>