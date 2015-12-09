<?php
$GLOBALS['engine_errors'] = array(
	'warning' => array(),
	'notice' => array(),
	'error' => array(),
);
error_reporting(E_ALL);
set_error_handler("Engine_ErrorHandler");
//-----------------------------------------------------------------------------------------------------------

function Engine_ErrorHandler($errno, $errstr, $errfile, $errline){ 
	switch ($errno){
		case E_ERROR: 	log_error	($errstr, $errfile, $errline); break;
		case E_PARSE: 	log_error	($errstr, $errfile, $errline); break;
		case E_WARNING: log_warning	($errstr, $errfile, $errline); break;		
		case E_NOTICE: 	log_notice	($errstr, $errfile, $errline); break;
		default: 		log_notice	($errstr, $errfile, $errline);
	}		
}

//---------------------------------------------------

function backtrace($deep = 2, $element = false){
	$bt = debug_backtrace();
	if (!$element){
		return $bt[$deep];
	} else {
		return $bt[$deep][$element];
	}
}

//---------------------------------------------------

function log_error($str, $file = '', $line = ''){
	if (empty($file) || empty($line)) {
		$file = backtrace(1, 'file');
		$line = backtrace(1, 'line');
	}
	
	$GLOBALS['engine_errors']['error'][] = array(
		'file' => $file,
		'line' => $line,
		'str' => $str,
	);	
}

//---------------------------------------------------

function log_info($str, $file = '', $line = ''){
	if (empty($file) || empty($line)) {
		$file = backtrace(1, 'file');
		$line = backtrace(1, 'line');
	}
	
	$GLOBALS['engine_errors']['info'][] = array(
		'file' => $file,
		'line' => $line,
		'str' => "<font class='debug_info font9'>".$str."</font>",
	);	
}

//---------------------------------------------------

function log_status($msg) {
	if (!is_string($msg)) {
		$msg = preget($msg);
	}
	error_log(strftime('%c')." - $msg\n", 3, 'status.log');
}

//---------------------------------------------------

function log_notice($str, $file = '', $line = ''){
	if (empty($file) || empty($line)) {
		$file = backtrace(1, 'file');
		$line = backtrace(1, 'line');
	}
	
	$GLOBALS['engine_errors']['notice'][] = array(
		'file' => $file,
		'line' => $line,
		'str' => $str,
	);	
}

//---------------------------------------------------

function log_warning($str, $file = '', $line = ''){
	if (empty($file) || empty($line)) {
		$file = backtrace(1, 'file');
		$line = backtrace(1, 'line');
	}
	
	$GLOBALS['engine_errors']['warning'][] = array(
		'file' => $file,
		'line' => $line,
		'str' => $str,
	);		
}
//---------------------------------------------------
?>