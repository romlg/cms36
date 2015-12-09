<?php

 class TProductsEd extends BaseEd {
 	
 	# название модуля
 	var $name     = 'products';
 	# название таблицы модуля
	var $table    = 'products';
	# список элемов в редаторе
	var $tabs     = array();
	# спиоск экшенов, доступных в редакторе
	var $actions  = array();
	
	//-------------------------------------------------------
	
 	# конструктор
 	function TProductsEd() {
 		global $str;
	    if (!empty($_GET['id'])){
                $temp = sql_getValue('SELECT name FROM products WHERE id='.$_GET['id']);
        }
        else { $temp = '';}

 		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'basic_caption'	=> array('Компоненты','Components'),
			'basic_tab'		=> array($temp,'Components'),
            'title'         => array($temp,'Components'),
		));
		
		$this->setup();
	}
	
	//-------------------------------------------------------
	
	function getDifTabs(){
		$id = get('id', 0, 'gp');
		$elems = &$GLOBALS['cfg']['types'][100]['product']['elements'];           
    	$sql = "SELECT solution_id FROM products WHERE id = ".$id;
    	$sol_type = sql_getValue($sql);
        
        if (!isset($_GET['frame']) || $_GET['frame'] != 'cnt'){
    		session_start();
    		$_SESSION['elements'][$id] = $sol_type;  
        	session_write_close();                
        } else {
			session_start();
			if ((!$_SESSION['elements'][$id] && $sol_type) || ($_SESSION['elements'][$id] && !$sol_type)){
            	echo "
            		<script>
            			parent.location.reload();
            		</script>
            	";
			}
			session_write_close();
        }
        
        if ($sol_type && !in_array('elem_composition', $elems)){
           	$elems[] = 'elem_composition';
           	foreach ($elems as $k=>$v){
           		if ($v == 'elem_params'){
           			unset($elems[$k]);
           		}
           	}
        }
        return $elems;

	}
		
	//-------------------------------------------------------
	
 }
 
 Registry::set('object_editor_submodule', Registry::get('TProductsEd'));
?>