<?php

// $Id: versions.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $

class TVersions extends TTable {

	var $name = "versions";
	var $table = "versions";

	######################

	function TVersions() {
		global $str;

		TTable::TTable();

		# �������� ���������
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
		'title'		=> array(
		'������ ������',
		'VERSIONS',
		),
		'uptime'		=> array(
		'����',
		'Date',
		),
		'datasize'		=> array(
		'������',
		'Size',
		),
		'empty'		=> array(
		'<i>��� ������ ������</i>',
		'<i>No texts available</i>',
		),
		'lang'		=> array(
		'����',
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

			# ����, ������� �����..
			$pfields = explode(", ",$pfields);
			foreach($pfields as $field) {
				$name = explode("fld[",$field) ;
				$name = explode("]",$name[1]);
				$fields[] = $name[0];
			}

			foreach ($data['rows'] as $key=>$val) {
				
				# �� ���������� � ������
				$flag = false;
				$changes = unserialize(gzuncompress($val['gzip']));
				foreach($changes as $changes_key=>$changes_value) {
					if(in_array($changes_key,$fields)) {
						# ���� ���� � ������ �����, �� ����������
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

		# ������ �������, ������� ���������� �������� �� ���������� ����
		$instance = get('instance', '', 'p');
		$ptable = get('ptable', '', 'p');
		$pfields = get('pfields', '', 'p');
		$pid = (int)get('pid', 0, 'p');
		$uptime = get('uptime', 0, 'p');
		$hash = get('hash', 0, 'p');

		# ����, ������� �����..
		$pfields = explode(", ",$pfields);
		foreach($pfields as $field) {
			$name = explode("fld[",$field) ;
			$name = explode("]",$name[1]);
			$fields[] = $name[0];
		}
		
		# �������� ������� ��������
		$row = sql_getRow('SELECT * FROM '.$ptable.' WHERE id='.$pid);

		# �������� ��� ���������
		$changes = sql_getRows('SELECT gzip FROM '.$this->table.' WHERE ptable="'.$ptable.'" AND pid='.$pid.' AND uptime >= '.$uptime.' ORDER BY uptime DESC');

		# �������� �������� ������� �������� ������� �� ��������...
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

		# $row - ������ ��������, ������� ���� �� �������� ����
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