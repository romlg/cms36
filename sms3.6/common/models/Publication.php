<?php

/**
 * Модель Публикации
 */

class Publication
{

    var $_table = "publications";
    var $_table_comments = "publications_comments";
    var $_table_meta = "publications_meta";

    var $_id = 0;
    var $_data = array();

    /**
     * @var string  название элемента в url
     */
    var $_url = 'p';

    /**
     * @var TPublications $_publ_obj
     */
    var $_publ_obj = null;

    function Publication($id = 0) {
        $this->_publ_obj = & Registry::get('TPublications');
        if ((int)$id) {
            $this->_id = (int)$id;
            $this->init();
        }
    }

    function init() {
        $this->_data = sql_getRow("SELECT * FROM {$this->_table} WHERE id={$this->_id} LIMIT 1");
    }

    function setData($data) {
        $this->_data = $data;
    }

    function getData() {
        return $this->_data;
    }

    /**
     * Возвращает ID публикации
     * @return int
     */
    function getId() {
        return $this->_id;
    }

    /**
     * Возвращает ID основного раздела
     * @return int
     */
    function getPid() {
        return $this->_data['pid'];
    }

    /**
     * Возвращает название публикации
     * @return string
     */
    function getName() {
        return $this->_data['name'];
    }

    /**
     * Возвращает дату публикации
     * @return string
     */
    function getDate() {
        return $this->_data['date'];
    }

    /**
     * Возвращает краткий текст о публикации
     * @return string
     */
    function getNotice() {
        return $this->_data['notice'];
    }

    /**
     * Возвращает текст о публикации
     * @return string
     */
    function getText() {
        return $this->_data['text'];
    }

    /**
     * Возвращает значение поля
     * @param string $name
     * @return string
     */
    function get($name) {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            return false;
        }
    }

    /**
     * Возвращает фото публикации
     * @param array $size
     * @param string $resize_method
     * @return string
     */
    function getImage($size = array(), $resize_method = 'adaptiveResize') {
        $image = $this->_data['image'];
        if ($size && $image) {
            if ($image[0] == '/') $image = substr($image, 1);
            $realSize = getimagesize($image);
            if (!$realSize) {
                $image = $this->_data['image'];
            } else {
                if ( (isset($size[0]) && $realSize[0] > $size[0]) ||
                     (isset($size[1]) && $realSize[1] > $size[1])
                ) {
                    include_once "phpThumb/image.class.php";
                    try {
                        $image = TImage::thumb(
                            $size[0],
                            $size[1],
                            '/' . $image,
                            array('method' => $resize_method)
                        );
                    } catch (Exception $e) {
                        $image = $this->_data['image'];
                    }
                } else {
                    $image = $this->_data['image'];
                }
            }
        }
        return $image;
    }

    /**
     * Возвращает отпарсенный шаблон с галереей
     * @param $tpl - название шаблона
     * @param int $limit - кол-во фотографий в одном ряду
     * @param boolean $type - тип выдачи массива, с разбивкой или без
     * @return string
     */
    function getGallery($tpl, $limit = 4, $type = true) {
        if (!$tpl) return "";

        $gallery = sql_getRows("
        SELECT *, image_small AS smallimagepath, image_large AS largeimagepath, image_large AS imagepath, name AS alt
        FROM {$this->_table}_gallery
        WHERE pid={$this->_id} AND visible=1
        ORDER BY priority");
        if (!$gallery) return "";

        foreach ($gallery as $key => $val) {
            $size = getimagesize(substr($val['image_small'], 1));
            $gallery[$key]['width'] = $size[0];
            if (is_file(substr($val['image_small'], 1))) $gallery[$key]['image_small_exist'] = 1;
            if (is_file(substr($val['image_large'], 1))) $gallery[$key]['image_large_exist'] = 1;
        }

        if ($type) {
            //формируем массив для построчного вывода
            $glr = array();
            $i = $j = 0;
            foreach ($gallery as $key => $val) {
                $glr[$i][] = $gallery[$key];
                $j++;
                if ($j >= $limit) {
                    $i++;
                    $j = 0;
                }
            }
        } else {
            $glr = $gallery;
        }

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        $view->assign(array(
            'gallery' => $glr,
            'count_gallery' => count($gallery),
            'count_gallery_pages' => count($gal['gallery'])
        ));
        $ret = $view->render($tpl);

        $view->__unset('gallery');

        return $ret;
    }

    /**
     * Возвращает отпарсенный шаблон с приложенными файлами
     * @param $tpl - название шаблона
     * @return string
     */
    function getFiles($tpl) {
        if (!$tpl) return "";

        $files = sql_getRows("
        SELECT *
        FROM {$this->_table}_file
        WHERE pid={$this->_id} AND visible=1
        ORDER BY priority");
        if (!$files) return "";

        foreach ($files as $key => $val) {
            $files[$key]['ext'] = $this->getFileExt($val['fname']);
            $files[$key]['size'] = $this->getFileSize(substr($val['fname'], 1));
            $files[$key]['ico'] = $this->getFileIco($val['fname']);
        }

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        $view->assign(array('files_block' => $files));
        $ret = $view->render($tpl);

        $view->__unset('files');

        return $ret;
    }

    /**
     * Функция вычисляет размер файла
     *
     * @param $file - путь к файлу
     * @return массив с размером и единицей измерения
     */
    function getFileSize($file) { // Узнаем размер файла с единицей измерения
        $units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $unit = '';
        $size = is_file($file) ? filesize($file) : 0;
        if (!$size) $size = 0;
        else {
            $pass = 0;
            while ($size >= 1024)
            {
                $size /= 1024;
                $pass++;
            }
            $size = round($size, 2);
            $unit = $units[$pass];
        }
        return array('size' => $size, 'unit' => $unit);
    }

    /**
     * Функция определения иконки файла по его расширению
     *
     * @param $file - путь к файлу
     * @return путь к картинке
     */
    function getFileIco($file) {
        $dot_pos = strrpos($file, ".");
        if ($dot_pos === false) return '/images/icons/xxx.gif';
        $ext = substr($file, $dot_pos + 1);
        if (is_file('images/icons/' . strtolower($ext) . '.gif'))
            return '/images/icons/' . strtolower($ext) . '.gif';
        else return '/images/icons/xxx.gif';
    }

    /**
     * Функция определения расширения файла
     */
    function getFileExt($file) {
        $dot_pos = strrpos($file, ".");
        if ($dot_pos === false) return "";
        $ext = substr($file, $dot_pos + 1);
        return $ext;
    }

    /**
     * Возвращает путь до публикации относительно текущей страницы
     * @return string
     */
    function getPath() {
        $currentDir = $_SERVER['REQUEST_URI'];
        if (strpos($currentDir, '?') !== false) $currentDir = substr($currentDir, 0, strpos($currentDir, '?'));
        $currentArr = explode("/", $currentDir);

        $ret = array();
        foreach ($currentArr as $p) {
            if ($p == $this->_url) break;
            if (!empty($p)) $ret[] = $p;
        }
        if ($ret) {
            return '/' . implode('/', $ret) . '/' . $this->_url . '/' . $this->_id;
        }

        return $this->getMainPath();
    }

    /**
     * Возвращает путь до основной страницы публикации
     * @return string
     */
    function getMainPath() {
        $data = $this->getData();
        if (!$data['pid'] && !$data['pids']) return '';

        $page = & Registry::get('TPage');
        $path = "";

        /**
         * @var TTreeUtils $utils
         */
        $utils = & Registry::get('TTreeUtils');
        if ($data['pid']) {
            $root_id = sql_getValue("SELECT root_id FROM tree WHERE id={$data['pid']}");
            if ($root_id == ROOT_ID) {$path = '/' . $page->dirs_lang . $utils->getPath($data['pid']);}
            else {
                global $site_domains;
                foreach ($site_domains AS $key => $value) {
                    if (isset($value['langs'])) {
                        foreach ($value['langs'] AS $subkey => $subvalue) {
                            if ($subvalue['root_id'] == $root_id) $path = "http://" . $key . "/" . $subkey . "/" . $utils->getPath($data['pid']);
                        }
                    } else {
                        if ($value['root_id'] == $root_id) $path = "http://" . $key . "/" . $utils->getPath($data['pid']);
                    }
                }
            }
        }
        if (!$path && $data['pids']) {
            $data['pids'] = explode(',', $data['pids']);
            foreach ($data['pids'] as $pid) {
                $pid = (int)$pid;
                $root_id = sql_getValue("SELECT root_id FROM tree WHERE id={$pid}");
                if ($root_id == ROOT_ID) {$path = '/' . $page->dirs_lang . $utils->getPath($pid);}
                else {
                    global $site_domains;
                    foreach ($site_domains AS $key => $value) {
                        if (isset($value['langs'])) {
                            foreach ($value['langs'] AS $subkey => $subvalue) {
                                if ($subvalue['root_id'] == $root_id) $path = "http://" . $key . "/" . $subkey . "/" . $utils->getPath($pid);
                            }
                        } else {
                            if ($value['root_id'] == $root_id) $path = "http://" . $key . "/" . $utils->getPath($pid);
                        }
                    }
                }
                if ($path) break;
            }
        }
        if ($path) $path .= '/' . $this->_url . '/' . $this->_id;

        if (strpos($path, "http://") !== false) {
            $path = "http://" . str_replace('//', '/', substr($path, strlen("http://")));
        } else {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }

    /**
     * Возвращает путь относительно переданной страницы
     * @param int $page_id - id страницы относительно которой нужно вернуть путь
     */
    function getRelativePath($page_id) {
        if (!$page_id) return $this->getPath();
        $lang = (LANG_DEFAULT != lang()) ? "/".lang() : "";
        $utils = & Registry::get('TTreeUtils');
        $path = $utils->getPath($page_id);
        return $lang.'/'. $path. '/' . $this->_url . '/' . $this->_id;
    }

    /**
     * Возвращает мета-теги
     * @return array
     */
    function getMeta() {
        return sql_getRow("SELECT * FROM {$this->_table_meta} WHERE pid={$this->_id} AND root_id=" . ROOT_ID);
    }

    /**
     * Возвращает кол-во комментариев
     * @return array
     */
    function getCommentsCount() {
        $ret = sql_getValue("SELECT COUNT(1) FROM {$this->_table_comments} WHERE publication_id={$this->_id} AND visible=1");
        return $ret;
    }

    /**
     * Возвращает дерево комментариев
     * @param string $user_model_name
     * @return array
     */
    function getComments($user_model_name = 'User') {
        $this->user_model_name = $user_model_name;
        $ret = null;
        $this->_commentsTree($ret);
        return $ret;
    }

    /**
     * Возвращает все комментарии в виде простого массива
     * @return array
     */
    function _getComments() {
        return sql_getRows("SELECT * FROM {$this->_table_comments} WHERE publication_id={$this->_id} AND visible=1 ORDER BY date");
    }

    /**
     * Построение дерева комментариев
     * @param array $list
     */
    private function _commentsTree(&$list, $level = 0) {
        static $tree;
        if (!isset($tree)) {
            $_tree = $this->_getComments();
            $tree = array();
            foreach ($_tree as $v) {
                $pid = $v['pid'] ? $v['pid'] : 'NULL';
                $tree[$pid][] = $v;
            }
        }
        if (!isset($list)) $list = $tree['NULL'];
        foreach ($list as $k => $v) {
            $list[$k] = $this->_formatComment($v);
            $list[$k]['level'] = $level;
            $list[$k]['menu'] = isset($tree[$v['id']]) ? $tree[$v['id']] : array();
            if ($list[$k]['menu']) $this->_commentsTree($list[$k]['menu'], $level + 1);
        }
    }

    /**
     * Форматирование комментария перед выводом
     * @param array $item
     * @return array
     */
    private function _formatComment($item) {
        $item['date'] = date('d.m.Y H:i', strtotime($item['date']));
        $item['comment_user'] = $item['user_id'] ? new $this->user_model_name($item['user_id']) : false;
        return $item;
    }

    /**
     * Сохранение нового комментария
     * @param string $text - текст комментария
     * @param int $user_id - ID пользователя
     * @param int $pid - ID родительского комментарий
     * @param string $name - имя пользователя, если $user_id пустое
     * @return mixed
     */
    function newComment($text, $user_id, $pid = 0, $name = '') {
        $page = & Registry::get('TPage');
        $moderate = (int)$page->tpl->messages['publications_comment_moderate'];
        $data = array(
            'pid' => $pid ? $pid : 'NULL',
            'publication_id' => $this->_id,
            'user_id' => $user_id ? $user_id : 'NULL',
            'date' => date('Y-m-d H:i:s'),
            'name' => $name,
            'text' => $text,
            'visible' => $moderate ? 0 : 1
        );
        $id = sql_insert($this->_table_comments, $data);
        touch_cache($this->_table_comments);
        return $id;
    }
}