<?php

require_once (module('stat'));

class TPathes extends TStat {

	var $name = 'stat/stat_pathes';

	########################

	function TPathes() {
		global $str;

		TStat::TStat();

		$str[get_class_name($this)] = $str['tstat'] + array(
			'pathes'	=> array(
				'Пути по сайту',
				'Site pathes',
			),
            'see_pages1'    => array(
                '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[see_page]=%s\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;Из них смотрело 1 страницу',
                'See one pages',
            ),
            'see_pages2'    => array(
                '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[see_page]=%s\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;Из них смотрело 2 страницы',
                'See two pages',
            ),
            'see_pages3'    => array(
                '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[see_page]=%s\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;Из них смотрело 3 страницы',
                'See three pages',
            ),
            'see_pages4'    => array(
                '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[see_page]=%s\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;Из них смотрело более 3 страниц',
                'See more then three pages',
            ),
		);
	}

	######################

	function Show() {
		global $limit;
		$this->Init();
		$this->AddStrings($ret);
		$offset	= (int)get('offset');
		$limit	= (int)get('limit', $this->Param('limit', $limit));

		$ret['navig'] = $this->NavigForm();
		$ret['site_select'] = $this->selectSite(array(
			'hidden'	=> array('show' => $this->show),
		));

		$count = sql_getValue("SELECT COUNT(DISTINCT(path)) FROM ".$this->sess_table." WHERE robot=0");
		$total_value = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0");
		$data = sql_getRows("SELECT path, COUNT(*) AS kol, COUNT(*)/".$total_value."*100 AS proc, loads FROM ".$this->sess_table." WHERE robot=0 AND path!='' GROUP BY path ORDER BY kol DESC, loads LIMIT ".$offset.", ".$limit);

        $count_pages[1] = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND path!='' AND LENGTH(path)-LENGTH(REPLACE(path,' ',''))+1 = 1");
        $count_pages[2] = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND path!='' AND LENGTH(path)-LENGTH(REPLACE(path,' ',''))+1 = 2");
        $count_pages[3] = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND path!='' AND LENGTH(path)-LENGTH(REPLACE(path,' ',''))+1 = 3");
        $count_pages[4] = sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND path!='' AND LENGTH(path)-LENGTH(REPLACE(path,' ',''))+1 > 3");

        $total_head = array(
                '',
                $this->_str('amount'),
        );
		$total[] = array(
                $this->str('total_period'),
    			$total_value,
		);
        for ($i = 1; $i<5; $i++) {
            $total[] = array(
                    sprintf($this->str('see_pages'.$i), ($i < 4 ? "=".$i : ">".($i-1))),
                    $count_pages[$i].' <span class="Tsmall">('.(round($count_pages[$i]/$total_value*100,2)).'%)</span>',
            );
        }

		// Построить массив для всех страниц ($this->path)
		if ($data) {
			$pages_id = array();
			foreach ($data as $row) {
                $pages_id = array_merge($pages_id, explode(' ', trim($row['path'])));
			}
			$pages_id = array_unique($pages_id);
			$this->path_pages = sql_getRows("SELECT id, CONCAT(host, uri) AS page FROM ".STAT_PAGES_TABLE." WHERE id IN (".join(', ', $pages_id).")", true);
			$this->path_keys = array_flip(array_keys($this->path_pages));
			foreach ($this->path_pages as $page_id => $href) {
				$ret['pathes']['row'][] = array(
					'key' => $this->path_keys[$page_id] + 1,
					'href' => $href,
				);
			}
		}
		// Main Table
		$ret['table'] = $this->stat_table(array(
			'columns'	=> array(
				array(
					'header'	=> 'pathes',
					'type'		=> 'path',
				),
				array(
					'header' 	=> 'amount',
					'align'		=> 'right',
					'width'		=> '20%',
				),
				array(
					'header' 	=> 'percent',
					'align'		=> 'right',
					'width'		=> '50%',
					'type'		=> 'graph',
				),
			),
			'data' => $data,
			'total' => $total,
            'total_head' => $total_head,
			'count' => $count,
			'offset' => $offset,
			'limit' => $limit,
		));

		return Parse($ret, 'stat/stat.tmpl');
	}

	######################

	### Формирует пути сайта по данным из path (исплользуя переменную $this->path)
	function table_get_path($val, $row) {
		$pages_id = explode(' ', trim($val));
		foreach ($pages_id as $page) {
			$ret[] = '<a href="http://'.$this->path_pages[$page].'" title="http://'.$this->path_pages[$page].'" target="_blank" class="Tpath">['.($this->path_keys[$page] + 1).']</a>';
		}
        $str = join('<span class="Tpath">&nbsp;&gt; </span>', $ret);
        $str = '<a href="#" onclick="window.open(\'stat.php?page=stat/stat_summary&adv[path]='.$val.'\', \'stat\', \'width=900, height=600, resizable=1, status=1\').focus(); return false;"><img src="images/icons/icon.plus.gif" width=16 heidht=16 border=0 alt="'.$this->str('more').'" align="absmiddle" hspace="3"></a>&nbsp;'.$str;
        return $str;
	}

	######################
}

$GLOBALS['stat__stat_pathes'] = & Registry::get('TPathes');

?>