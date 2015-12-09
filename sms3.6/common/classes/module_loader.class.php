<?php

class TModuleLoader{

	var $files;
	var $modules;
	var $enabled_modules = array();
	var $core;
	var $prefix;
	var $check_files;

	/*
		устанавливает check_files и core
	*/
	function TModuleLoader($files = array('init.php', 'module.ini'), $core = '') {
		$core = !empty($core) ? $core : PATH_CORE;
		$this->core = $core;
		$this->check_files = $files;
	}

	function populateEnabledModules($modules) {
		$this->enabled_modules = $modules;
	}
/*
    function searchInDirs($search_pattern, $modules){
        $dirs = glob($search_pattern, GLOB_ONLYDIR);
        if (empty($dirs) || !is_array($dirs)){
            return false;
        }
        $mod = array();
        foreach ($dirs as $path) {
            $path = path($path)."/";
            if (!$this->isModuleInPath($path)) {
                continue;
            }
            $ini = ini_read($path.'module.ini', true);
            $ini['module']['path'] = $path;
            $name = $ini['module']['name'];
            if (isset($modules[$name])) {
                continue;
            }
            $mod[$name] = $ini;
        }
        return $mod;
    }
*/
/*
    function autoFindModules() {
        global $domain, $site_domains;

        $modules = array();

        timer_start('auto modules');
        $inc = getIncludePaths();
        $dir = getcwd();

        foreach ($inc as $v) {
            $path = path($v)."/";

            if (isset($site_domains) && in_array($domain, array_keys($site_domains))){
                // Сначала ищем в директории с названием $site_domains[$domain]['modules']
                $search_pattern = ($path == './') ? $dir.'/modules/'.$site_domains[$domain]['modules'] : $path.PATH.'/modules/'.$site_domains[$domain]['modules'];
                $search_pattern = realpath($search_pattern).'/*';
                $modules1 = $this->searchInDirs($search_pattern, $modules);
                if ($modules1) $modules = array_merge($modules, $modules1);
            }

            // Теперь ищем в общей папке с модулями
            $search_pattern = ($path == './') ? $dir.'/modules/*' : $path.ENGINE_VERSION.'/'.ENGINE_TYPE.'/modules/*';
            $modules2 = $this->searchInDirs($search_pattern, $modules);
            if ($modules2) $modules = array_merge($modules, $modules2);

			/// @todo Разобраться с путями: откуда все же грузить модули  //
            $search_pattern = ($path == './') ? $dir.'/modules/*' : $path.$this->core.'modules/*';
            $modules3 = $this->searchInDirs($search_pattern, $modules);
            if ($modules3) $modules = array_merge($modules, $modules3);
        }
        $this->modules = $modules;
        timer_end('auto modules');
	}
*/
/*
	function isModuleInPath($path) {
		$path = path($path)."/";
		foreach ($this->check_files as $v) {
			if (is_readable($path.$v)) {
				continue;
			}
			return false;
		}
		return true;
	}
*/
/*
	// подключает модули
	function initModules() {
		if (!is_array($this->modules)) {
			return false;
		}
		foreach ($this->modules as $k => $v) {
			if (!in_array($v['module']['name'], $this->enabled_modules)) {
				continue;
			}
			include_once ($v['module']['path'].'init.php');
		}
		return true;
	}
*/
	function isModuleEnabled($module_name) {
		return (in_array($module_name, $this->enabled_modules));
	}
/*
	function registerClassFile($class_name, $filename) {
		//проверить
		include_once(base($filename));
        $this->files[strtolower($class_name)] = $filename;
	}
*/

	function getFileByClassName($class_name) {
		$class_name = strtolower($class_name);
		if(!array_key_exists($class_name, $this->files)) {
			return false;
		}
		return $this->files[$class_name];
	}

	function loadClassByName($class_name) {
		if (class_exists($class_name)) {
			return true;
		}

		$filename = $this->getFileByClassName($class_name);
		if (!$filename) {
			return false;
		}
		include_once(base($filename));

		if (!class_exists($class_name)) {
			return false;
		}

		return true;
	}

	function &getModuleObject($class_name) {
        /*if (!$this->loadClassByName($class_name)) {
			log_error('Class <font class="debug_h">'.$class_name.'</font> couldn\' be loaded');
			$res = false;
			return $res;
		}  */
		$module_obj = & Registry::get($class_name);
		return $module_obj;
	}

	function callModuleMethod($class_name, $do, &$params) {
		$module_obj = &$this->getModuleObject($class_name);

		if (!method_exists($module_obj, $do)) {
            log_error('Method <font class="debug_h2">'.$do.'</font> couldn\'t be found in class <font class="debug_h">'.$class_name.'</font>');
			return false;
		}
		$obj = call_user_func_array(array(&$module_obj, $do), array(&$params));
		$res = &$obj;
		return $res;
	}
}

?>