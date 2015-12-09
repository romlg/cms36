<?php

// ���� �� ��� - �� ����� ��������...
// ���������������� 31 ������ � ��������������� 32..
// parse_blocks -> ������ �������
// get_blocks -> �����
// @ dmitriy

class TPage extends TMain {

	var $name = 'page';

	function setup() {
		TMain::setup();
	}

	function show_page() {
		$config_obj = & Registry::get('TConfig');
		global $site_domains, $domain;

		if ($this->content['type'] == 'module' && !empty($this->content['elem_module']['module'])) {
			$module = $this->content['elem_module']['module'];
			$config = $config_obj->getConfigByModuleName($module, $this->content['root_id']);
		}
		else {
			$config = $config_obj->getConfigByType($this->content['type'], $this->content['root_id']);
		}

		//todo ������ ������
		//����������� ����� � �������(XSS)
		if (count($this->pids) == 2 && $this->pids['1']['page'] == 'search'){
			//������ htmlspecialchars �� ��� ������ � get
			foreach ($_GET as $k=>$v){
				if (is_array($v)){
					foreach ($v as $k2=>$v2){
						if (!is_array($v2)) $_GET[$k][$k2] = htmlspecialchars($v2);
					}
				} else {
					$_GET[$k] = htmlspecialchars($v);
				}
			}
		}
        // ���� ��� �������� �������� ������ ��������� ���� ������, �� ����� ���, ����� - �����
        if (isset($site_domains)) {
            $current_domain = $site_domains[$domain];
            if ($current_domain && $current_domain['templates'] && !$this->template) {
            	if (is_file('templates/'.$current_domain['templates'].'/'.$config['template'].'.html'))
                	$this->template = $current_domain['templates'].'/'.$config['template'];
                else $this->template = $config['template'];
            } else if (!$this->template) $this->template = $config['template'];
        } else $this->template = $this->template ? $this->template : $config['template'];

        $this->block_todown('meta', $config['blocks']);
        if (isset($config['blocks']['header'])) $this->block_todown('header', $config['blocks']);
        if (isset($config['blocks']['footer'])) $this->block_todown('footer', $config['blocks']);
        if (isset($config['blocks'])) $this->get_blocks($config['blocks']);
	}

	/**
	 * �������� ����������� ���� �������� � ����� ���, ����� ������ ���� �������
	 */
	function block_todown($block, &$blocks){

		$blocks_up = array();
		$blocks_down = array();
		foreach ($blocks as $k=>$v){
			if ($k == $block) {
				$blocks_down[$k] = $v;
			} else {
				$blocks_up[$k] = $v;
			}
		}
		$blocks = array_merge($blocks_up, $blocks_down);
	}

	/**
	 * ��������� � ���������� ������ �����
	 */
	function get_blocks($blocks = array()) {

		foreach($blocks as $block_name => $block) {
			# ������ ������
			$module_loader = & Registry::get('TModuleLoader');
			$class_name = isset($block['class']) ? $block['class'] : DEFAULT_CLASS;
			//$GLOBALS['blocks'][$class_name] = $module_loader->getModuleObject($class_name);
			$GLOBALS['blocks'][$class_name] = &Registry::get($class_name);

		}

		foreach($blocks as $block_name => $block) {
			timer_start('block='.$block_name);
			# ������ ������ �������� �����
			$data = array();

			$class_name = isset($block['class']) ? $block['class'] : DEFAULT_CLASS;
			$block['cache']	= isset($block['cache'])?$block['cache']:false;
			$tables = isset($block['tables'])?$block['tables']:array();
			$block['cache_tables'] = isset($block['cache_tables'])?$block['cache_tables']:array();

			if(CACHE_BLOCKS && $block['cache']) {
               // ������� ����� cache/blocks � ������������ 0.001
               if (rand(0, 999) == 13) $this->cleanCache();
                $cache_id = md5($module_loader->callModuleMethod($class_name, "_cache", array_merge($block,array('block_name' => $block_name))));
				if($this->is_cached($this->content['type'],$block_name,$cache_id,$tables)) {
                    $data = $this->get_from_cache($this->content['type'],$block_name,$cache_id);
				}
				else {
					$data = $this->generate_block_data($block_name,$block);
					$this->save_in_cache($this->content['type'],$block_name,$cache_id,$data);
				}
			}
			else {
				$data = $this->generate_block_data($block_name,$block);
			}
			$this->tpl->assign((array)$data);
			timer_end('block='.$block_name);
		}
	}

	/**
	 * ������� ������� ����� �� ����� cache/blocks
	 */
	function cleanCache() {
		$this->DeleteFolder(CACHE_BLOCKS_PATH);
	}

	function DeleteFolder($dir) {
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle)))
			if (!is_dir($dir.'/'.$file)) {
				unlink($dir.'/'.$file);
			}
			elseif ($file != '.' && $file != '..') {
				$this->DeleteFolder($dir.'/'.$file);
			}
			closedir($handle);
		}
	}

	/**
	 * ��������� ������������ ����
	 */
	function is_cached($type,$block_name,$cache_id,$tables) {
		if(!is_file(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id)) return false;
        $file_time = filemtime(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id);
		if(empty($tables)) {
			if(is_file(PATH_CACHE_TABLES.'.cache')) {
				if($file_time > filemtime(PATH_CACHE_TABLES.'.cache')) return true;
			}
		}
		else {
			foreach($tables as $table) {
				if(!is_file(PATH_CACHE_TABLES.'.'.$table) || $file_time < filemtime(PATH_CACHE_TABLES.'.'.$table)) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * ���������� �� ����
	 */
	function get_from_cache($type,$block_name,$cache_id) {
		if(!is_file(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id)) return false;
		$handler = fopen(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id,'r');
		$data = fread($handler,filesize(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id));
		fclose($handler);
		return unserialize($data);
	}

	/**
	 * ��������� � ���
	 */
	function save_in_cache($type,$block_name,$cache_id,$data) {
		$handler = fopen(CACHE_BLOCKS_PATH.$type.'-'.$block_name.'-'.$cache_id,'w');
		fwrite($handler,serialize($data));
		fclose($handler);
		return true;
	}

	/**
	 * ���������� ������ ��� ������� ��������� �����
	 */
	function generate_block_data($block_name,$block) {

		$data = array();

		$class_name = isset($block['class']) ? $block['class'] : DEFAULT_CLASS;
		if (empty($block['method'])) {
			log_error('Block "'.$block_name.'" method not defined');
			return false;
		}


		if (!isset($block['parse'])) {
			$block['parse'] = true;
		}

		$params = !empty($block['params']) ? $block['params'] : array();
		$params['_block'] = &$block;
		$module_loader = & Registry::get('TModuleLoader');
		$block_vars = $module_loader->callModuleMethod($class_name, $block['method'], $params);

		# ��� ������
		if (empty($block_vars)) {
			return $data;
		}

		$names = isset($block['names']) ? $block['names'] : array($block_name);
		$tmpls = isset($block['tmpls']) ? $block['tmpls'] : $names;

		# debug ��� �����
		if (!empty($block['debug']) && $block['debug']) {
			pr($block_vars, 'block "'.$block_name.'"');
		}

		# ���� �� ���� ������� - ���������� ������ ������� ������ $block['method'] (� ����������� �� ������)
		if (!$block['parse']) {
			$data[$block_name] = $block_vars;
			return $data;
		}

		# �������������� ������ ������ (������ �������)
		$block_vars_keys = array_keys($block_vars);
		$data = array();
		$this->tpl->assign($block_vars);
		global $site_domains, $domain;
		for ($i = 0; $i < count($names); $i++) {
			if (isset($site_domains) && isset($site_domains[$domain]))
				$template = $tmpls[$i].'.html';
			else $template = $tmpls[$i].'.html';

			$var_name = $names[$i];
			$flag = true;

			//���������� ��� ������ �������� � ������ ������, ������� ���� � ���������� �����
			$tamplate_name = $template;

            // �������� ������� ���������� �������
            // ���� ���, ���� � ����� ����� ��������
            $path_template = 'templates/'.$site_domains[$domain]['templates'].'/'.$template;
            if ($this->tpl->template_exists($path_template)){
                $flag = true;
                $template = $path_template;
            } else {
                // ���� � ����� ����� ��������
                $flag = false;

                if ($this->tpl->template_exists($template)) {
                    $flag = true;
                } else {
                    log_error('Template "'.$tamplate_name.'" for block "'.$block_name.'" does not exists');
                }
            }

			if ($flag){
				$data[$var_name] = $this->tpl->render($template);
			}
		}
		$this->tpl->assign($block_vars,false);
		return $data;
	}

	/**
	 * ������ ������� �������� ������
	 */
	function parse_blocks($blocks) {
		foreach ($blocks as $block_name => $block) {

			$class_name = isset($block['class']) ? $block['class'] : DEFAULT_CLASS;

			// @dirty trick

			$module_loader = & Registry::get('TModuleLoader');
			$module_obj = $module_loader->getModuleObject($class_name);

			$params = !empty($block['params']) ? $block['params'] : array();
			$params['_block'] = &$block;

			// ���� ���������� ����� check_cache ��� �������, �� cached = $module_obj->block['method'](); ���� ��� �� cached = -1.
			## ��������: -1: �� ����������, 0: �� �����������, 1: �����������
			//$cached = method_exists($module_obj,'checkCache')?$module_loader->callModuleMethod($class_name, 'checkCache', $params):-1;
			//if(!is_file(CACHE_PAGES.md5('block_'.$class_name.$block['method'])) && $cached==1) {
			//$cached = 0;
			//}
			# ���� ���� ������������

			if (empty($block['method'])) {
				log_error('error', false,'Block "'.$block_name.'" method not defined');
				continue;
			}

			// �����
			$names = isset($block['names']) ? $block['names'] : array($block_name);
			// �������
			$tmpls = isset($block['tmpls']) ? $block['tmpls'] : $names;
			// parse
			if (!isset($block['parse'])) {
				$block['parse'] = true;
			}


			$block_vars = $module_loader->callModuleMethod($class_name, $block['method'], $params);

			// debug ��� �����
			if (!empty($block['debug']) && $block['debug']) {
				pr($block_vars, 'block "'.$block_name.'"');
			}
			//�������� ���������� � ������� ������ ��� ��������� ������ ����� � ����� �����������
			if (!$block['parse']) {
				$this->tpl->assign(array($block_name => $block_vars));
				continue;
			}
			if (empty($block_vars)) {
				$block_vars = array();
			}
			$old_vars = array_keys($block_vars);
			$this->tpl->assign($block_vars);


			$new_values = array();
			for ($i = 0; $i < count($names); $i++) {
				$template = $tmpls[$i].'.html';
				$var_name = $names[$i];
				if (!$this->tpl->template_exists($template)) {
					log_error('template', false, 'Template "'.$template.'" for block "'.$block_name.'" does not exists');
					$this->tpl->assign($var_name, false);
					continue;
				}
				$new_values[$var_name] = $this->tpl->fetch($template);
			}
			$this->tpl->clear_assign($old_vars);
			$this->tpl->assign($new_values);
		}
	}

	/**
	 * ��������� � ��� �������� ����
	 */
	function show_pids($cfg) {
		return array('pids' => $this->pids);
	}

	/**
	 * ��������� ���� ��� ��������, ���� ����
	 */
	function show_tabs($param) {
		$sql = "select * from elem_tab where pid = ".$this->content['id'];
		$ret = sql_getRows($sql);
		return array('tabs' => $ret);
	}

	/**
	 *  ��������� ���� ��� ������������� ������
	 **/
	function show_files($param) {
		$sql = "select * from elem_file where pid = ".$this->content['id'];
		$ret = sql_getRows($sql);
		return array('files' => $ret);
	}

	/**
	 * ��������� � ��� �������� ���������
	 */
	function show_title($params) {
		if (isset($params['title'])) {
			return array('title' => $params['title']);
		}
		return array('title' => $this->content['name']);
	}

	/**
	 * ��������� � ��� �������� �����
	 */
	function show_text($params) {
		if (isset($params['text'])) {
			return array('text' => $params['text']);
		}
		if (!isset($this->content['elem_text']['text'])) {
			return array('text' => '');
		}
		return array('text' => $this->content['elem_text']['text']);
	}

	function show_content($param) {
        return $this->content;
	}

	function show_parent_content($param) {
		if (empty($this->content['pid'])) {
			return array();
		}
		$tree_obj = & Registry::get('TTreeUtils');
		$content = $tree_obj->getContent($this->content['pid']);
		return array($content);

	}

	/**
	 * ��������� � ��� �������� meta ����
	 */
	function show_meta(&$params) {
		if (empty($params['simple'])) {
			$params['simple'] = true;
		}
		$ret['meta'] = array();
		$page_obj = & Registry::get('TPage');
		$meta = $page_obj->content['elem_meta'];

		$tpl_obj = & Registry::get('TTemplate');
		$title = $tpl_obj->get_config_vars('title');

		// ���� ������ ��������� ��� �������� - ������ ���
		if (!empty($meta['title'])) {
			$ret['meta']['title'] = $title.' &mdash; '.$meta['title'];
		}
		else {
			// ������� ��������� �� ����� ����� � ����� �������
			$ret['meta']['title'] = $title.' &mdash; '.$page_obj->content['name'];
			// ������ ��� �����
			if (count($page_obj->pids) == 1) {
				$ret['meta']['title'] = $title;
			}
			// ���� !simple, ������ � ��������� ���� � ��� �����
			elseif (!$params['simple'] && !empty($page_obj->pids) && count($page_obj->pids) > 1) {
				$path = array();
				for ($i = 1; $i < count($page_obj->pids); $i++) {
					$path[] = $page_obj->pids[$i]['name'];
				}
				$ret['meta']['title'] = $title.' &mdash; '.implode(' :: ', $path);
			}
		}

		$ret['meta']['description'] = $meta['description'];
		$ret['meta']['keywords'] = $meta['keywords'];

		return $ret;
	}

	function show_404($params) {
		$GLOBALS['document_status'] = 404;
		return array();
	}
}
// end of class TPage

?>