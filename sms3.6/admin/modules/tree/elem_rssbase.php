<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
define ('USE_ED_VERSION', '1.0.2');
class TRssbaseElement extends TElems {

	######################
	var $elem_name  = "rss";  		
	var $elem_table = "rss_properties";               
	var $elem_type  = "single";
	var $elem_str = array(                  
		'encoding'	=> array('Кодировка',		  'Encoding'),
		'about'		=> array('Адрес',		  'about'),
		'title'		=> array('Заголовок',		  'title'),
		'description'	=> array('Описание',		  'description'),
		'image_link'	=> array('Ссылка на изображение',		  'image_link'),
		'category'		=> array('Категория (только rss 2.0)',		  'category'),
		'cache'			=> array('Кэш (только rss 2.0)',		  'cache'),
		'publisher'		=> array('Издатель',		  'publisher'),
		'creator'		=> array('Создатель',		  'creator'),
		'copyright'		=> array('Копирайт',		  'copyright'),
		'coverage'		=> array('Информация для копирайта',		  'coverage'),
		'contributor'	=> array('Распространитель',		  'contributor'),
		'period'		=> array('Период',		  'period'),
		'frequency'		=> array('Частота',		  'frequency'),
		'version'		=> array('Версия',		  'version'),
		
		'hourly'	=> array('Каждый час',		  'hourly'),
		'daily'		=> array('Каждый день',		  'daily'),
		'weekly'	=> array('Каждую неделю',		  'weekly'),
	);
	//поля для выборки из базы элема
   
	var $elem_fields = array(
	  'columns' => array(
	  	'version'=>array(
			'type'  => 'select',
			'option'  => array(
				'0.91'=>'0.91',
				'1.0'=>'1.0',
				'2.0'=>'2.0',
			),
		),
		'encoding'=>array(
			'type'  => 'select',
			'func'  => 'get_encodings',
		),
		'about'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'title'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'description'=>array(
			'type'  => 'textarea',
			'rows'  => '5',
			'cols'	=> '40',
		),
		'image_link'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'category'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'cache'=>array(
			'type'  => 'text',
			'size'  => '10',
		),
		'publisher'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'contributor'=>array(
			'type'  => 'text',
			'size'  => '50',
		),	
		'creator'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'copyright'=>array(
			'type'  => 'text',
			'size'  => '50',
		),
		'coverage'=>array(
			'type'  => 'textarea',
			'rows'  => '5',
			'cols'	=> '40',
		),
		'period'=>array(
			'type'  => 'select',
			'func'  => 'get_periods',
		),
		'frequency'=>array(
			'type'  => 'text',
			'size'  => '10',
		),
	  ),
	  'id_field' => 'pid',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script;

	//----------------------------------------------------------------------------------
	
	function get_periods(){
		$periods = array(
			'hourly' => $this->str('hourly'), 
			'daily' => $this->str('daily'),  
			'weekly' => $this->str('weekly'), 
		);		
		return $periods;
	}
	
	//----------------------------------------------------------------------------------
	
	function get_encodings(){
		$encodings = array(
			 "X-MAC-ARABIC" => "Arabic (Macintosh)",                   
			 "windows-1256" => "Arabic (Windows)",                     
			 "iso-8859-2" => "Central European (ISO-8859-2)",          
			 "X-MAC-CENTRALEURROMAN" => "Central European (MacCE)",    
			 "windows-1250" => "Central European (Windows-1250)",      
			 "iso-8859-5" => "Cyrillic (ISO-8859-5)",                  
			 "KOI8-R" => "Cyrillic (KOI8-R)",                          
			 "x-mac-cyrillic" => "Cyrillic (MacCyrillic)",             
			 "windows-1251" => "Cyrillic (Windows-1251)",              
			 "iso-8859-7" => "Greek (ISO-8859-7)",                     
			 "x-mac-greek" => "Greek (MacGreek)",                      
			 "windows-1253" => "Greek (Windows-1253)",                  
			 "X-MAC-HEBREW" => "Hebrew (Macintosh)",                    
			 "windows-1255" => "Hebrew (Windows)",      
			 "Shift_JIS" => "Japanese (Shift_JIS)",     
			 "EUC-JP" => "Japanese (EUC)",              
			 "ISO-2022-JP" => "Japanese (JIS)",         
			 "EUC-KR" => "Korean (EUC-KR)",             
			 "gb2312" => "Simplified Chinese (gb2312)", 
			 "big5" => "Traditional Chinese (big5)",    
			 "X-MAC-THAI" => "Thai (Macintosh)",        
			 "Windows" => "Thai (Windows)",             
			 "iso-8859-5" => "Turkish (Latin5)",        
			 "X-MAC-TURKISH" => "Turkish (Macintosh)",  
			 "windows-1254" => "Turkish (Windows)",     
			 "utf-8" => "UTF-8",                       
			 "iso-8859-1" => "Western (Latin1)",       
			 "macintosh" => "Western (Macintosh)",      
			 "windows-1252" =>  "Western (Windows 1252)"
		);
		return $encodings;
	}
	########################
	
}
?>