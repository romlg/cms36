<?php

require_once (PATH_COMMON_CLASSES.'view.php');

class TRusoft_View extends Zend_View {

    function get_config_vars($name=null) {
        if(!isset($name) && is_array($this->messages)) {
            return $this->messages;
        } else if(isset($this->messages[$name])) {
            return $this->messages[$name];
        }
    }

    /**
     * executes & displays the template results
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     */
    function display($resource_name, $cache_id = null, $compile_id = null) {
        // Был старый выхзов, учитывал кеширование
        //$this->fetch($resource_name, $cache_id, $compile_id, true);
        $this->render($resource_name);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $tpl_file
     * @return boolean
     */
    function template_exists($tpl_file) {
        $exist = false;

        // Проверим если у шаблона уже задан путь, проверим на существование
        $dirs = explode("/", $tpl_file);
        if (count($dirs)>1) {
            if (file_exists($tpl_file)) {
                return true;
            }
            return false;
        }

        foreach ($this->getScriptPaths() AS $path) {
            if (file_exists($path.$tpl_file)) {
                $exist = true;
                break;
            }
        }
        return $exist;
    }
}

?>