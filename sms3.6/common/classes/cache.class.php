<?

class Cache{
	
	function Cache(){
		
		//if (CACHE_DATA === true){
			require_once(common_class('cache/data_cache'));
			//подключем alias
			require_once(common_lib('data_cache'));
	//	} 
		//if (CACHE_PAGE === true){
			require_once(common_class('cache/page_cache'));
			//подключем alias
			//require_once(common_lib('page_cache'));
		//}
		//if (CACHE_BLOCKS === true){
			require_once(common_class('cache/block_cache'));
			//подключем alias
			//require_once(common_lib('block_cache'));
		//}
	}
}	
?>