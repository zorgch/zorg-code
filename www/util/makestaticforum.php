<?php
/**
 * Save static Forum HTML-File
 * @package zorg\Forum
 */
/**
 * File includes
 */
include_once(__DIR__.'/../includes/usersystem.inc.php');

if ($user->is_loggedin() && !empty(USER_SPECIAL) && $user->typ >= USER_SPECIAL)
{
	/** get contents of a file into a string */
	$somecontent = @file_get_contents(SITE_URL . '/forum.php');
	$filename = __DIR__.'/../forum.html';
	
	/** Let's make sure the file exists and is writable first. */
	if (is_writable($filename))
	{
	   // In our example we're opening $filename in append mode.
	   // The file pointer is at the bottom of the file hence
	   // that's where $somecontent will go when we fwrite() it.
	   if (!$handle = fopen($filename, 'w')) {
	         echo "Cannot open file ($filename)";
	         exit;
	   }
	
	   /** Write $somecontent to our opened file. */
	   if (fwrite($handle, $somecontent) === FALSE)
	   {
	       echo "Cannot write to file ($filename)";
	       exit;
	   }
	  
	   echo "Success, wrote to file ($filename)";
	  
	   fclose($handle);
	
	} else {
	   echo "The file $filename is not writable";
	}
} else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	echo 'Peremission denied!';
}
