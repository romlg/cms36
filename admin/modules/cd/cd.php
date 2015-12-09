<?
//include_once 'modules/tree/treecheck.php';

class TCd extends TTable/* extends TTreeCheck*/{
	
	//Местные переменные
	var $name = "cd";
	var $table = "tree";
	var $cd_table = "tree_cd";
	var $proc = null;
	var $backup_dir = "backup";
	

	######################
	
    function TCd(){
        //parent::TTreeCheck();
        global $str;
        $str['tcd'] = array(
			'title' => array(
				'CD-презентация',
				'CD-presentation',
			),
		);
    }

    
    function Show() {
		//Проверка на существование файла процесса и взависимости от этого грузить соответствующий шаблон
		$ret = array();
		if(is_file("modules/cd/core/info.txt")){
			//Если существует - значит презентация создается, выводим статус
			$ret=$this->getStatVars();
			return $this->Parse($ret, 'stat.tmpl');
		}else{
			//Если не существует - значит презентация не создается, выводим главный интерфейс
			$ret=$this->getMainVars();
			$ret['server']=$_SERVER['HTTP_HOST'];
			$ret['backup_dir']=$this->backup_dir;
			return $this->Parse($ret, 'cd.tmpl');			
		}
	}

	function getStatVars(){
		$query=mysql_query("select * from $this->cd_table");
		$i=0;
		$ret['itemz_count']=mysql_num_rows($query);
		$ret['itemz_start']=mysql_num_rows(mysql_query("select * from $this->cd_table where uptime=0"));
		while($str=mysql_fetch_array($query)){
			$ret['itemz'][$i][0]=$str['id'];
			$ret['itemz'][$i][1]=$str['uptime'];
			$ret['itemz'][$i][2]=str_replace("'",'"',name_path_by_id($str['id']));
			$i++;
		}
		return $ret;
	}
	
	//Переменные для шаблона главного интерфейса
	function getMainVars(){
		$ret=array();
		$cddir=opendir($this->backup_dir.'/presentations');
		$files = array();
		while($file=readdir($cddir)){
			if(($file!='.')&&($file!='..')){
				$files[] = $file;
			}
		}
		sort($files, SORT_STRING);
		foreach ($files as $k=>$v)
			$ret['old_cd'][]=Array($v,'от '.' '.substr($v,6,2).'.'.substr($v,4,2).'.'.substr($v,0,4));
		
		$ret['tree']=$this->draw_tree();
		return $ret;
	}
	
	//Функция вывода дерева
	function draw_tree(){
		global $tree_result,$ids;
		$root_query=mysql_query("select * ".(LANG_SELECT ? ", name_".lang()." as name" : "")." from `$this->table` ORDER BY priority");
		$id=0;
		while ($result=mysql_fetch_array($root_query)){
			$tree_result[]=$result;
			$ids[$result['id']]=$id;
			$id++;	
		}
		
		echo '<b>Выберите разделы, которые войдут в презентацию:</b><p>';
		$ret='';
		$pid_query=mysql_query("select id from tree where id=pid");
		while ($pid_result=mysql_fetch_array($pid_query)){
			echo draw_line($pid_result['id'],false,$this->cd_table);
		}
		return $ret;
	}
	
}

$GLOBALS['cd'] = &Registry::get('TCd');



		
function result_id_by_snap($snap){
	global $ids;
	global $tree_result;
	foreach ($tree_result as $node) {
		if (($node['pid']==$snap)&&($node['id']!=$node['pid'])){
			$result[]=$ids[$node['id']];
		}
	}
	if (isset($result)){
		return $result;
	}
}

function draw_line($id,$line,$cd_table){
	global $ids;
	global $tree_result;
	global $site_domains;
	$ret='';
	$result_id=$ids[$id];
	$snapped=result_id_by_snap($tree_result[$result_id]['id']);
	
	if($tree_result[$result_id]['visible']==1){
		$style="color: #444444; text-decoration: none; font-weight: bold;";
	}else{
		$style="color: #aaaaaa; text-decoration: none; font-weight: bold;";
	}
		
	$query_check=mysql_query("select * from `$cd_table` where id=$id");
	if ($query_check && mysql_num_rows($query_check)){
		$checkbox=" checked ";
	}else {
		$checkbox=" ";
	}

	
	switch ($tree_result[$result_id]['type']){
		case "catalog":
			$img="/admin/images/icons/icon.orders.gif";
			break;
		case "text":
			$img="/admin/images/icons/folder.gif";
			break;
		case "module":
			$img="/admin/images/icons/icon.module.gif";
			break;
		case "home":
			$img="/admin/images/icons/icon.domik.gif";
			break;
	}
	
if($line){
		echo "<div style='/*background: url(/admin/modules/cd/img/line.gif) no-repeat; */padding-left: 8px; height: 30px;'>\n";	
	}else{
		echo "<div style='/*background: url(/admin/modules/cd/img/white.gif) no-repeat; */padding-left: 10px; height: 30px;'>\n";	
	}
	
	if (sizeof($snapped)>0){
		echo "<a id='plus_minus_$id' style='/*background: url(/admin/images/icons/white.gif);*/ ' style='$style' href=\"javascript:toggle_visibility('container_$id');toggle_plus_minus('plus_minus_$id')\">";
		echo "+&nbsp;";
		echo "</a>";
	}else{
		echo "<span>&nbsp;&nbsp;&nbsp;</span>\n";
	}

	echo "<input type='checkbox' id='cb_node_".$tree_result[$result_id]['id']."' name=$id $checkbox>\n";
	
	if ($tree_result[$result_id]['id'] == $tree_result[$result_id]['pid'])
			$tree_result[$result_id]['name'] = getSiteByRootID($tree_result[$result_id]['root_id'])." - ".$tree_result[$result_id]['name'];

	if (sizeof($snapped)>0){
		echo "<a style='$style' href=\"javascript:toggle_visibility('container_$id');toggle_plus_minus('plus_minus_$id')\"><img src='$img' border='0' />".$tree_result[$result_id]['name']."</a>\n";
	}else{
		echo "<a style='$style' href=\"javascript:toggle_none()\"><img src='$img' border='0' />".$tree_result[$result_id]['name']."</a>\n";
	}
	
	echo "</div>\n\n";
	
	if (sizeof($snapped)>0){
		echo "<div class='container' id='container_$id' style='display: none;'>\n";
		foreach ($snapped as $child){
			draw_line($tree_result[$child]['id'],true,$cd_table);	
		}
		echo "</div>\n\n";
	}
	//return $ret;
}

function name_path_by_id($id){
	$path=array();
	$query_root=mysql_query("select id from tree where id=pid");
	while($str=mysql_fetch_array($query_root)){
		
	}
	$ended=false;
	while(!$ended){
		$query=mysql_query("select * ".(LANG_SELECT ? ", name_".lang()." as name" : "")." from `tree` where id=$id");
		$result=mysql_fetch_array($query);
		$id=$result['pid'];
		array_unshift($path,$result['name']);
		if($id==98){
			array_unshift($path,'Каталог');
		}
		if($result['id']==$result['pid']){
			$ended=1;
		}
	}
	return implode('-->',$path);
}

?>