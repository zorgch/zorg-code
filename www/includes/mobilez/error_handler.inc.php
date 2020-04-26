<?php
/**
 * DEFINE GLOBALS
 */
$errors = array();	// Initialize empty $errors Array

/**
 * Error Handler
 *
 * @ToDo Use debug_backtrage() http://us3.php.net/manual/en/function.debug-backtrace.php 
 *
 * @author IneX
 * @date 16.01.2016
 * @version 1.0
 * @package Mobilezorg
 * @subpackage Error
 */
class Error_Handler
{
	/**
	 * Error Collector
	 * Adds an error and all it's details to the global {$errors} array
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string {$text} is the error text
	 * @param string Optional: {$file} is the file where the error occured
	 * @param integer Optional: {$line} is the line in file where the error occured
	 * @param string Optional: {$function} is the function in which the error occured
	 * @param string Optional: {$class} is the class to which the errorous function belongs to
	 * @global array $errors Global Array to collect {$errors}
	 * @return array
 	 */
	static function addError($text, $file = false, $line = false, $function = false, $class = false)
	{
		global $errors;
		
		array_push($errors, array(
									 'message'	=> $text
									,'file'		=>  $file
									,'line'		=> $line
									,'function' => $function
									,'class'	=> $class
								));
		error_log(sprintf('[%s] <%s:%d> %s', $text, $function, $line, $file));
		return $errors;
	}
}
