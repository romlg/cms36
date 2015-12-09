<?php

class Registry {

	var $core;
    var $files;
    var $check_files;

	static $objects;

	//--------------------------------------------------------------------
	static function &get($classname){
		$classname = strtolower($classname);
		if (!isset(Registry::$objects[$classname])){
			if (class_exists($classname)){
				Registry::$objects[$classname] = new $classname();
			} else {
                // Будем искать нужный класс
                //$path = $this->findOneModule($classname);
                $path = Registry::findOneModule($classname);
                if ($path) {
                    include_once ($path);
                    Registry::$objects[$classname] = new $classname();
                } else {
                    echo "<b>не могу найти класс ".$classname."</b>";
                    pr (debug_backtrace());
                    die();
                }
			}
		}
		return Registry::$objects[$classname];
	}

	//--------------------------------------------------------------------
	static function set($classname, &$object){
		$classname = strtolower($classname);
		Registry::$objects[$classname] = $object;
	}

	//--------------------------------------------------------------------
	static function findOneModule($classname) {
        global $domain, $site_domains;

        $inc = getIncludePaths();
        $dir = getcwd();

        // узнаем имя модуля
        if (substr(strtolower($classname), 0, 1) == "t") {
            $filename = substr(strtolower($classname), 1);
        }
        $modulename = $filename;
        $filename = $filename.".class.php";

        foreach ($inc as $v) {
            $path = path($v)."/";
            if (isset($site_domains) && in_array($domain, array_keys($site_domains))) {
                // Сначала ищем в директории с названием $site_domains[$domain]['modules']
                $search_pattern = ($path == './') ? $dir.'/modules/'.$site_domains[$domain]['modules'] : $path.PATH.'/modules/'.$site_domains[$domain]['modules'];
                $search_pattern = realpath($search_pattern).'/'.$modulename.'/';
                if (is_file($search_pattern.$filename)) {
                    break;
                }
            }

            // Теперь ищем в общей папке с модулями
            $search_pattern = ($path == './') ? $dir.'/modules/' : $path.ENGINE_VERSION.'/'.ENGINE_TYPE.'/modules/';
            $search_pattern = $search_pattern.$modulename.'/';
            if (is_file($search_pattern.$filename)) {
                break;
            }
        }

        if (is_file($search_pattern.$filename)) {
            return $search_pattern.$filename;
        } else {
            return false;
        }
	}
}

?>