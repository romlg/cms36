<?php

class TNews {

	var $table = 'elem_news';
	var $sql = " n.pid=t.id AND n.visible>0 "; // ����� ����� ��� ���� ��������
	var $month = array('01' => '������', '02' => '�������', '03' => '�����', '04' => '������', '05' => '���', '06' => '����', '07' => '����', '08' => '�������', '09' => '��������', '10' => '�������', '11' => '������', '12' => '�������');

	/**
	 * ������� ���������� ������ ���������� �� post � get
	 *
	 */
	function getParams($params){
		$param = array();
		$param['id'] 			=	 get('id',null,'pg');
		$param['offset'] 		=	 (int)get('offset',0,'pg');
		$param['limit'] 		=	 (int)get('limit',0,'pg');
		return $param;
	}

	/**
	 *  ������� ���������� ���� �� ������� 
	 * 
	 */
	function getPathToNews($id, $param=array()) {
		$page = & Registry::get('TPage');
		if ($page->content['page'] == 'item') {
			return $param['path'].'item/'.$id;
		}
		$path = "";
		$tree = & Registry::get('TTreeUtils');
		$page_id = sql_getValue('SELECT pid FROM '.$this->table.' WHERE id='.$id);
		$path = $tree->getPath($page_id);
		return $path.'/item/'.$id;
	}
	
	// ----------------------------------------------------------
	// ---- ��� ������� -----------------------------------------
	// ----------------------------------------------------------
	function show(&$params) {
		
		$param = $this->getParams($params);

		$page = & Registry::get('TPage');

		if (empty($param['limit'])) $param['limit'] = $page->tpl->get_config_vars('count_hot_news');
		if (empty($param['limit']) || !is_numeric($param['limit'])) $param['limit'] = 20;
		
		$list = sql_getRows("SELECT n.* FROM ".$this->table." AS n, tree AS t WHERE ".$this->sql." AND n.pid=".$page->content['id']." ORDER BY n.date DESC LIMIT ".$param['offset'].",".$param['limit']);
		foreach ($list as $key=>$val) {
			$list[$key]['offset'] = sql_getValue('SELECT COUNT(*) FROM '.$this->table.' WHERE visible > 0 AND pid='.$page->content['id'].' AND date > "'.$val['date'].'" ORDER BY date');
		}

		// ���������
		$all_count = (int)sql_getValue('SELECT COUNT(*) FROM '.$this->table.' AS n, tree AS t WHERE '.$this->sql.' AND n.pid='.$page->content['id']);
		if ($all_count > $param['limit']) $ret['pages'] = TContent::getNavigation($all_count, $param['limit'], $param['offset'], $page->content['href']);

		$ret['list'] = $list;
		return $ret;
	}

	/**
	 *  ������� ��� ����������� ����� ������� 
	 * 
	 */
	function getOneNews(&$params) {

		$page = & Registry::get('TPage');

		$real_path = $_SERVER['REQUEST_URI'];
		if (substr($real_path, -1) != '/') $real_path .= '/'; // ������� ����
		
		$query = explode('?', $real_path);
		$pids = explode('/', $query[0]);

		$pos = array_search('item', $pids); // ������� ����� item � ���� ��������

		if ($pos !== false) $id = (int)$pids[$pos + 1]; // ���������� id ������� (������ ���������� � ���� ����� item)

		$param = $this->getParams($params);
		$param['path'] = '';
		for ($i=0; $i<=$pos-1; $i++)
			$param['path'] .= $pids[$i].'/';

		if (!$id) redirect('/404/');
		
		$param['id'] = $id;
		
		$ret = array();

		if (empty($param['limit'])) $param['limit'] = $page->tpl->get_config_vars('count_hot_news');
		if (empty($param['limit']) || !is_numeric($param['limit'])) $param['limit'] = 20;
		
		$ret['news_item'] = sql_getRow('SELECT * FROM '.$this->table.' WHERE id='.$param['id'].' AND visible > 0');
		if (empty($ret['news_item'])) redirect('/404');
		$ret['news_item']['href'] = $this->getPathToNews($ret['news_item']['id'], $param);
		$ret['news_item']['topic_href'] = $param['path'];
		
		// ���������
		$where = '1 ';
		if ($param['path'] != '/news/') $where .= ' AND pid='.$ret['news_item']['pid'];
		
		// ����� �������� � ������ ���������
		$all_count = sql_getValue('SELECT count(*) FROM '.$this->table.' WHERE '.$where.' AND visible > 0');
		// ����������� ������� ���������� ���������
		$pages = pages($all_count, 1, $param['offset'], $param['path'].'item');
		// ������ �� ���������� � ��������� �������
		$prev = sql_getValue('SELECT id FROM '.$this->table.' WHERE '.$where.' AND visible > 0 AND date > "'.$ret['news_item']['date'].'" LIMIT 1');
		$next = sql_getValue('SELECT id FROM '.$this->table.' WHERE '.$where.' AND visible > 0 AND date < "'.$ret['news_item']['date'].'" LIMIT 1');
		if ($prev) $pages['prev_url'] = $this->getPathToNews($prev, $param); else $pages['prev_url'] = '';
		if ($next) $pages['next_url'] = $this->getPathToNews($next, $param); else $pages['next_url'] = '';
		// ������� �������� � ������� ��������
		$sql = 'SELECT COUNT(*) FROM '.$this->table.' WHERE '.$where.' AND visible > 0 AND date > "'.$ret['news_item']['date'].'" AND id <> '.$ret['news_item']['id'];
		$pages['current_page'] = (int)sql_getValue($sql) + 1;
		$pages['current_offset'] = $pages['current_page'] - 1;
		// page_url
		$pages['page_url'] = $param['path'].'item';
		// id ��������
		foreach ($pages['pages'] as $key => $val) {
			$pages['ids'][$val] = sql_getValue('SELECT id FROM '.$this->table.' WHERE '.$where.' AND visible > 0 ORDER BY date DESC LIMIT '.($val-1).', 1');
		}
		$page->tpl->assign(array( 'pages'        => $pages,
		));
		$ret['pages'] = $page->tpl->fetch('ids_pages.html');
		
		$tree = & Registry::get('TTreeUtils');
		$page->tpl->_tpl_vars['pids_full']['pids'] = $tree->getPidsByUrl($param['path']);
		$last = end($page->tpl->_tpl_vars['pids_full']['pids']);
		$page->content['id'] = $last['id'];
		$page->tpl->_tpl_vars['pids_full']['pids'][] = array(
			'name'	=> strip_tags($ret['news_item']['name']),
		);
		$page->pids = $page->tpl->_tpl_vars['pids_full']['pids'];
		$page->tpl->_tpl_vars['content']['name'] = $page->content['name'] = strip_tags($ret['news_item']['name']);
		
		return $ret;
	}
	
	function getChilds($id){
		$tree = & Registry::get('TTreeUtils');
		$childs = $tree->getSubItems($id);
		$ids = array();
		foreach ($childs as $key=>$val) $ids[] = $val['id'];
		return $ids;
	}
	
	/**
	 * ������� ��� ��������� ����������� ID ����
	 *
	 */
	function _cache($block) {
		$page_obj = & Registry::get('TPage');
		$cache_id = lang().'_'.$page_obj->content['id'];
		// �� �����, ��� ���� � get, ��������� ��� 
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $cache_id .= '_'.$_SERVER['REDIRECT_QUERY_STRING'];
		return $cache_id;
	}
	
	/**
	 * ������� ��� ��������� ������� �������� ��� RSS
	 *
	 */
	function rss(&$params) {
		$list = getSql("SELECT id, date, UNIX_TIMESTAMP(date) as ts, text_".lang()." as text, description_".lang()." as description FROM ".$this->table." WHERE visible > 0 AND root_id=".ROOT_ID." ORDER BY date DESC LIMIT ".$params['limit']);
		header('Content-Type: text/xml');
		return array('list'=>$list);
	}
}
// end of class TNews

?>