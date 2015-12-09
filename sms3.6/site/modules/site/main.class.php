<?php
class TMain {

	var $pids;
	// ���� ������ ������������ � setup ������-�������
	var $allowed_methods;

	// ������� ������� ��������
	var $content;
	var $shown_elems;


	var $url; // ������ ����. ������� � ������
	var $dirs_url; // ������� ���� ��� lang. ������� � ������
	var $dirs_lang; // ������� lang. ������� � ������
	var $dirs; // ������ ���� url
	var $stop; // �������� ��� ��������� ����� ����������
	var $root;

	// ��������� - ���� �������� ���� ����������, ��� ���������� ��� ������ �������� ����������� ����������� �������
	// ������������ ������ ��� ������ save_in_cache()
	var $_save_in_cache;
	// ��������� - ���� ������������� ���������� �� ��������������� �������
	// ������������ ������ ��� ������ restore_from_cache()
	var $_restore_from_cache;
	var $elem_id; // id �������� � ����� url (id).(type).html
	var $elem_type; // type �������� � ����� url (id).(type).html

	var $root_path;
	var $base_href;
	var $lang;

	var $template;

	function TMain() {
	}

	//������� ������ ����������. ������� ����� ��� ������� ����������
	function setup() {
		$lang_obj = & Registry::get('TLang');
		$this->lang = $lang_obj->getCurrentLanguage();
		$this->dirs_lang = $lang_obj->getLangDirPrefix();
		$this->root = $lang_obj->getLangRootId();

        $this->tpl = & Registry::get('TRusoft_View');
        $this->tpl->template_dir = "templates";
        // �������� ��������� ���������
        $messages['messages'] = $this->getMessagesForTemplate();
        $this->tpl->assign((array)$messages);

		$this->root_path = $this->root_path();
		$this->base_href = $this->base_href();

		//$this->allowed_items = array('news', 'doc', 'tab');
	}

	function execModuleMethod($module, $method,  $dirs) {
		$this->init($dirs);

		$config_obj = & Registry::get('TConfig');
		$module_config = $config_obj->getConfigByModuleName($module, ROOT_ID);

		$module_loader = & Registry::get('TModuleLoader');
		$method = !empty($method) ? $method : $module_config['do'];
		$value = $module_loader->callModuleMethod($module_config['class'], $method, $this->dirs);

		// @todo ���������� ����������, ���� ������ ����� �������� ���� ���������
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
		$module_loader = & Registry::get('TModuleLoader');
		$node_id = empty($this->dirs) ? $this->root : $this->dirs;

		$this->pids = $tree_obj->getPids($node_id);
		$this->content = end($this->pids);
		$this->content = $tree_obj->getContent($this->content['id']);

		// ������ �������� ���� �� ���� � ��
		if (isset($this->content['redirect']) && !empty($this->content['redirect'])) {
			redirect($this->content['redirect']);
		}

		// ���� 1) ����� �������� ENABLE_JUMP=1, 2) ������� �������� ����� ���� �� �������� �����,  3) ������� ������� �������� ������, 4) ���� �������� ��������
		// �� ��������� �� �������� ��������
		if (ENABLE_JUMP) {
			global $jump_params, $site_domains, $domain;
			if (!isset($jump_params))
				$jump_params = array(
					'types'	=> array('text'), // � ����� ����� ������� �������� �������
					'elems'	=> array( // � ���� �������� � ����� ��������� ������� ��������
						'elem_text' => 'text',
					),
				);
			if (in_array($this->content['type'], $jump_params['types'])) {
				$count_empty_fields = 0; // ���������� ������������ ������ �����
				$count_fields = 0; // ����� ���������� ����������� �����
				foreach ($jump_params['elems'] as $table=>$field) {
					$count_fields++;
                    if ($table == 'tree') {
                        if (!isset($this->content) || empty($this->content[$field])) $count_empty_fields++;
                    } else {
                        if (!isset($this->content[$table]) || empty($this->content[$table][$field])) $count_empty_fields++;
                    }
				}
				if ($count_empty_fields == $count_fields) {
				    $where = '';
				    if (isset($jump_params['types2'])) {
				        $where .= ' AND type IN ("'.implode('","', $jump_params['types2']).'")';
				    }
					$child_page = sql_getValue("SELECT page FROM ".$tree_obj->table." WHERE visible>0 AND pid=".$this->content['id'].$where." ORDER BY priority LIMIT 1");

					if ($child_page) {
						$redirect = $this->base_href;
						if (strpos($_SERVER['REQUEST_URI'], '/cd/') !== false) $redirect .= 'cd/';
						if (count($site_domains[$domain]['langs']) > 1) $redirect .= $this->lang.'/';
						$redirect .= $this->content['href'].'/'.$child_page;
						redirect($redirect);
					}
				}
			}
		}
		//$this->tpl->assign('content', $this->content);

		// �������� ����� ��������� ����� ���������� (last-Modifined)
		$handle = opendir(PATH_CACHE_TABLES);
		$uptimes = array();
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
			    $uptimes[] =  filemtime(PATH_CACHE_TABLES.$file);
			}
		}
		closedir($handle);

		//����� ����� ���� �� $this->content , ������� � ���� 20060606153438
		foreach ($this->content as $k => $v) {
			$time = '';
			if (is_array($v)){
				if (!isset($v['uptime'])) continue;
				$time = $v['uptime'];
			} else if ($k == 'uptime') $time = $v;


			if (!empty($time)) {
			    if (strpos($time, '-') === false) {
				    $time = substr($time,0,4).'-'.substr($time,4,2).'-'.substr($time,6,2).' '.substr($time,8,2).':'.substr($time,10,2).':'.substr($time,12,2);
			    }
				$uptimes[] = strtotime($time);
			}
		}
		$GLOBALS['update_time'] = max($uptimes);

		// ���� ������� �� ��� �������, �� �� ��� �� ��������, �� ���������� �� �����
		$cache_page = & Registry::get('TPageCache');
		$cache_page->IfModifiedSince($GLOBALS['update_time']);

		$this->show_page();
		$this->display();
	}

	function init($dirs) {

		$this->dirs = $dirs;
		$this->setup();

		// @todo ������������� � ������������ ���� ��������� elem � ����
		// ���������, ���� �� � ���� �������
		/*if(isset($this->dirs) && !empty($this->dirs)) {
			if(strpos($this->dirs[count($this->dirs)-1], '.html')) {
				if(count($elem = split('\.', array_pop($this->dirs))) != 3) $this->error_404('unknown element in URL');
				// ���������, �������� �� ������ ������� ��� �����������
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

		$this->url = $this->dirs_lang.$this->dirs_url;

		$this->assign_vars();
	}

	//� ������� ������ ����������� ������ ����������, ����������� �� ������� ����������
	function assign_vars() {
		$this->tpl->assign('base_href', $this->base_href);
		$this->tpl->assign('lang', $this->lang);
		$this->tpl->assign('dirs_url', $this->dirs_url);
		$this->tpl->assign('dirs_lang', $this->dirs_lang);
		$this->tpl->assign('url', $this->url);
		$this->tpl->assign('root', $this->root);
	}

	//���������� ����� � ����������, �������, ����������� �������.
	function display() {
		// �������� �������� �� ��������
		if (!$this->template) {
			log_error('No template for display');
			return;
		}

		// @todo �������� �� ����������� �����������
		if (isset($this->cacheble)) {
			$this->caching = SMARTY_CACHING;
			$this->tpl->display($this->template.'.html', $this->dirs_lang.$this->dirs_url);
		}
		else {
			$this->caching = false;
			$display = $this->tpl->render($this->template.'.html');
			$display = preg_replace_callback('#href="([^"]*)"#is', 'set_encode_url', $display);
			$display = preg_replace_callback('#href=\'([^\']*)\'#is', 'set_encode_url2', $display);
			echo $display;
		}
	}

	// �������������� ���������� root_path
	function root_path() {
		$path = dirname($_SERVER['SCRIPT_NAME']);
		if (substr($path, -1, 1) == '\\') $path = substr($path, 0, -1);
		if (substr($path, -1, 1) != '/') $path .= '/';
		return $path;
	}

	// �������������� ���������� base_href
	function base_href() {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$method = 'https://';
		}
		else $method = 'http://';
		return $method.$_SERVER['HTTP_HOST'].$this->root_path;
	}

	// �������������� ��������� ��������� ��� �������
	function getMessagesForTemplate () {
        $row = sql_getRows("SELECT IF(module='site',name, CONCAT(module,'_',name)) AS name, value FROM strings WHERE lang='".lang()."' AND root_id='".ROOT_ID."' ORDER BY module ASC", true);
        return $row;
	}
}
// TMain class

?>