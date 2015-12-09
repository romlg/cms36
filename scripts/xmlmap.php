<?php
require ('../admin/connect.php');
class TMap {
	function showMap()
	{
		$res = mysql_query('SELECT * FROM map_areas');
		$areas = array();
		while ($row = mysql_fetch_assoc($res))
		{
			$areas[] = $row;
		}

		$res = mysql_query('SELECT * FROM map_cities ORDER BY priority');
		$cities = array();
		while($row = mysql_fetch_assoc($res)){
			$cities[$row['id']] = $row;
		}

		$res = mysql_query('SELECT * FROM map_regions');
		$regions = array();
		while($row = mysql_fetch_assoc($res)){
			$regions[$row['area_id']][] = $row;
		}

		$dealers = array();
		foreach ($regions as $key=>$val) {
			foreach ($val as $k=>$v) {
				$dealers[$v['id']] = isset($cities[$v['dealer']]) ? $cities[$v['dealer']] : null;
			}
		}

		$output = $this->header($cities);

		foreach($areas as $area){
			if (substr($area['color'], 0, 1) == '#') {
				$area['color'] = substr($area['color'], 1);
			}
			$output .= '<AREA name="'.iconv("WINDOWS-1251","UTF-8",htmlspecialchars($area['title'])).'" color="'.$area['color'].'">';
			if (isset($regions[$area['id']])) foreach ($regions[$area['id']] as $region) {
				$output .= '<OBL id="'.$region['num'].'" name="'.iconv("WINDOWS-1251","UTF-8",htmlspecialchars($region['name'])).'" info="'.(isset($dealers[$region['id']]) ? iconv("WINDOWS-1251","UTF-8",htmlspecialchars($dealers[$region['id']]['text'])) : '').'" />';
			}
			$output .= '</AREA>';
		}

		$output .= $this->footer();

		header('Content-type: text/xml');
		echo  $output;
		exit();
	}

	function header($cities) {
		$global = mysql_fetch_assoc(mysql_query('SELECT value FROM strings WHERE module="site" AND name="regions_map_global" AND lang="ru"'));
		if (!$global) $global = 'color="CCCCCC" smap="1" zoom="3" smap_scale="50" smap_x="250" smap_y="140" bord_color="CCCCCC" bg_color="CCCCCC" f_color="FFFFFF"';
		else $global = $global['value'];
		$str = '<?xml version="1.0" encoding="UTF-8" ?><global><global '.$global.' />';
		foreach($cities as $city){
			if ($city['visible'] > 0)
				$str .= '<CITY x="'.$city['x'].'" y="'.$city['y'].'" name="'.iconv("WINDOWS-1251","UTF-8",$city['name']).'" '.($city['dealer'] ? 'type="dealer"' : '').'/>';
		}
		$str .= '</global>';
		return $str;
	}

	function footer() {
		return '';
	}
}
ini_set('display_errors', 0);
$m = new TMap();
$m->showMap();
?>