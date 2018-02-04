<?php
/**
 * @const NO_STRING_FOUND String for empty / not found references to be replaced
 */
if (!defined('NO_STRING_FOUND')) define('NO_STRING_FOUND', 'Reference not found in String list');

/**
 * Import an Array with strings and make it globally available
 * @include strings.array.php 	Strings die im Zorg Code benutzt werden
 */
$GLOBALS['strings'] = include_once( __DIR__ .'/strings.array.php');

/**
 * Get text string
 * This function translated placeholders in the code to a corresponding
 * text string from the list of valid strings. This helps to not have
 * any hard-coded messages / strings as part of the code. And allows
 * same strings to be reused at different points in the whole code.
 * 
 * Features:
 * - singular & plurarl
 * - values können übergeben werden
 * - statt einem string kann ein Template benutzt werden
 *
 * @TODO make it work to output a Smarty-Template...
 *
 * @author IneX
 * @date 04.02.2017
 * @version 1.0
 * @package Zorg
 * @subpackage Strings
 *
 * @param $reference The placeholder reference to be replaced with a string
 * @param $context The context from where to pull and replace the given reference
 * @param $values Optional: any values which shall be replaced within the string
 * @param $tploutput Optional: reference to template instead of a simple string, e.g. 'db:123', 'file:template.tpl'
 * @return string|null The string which replaced the passed and matched placeholder
 */
function t($reference, $context='global', $values=NULL, $tploutput=NULL)
{
	//global $smarty;
	
	if ($found_string = findReferenceInArray($context, $reference))
	{
		try {
			$string = ( !empty($values) && count($values) > 0 ? vsprintf($found_string, $values) : $found_string );
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
	else {
		$string = sprintf('[WARN] %s: %s in %s', NO_STRING_FOUND, $reference, $context);
		error_log($string);
	}

	/*if (!empty($tploutput))
	=> does this need to be part of $found_string?
	{
		// Assign passed values to Smarty. This makes it available as:
		// {$reference-text.value-text}
		foreach ($values as $value)
		{
			$smartyArray[$reference] = [ $value ];
			$smarty->assign($reference, $smartyArray);
		}
		$string = $smarty->fetch($tploutput);
	}*/
	
	return $string;
}

/** 
 * Find & return a given reference in the Strings-Array
 *
 * @param $reference The placeholder reference to be replaced with a string
 * @param $context The context from where to pull and replace the given reference
 * @global $strings Array with all the strings
 */
function findReferenceInArray($context, $reference)
{
	global $strings;
	
	if (is_array($strings))
	{
		if (array_key_exists($context, $strings))
		{
			if (array_key_exists($reference, $strings[$context]))
			{
				$found_string = $strings[$context][$reference];
				if (!empty($found_string) )
				{
					return $found_string;
				} else {
					error_log('[WARN] Reference text is empty or invalid');
					return false;
				}
			} else {
				error_log('[WARN] Reference not found in $strings: ' . $context);
				return false;
			}
		} else {
			error_log('[WARN] Topic not found in $strings: ' . $context);
			return false;
		}
	}  else {
		error_log('[WARN] Strings Array could not be loaded');
		return false;
	}
}

/** 
 * Return single or plural string
 *
 * @param $array The array containing two values: one for singular & one for pluaral
 * @param $value The integer value to check against, whether it's singular or plural
 */
function checkSingleOrPlural($array, $integer)
{
	
}
