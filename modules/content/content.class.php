<?php

include_once 'phpmailer/class.phpmailer.php';

class TContent {

	function TContent(){
	    $GLOBALS['update_time'] = 0;
	}

	/**
	 * Функция построения постраничной навигации
	 *
	 * @param $count - общее количество элементов
	 * @param $limit - количество элементов на странице
	 * @param $offset - номер текущей страницы
	 * @param $url - ссылка
	 * @param $limits - массив допустимых количеств элементов на странице
	 * @return отпарсенный шаблон
	 */
	function getNavigation($count,$limit,$offset,$href,$limits=array(),$tmpl = 'pages.html'){
		$url = '';
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
			$url = $_SERVER['REDIRECT_QUERY_STRING'];
			$turl = explode('&', $url);
			foreach ($turl as $k=>$v){
				$turl2[$k] = explode('=', $v);
				$turl2[$k][0] = htmlspecialchars(urldecode($turl2[$k][0]));
				if ($turl2[$k][0] == 'offset' || $turl2[$k][0] == 'limit'){
					unset($turl[$k]);
					unset($turl2[$k]);
				}
			}
			$url = implode('&',$turl);
		}

		$pages = pages($count, $limit, $offset, $href."?".$url);
		$pages['limits']['65535'] = 'всё';

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        $view->assign(array('pages' => $pages));
        return $view->render($tmpl);
	}

	/**
	 * Функция возвращает путь к версии для печати
	 *
	 */
	function getPrintUrl(){
		$page = & Registry::get('TPage');
		$path = $page->tpl->_tpl_vars['params']['dirs'];
		if ($path[0] == '/') $path = substr($path, 1);
		$path = '/print/'.$path;
        return $path;
	}

	/**
	 * Функция построения списка файлов, прикрепленных к странице
	 *
	 */
	function getFiles(){
		$page = & Registry::get('TPage');
		$files = sql_getRows("SELECT id, ".(LANG_SELECT ? "IF (name_".lang()." <> '', name_".lang().", name_".DEFAULT_LANG.") as name" : "name").", fname FROM elem_file WHERE pid=".$page->content['id']." AND visible > 0 ORDER BY priority");
		foreach ($files as $key=>$val){
			$files[$key]['ext'] = $this->getFileExt($val['fname']);
			$files[$key]['size'] = $this->getFileSize(substr($val['fname'], 1));
			$files[$key]['ico'] = $this->getFileIco($val['fname']);
		}
		if (!empty($files)) return array('files_block' => $files);
	}

	/**
	 * Функция вычисляет размер файла
	 *
	 * @param $file - путь к файлу
	 * @return массив с размером и единицей измерения
	 */
	function getFileSize($file) {  // Узнаем размер файла с единицей измерения
		$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
		$unit = '';
		$size = is_file($file) ? filesize($file) : 0;
		if (!$size) $size = 0;
		else {
			$pass = 0;
			while( $size >= 1024 )
			{
				$size /= 1024;
				$pass++;
			}
			$size = round($size, 2);
			$unit = $units[$pass];
		}
		return array('size'=>$size,'unit'=>$unit);
	}

	/**
	 * Функция определения иконки файла по его расширению
	 *
	 * @param $file - путь к файлу
	 * @return путь к картинке
	 */
	function getFileIco($file) {
		$dot_pos = strrpos($file,".");
		if ($dot_pos === false) return '/images/icons/xxx.gif';
		$ext = substr($file,$dot_pos+1);
		if (is_file('images/icons/'.strtolower($ext).'.gif'))
		return '/images/icons/'.strtolower($ext).'.gif';
		else return '/images/icons/xxx.gif';
	}

	/**
	 * Функция определения расширения файла
	 *
	 */
	function getFileExt($file) {
		$dot_pos = strrpos($file,".");
		if ($dot_pos === false) return "";
		$ext = substr($file,$dot_pos+1);
		return $ext;
	}

	/**
	 * Функция генерирует уникальный ID кеша
	 *
	 */
	function _cache($block) {
		$page_obj = & Registry::get('TPage');

		$cache_id = lang().'_'.$page_obj->content['id'].'_'.$_SERVER['REQUEST_URI'];
		// из всего, что есть в get, формируем кеш
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $cache_id .= '_'.$_SERVER['REDIRECT_QUERY_STRING'];

		return $cache_id;
	}

	/**
	 * Функция закачки файла
	 *
	 */
	function download() {
		// Проверяем: если нет в начале files/ то подставляем
		$filename = get('filename','','pg');
		path($filename);
		$fd = FILES_DIR;
		$rp = realpath($fd);

		if (strpos($filename, '/') == 0) $filename = substr($filename, 1);
		if (strpos($fd, '/') == 0) $fd = substr($fd, 1);
		if (substr($fd, -1) == '/') $fd = substr($fd, 0, -1);

		if (substr($filename, 0, strlen($fd)) != $fd) {
			$filename = $fd.'/'.$filename;
		}

		$vp = dirname(realpath($filename));
		if (substr($vp, 0, strlen($rp)) != $rp) redirect('404');

		ob_clean();
		header("Accept-Ranges: bytes");
		header("Content-Length: ".filesize($filename)."");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".basename($filename)."\"");

		readfile($filename);
		exit();
	}

	//ФМБАшная галерея
	function showGallery($params, $pageid=""){
		$ret = array ();
		$page = & Registry::get('TPage');
		$pageid = ($pageid)?$pageid:$page->content['id'];
		$gallery = sql_getRows("SELECT *, image_small AS smallimagepath, image_large AS largeimagepath, image_large AS imagepath, name AS alt FROM elem_gallery WHERE visible > 0 AND pid=".$pageid." ORDER BY priority");
		if (count($gallery)) {
			$ret['gallery'] = $this->getGallery($gallery);
    	    $ret['count_gallery'] = $gallery['count_gallery'];
			$ret['count_gallery_pages'] = count($ret['gallery']);
		}

		return $ret;
	}
	function getGallery($gallery) {
		    $gallerycnt = count($gallery);
		    foreach ($gallery as $key=>$val) {
			    $size = getimagesize(substr($val['image_small'], 1));
			    $gallery[$key]['width'] = $size[0];
			    if (is_file(substr($val['image_small'], 1))) $gallery[$key]['image_small_exist'] = 1;
			    if (is_file(substr($val['image_large'], 1))) $gallery[$key]['image_large_exist'] = 1;
		    }

		    //формируем массив для построчного вывода
		    $glr = array();
		    $i = $j = 0;
		    foreach ($gallery as $key=>$val) {
			    $glr[$i][] = $gallery[$key];
			    $j++;
			    if ($j>=4) {
				    $i++;
				    $j=0;
			    }
		    }
		    $gallery = $glr;

		    return $gallery;
	}

	/**
	 * Формирование подменю текущей страницы
	 *
	 */
	function getContentMenu(&$params) {
		$menu = new TMenu();
		$page = & Registry::get('TPage');
		$list = array();

		$level = $page->content['level'];
		$pid = $page->content['id'];
		while (empty($list) && $pid != ROOT_ID) {
			$list = $menu->menu($pid, $pid, 1, 1, $params['types']);
			$pid = $page->pids[$level]['pid'];
			$level--;
		}

		return array('menu' => $list);
	}

	/**
	/*	Выборка id подразделов
	/*
	/**/
	function getChilds($id){
		global $catalog_childs_ids;
		if (!$catalog_childs_ids) {
			$rows = sql_getRows('SELECT id, pid
			FROM tree AS t1
			LEFT JOIN sites_tree AS t2 ON (t1.id=t2.tree_id AND t2.root_id='.ROOT_ID.')
			WHERE type="catalog" AND (IF(t2.visible IS NOT NULL, t2.visible > 0, t1.visible > 0))');
			foreach ($rows as $key=>$val) {
				$catalog_childs_ids[$val['pid']][] = $val['id'];
			}
		}
		$ids = isset($catalog_childs_ids[$id]) ? $catalog_childs_ids[$id] : array();
		if ($ids) foreach ($ids as $key=>$val)
			$ids = array_merge($ids, $this->getChilds($val));
		return $ids;
	}

    /**
     * Анонсы разделов
     *
     * @return array
     */
    function getAnonce(){
    	$ret['anonce'] = sql_getRows('SELECT * FROM tree WHERE visible > 0 AND description<>"" AND image <> "" ORDER BY pid, priority');
    	$tree = &Registry::get('TTreeUtils');
    	foreach ($ret['anonce'] as $key=>$val) {
    		$ret['anonce'][$key]['href'] = $tree->getPath($val['id']);
    	}
    	return $ret;
    }

    /**
     * Пример модуля с формой
     * @return array
     */
    function showSomeForm() {
        $page = & Registry::get('TPage');

        $form = new TForm_generator();

        $form->zend_form->setAction('/' . $page->content['href'])
                ->setMethod('POST')
                ->setAttrib('accept-charset', 'windows-1251')
                ->setAttrib('enctype', 'multipart/form-data');

        $elements = array(
            array(
                'name' => 'fio',
                'type' => 'text',
                'text' => 'ФИО',
                'req' => 1
            ),
            array(
                'name' => 'phone',
                'type' => 'text',
                'text' => 'Телефон',
                'req' => 0,
                'check' => 'phone',
            ),
            array(
                'name' => 'text',
                'type' => 'textarea',
                'text' => 'Текст',
                'req' => 0
            ),
            array(
                'name' => 'number1',
                'type' => 'checkbox',
                'text' => 'Выберите числа',
                'req' => 0,
                'options' => array('1' => '10', '2' => '20', '3' => '30')
            ),
            array(
                'name' => 'number2',
                'type' => 'radio',
                'text' => 'И тут тоже',
                'req' => 0,
                'options' => array('1' => '10', '2' => '20', '3' => '30')
            ),
            array(
                'name' => 'number3',
                'type' => 'select',
                'text' => 'И тут',
                'req' => 0,
                'options' => array('1' => '10', '2' => '20', '3' => '30')
            ),
            array(
                'name' => 'file',
                'type' => 'file',
                'text' => 'И файл приложите',
                'req' => 0,
            ),
            array(
                'name' => 'email',
                'type' => 'text',
                'text' => 'E-mail',
                'req' => 0,
                'check' => 'email'
            ),
            array(
                'name' => 'zip',
                'type' => 'text',
                'text' => 'Индекс',
                'req' => 0,
                'check' => 'zip'
            ),
            array(
                'type' => 'captcha',
                'text' => 'Проверочный код',
                'req' => 1,
                'check' => 'captcha'
            ),
            array(
                'type' => 'submit',
                'text' => 'Отправить',
            ),
        );

        $form->generateElements($elements);

        if (!empty($_POST)) {
            if ($form->zend_form->isValid($_POST)) {
                $values = $form->zend_form->getValues();
                pr("Это данные с формы, делайте с ними что хотите:");
                pr($values);
                pr($_FILES);
            }
        }

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        $ret['form'] = iconv('utf-8', 'windows-1251', $form->zend_form->render($view));
        return $ret;
    }

    /**
     * Рекурсивно меняем кодировку
     * @param $str
     * @param $charset_in
     * @param $charset_out
     * @return array|string
     */
    function recursive_iconv($str, $charset_in = 'windows-1251', $charset_out = 'utf-8') {
        if (is_array($str)) {
            $ret = array();
            foreach ($str as $k=> $v) $ret[$k] = $this->recursive_iconv($v, $charset_in, $charset_out);
            return $ret;
        } elseif (is_string($str)) {
            return iconv($charset_in, $charset_out, $str);
        } else return $str;
    }

}

?>