<?php
/**
 * Database Connection Class
 *
 * @author IneX
 * @version 1.0
 * @package zorg\Mobilezorg
 *
 * @ToDo [12-Jan-2016 23:59:29 Europe/Berlin] PHP Fatal error:  Cannot redeclare class dbconn in /Users/oraduner/Sites/zooomclan/www/includes/mysql.inc.php on line 16
 */

// class dbconn
// {
// 	/**
// 	* Database Connection
// 	*
// 	* @author IneX
// 	* @version 1.0
// 	* @since 1.0
//  	*/
// 	function dbconn()
// 	{
// 		try {
// 			$db = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DBNAME.';charset='.MYSQL_CHARSET, MYSQL_DBUSER, MYSQL_DBPASS);
// 			$db->exec("set names utf8");
// 			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
//
// 			return $db;
// 		} catch (PDOException $e) {
// 		    die("Error: " . $e->getMessage() . "<br/>");
// 		}
// 	}
//
// }
//
// // Instantiate new DB Connection Object
// $db = new dbconn();

try {
	$PDO_OPTIONS = array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    $PDO_DSN = 'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'] . ';charset=' . MYSQL_CHARSET;
    $pdo_db = new PDO($PDO_DSN, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $PDO_OPTIONS);
    $pdo_db->exec('set names ' . MYSQL_CHARSET); // Execute an SQL statement and return the number of affected rows
} catch (PDOException $err) {
	Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, $_ENV['MYSQL_DATABASE'], 'PDO');
}
