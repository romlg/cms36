<?php

// $Id: versions.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $

class TVersions extends TTable {

	var $name = "versions";
	var $table = "versions";

	######################

	function TVersions() {
		global $str;

		TTable::TTable();

		# языковые константы
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
		'title'		=> array(
		'ВЕРСИИ ТЕКСТА',
		'VERSIONS',
		),
		'uptime'		=> array(
		'Дата',
		'Date',
		),
		'datasize'		=> array(
		'Размер',
		'Size',
		),
		'empty'		=> array(
		'<i>Нет версий текста</i>',
		'<i>No texts available</i>',
		),
		'lang'		=> array(
		'Язык',
		'Language',
		),
		));
	}

	######################

	function Show() {
		$ptable = get('ptable');
		$pfields = get('pfields');
		$pid = (int)get('pid');

		$data['pid'] = get('pid');
		$data['instance'] = get('instance');
		$data['ptable'] = get('ptable');
		$data['close'] = $this->str('close');
		$data['pfields'] = $pfields;

		if ($ptable && $pid) {
			$data['rows'] = sql_getRows("SELECT UNIX_TIMESTAMP(uptime) as _uptime, hash, uptime, pid, gzip, lang FROM $this->table WHERE ptable='$ptable' AND pid=$pid AND lang='".lang()."' ORDER BY uptime desc");
		}
		$this->AddStrings($data);

		if (!empty($data['rows'])) {

			# поля, которые нужны..
			$pfields = explode(", ",$pfields);
			foreach($pfields as $field) {
				$name = explode("fld[",$field) ;
				$name = explode("]",$name[1]);
				$fields[] = $name[0];
			}

			foreach ($data['rows'] as $key=>$val) {
				
				# не отображать в списке
				$flag = false;
				$changes = unserialize(gzuncompress($val['gzip']));
				foreach($changes as $changes_key=>$changes_value) {
					if(in_array($changes_key,$fields)) {
						# если были в списке полей, то отображать
						$flag=true;
					}
				}
				
				if($flag) {
					$data['rows'][$key]['_uptime'] = $this->Age($val['_uptime']);
					$data['rows'][$key]['hash'] = $val['hash'];
					$data['rows'][$key]['lang'] = $this->str($val['lang']);
				}
				else {
					unset($data['rows'][$key]);
				}
				$flag = false;
			}
		}

		if (empty($data['rows'])) $data['empty'] = $this->str('empty');

		$GLOBALS['title'] = $this->str('title');

		return $this->Parse($data, 'versions.show.tmpl');
	}

	######################

	function GetText() {

		# выдача скрипта, который возвращает значение из модального окна
		$instance = get('instance', '', 'p');
		$ptable = get('ptable', '', 'p');
		$pfields = get('pfields', '', 'p');
		$pid = (int)get('pid', 0, 'p');
		$uptime = get('uptime', 0, 'p');
		$hash = get('hash', 0, 'p');

		# поля, которые нужны..
		$pfields = explode(", ",$pfields);
		foreach($pfields as $field) {
			$name = explode("fld[",$field) ;
			$name = explode("]",$name[1]);
			$fields[] = $name[0];
		}
		
		# выбираем текущее значение
		$row = sql_getRow('SELECT * FROM '.$ptable.' WHERE id='.$pid);

		# выбираем все изменения
		$changes = sql_getRows('SELECT gzip FROM '.$this->table.' WHERE ptable="'.$ptable.'" AND pid='.$pid.' AND uptime >= '.$uptime.' ORDER BY uptime DESC');

		# начинаем изменять текущие значения проходя по массивам...
		foreach($changes as $value) {
			$a = unserialize(gzuncompress($value));
			$row = array_merge($row,$a);
		}

		$keys = array();
		$values = array();
		foreach($row as $key=>$value) {
			if(!in_array($key,$fields)) unset($row[$key]);
			else {
				$keys[] = "'".$key."'";
				$values[] = "'".$value."'";
			}
		}

		# $row - массив значений, которые были на заданную дату
		$script = '<script type="text/javascript">'."\n";
		$script .= "function data() {\n";
		$script .= "  this.key = new Array(".implode(",",$keys).");\n";
		$script .= "  this.value = new Array(".implode(",",$values).");\n";
		$script .= "}\n";
		$script .= "data = new data();";
		$script .= 'window.returnValue = data;'."\n";
		$script .= 'window.close();';
		$script .= '</script>';
		return $script;		
	}

	######################

	function Info() {
		return array(
		'version'	=> get_revision('$Revision: 1.1 $'),
		'checked'	=> 0,
		'disabled'	=> 0,
		'type'		=> 'checkbox',
		);
	}

	######################
}

$GLOBALS['versions'] = & Registry::get('TVersions');

?>