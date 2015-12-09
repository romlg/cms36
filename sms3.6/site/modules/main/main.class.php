<?php
class TMain
{

    var $pids;
    // этот массив определяется в setup класса-потомка
    var $allowed_methods;

    // контент текущей страницы
    var $content;
    var $shown_elems;


    var $url; // полный путь. сетится в шаблон
    var $dirs_url; // огрызок пути без lang. сетится в шаблон
    var $dirs_lang; // огрызок lang. сетится в шаблон
    var $dirs; // массив пути url
    var $stop; // означает что обработку нужно остановить
    var $root;

    // службеная - если записать сюда переменные, они сохрянятся при первой операции кеширования результатов шаблона
    // использовать только при помощи save_in_cache()
    var $_save_in_cache;
    // службеная - сюда присваиваются переменные из закешированного шаблона
    // использовать только при помощи restore_from_cache()
    var $_restore_from_cache;
    var $elem_id; // id элемента в конце url (id).(type).html
    var $elem_type; // type элемента в конце url (id).(type).html

    var $root_path;
    var $base_href;
    var $lang;

    var $template;

    function TMain() {
    }

    //готовит всякие переменные. которые потом все нещадно используют
    function setup() {
        $lang_obj = & Registry::get('TLang');
        $this->lang = $lang_obj->getCurrentLanguage();
        $this->dirs_lang = $lang_obj->getLangDirPrefix();
        $this->root = $lang_obj->getLangRootId();

        $this->tpl = & Registry::get('TRusoft_View');
        $this->tpl->template_dir = "templates";
        // Загрузим строковые константы
        $messages['messages'] = $this->getMessagesForTemplate();
        $this->tpl->assign((array)$messages);

        $this->root_path = $this->root_path();
        $this->base_href = $this->base_href();

        //$this->allowed_items = array('news', 'doc', 'tab');
    }

    function execModuleMethod($module, $method, $dirs) {
        $this->init($dirs);

        $config_obj = & Registry::get('TConfig');
        $module_config = $config_obj->getConfigByModuleName($module, ROOT_ID);

        $module_loader = & Registry::get('TModuleLoader');
        $method = !empty($method) ? $method : $module_config['do'];
        $value = $module_loader->callModuleMethod($module_config['class'], $method, $this->dirs);

        // @todo определить переменную, куда методы будут выводить свой результат
        $this->tpl->assign($module_config['tpl_var'], $value);
    }

    /*function initModule($handler_module, $method,  $dirs) {
         $this->init($dirs);

         $config_obj = &TConfig::create();
         $module_config = $config_obj->getConfigByModuleName($handler_module);

         $module_loader = &TModuleLoader::create();
         $method = !empty($method) ? $method : $module_config['do'];
         $value = $module_loader->callModuleMethod($module_config['class'], $method, $this->dirs);

         if (!empty($module_config['template'])) {
             $this->template = $module_config['template'];
         }

         $this->parse_blocks($module_config);
         $this->tpl->assign($module_config['tpl_var'], $value);
         $this->display();
     }*/

    function initPage($dirs) {
        $this->init($dirs);

        $tree_obj = & Registry::get('TTreeUtils');
        $config_obj = & Registry::get('TConfig');
        //$module_loader = & Registry::get('TModuleLoader');
        $node_id = empty($this->dirs) ? $this->root : $this->dirs;

        $this->pids = $tree_obj->getPids($node_id);
        $this->content = end($this->pids);
        $this->content = $tree_obj->getContent($this->content['id']);

        // Делаем редирект если он есть в БД
        if (isset($this->content['redirect']) && !empty($this->content['redirect'])) {
            redirect($this->content['redirect']);
        }

        // Если 1) задан параметр ENABLE_JUMP=1, 2) текущая страница имеет один из заданных типов,  3) контент текущей страницы пустой, 4) у страницы нет приложенных публикаций; 5) есть дочерняя страница
        // То переходим на дочернюю страницу
        if (ENABLE_JUMP) {
            global $jump_params, $site_domains, $domain;
            if (!isset($jump_params))
                $jump_params = array(
                    'types' => array('text'), // с каких типов страниц разрешен переход
                    'elems' => array( // в этих таблицах и полях проверяем наличие контента
                        'elem_text' => 'text',
                    ),
                );
            if (in_array($this->content['type'], $jump_params['types'])) {
                $jump = true;
                if (isset($jump_params['fields'])) {
                    $count_equal_fields = 0; // Количество обнаруженных полей с правильным значением
                    $count_fields = 0; // Общее количество проверяемых полей
                    foreach ($jump_params['fields'] as $k=>$v) {
                        $count_fields++;
                        if (isset($this->content[$k]) && $this->content[$k] == $v) $count_equal_fields++;
                    }
                    if ($count_equal_fields == $count_fields) {
                        $jump = false;
                    }
                }
                if ($jump) {
                    $count_publ = 0;
                    if (sql_getRow("SHOW TABLES LIKE 'publications'")) {
                        $count_publ = (int)sql_getValue("SELECT COUNT(1) FROM publications WHERE (pid='" . $this->content['id'] . "' OR FIND_IN_SET('" . $this->content['id'] . "', pids)) AND visible=1");
                    }
                    if (!$count_publ) {
                        $count_empty_fields = 0; // Количество обнаруженных пустых полей
                        $count_fields = 0; // Общее количество проверяемых полей
                        foreach ($jump_params['elems'] as $table => $field) {
                            $count_fields++;
                            if (!isset($this->content[$table]) || empty($this->content[$table][$field])) $count_empty_fields++;
                        }
                        if ($count_empty_fields == $count_fields) {
                            $where = '';
                            if (isset($jump_params['types2'])) {
                                $where .= ' AND type IN ("' . implode('","', $jump_params['types2']) . '")';
                            }
                            $child_page = sql_getValue("SELECT page FROM " . $tree_obj->table . " WHERE visible>0 AND pid=" . $this->content['id'] . $where . " ORDER BY priority, name LIMIT 1");

                            if ($child_page) {
                                $redirect = $this->base_href;
                                if (strpos($_SERVER['REQUEST_URI'], '/cd/') !== false) $redirect .= 'cd/';
                                if (count($site_domains[$domain]['langs']) > 1) $redirect .= $this->lang . '/';
                                $redirect .= $this->content['href'] . '/' . $child_page;
                                redirect($redirect);
                            }
                        }
                    }
                }
            }
        }
        //$this->tpl->assign('content', $this->content);

        // выбираем самое последнее время обновления (last-Modifined)
        $handle = opendir(PATH_CACHE_TABLES);
        $uptimes = array();
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $uptimes[] = filemtime(PATH_CACHE_TABLES . $file);
            }
        }
        closedir($handle);

        //далее берем даты из $this->content , которые в виде 20060606153438
        foreach ($this->content as $k => $v) {
            $time = '';
            if (is_array($v)) {
                if (!isset($v['uptime'])) continue;
                $time = $v['uptime'];
            } else if ($k == 'uptime') $time = $v;


            if (!empty($time)) {
                if (strpos($time, '-') === false) {
                    $time = substr($time, 0, 4) . '-' . substr($time, 4, 2) . '-' . substr($time, 6, 2) . ' ' . substr($time, 8, 2) . ':' . substr($time, 10, 2) . ':' . substr($time, 12, 2);
                }
                $uptimes[] = strtotime($time);
            }
        }
        $GLOBALS['update_time'] = max($uptimes);

        // Если контент не был изменен, то мы его ни генерить, ни передавать не будем
        $cache_page = & Registry::get('TPageCache');
        $cache_page->IfModifiedSince($GLOBALS['update_time']);

        $this->show_page();
        $this->display();
    }

    function init($dirs) {

        $this->dirs = $dirs;
        $this->setup();

        // @todo перепроверить и пересмотреть блок обработки elem в пути
        // проверяем, есть ли в пути объекты
        /*if(isset($this->dirs) && !empty($this->dirs)) {
              if(strpos($this->dirs[count($this->dirs)-1], '.html')) {
                  if(count($elem = split('\.', array_pop($this->dirs))) != 3) $this->error_404('unknown element in URL');
                  // проверяем, разрешен ли данный элемент для отображения
                  if(!in_array($elem[1], $this->allowed_items)) {
                      $this->error_404('element "'.$elem[1].'" in URL is not allowed!');
                      return;
                  }
                  $this->elem_id = $elem[0];
                  $this->elem_type = $elem[1];
                  //echo("elem_id=$this->elem_id elem_type=$this->elem_type");
              }
          }*/

        $this->dirs_url = '';
        if (isset($this->dirs)) {
            $this->dirs_url .= implode('/', $this->dirs);
        }

        $this->url = $this->dirs_lang . $this->dirs_url;

        $this->assign_vars();
    }

    //в главный шаблон присваивает всякие глобальные, независящие от модулей переменные
    function assign_vars() {
        $this->tpl->assign('base_href', $this->base_href);
        $this->tpl->assign('lang', $this->lang);
        $this->tpl->assign('dirs_url', $this->dirs_url);
        $this->tpl->assign('dirs_lang', $this->dirs_lang);
        $this->tpl->assign('url', $this->url);
        $this->tpl->assign('root', $this->root);
    }

    //собственно здесь и вызывается, наконец, отображение шаблона.
    function display() {
        // основную страницу не кешируем
        if (!$this->template) {
            log_error('No template for display');
            return;
        }

        // @todo проверка на возможность кэширования
        if (isset($this->cacheble)) {
            $this->caching = SMARTY_CACHING;
            $this->tpl->display($this->template . '.html', $this->dirs_lang . $this->dirs_url);
        }
        else {
            $this->caching = false;
            $display = $this->tpl->render($this->template . '.html');
            $display = preg_replace_callback('#href="([^"]*)"#is', 'set_encode_url', $display);
            $display = preg_replace_callback('#href=\'([^\']*)\'#is', 'set_encode_url2', $display);
            $display = preg_replace('#\[\[FORM\s([^\]]*)\]\]#is', '<script src="/'.lang().'/?get_form=$1" type="text/javascript"></script>', $display);
            $display = preg_replace('#\[\[FORMPOPUP\s([^\]]*)\]\]#is', '<script src="/'.lang().'/?get_form=$1&is_popup" type="text/javascript"></script>', $display);
            echo $display;
        }
    }

    // Подготавливает переменную root_path
    function root_path() {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        if (substr($path, -1, 1) == '\\') $path = substr($path, 0, -1);
        if (substr($path, -1, 1) != '/') $path .= '/';
        return $path;
    }

    // Подготавливает переменную base_href
    function base_href() {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $method = 'https://';
        }
        else $method = 'http://';
        return $method . $_SERVER['HTTP_HOST'] . $this->root_path;
    }

    // Подготавливает строковые константы для шаблона
    function getMessagesForTemplate() {
        $single = true;
        if ($single) {
            $status = sql_getRows('SHOW TABLE STATUS LIKE "sites"');
            if (!empty($status)) {
                if ((int)sql_getValue('SELECT COUNT(*) FROM sites') > 1) $single = false;
            }
        }
        if ($single) {
            $status = sql_getRows('SHOW TABLE STATUS LIKE "sites_langs"');
            if (!empty($status)) {
                if ((int)sql_getValue('SELECT COUNT(*) FROM sites_langs') > 1) $single = false;
            }
        }
        if (!$single) {
            $row = sql_getRows("
            SELECT
                IF(t2.value IS NULL, IF(t1.module='site', t1.name, CONCAT(t1.module,'_',t1.name)), IF(t2.module='site', t2.name, CONCAT(t2.module,'_',t2.name))) AS name,
                IFNULL(t2.value,t1.value) AS value
            FROM strings AS t1
            LEFT JOIN strings AS t2 ON (t1.module=t2.module AND t1.name=t2.name AND t2.lang='" . lang() . "' AND t2.root_id='" . ROOT_ID . "')
            WHERE t1.lang='" . getLangByRootID(getMainRootID()) . "' AND t1.root_id='" . getMainRootID() . "'
            ORDER BY IFNULL(t2.module,t1.module) ASC", true);
            return $row;
        }
        $row = sql_getRows("SELECT IF(module='site',name, CONCAT(module,'_',name)) AS name, value FROM strings WHERE lang='" . lang() . "' AND root_id='" . ROOT_ID . "' ORDER BY module ASC", true);
        return $row;
    }
}

// TMain class

?>