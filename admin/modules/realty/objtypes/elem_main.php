<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
define ('USE_ED_VERSION', '1.0.2');

class TMainElement extends TElems {

	######################
    var $elem_name  = "elem_main";
	var $elem_table = "obj_types";
	var $elem_type  = "single";
	var $elem_str = array(                       //строковые константы
		'name'			=> array('Название типа',		'Title',),
		'pid'			=> array('Принадлежит',			'Put into type',),
		'belong'		=> array('Вид сделки',			'Transaction',),
		'all'			=> array('нет',					'none'),
	);
	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns'		=> array(
			'id'		=> array(
				'type'	=> 'hidden',
			),
			'name'		=> array(
				'type'	=>'text',
			),
			'priority'	=> array(
				'type'	=>'hidden',
			),
			'belong'=> array(
				'type'	=> 'select',
				'func'	=> 'getBelong',
				'onChange'	=> 'changeBelong(this.value)',
			),
			'pid'=> array(
				'type'	=> 'select',
				'option'=> array(),
			),
		),
		'id_field'	=> 'id',
		'title'		=> 'Тип',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $prefix = "----";
	var $script = "
		{literal}
		window.onload = function(){
			changeBelong(document.getElementById('fld[belong]').value);
		}
	
		function changeBelong(num){
			url = '/admin/page.php?page=objtypes&do=editgettypes&belong='+num+'&id='+document.forms.editform.elements['id'].value;
			loadXMLDoc(url);
		}

		function loadXMLDoc(url) {
			if (window.XMLHttpRequest) {
				req = new XMLHttpRequest();
				req.onreadystatechange = processReqChange;
				req.open(\"GET\", url, true);
				req.send(null);
			} else if (window.ActiveXObject) {
				req = new ActiveXObject(\"Microsoft.XMLHTTP\");
				if (req) {
					req.onreadystatechange = processReqChange;
					req.open(\"GET\", url, true);
					req.send();
				}
			}
		}

		function cleanNode(dest)
		{
  			while (dest.firstChild)
    			dest.removeChild(dest.firstChild);
		}
		
		function processReqChange(){
			if (req.readyState == 4) {
				if (req.status == 200) {
					response = req.responseXML.documentElement;
					items = response.getElementsByTagName('item');
					if (items.length == 0) { hideRow('tr_fld[pid]');}
					else {
						obj = document.getElementById('fld[pid]');
						cleanNode(obj);
		
						for (i=0; i<items.length; i++){
							var option = document.createElement('<OPTION>');
							option.value = items[i].childNodes(0).text;
							option.innerText = items[i].childNodes(1).text;
							if (items[i].childNodes(2).text == '1') option.selected='selected';
							obj.appendChild(option);
						}
				   }
				} else {
					alert('There was a problem retrieving the XML data:' + req.statusText);
				}
			}
		}
{/literal}

";

	#####################################
	
    function getBelong(){
    	return sql_getRows('SELECT id, name FROM obj_transaction', true);
    }

	function ElemEdit($id, $row, $elem_id = '0') {
		if (!$id) {
			if ($row['pid'] != 'NULL')
				$sql = 'INSERT INTO '.$this->elem_table.' (name, belong, pid) VALUES ("'.$row['name'].'", '.$row['belong'].', '.$row['pid'].')';
			else 
				$sql = 'INSERT INTO '.$this->elem_table.' (name, belong) VALUES ("'.$row['name'].'", '.$row['belong'].')';
		} else {
			$sql = 'UPDATE '.$this->elem_table.' SET name="'.$row['name'].'", belong='.$row['belong'].', pid='.$row['pid'].' WHERE id='.$id;
		}
		sql_query($sql);
		$err = mysql_error();
		if (!empty($err)) return $err;
		else return 1;
	}    
}
?>