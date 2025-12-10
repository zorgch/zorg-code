<?php
/**
 * zorg Localizations
 *
 * @package zorg\Utils\Localization
 */

/**
 * @const NO_STRING_FOUND String for empty / not found references to be replaced
 */
if (!defined('NO_STRING_FOUND')) define('NO_STRING_FOUND', (isset($_ENV['STRING_NOT_FOUND']) ? $_ENV['STRING_NOT_FOUND'] : null));

/**
 * Import an Array with strings and make it globally available
 * @include strings.array.php 	Strings die im Zorg Code benutzt werden
 */
$GLOBALS['strings'] = include_once INCLUDES_DIR.'strings.array.php';

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
 * // TODO make it work to output a Smarty-Template...
 *
 * @version 1.1
 * @since 1.0 `04.02.2017` `IneX` Function added
 * @since 1.1 `13.01.2024` `IneX` Enforce a single $values string to array()
 *
 * @param string $reference The placeholder reference to be replaced with a string
 * @param string $context The context from where to pull and replace the given reference
 * @param array|string $values Optional: any values which shall be replaced within the string
 * @param string $tploutput Optional: reference to template instead of a simple string, e.g. 'db:123', 'file:template.tpl'
 * @return string|null The compiled string from passed and matched values
 */
function t($reference, $context='global', $values=NULL, $tploutput=NULL)
{
	//global $smarty;

	/**
	 * Validate the passed $values
	 */
	$values_count = 0;
	if (isset($values) && is_string($values) && $values !== '')
	{
		$values = array($values); // Force $values to be array()
	} elseif (isset($values) && $values === '') {
		error_log(sprintf('[WARN] <%s:%d> A value for %s was passed but it is empty!', __FILE__, __LINE__, $reference));
	}
	if (isset($values) && is_array($values))
	{
		/** Check if any of the $values is empty */
		foreach ($values as $key=>$value) {
			if (empty($value)) error_log(sprintf('[WARN] <%s:%d> Value %s for string "%s" was passed but is empty!', __FILE__, __LINE__, $key+1, $reference));
		}
		$values_count = count($values);
	}

	/**
	 * Resolve the placeholder reference
	 */
	if ($found_string = findReferenceInArray($context, $reference))
	{
		/** Check if the number of $values matches the sprintf-placeholders */
		$sprintf_count = substr_count($found_string, '%');
		if ($values_count != $sprintf_count) error_log(sprintf('[NOTICE] <%s:%d> Possible mismatch between values (num: %d) & sprintf (num: %d) for string "%s"', __FILE__, __LINE__, $values_count, $sprintf_count, $found_string));

		/**
		 * Replace & return - or return only - a matched string
		 * vsprintf = sprintf with an array for params
		 */
		$string = ( !empty($values) && $values_count > 0 ? vsprintf($found_string, $values) : $found_string );
	}
	else {
		$string = sprintf('[WARN] <%s:%d> %s: %s in %s', __FILE__, __LINE__, NO_STRING_FOUND, $reference, $context);
		error_log($string);
	}

	/*if (!empty($tploutput))
	// FIXME does this need to be part of $found_string?
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

	/** Trim multiple tabs from String */
	$string = preg_replace('/\t{2,}/', '', $string);

	/** Return String */
	return $string;
}

/**
 * Find & return a given reference in the Strings-Array
 *
 * @param string $context The context from where to pull and replace the given reference
 * @param string $reference The placeholder reference to be replaced with a string
 * @var $strings Array with all the strings
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
					error_log(sprintf('[WARN] <%s:%d> Reference text is empty or invalid', __FILE__, __LINE__));
					return false;
				}
			} else {
				error_log(sprintf('[WARN] <%s:%d> Reference not found in $strings: %s', __FILE__, __LINE__, $context));
				return false;
			}
		} else {
			error_log(sprintf('[WARN] <%s:%d> Topic not found in $strings: %s', __FILE__, __LINE__, $context));
			return false;
		}
	}  else {
		error_log(sprintf('[WARN] <%s:%d> Strings Array could not be loaded', __FILE__, __LINE__));
		return false;
	}
}

/**
 * Return single or plural string
 * @TODO Single/Plural Strings-Feature is yet to be implemented...
 * @param $array The array containing two values: one for singular & one for pluaral
 * @param $value The integer value to check against, whether it's singular or plural
 */
function checkSingleOrPlural($array, $integer)
{

}
