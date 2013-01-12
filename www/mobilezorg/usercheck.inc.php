<?php
/**
* User Check
* 
* Zeigt die Loginseite, falls der User nicht eingeloggt ist
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage functions
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
/**
 * Globals
 */
global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) header('Location: login.php');

?>