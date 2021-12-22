<?php
/** ERROR-HANDLER SETTINGS */
if (!isset($errlog_settings))
{
	$errlog_settings = array(
		'display' => array(
			'fatal' => true,
			'error' => true,
			'warning' => true,
			'unknown' => false),  // schaltet das wenn möglich nicht ein - z.b. auf der startseite gibt das alle 6500 (!) errors aus
		'errlog' => array(
			'fatal' => true,
			'error' => true,
			'warning' => true,
			'unknown' => false)  // schaltet das wenn möglich nicht ein - z.b. auf der startseite speichert das alle 6500 (!) errors im file (das gibt ca. 1MB)
	);
}

if (!defined('FATAL')) define('FATAL', E_USER_ERROR);
if (!defined('ERROR')) define('ERROR', E_USER_WARNING);
if (!defined('WARNING')) define('WARNING', E_USER_NOTICE);

//error_reporting(FATAL | ERROR | WARNING);
//set_error_handler('zorgErrorHandler');

function zorgErrorHandler ($errno, $errstr, $errfile, $errline)
{
	global $errlog_settings;

	switch ($errno) {
		case FATAL:
			$prefix = "FATAL Error";
			$errtype = 'fatal';
		break;
		case ERROR:
			$prefix = "Error";
			$errtype = 'error';
		break;
		case WARNING:
			$prefix = "Warning";
			$errtype = 'warning';
		break;
		default:
			$prefix = "Unkown error type";
			$errtype = 'unknown';
		break;
	}

	$time = date("Y-m-d H:i:s");
	$errstr = str_replace(array("\r", "\n"), '', $errstr); // Remove all line breaks from $errstr
	$str = "[$time] [$prefix<$errno>] $errstr ($errfile : $errline)\n";

	if ($errlog_settings['display'][$errtype]) {
		echo $str . '<br />';
	}
	if ($errlog_settings['errlog'][$errtype]) {
		//$filename = date("Y-m-d") . '.log';
		//error_log($str, 3, ERRORLOG_DIR.$filename);
		error_log($str, 3, ERRORLOG_FILE);
	}

	if ($errno == FATAL) exit(1);
}
