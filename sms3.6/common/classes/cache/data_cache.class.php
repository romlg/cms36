<?

class TDataCache{
	
	var $lifetime = CACHE_DATA_LIFETIME;
	var $cacheDir = CACHE_DATA_DIR;
	var $sesId = '';
	var $enabling = true;
	var $curCacheDir = '';
	var $fileLocking = CACHE_DATA_FILE_LOCKING;
	var $readControl = CACHE_DATA_READ_CONTROL;
	var $readControlType = CACHE_DATA_READ_CONTROL_TYPE;//'crc32';

	//--------------------------------------------------------------------------

	function TDataCache(){
		//проверяем, есть ли у нас сессия, елси нет, то папка для кэша глобальная
		if (isset($_COOKIE[session_name()])){
			 $this->sesId = $_COOKIE[session_name()];
		} else if (isset($_COOKIE['sid'])){
			$this->sesId = $_COOKIE['sid'];
		} else {
			$this->sesId = '';
		}

		if (!$this->_setup()) $this->enabling = false;
		if (rand(1,8) == 2) $this->clear();		
	}
	
	//--------------------------------------------------------------------------
	
	//проверяет наличие переменной в кэше
	function test($id, $global = false){	
		//pr($id);	
		$file = $this->_file($id, $global);
		//pr($file);
		if (@file_exists($file)) {
            $filemtime = @filemtime($file);
            $refresh = $this->_refreshTime();
            if (is_null($refresh)) {
                return $filemtime;
            }
            if ($filemtime > $refresh) {
                return $filemtime;
            }
        }
        return false;
	}
	
	//--------------------------------------------------------------------------
	
	//проверяет наличие переменной в кэше
	function table_test($id, $tables, $global = false){	
		if ($time = $this->test($id, $global)){
			
			foreach ($tables as $k=>$table){
				$file = PATH_CACHE_TABLES."/.".$table;
				if (!is_file($file)) $file = PATH_CACHE_TABLES."/.cache";

				if (is_file($file)){
					if (filemtime($file) > $time) return false;
				} else {
					return false;
				}
			}
			return true;
		}
        return false;
	}
	
	//--------------------------------------------------------------------------
	
	function save($id, $data, $global = false){
		if (!$this->enabling) return false;
		clearstatcache();
		$data = serialize($data);
		
        $file = $this->_file($id, $global);
		$count = 30;
        while (1 == 1 && $count > 0) {
	        $fp = fopen($file, "wb");	              
	        if ($fp) {
	            // we can open the file, so the directory structure is ok
	            if ($this->fileLocking) @flock($fp, LOCK_EX);
	            if ($this->readControl) {
	                @fwrite($fp, $this->_hash($data, $this->readControlType), 32);
	            }
	            $len = strlen($data);
	            @fwrite($fp, $data, $len);
	            if ($this->fileLocking) @flock($fp, LOCK_UN);
	            @fclose($fp);
	            $result = true;
	            break;
	        }            
	        $count--;
		}
        return $result;
	}
	
	//--------------------------------------------------------------------------
	
	function get($id, $global = false){
		if (!$this->enabling) return false;
		
		clearstatcache();
        $file = $this->_file($id, $global);

        if (is_null($this->lifetime)) {
            if (!@file_exists($file)) return false;
        } else {
	        if (!($this->test($id, $global))) {
	            // The cache is not hit !
	            return false;
	        }
        }
        // There is an available cache file !
        $fp = @fopen($file, 'rb');
        if (!$fp) return false;
        if ($this->fileLocking) @flock($fp, LOCK_SH);
        $length = @filesize($file);
        $mqr = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        if ($this->readControl) {
            $hashControl = @fread($fp, 32);
            $length = $length - 32;
        } 
        if ($length) {
            $data = @fread($fp, $length);
        } else {
            $data = '';
        }
        set_magic_quotes_runtime($mqr);
        if ($this->fileLocking) @flock($fp, LOCK_UN);
        @fclose($fp);
		if ($this->readControl) {
            $hashData = $this->_hash($data, $this->readControlType);
		    if ($hashData != $hashControl) {
                // Problem detected by the read control !
                log_notice('Ошибка доступа к кэшу данных');
                $this->_remove($file);
		        return false;    
            }
        }
        return unserialize($data);
	}
	
	//--------------------------------------------------------------------------
	//просматривает папку с сессиями и очищает все папки в кэше, для которых нет сессии
	function clear(){
		if (!$this->enabling) return false;	
		return rmTempDir($this->cacheDir);
	}
	
	//--------------------------------------------------------------------------
	
	function _setup(){
		if (!is_dir($this->cacheDir)){
			log_error('Папка "'.$this->cacheDir.'" кэша данных не создана');
			return false;
		} 	
		$this->curCacheDir = $this->cacheDir . "/" . $this->sesId;
		if (!is_dir($this->curCacheDir)){
			if (!mkdir($this->curCacheDir, 0775)) return false;
		}			
		return true;	
	}
	
	//--------------------------------------------------------------------------
	//возвращает название файла с кэшем для данного id
	function _file($id, $global){
		if (!$this->enabling) return false;

		$curCacheDir = $this->curCacheDir;
		if (substr($curCacheDir, -1) == "/") $curCacheDir = substr($curCacheDir, 0, -1);
		
		$curCacheDir = explode("/", $curCacheDir);
		if ($global && $this->sesId) array_pop($curCacheDir);
		
			
		return implode("/", $curCacheDir) . "/" . $id;		
	}
	
	//--------------------------------------------------------------------------

	function _remove($id, $global = false){
		if (!$this->enabling) return false;
		$file = $this->_file($id, $global);
		if (!unlink($file)){
			log_notice('Ошибка удаления файла кэша данных "'.$id.'"');
			return false;
		}
	}
		
	//--------------------------------------------------------------------------
	function _hash($data, $controlType){
		if (!$this->enabling) return false;
		
        switch ($controlType) {
	        case 'md5':
	            return md5($data);
	        case 'crc32':
	            return sprintf('% 32d', crc32($data));
	        case 'strlen':
	            return sprintf('% 32d', strlen($data));
	        default:
            log_notice('Не определенн контроль записи "'.$controlType.'" кэша данных');
        }
    }
	//--------------------------------------------------------------------------
	function _refreshTime(){
		if (!$this->enabling) return false;
		
        if (is_null($this->lifetime)) {
            return null;
        }
        return time() - $this->lifetime;
    }
	
	//--------------------------------------------------------------------------
	
	
}
?>