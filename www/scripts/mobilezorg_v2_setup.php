<?php
/**
 * Mobilezorg V2 Migration-Script
 * @package zorg\Scripts
 */

/**
 * FILE INCLUDES
 */
if (!require_once rtrim($_SERVER['DOCUMENT_ROOT'].'/mobilezorg-v2/config.php','/\\')) die('ERROR: Configurations could NOT be loaded!');
if (!require_once rtrim($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php','/\\')) die('ERROR: Configurations could NOT be loaded!');

/**
 * CONSTANTS
 * Most are already defined in the included config.php!
 */
if (!defined('USER_FILES_TESTDIR')) define('USER_FILES_TESTDIR', '000_folder_test_mobilez_setup/');
if (!defined('IMAGE_TESTURL')) define('IMAGE_TESTURL', 'http://maps.googleapis.com/maps/api/staticmap?size=120x120&scale=1&zoom=0');
if (!defined('IMAGE_TESTFILE')) define('IMAGE_TESTFILE', '001_image_test_mobilez_setup.png');
if (!defined('DB_TEXT_COLUMN')) define('DB_TEXT_COLUMN', 'text');


/**
 * Mobile Zorg v2 Setup
 * This Class contains two types of functions:
 *   1) Checker Function: verifies the status of required structures
 *   2) Setup Function: creates the missing but required structure
 * For the second function, it's required to command exactly, what needs to be set up.
 *
 * @author IneX
 * @date 06.02.2016
 * @version 1.0
 * @package Mobilezorg
 * @subpackage Setup
 */
class mobilezSetup
{
	private static function mobilez_setup_htmloutput($string, $severity, $command = '')
	{
		$logLevel = array(1 => 'Info', 2 => 'Warning', 3 => 'Error', 4 => 'OK');
		
		$output  = '';
		$output .= '<tr>';
		$output .= '<td><span class="'.$severity.'">['.$logLevel[$severity].']</span></td>';
		$output .= '<td>'.$string.'</td>';
		$output .= ((!empty($command)) ? '<td><button type="submit" name="setup" value="'.$command.'">do setup</button></td>' : '<td></td>');
		$output .= '</tr>';
		echo $output;
	}
	
	static function mobilez_setup_check()
	{
		global $pdo_db;
		
		/**
		 * Check Folder Structure
		 */
		if (!file_exists(USER_FILES_DIR))
		{
			self::mobilez_setup_htmloutput(USER_FILES_DIR, 2, 'user_files_dir');
		} else {
			self::mobilez_setup_htmloutput(USER_FILES_DIR, 4);
		}
		if (!file_exists(USER_FILES_DIR.USER_FILES_TESTDIR))
		{
			self::mobilez_setup_htmloutput(USER_FILES_TESTDIR, 2, 'user_files_testdir');
		} else {
			self::mobilez_setup_htmloutput(USER_FILES_TESTDIR, 4);
		}
		if (!file_exists(USER_FILES_DIR.USER_FILES_TESTDIR.IMAGE_TESTFILE))
		{
			self::mobilez_setup_htmloutput(IMAGE_TESTFILE, 2, 'image_test');
		} else {
			if (!chmod(USER_FILES_DIR.USER_FILES_TESTDIR.IMAGE_TESTFILE, 0664))
			{
				self::mobilez_setup_htmloutput(IMAGE_TESTFILE.' chmod 0664', 2, 'image_test');
			} else {
				self::mobilez_setup_htmloutput(IMAGE_TESTFILE.' chmod 0664', 4);
			}
		}
				
		/**
		 * Check Database Structure
		 */
		try {
			$statement = sprintf('SHOW TABLES LIKE "%s"', DB_CHAT_TABLE);
			$query = $pdo_db->query($statement);
			$row = $query->fetchColumn(); // Returns rows or "false"
			if (!$row) {
				self::mobilez_setup_htmloutput($statement, 2, 'create_db_table');
			} else {
				self::mobilez_setup_htmloutput($statement, 4);
			}
		} catch(PDOException $err) {
			self::mobilez_setup_htmloutput($err->getMessage(), 3, 'create_db_table');
		}
		
		try {
			$statement = sprintf('SHOW TABLE STATUS WHERE NAME LIKE "%s"', DB_CHAT_TABLE);
			$query = $pdo_db->query($statement);
			$rows = $query->fetchAll(); // Returns rows or "false"
			foreach ($rows as $row)
			{
				if (!empty($row['Collation'])) {
					self::mobilez_setup_htmloutput($row['Collation'], 4, 'change_db_table_encoding');
				} else {
					self::mobilez_setup_htmloutput($statement, 2);
				}
			}
		} catch(PDOException $err) {
			self::mobilez_setup_htmloutput($err->getMessage(), 3, 'change_db_table_encoding');
		}
		
		try {
			$statement = sprintf('SHOW FIELDS FROM %s where Field ="%s"', DB_CHAT_TABLE, DB_TEXT_COLUMN);
			$query = $pdo_db->query($statement);
			$rows = $query->fetchAll(); // Returns rows or "false"
			foreach ($rows as $row)
			{
				if (!empty($row['Type'])) {
					self::mobilez_setup_htmloutput($row['Type'], 4, 'change_db_table_column_text');
				} else {
					self::mobilez_setup_htmloutput($statement, 2);
				}
			}
		} catch(PDOException $err) {
			self::mobilez_setup_htmloutput($err->getMessage(), 3, 'change_db_table_column_text');
		}
		
		
	}
	
	static function mobilez_setup_create($setup)
	{
		global $pdo_db, $user;
		
		switch ($setup)
		{
			case 'user_files_testdir':
				if (!$user->get_and_create_user_files_dir(USER_FILES_TESTDIR))
				{
					self::mobilez_setup_htmloutput('Setup: '.USER_FILES_TESTDIR, 3);
				} else {
					self::mobilez_setup_htmloutput('Setup: '.USER_FILES_TESTDIR, 4);
				}
				break;
				
			case 'user_files_dir':
				if (!file_exists(USER_FILES_DIR))
				{
					if (!mkdir(USER_FILES_DIR, 0775))
					{
						self::mobilez_setup_htmloutput('Setup: '.USER_FILES_DIR, 3);
					} else {
						self::mobilez_setup_htmloutput('Setup: '.USER_FILES_DIR, 4);
					}
				}
				break;
			
			case 'image_test':
				if(@copy(IMAGE_TESTURL, USER_FILES_DIR.USER_FILES_TESTDIR.IMAGE_TESTFILE))
				{
					self::mobilez_setup_htmloutput(IMAGE_TESTFILE, 4);
					if (!chmod(USER_FILES_DIR.USER_FILES_TESTDIR.IMAGE_TESTFILE, 0664))
					{
						self::mobilez_setup_htmloutput('Setup: '.IMAGE_TESTFILE.' chmod 0664', 2);
					} else {
						self::mobilez_setup_htmloutput('Setup: '.IMAGE_TESTFILE.' chmod 0664', 4);
					}
				} else {
					self::mobilez_setup_htmloutput('Setup: '.IMAGE_TESTFILE, 3);
				}
				break;
			
			case 'create_db_table':
				self::mobilez_setup_htmloutput('Sorry, DB tables have to be created manually!', 1);
				break;
			
			case 'change_db_table_encoding':
				try {
					$statement = sprintf('ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_CHAT_TABLE);
					$query = $pdo_db->prepare($statement);
					$query->execute(); // Returns rows or "false"
					if (!$query) {
						self::mobilez_setup_htmloutput('Setup: '.$statement, 3);
					} else {
						self::mobilez_setup_htmloutput('Setup: '.$statement, 4);
					}
				} catch(PDOException $err) {
					self::mobilez_setup_htmloutput($err->getMessage(), 3);
				}
				break;
			
			case 'change_db_table_column_text':
				try {
					$statement = sprintf('ALTER TABLE %1$s MODIFY COLUMN %2$s TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_CHAT_TABLE, DB_TEXT_COLUMN);
					$query = $pdo_db->prepare($statement);
					$query->execute(); // Returns rows or "false"
					if (!$query) {
						self::mobilez_setup_htmloutput('Setup: '.$statement, 3);
					} else {
						self::mobilez_setup_htmloutput('Setup: '.$statement, 4);
					}
				} catch(PDOException $err) {
					self::mobilez_setup_htmloutput($err->getMessage(), 3);
				}
				break;
			
			default:
				return false;
		}
	}
}

echo '<!doctype html><html><head><title>Mobile Zorg v2 Setup</title><style>body{font-family:Helvetica,Arial,sans-serif;}a{text-decoration:none;color:navy;}.\31{color:navy;}.\32{color:darkorange;}.\33{color:red;}.\34{color:green;}tr{text-align:left;}td{padding:5px;}</style></head><body>';

if($_GET['pw'] == 'schmelzigel') {
	echo '<form method="post" action="'.$_SERVER['REQUEST_URI'].'"><h1>Mobile Zorg v2 Setup</h1><table><tr><th>Status</th><th>Result</th><th>Action</th></tr>'; // Little HTML-Beautifier
	
	$mobilezSetup = new mobilezSetup();
	if (!empty($_POST['setup'])) $mobilezSetup->mobilez_setup_create($_POST['setup']);
	$mobilezSetup->mobilez_setup_check();
	
	echo '</table></form>'; // Close HTML
} else {
	echo '<code>tell me the magic word</code>';
}
echo '</body></html>'; // Close HTML
