<?php

/* $Id: csv_tools.php,v 1.1 2009-02-18 13:09:08 konovalova Exp $
 */
 require ('ss_zip.class.php');
class TCSVTools extends TTable {

	var $name = 'csv_tools';
	var $table = 'csv_tools';

	########################

	function TCSVTools() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'download' => array(
				'Скачать',
				'Download',
				'link'	=> 'cnt.tmpcsv_tools.location=\'page.php?page=csv_tools&do=download&id=\'+cnt.ID+\'\'',
				'img' 	=> 'icon.cascade.gif',
				'display'	=> 'none',
			),
			'upload' => array(
				'Загрузить',
				'upload',
				'link'	=> 'cnt.upload()',
				'img' 	=> 'icon.download.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'CSV импорт/экспорт ',
				'CSV import/export ',
			),
			'table'	=> array(
				'Название раздела',
				'Name',
			),
			'upload_title'	=> array(
				'Загрузка CSV файла в таблицу ',
				'Upload CSV file into the table ',
			),
			'upload'	=> array(
				'Выберите файл',
				'Slect file',
			),


		));
	}
	
	########################
	/*
	function EditForm(){
		$id = (int)get('id');
		if ($id) $row = $this->GetRow($id);
		$GLOBALS['title'] = $this->str('title_one');
		$row['id'] = $id;
		$row['visible_checked'] = $row['visible']?'checked':(!$id?'checked':'');		
		$trgt = get('trgt');
		$row['target'] = $trgt?$trgt:'tmp'.$this->name;
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}      */

	########################
/*
	function Edit() {
		$res = $this->Commit(array('name'), true);
		if (is_int($res)) return "<script>alert('".$this->str('saved')."'); window.parent.location.reload();</script>";
		return $this->Error($res);
	}
			*/
	########################

	function Show() {
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('table.lib'));
		$ret['thisname'] = $this->name;

		$ret['table'] = table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'table_name',
					'display'	=> 'table',
				),
			),
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'click'	=> 'ID=cb.value',
			//'_sql'=>1,
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');

	}

	######################
	function get_config(){
		$id = mysql_escape_string(get('id',0,'gp'));
		if(!$id) return;
		//генерим массив необходимых полей и названия таблицы
		$config = mysql_query('SELECT * FROM csv_tools WHERE id='.$id);
		$config = mysql_fetch_assoc($config);
		if(!$config) return;

		if(empty($config['fields_request'])){
			$config['fields_request'] = "*";
		}
		$config['fields_request'] = explode(",", $config['fields_request']);

		if(empty($config['fields_ignored'])){
			$config['fields_ignored'] = null;
		}else{
			$config['fields_ignored'] = explode(",", $config['fields_ignored']);
		}

		if ($config['fields_request'][0] == '*') { // если поля не перечислены , и задан SKIP делаем полный список
			foreach($this->getRows('DESCRIBE '.$config['table_base']) AS $field){
				if(!$config['fields_ignored'] || !in_array($field['Field'], $config['fields_ignored'])){
					$fields_request[] = $field['Field'];
				}
			}
			$config['fields_request'] = $fields_request;
		}

		if(empty($config['keys'])){
			if(in_array('id', $config['fields_request']))
				$config['keys'] = 'id';
			else
				$config['keys'] = current($config['fields_request']);
		}
		$config['keys'] = explode(",", $config['keys']);
		return $config;
	}

	########################
	function download(){
		$config = $this->get_config();
		if(!$config) return "<SCRIPT>alert('".$this->str('err_no_config')."')</SCRIPT>";
		$rows = mysql_query('SELECT '.implode(',', $config['fields_request']).' FROM  '.$config['table_base']);
//pr('SELECT '.implode(',', $config['fields_request']).' FROM  '.$config['table_base']);
		if (!$rows) return "<SCRIPT>alert(\"Error: ".mysql_error()."\")</SCRIPT>";

		require (module('Bs_CsvUtil.class.php'));
		$Bs_CsvUtil = & new Bs_CsvUtil();

		// первой строкой выдаем заголовки таблицы
		$data=$Bs_CsvUtil->arrayToCsvString($config['fields_request'], ';', 'none', TRUE)."\n";
		// теперь собственно данные построчно:
		while ($row = mysql_fetch_assoc($rows)) {
			$csv_row = $Bs_CsvUtil->arrayToCsvString($row, ';', $trim='none', TRUE);
			/*
			$comma = '';$csv_row = '';
			foreach($row AS $val){
				$csv_row .= $comma.'"'.$val.'"';
				$comma = '; ';
			}*/
			//добавляем возможность скачивания zip архива
			//(strtr($csv_row, array("\r" => '\r', "\n" => '\n'))."\n");
			$data .= strtr($csv_row, array("\r" => '\r', "\n" => '\n'))."\n";
		}
		// new empty archive with compression level 6
		$zip= new ss_zip('',6);
		$zip->add_data($config['table_base'].'.csv', $data);
		$zip->save('modules/csv_tools/'.$config['table_base'].".zip",'d');
	 }
############################## загрузка CSV  №№№№№№№№№№№№№№№№№№№№№№

	function show_upload_form(){
		$id = mysql_escape_string(get('id'));
		if(!$id) return "<SCRIPT>alert('".$this->str('err_no_id')."')</SCRIPT>";
		$config = $this->get_config();

		$row = array('upload', 'upload_submit', 'id'=>$id, 'table'=>$config['table_base']);
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.upload_form.tmpl');
	}

	function upload(){
		if(!$_FILES['file'])
			return '<script>alert("Файл потеряли")</script>';
		if(!($config = $this->get_config()))
			return "<SCRIPT>alert('".$this->str('err_no_config')."')</SCRIPT>";

		require (module('Bs_CsvUtil.class.php'));
		$Bs_CsvUtil = & new Bs_CsvUtil();
		$fInfo = pathinfo($_FILES['file']['name']);

		# gzip decode
		if ($fInfo["extension"]=="gz") {
			$zp = gzopen($_FILES['file']["tmp_name"], "rb");
			if ($zp) {
					while ($buf = gzread($zp, 65535)) $data.= $buf;
					gzclose($zp);
			}
			else {
				$err = "# err: gzopen";
			}
			$data = $Bs_CsvUtil->csvStringToArray($data, ';', 'both', TRUE, FALSE, TRUE);
		}
		elseif ($fInfo["extension"]=="zip" && @function_exists("zip_open")) {

			$zip = zip_open($_FILES['file']["tmp_name"]);

			if ($zip) {
			   //	while (
			   // читаем только первый файл в архиве
					$zip_entry = zip_read($zip);//) {
					if (zip_entry_open($zip, $zip_entry, "r")) {
						$data = zip_entry_read($zip_entry,
						zip_entry_filesize($zip_entry));
						zip_entry_close($zip_entry);
					}
				//}
				zip_close($zip);
			}
		   $data = $Bs_CsvUtil->csvStringToArray($data, ';', 'both', TRUE, FALSE, TRUE);
		}
		else {
			$data = $Bs_CsvUtil->csvFileToArray($_FILES['file']['tmp_name'], ';', 'both', TRUE, FALSE, TRUE);
		}
		if(!count($data)) return '<script>alert("Пустой файл")</script>';

		$errs = 0;
		if (count($data[count($data)-1])==1){unset($data[count($data)-1]);};
		foreach ($data AS $i=>$row){
			if(count($row)==count($config['fields_request'])){

				// готовим WHERE
				$where  = array();
				foreach($config['keys'] AS $n=>$w){
					$where[] = $w.'='.'"'.$row[$n].'"';
				}
				$where  = ' WHERE '.implode(' AND ', $where);

				$n = 0; $comma = ''; $sql ='';
				foreach ($config['fields_request'] AS $field){
					$value = $row[$n]=='"'?'':strtr($row[$n], array('\r' => "\r", '\n' => "\n"));
					$_row[$field] = $value;
					$sql.= $comma.$field."='$value'";
					$n++; $comma = ', ';
				}

				//echo "SELECT ".implode(',',$config['keys'])." FROM ".$config['table_base'].$where.'<br>';
				if($this->getrow("SELECT ".implode(',',$config['keys'])." FROM ".$config['table_base'].$where))
					$sql = 'UPDATE '.$config['table_base'].' SET '.$sql.$where;
				else
					$sql = 'INSERT '.$config['table_base'].' SET '.$sql;
				//echo $sql."<br>";

				if($res = my_query($sql)){
					//echo "$i ";
				}else{
					echo "<br>строка ".($i+1)." ошибка: ".mysql_error().'<br>';
					echo 'SQL was:'. $sql.'<br>';
					$errs++;
				}

			}else{
				 echo '<br>Неправильная строка '.($i+1). ' '.htmlspecialchars(implode(' ', $row));
				 $errs++;
			}
		}
		echo "<br>---------------------------------------<br>
		Обработано ".($i+1)." строк, из них неудачно: $errs";
		unlink($_FILES['file']['tmp_name']);
		touch_cache($config['table_base']);
	}

}

$GLOBALS['csv_tools'] = & Registry::get('TCSVTools');

?>