<?php
//-----------------------------------------------------------------------------------------------------------

function is_devel() {
	if (defined('DEV_MODE')) {
		return DEV_MODE;
	}
	return false;
}

//-----------------------------------------------------------------------------------------------------------
//таймеры
//-----------------------------------------------------------------------------------------------------------

function getmicrotime() {
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

//-----------------------------------------------------------------------------------------------------------

function timer_start($name) {
	$GLOBALS['timers'][md5($name)] = array(
		'name' => $name,
		'start_time' => getmicrotime(),
	);
}

//-----------------------------------------------------------------------------------------------------------

function timer_end($name) {
	$mt = getmicrotime();
	$md5 = md5($name);
	if (!isset($GLOBALS['timers'][$md5]['start_time'])) {
		return;
	}
	$GLOBALS['timers'][$md5] = array(
		'name' => $name,
		'time' => ($mt - $GLOBALS['timers'][$md5]['start_time']),
	);
}


//-----------------------------------------------------------------------------------------------------------

function pr($var, $name = '') {
	if (!is_devel()) return;
	watch($var, $name = '');
}
//-----------------------------------------------------------------------------------------------------------

function watch($var, $name = '') {
	echo '<pre>';
	$type = gettype($var);

	$backtrace = debug_backtrace();
	$caller = array_shift($backtrace);
	if ($backtrace[1]['function'] == 'pred') {
		$caller = array_shift($backtrace);
	}
	$caller = array_shift($backtrace);
	echo $caller['file'].', '.$caller['line']."\n";

	echo (is_numeric($name) || $name ? $name : '').'('.$type.') = ';
	if ($type == 'boolean') {
		echo ($var === true) ? 'true' : 'false';
	}
	elseif (empty($var)) {
		$var = ($type == 'NULL') ? 'NULL' : $var;
		$var = (in_array($type, array('string', 'array', 'object', 'resource'))) ? 'empty!' : $var;
		echo $var;
	}
	else {
		($type == 'object') ? var_dump($var) : print_r($var);
	}
	echo '</pre>';
}
//-----------------------------------------------------------------------------------------------------------

function showDebugInfo(){
	if (!is_devel()) return;
	require_once(common_class('sql_highlighter'));

	global $engine_errors, $sql_queries, $sql_errors, $timers;

	$high = new TSqlHighlighter();
	$time_sum = 0;
	if (!empty($sql_queries)){
		foreach ($sql_queries as $k=>$v){
			$time_sum += $v['time'];
			$sql_queries[$k]['sql'] = $high->highlight($v['sql']);
		}
	}
	if (!empty($sql_errors)){
		foreach ($sql_errors as $k=>$v){
			$sql_errors[$k]['sql'] = $high->highlight($v['sql']);
		}
	}

	 $tpl = & Registry::get('TRusoft_View');
	 $tpl->template_dir = find_dir("../templates");

	 $ret = array(
 		'timers' 		=> $timers,
 		'sql'			=> array(
 			'count'=> count($sql_queries),
 			'time' => $time_sum,
 		),
 		'engine_errors' => $engine_errors,
 		'sql_queries' 	=> $sql_queries,
 		'sql_errors' 	=> $sql_errors,
 	 );
 	 $tpl->assign($ret);
	 return $tpl->render($tpl->template_dir.'debug.tpl');
}

//-----------------------------------------------------------------------------------------------------------
?>