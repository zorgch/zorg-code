<?
	/**
	 * returns a user-file
	 *
	 * URL-Parameter:
	 * - id:		file-id from db
	 * or 
	 * - user:	user-id from db
	 * - file: 	filename
	 */

	 	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');

	if ($_GET['user'] && $_GET['file']) {
		if (is_numeric($_GET['user'])) {
			$e = $db->query("SELECT * FROM files WHERE user='$_GET[user]' AND name='".addslashes($_GET[file])."'", __FILE__, __LINE__);
			$d = $db->fetch($e);
		}
	}else{
		if (is_numeric($_GET['id'])) {
			$e = $db->query("SELECT * FROM files WHERE id='$_GET[id]'", __FILE__, __LINE__);
			$d = $db->fetch($e);
		}
	}
	
	if ($d) {
		$lastmod = filemtime($_SERVER['DOCUMENT_ROOT']."/../data/files/$d[user]/$d[name]"); 
	  
	   header("Content-Type: $d[mime]");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastmod) ." GMT"); 
		
	   	    
	   /*
	   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	  
	   header("Pragma: no-cache"); 
	   header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate"); 
	   */
	   	      
	   readfile($_SERVER['DOCUMENT_ROOT']."/../data/files/$d[user]/$d[name]");
	}else{
		echo "File not found.";
	}
?>