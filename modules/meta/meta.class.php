<?php

class TMeta {

	/**
	 * ¬ставл€ет в код страницы meta тэги
	 */
	function show_meta($params) {
		$ret['meta'] = array();
		$page_obj = & Registry::get('TPage');
		$meta = isset($page_obj->content['elem_meta']) ? $page_obj->content['elem_meta'] : array();

		$tpl_obj = & Registry::get('TTemplate');
		$title = $tpl_obj->get_config_vars('title');

		// если задан title - то ставим его
		if (!empty($meta['title'])) {
            if (isset($page_obj->pids[1]) && $page_obj->pids[1]['page'] == 'aboutsite')
                $ret['meta']['title'] = $meta['title'];
            else
                $ret['meta']['title'] = $title.'  '.$meta['title'];
		} else $ret['meta']['title'] = $title.'  '.$page_obj->content['name'];

		// если задано описание страницы, ставим его, иначе берем из контента
		if(!empty($meta['description']) && !isset($_GET['prod_id'])){
			$ret['meta']['description'] = $meta['description'];
		}else{
			$ret['meta']['description'] = $this->get_page_description($page_obj, $tpl_obj);
		}
		// если заданы ключевые слова, ставим их, иначе берем из контента
		if(!empty($meta['keywords']) && !isset($_GET['prod_id'])){
			$ret['meta']['keywords'] = $meta['keywords'];
		}else{
			$ret['meta']['keywords'] = $this->get_page_keywords($page_obj, $tpl_obj);
		}
		return $ret;
	}

	function get_page_keywords($page, $tpl_obj) {
		$lenght = 1000;

        $data = $page->tpl->_tpl_vars['content']['name'];
        if (isset($page->tpl->_tpl_vars['content']['elem_text']['text'])) $data .= " ".$page->tpl->_tpl_vars['content']['elem_text']['text'];

        if (isset($page->pids[1]) && $page->pids[1]['page'] == 'tender') {   // ƒл€ тендеров - особые meta теги
            $data .= $this->getTenderMeta($page);
        } else if (isset($page->content['elem_module']['module'])) {
        	$func = 'get'.$page->content['elem_module']['module'].'Meta';
        	if (method_exists($this, $func)) $data .= $this->$func($page);
        }

		$data .= $tpl_obj->get_config_vars('auto_keywords');
		$title = $tpl_obj->get_config_vars('title');
		$name = $page->content['name'];

		$res = $title; // первое слово - название сайта
		if ($name) $res .= ', '.$name; // второе слово - название страницы
		$res = strtolower(strip_tags($res));
		$res = str_replace('"', '', $res);

		if ($data) {
			$data = preg_replace('/(&\w+;)|\'/', ' ', strtolower(strip_tags($data)));
			$words = preg_split('/(\s+)|([\.\,\:\(\)\"\'\!\;'.chr(171).chr(187).'])/m', $data);

			$stop = $tpl_obj->get_config_vars('auto_keywords_stop');
			$stop = preg_split('/(\s+)|([\.\,\:\(\)\"\'\!\;])/m', $stop.' '.$res);
			foreach ($words as $n => $word){
				if (strlen($word)<4 ||
					(int)$word ||
					strpos($word, '/')!==false ||
					strpos($word, '@')!==false ||
					strpos($word, '_')!==false ||
					strpos($word, '=')!==false ||
					in_array(strtolower($word), $stop)
					) unset($words[$n]);
			}
			// получаем массив с числом каждого слова
			$words = array_count_values($words);
			arsort($words); // сортируем - наиболее частые - вперед
			$words = array_keys($words);
			foreach ($words as $word) {
				if (strlen($res)>$lenght) break;
				$res .= ', '.$word;
			}
		}
		return $res;
	}

	function get_page_description($page, $tpl_obj) {
		$lenght = $tpl_obj->get_config_vars('auto_description_len');
		if(!$lenght) $lenght = 300;
		$description = isset($page->content['elem_text']['text']) ? $page->content['elem_text']['text'] : "";
        if (isset($page->pids[1]) && $page->pids[1]['page'] == 'tender') {   // ƒл€ тендеров - особые meta теги
            $description .= $this->getTenderMeta($page);
        } else if (isset($page->content['elem_module']['module'])) {
        	$func = 'get'.$page->content['elem_module']['module'].'Meta';
        	if (method_exists($this, $func)) $description .= $this->$func($page);
        }

		$description = strip_tags($description);
		// ¬ырезаем \n \r \t
		$description = str_replace(array(chr(13), chr(10), chr(9)), '', $description);
		// вырезаем двойные пробеыл
		$description = substr($description, 0, $lenght).'...';
		return $description;
	}

    function getItemMeta($page){
        $data = '';
        if (isset($page->tpl->_tpl_vars['news_item']))
        	$data .= ' '.$page->tpl->_tpl_vars['news_item']['text'];
        return $data;
    }

    function getObjectMeta($page){
        $data = '';
        if (isset($page->tpl->_tpl_vars['info'])) {
	        $descr_array = array('name', 'address', 'description', 'lot_id', 'purpose', 'remont', 'windows', 'registration', 'documents', 'f_release', 'transaction_type', 'object_type', 'heating', 'decoration', 'floor', 'electricity', 'infrastructure');
	        foreach ($descr_array as $key=>$val) {
	            $data .= ' '.$page->tpl->_tpl_vars['info'][$val];
	        }
        }
        return $data;
    }
}

?>