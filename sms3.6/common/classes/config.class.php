<?php

class TConfig
{

    var $types = array();
    var $types_merge = array();

    function populateTypes() {
        global $cfg, $site_domains;

        if (ENGINE_TYPE == 'site') {
            // ѕолучаем конфиг только дл€ текущего сайта
            if (isset($cfg['types'][ROOT_ID])) {
                $this->types[ROOT_ID] = $cfg['types'][ROOT_ID];
            }
            foreach ($this->types[ROOT_ID] as $k => $v) {
                $this->types[ROOT_ID][$k] = $this->getConfigByTypeDerivative($k, ROOT_ID);
            }
        } else {

            // ѕолучаем конфиги всех сайтов
            foreach ($site_domains as $site => $value) {
                foreach ($value['langs'] as $l) {
                    if (!isset($cfg['types'][$l['root_id']])) continue;
                    $this->types[$l['root_id']] = $cfg['types'][$l['root_id']];
                    foreach ($this->types[$l['root_id']] as $k => $v) {
                        $this->types[$l['root_id']][$k] = $this->getConfigByTypeDerivative($k, $l['root_id']);
                    }
                }
            }
        }
    }

    function populateFunctionModules() {
        global $cfg, $site_domains;
        foreach ($site_domains as $site => $value) {
            foreach ($value['langs'] as $l) {
                $function_modules[$l['root_id']] = $cfg['function_modules'][$l['root_id']];
                foreach ($function_modules[$l['root_id']] as $module_name => $module_config) {
                    $derived_type = array_key_exists('derive_from', $module_config) ? $module_config['derive_from'] : '';
                    if (!$derived_type) {
                        continue;
                    }
                    $derived_type_cfg = $this->getConfigByTypeDerivative($derived_type, $l['root_id']);
                    $function_modules[$l['root_id']][$module_name] = $this->mergeConfigBlocks($derived_type_cfg, $function_modules[$l['root_id']][$module_name]);
                }
            }
        }
        $this->function_modules = $function_modules;
    }

    function getElemsByTypes($types) {
        $item_elems = array();
        foreach ($types as $type) {
            if (empty($this->types[$type])) {
                continue;
            }
            $item_elems = array_unique(array_merge($item_elems, $this->types[$type]['elements']));
        }
        return $item_elems;
    }

    function getAllElems() {
        $item_elems = array();
        foreach ($this->types as $type) {
            if (!isset($type['elements'])) {
                continue;
            }
            $item_elems = array_unique(array_merge($item_elems, $type['elements']));
        }
        return $item_elems;
    }

    function getAllTypes($root_id) {
        return isset($this->types[$root_id]) ? array_keys($this->types[$root_id]) : array();
    }

    function getAllModules($root_id) {
        return array_keys($this->function_modules[$root_id]);
    }

    function hasType($type, $root_id) {
        return in_array($type, $this->getAllTypes($root_id));
    }

    function hasModule($module_name, $root_id) {
        return in_array($module_name, $this->getAllModules($root_id));
    }

    function getConfigByType($type, $root_id) {
        if (!$this->hasType($type, $root_id)) {
            return array();
        }
        return $this->types[$root_id][$type];
    }

    function getConfigByModuleName($module_name, $root_id) {
        if (!$this->hasModule($module_name, $root_id)) {
            return array();
        }
        return $this->function_modules[$root_id][$module_name];
    }

    function getConfigByTypeDerivative($type, $site) {
        $current_type_cfg = $this->getConfigByType($type, $site);
        $parent_type = array_key_exists('derive_from', $current_type_cfg) ? $current_type_cfg['derive_from'] : '';
        if (!$parent_type) {
            return $current_type_cfg;
        }

        $parent_type_cfg = $this->getConfigByTypeDerivative($parent_type, $site);
        $current_type_cfg = $this->mergeConfigBlocks($parent_type_cfg, $current_type_cfg);
        return $current_type_cfg;

    }

    // возвращает структуру конфига по типу контента
    function getConfigByTypeRecursive($type) {
        $root_type = array_shift(array_keys($this->types));
        $root_cfg = $this->types[$root_type];
        $this->types_merge = array();

        $found = $this->checkType($type, $root_type, true);

        if ($found && !empty($this->types_merge)) {
            $this->types_merge = array_reverse($this->types_merge);
            foreach ($this->types_merge as $tm) {
                $root_cfg = $this->mergeConfigBlocks($root_cfg, $tm);
            }
        }

        if (!isset($root_cfg['template'])) {
            $root_cfg['template'] = 'page';
        }

        return $root_cfg;
    }

    // рекурсивна€ функци€, используетс€ FindConfigByType
    function checkType($type, $type_name, $new = false) {

        // recursive counter
        static $count = 0;
        $count++;
        // if count > 50 - infinite recursive - quit
        if ($count > 50) {
            return;
        }

        static $types = array();

        if ($new) {
            $count = 0;
            $types = array();
        }

        if ($type_name == $type) {
            return true;
        }

        $types[] = $type_name;

        foreach ($this->types[$type_name]['nested'] as $t_name) {
            if (in_array($t_name, $types)) {
                continue;
            }

            if (!$this->checkType($type, $t_name)) {
                continue;
            }
            $this->types_merge[] = $this->types[$t_name];

            return true;
        }

        return false;
    }

    // "сливает" в первую структуру конфига то, что есть во второй
    function mergeConfigBlocks($cfg1, $cfg2) {
        if (isset($cfg2[0])) {
            $cfg1[0] = $cfg2[0];
            unset($cfg2[0]);
        }
        if (isset($cfg2['name'][0])) {
            $cfg1[0] = $cfg2['name'][0];
        }

        if (isset($cfg2[1])) {
            $cfg1[1] = $cfg2[1];
            unset($cfg2[1]);
        }
        if (isset($cfg2['name'][1])) {
            $cfg1[1] = $cfg2['name'][1];
        }

        if (isset($cfg2['name'])) unset($cfg2['name']);

        if (isset($cfg2['elements'])) {
            $cfg1['elements'] = $cfg2['elements'];
            unset($cfg2['elements']);
        }
        if (isset($cfg2['nested'])) {
            $cfg1['nested'] = $cfg2['nested'];
            unset($cfg2['nested']);
        }
        if (isset($cfg2['template'])) {
            $cfg1['template'] = $cfg2['template'];
            unset($cfg2['template']);
        }

        if (isset($cfg2['blocks']) && is_array($cfg2['blocks'])) {
            foreach ($cfg2['blocks'] as $key => $block) {
                /* проблема с переносом модулей на несколько уровней вниз
                    if (empty($block)) {
                        unset($cfg1['blocks'][$key]);
                        continue;
                    }*/
                $cfg1['blocks'][$key] = $block;
            }
            unset($cfg2['blocks']);
        }

        if (!empty($cfg2)) {
            //все остальное ƒо 2 уровн€ вложенности
            foreach ($cfg2 as $k => $v) {
                if (!isset($cfg1[$k])) {
                    $cfg1[$k] = $v;
                } else {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            //всегда переобпредел€ем на втором уровне вложенности
                            $cfg1[$k][$k2] = $v2;
                        }
                    } else {
                        $cfg1[$k] = $v;
                    }
                }

            }
        }
        return $cfg1;
    }

}

?>